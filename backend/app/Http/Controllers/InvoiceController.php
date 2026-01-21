<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Account;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Get all invoices with optional filters.
     */
    public function index(Request $request)
    {
        $query = Invoice::where('user_id', auth()->id());

        // Filter by customer
        if ($request->has('customer_name') && $request->customer_name) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $invoices = $query->with('items')->latest('invoice_date')->paginate(15);

        return response()->json([
            'data' => $invoices->items(),
            'meta' => [
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
            ],
        ]);
    }

    /**
     * Get aging report.
     */
    public function agingReport()
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->where('status', '!=', 'paid')
            ->with('items')
            ->get();

        $summary = [
            'total' => 0,
            'overdue' => 0,
            '0-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            '90+' => 0,
        ];

        foreach ($invoices as $invoice) {
            $balance = $invoice->balance;
            $summary['total'] += $balance;

            $bucket = $invoice->getAgingBucket();
            if ($bucket === '0-30') {
                $summary['0-30'] += $balance;
            } elseif ($bucket === '31-60') {
                $summary['31-60'] += $balance;
            } elseif ($bucket === '61-90') {
                $summary['61-90'] += $balance;
            } elseif ($bucket === '90+') {
                $summary['90+'] += $balance;
            } elseif ($invoice->isOverdue()) {
                $summary['overdue'] += $balance;
            }
        }

        return response()->json(['data' => $summary]);
    }

    /**
     * Create a new invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|unique:invoices',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.account_id' => 'required|exists:accounts,id',
        ]);

        try {
            DB::beginTransaction();

            // If customer_id is provided, get customer details
            if ($validated['customer_id']) {
                $customer = \App\Models\Customer::find($validated['customer_id']);
                if ($customer) {
                    $validated['customer_name'] = $customer->name;
                    $validated['customer_email'] = $validated['customer_email'] ?? $customer->email;
                    $validated['customer_phone'] = $validated['customer_phone'] ?? $customer->phone;
                }
            }

            // Calculate total
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }

            // Create invoice
            $invoice = Invoice::create([
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'invoice_number' => $validated['invoice_number'],
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'total_amount' => $total,
                'paid_amount' => 0,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create invoice items
            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['quantity'] * $item['unit_price'],
                    'account_id' => $item['account_id'],
                ]);
            }

            DB::commit();

            return response()->json([
                'data' => $invoice->load('items'),
                'message' => 'Invoice created successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get a single invoice.
     */
    public function show(Invoice $invoice)
    {
        // Verify ownership
        if ($invoice->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json(['data' => $invoice->load('items')]);
    }

    /**
     * Update an invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Verify ownership
        if ($invoice->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.account_id' => 'required|exists:accounts,id',
        ]);

        try {
            DB::beginTransaction();

            // If customer_id is provided, get customer details
            if ($validated['customer_id']) {
                $customer = \App\Models\Customer::find($validated['customer_id']);
                if ($customer) {
                    $validated['customer_name'] = $customer->name;
                    $validated['customer_email'] = $validated['customer_email'] ?? $customer->email;
                    $validated['customer_phone'] = $validated['customer_phone'] ?? $customer->phone;
                }
            }

            // Calculate new total
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }

            // Update invoice (only if in draft status)
            if ($invoice->status === 'draft') {
                $invoice->update([
                    'customer_id' => $validated['customer_id'] ?? null,
                    'customer_name' => $validated['customer_name'] ?? null,
                    'customer_email' => $validated['customer_email'] ?? null,
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'invoice_date' => $validated['invoice_date'],
                    'due_date' => $validated['due_date'],
                    'total_amount' => $total,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Delete old items and create new ones
                $invoice->items()->delete();
                foreach ($validated['items'] as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['quantity'] * $item['unit_price'],
                        'account_id' => $item['account_id'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'data' => $invoice->load('items'),
                'message' => 'Invoice updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Invoice $invoice)
    {
        // Verify ownership
        if ($invoice->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($invoice->status !== 'draft') {
            return response()->json(['error' => 'Can only delete draft invoices'], 400);
        }

        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    /**
     * Send invoice to customer.
     */
    public function send(Invoice $invoice)
    {
        // Verify ownership
        if ($invoice->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($invoice->status !== 'draft') {
            return response()->json(['error' => 'Invoice already sent'], 400);
        }

        $invoice->update(['status' => 'sent']);

        // TODO: Send email to customer

        return response()->json([
            'data' => $invoice,
            'message' => 'Invoice sent successfully',
        ]);
    }

    /**
     * Create transaction from invoice (when invoice is finalized).
     */
    public function finalize(Invoice $invoice)
    {
        // Verify ownership
        if ($invoice->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Check if transaction already exists
            if ($invoice->related_transaction_id) {
                return response()->json(['error' => 'Invoice already finalized'], 400);
            }

            // Find AR account (accounts receivable)
            $arAccountId = Setting::get('ar_account_id');
            $arAccount = null;

            if ($arAccountId) {
                $arAccount = Account::find($arAccountId);
            }

            // Fallback to searching by code or name
            if (!$arAccount) {
                $arAccount = Account::where('code', 'AR')->first();
            }

            if (!$arAccount) {
                $arAccount = Account::where('account_type', 'asset')
                    ->where('name', 'like', '%Receivable%')
                    ->first();
            }

            if (!$arAccount) {
                throw new \Exception('Accounts Receivable account not found. Please create an AR account first.');
            }

            // Create transaction (Journal Entry)
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'reference' => 'INV-' . $invoice->invoice_number,
                'description' => 'Invoice: ' . $invoice->invoice_number,
                'transaction_date' => $invoice->invoice_date,
                'type' => 'journal',
                'status' => 'approved',
                'amount' => $invoice->total_amount,
                'notes' => 'Generated from invoice',
            ]);

            // Debit Accounts Receivable
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'account_id' => $arAccount->id,
                'type' => 'debit',
                'amount' => $invoice->total_amount,
            ]);

            // Credit Revenue accounts (one per invoice item)
            foreach ($invoice->items as $item) {
                if ($item->account_id) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'account_id' => $item->account_id,
                        'type' => 'credit',
                        'amount' => $item->amount,
                    ]);
                }
            }

            // Link transaction to invoice
            $invoice->update([
                'related_transaction_id' => $transaction->id,
                'status' => 'unpaid',
            ]);

            DB::commit();

            return response()->json([
                'data' => $invoice,
                'message' => 'Invoice finalized successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Record payment for invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        // Verify ownership
        if ($invoice->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $amount = $validated['amount'];

            // Check if payment exceeds balance
            if ($amount > $invoice->balance) {
                return response()->json(['error' => 'Payment amount exceeds invoice balance'], 400);
            }

            // Update invoice paid amount
            $newPaidAmount = $invoice->paid_amount + $amount;
            $invoice->update(['paid_amount' => $newPaidAmount]);

            // Determine new status
            if ($newPaidAmount >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
            } elseif ($newPaidAmount > 0) {
                $invoice->update(['status' => 'partially_paid']);
            }

            // Create payment transaction
            $paymentTransaction = Transaction::create([
                'user_id' => auth()->id(),
                'reference' => 'PAYMENT-' . $invoice->invoice_number,
                'description' => 'Payment for invoice: ' . $invoice->invoice_number,
                'transaction_date' => $validated['payment_date'],
                'type' => 'cash_receipt',
                'status' => 'approved',
                'amount' => $amount,
                'notes' => 'Payment method: ' . $validated['payment_method'],
            ]);

            // Find AR account
            $arAccount = Account::where('code', 'AR')->first();
            if (!$arAccount) {
                $arAccount = Account::where('account_type', 'asset')
                    ->where('name', 'like', '%Receivable%')
                    ->first();
            }

            // Find Cash account based on payment method
            $cashAccountCode = match ($validated['payment_method']) {
                'check' => 'CHK',
                'bank_transfer' => 'BANK',
                'credit_card' => 'CC',
                default => 'CASH',
            };

            $cashAccount = Account::where('code', $cashAccountCode)->first();

            if (!$cashAccount) {
                $cashAccount = Account::where('account_type', 'asset')
                    ->where('name', 'like', '%Cash%')
                    ->first();
            }

            // Debit Cash
            TransactionItem::create([
                'transaction_id' => $paymentTransaction->id,
                'account_id' => $cashAccount->id,
                'type' => 'debit',
                'amount' => $amount,
            ]);

            // Credit Accounts Receivable
            TransactionItem::create([
                'transaction_id' => $paymentTransaction->id,
                'account_id' => $arAccount->id,
                'type' => 'credit',
                'amount' => $amount,
            ]);

            DB::commit();

            return response()->json([
                'data' => $invoice,
                'message' => 'Payment recorded successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
