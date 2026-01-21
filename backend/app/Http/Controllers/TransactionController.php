<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    /**
     * Get all transactions with server-side pagination, filtering, and sorting.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::query();

        // Search by reference or description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        // Filter by account (filter by accounts in transaction items)
        if ($request->has('account_id') && $request->account_id) {
            $query->whereHas('items', function ($subQuery) use ($request) {
                $subQuery->where('account_id', $request->account_id);
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'transaction_date');
        $sortOrder = $request->input('sort_order', 'desc');
        
        if (in_array($sortBy, ['reference', 'type', 'status', 'amount', 'transaction_date', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('transaction_date', 'desc');
        }

        // Pagination
        $perPage = min($request->input('per_page', 20), 100);
        $page = max($request->input('page', 1), 1);
        
        $total = $query->count();
        
        $paginated = $query
            ->with(['user', 'items.account'])
            ->paginate($perPage, ['*'], 'page', $page);

        // Calculate debit/credit totals for each transaction
        $transactions = $paginated->getCollection()->map(function ($transaction) {
            $transaction->debit_total = $transaction->items->where('type', 'debit')->sum('amount');
            $transaction->credit_total = $transaction->items->where('type', 'credit')->sum('amount');
            return $transaction;
        });

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ],
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request): JsonResponse
    {
        // Parse items if it comes as JSON string from FormData
        $items = $request->input('items');
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }
        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'reference' => 'required|string|unique:transactions|max:100',
            'description' => 'nullable|string|max:1000',
            'payee_description' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
            'type' => 'required|in:receipt,payment,journal,transfer,cash_receipt,gcash,bank_transfer,check,check_disbursement,credit_card,debit_card',
            'status' => 'required|in:draft,pending,approved,rejected',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'check_number' => 'nullable|string|max:100',
            'check_date' => 'nullable|date',
            'bank' => 'nullable|string|max:100',
            'billing_number' => 'nullable|string|max:100',
            'collection_receipt' => 'nullable|string|max:100',
            'delivery_receipt' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'attached_file' => 'nullable|file|max:10240',
            'items' => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.type' => 'required|in:debit,credit',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
            'items.*.department_id' => 'nullable|exists:departments,id',
            'items.*.project_id' => 'nullable|exists:projects,id',
            'items.*.subsidiary_account_id' => 'nullable|exists:subsidiary_accounts,id',
        ]);

        // Verify double-entry: total debits = total credits
        $debits = collect($validated['items'])
            ->where('type', 'debit')
            ->sum('amount');
        $credits = collect($validated['items'])
            ->where('type', 'credit')
            ->sum('amount');

        if (abs($debits - $credits) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Debits must equal credits. Currently: Debits ' . $debits . ', Credits ' . $credits,
            ], 422);
        }

        try {
            // Handle file uploads
            $imagePath = null;
            $attachedFilePath = null;
            
            if ($request->hasFile('receipt_image')) {
                $file = $request->file('receipt_image');
                $imagePath = $file->store('receipts', 'public');
            }
            
            if ($request->hasFile('attached_file')) {
                $file = $request->file('attached_file');
                $attachedFilePath = $file->store('attachments', 'public');
            }

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'reference' => $validated['reference'],
                'description' => $validated['description'] ?? null,
                'payee_description' => $validated['payee_description'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'type' => $validated['type'],
                'status' => $validated['status'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
                'check_number' => $validated['check_number'] ?? null,
                'check_date' => $validated['check_date'] ?? null,
                'bank' => $validated['bank'] ?? null,
                'billing_number' => $validated['billing_number'] ?? null,
                'collection_receipt' => $validated['collection_receipt'] ?? null,
                'delivery_receipt' => $validated['delivery_receipt'] ?? null,
                'receipt_image' => $imagePath,
                'attached_file' => $attachedFilePath,
            ]);

            // Create line items
            foreach ($validated['items'] as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $item['account_id'],
                    'type' => $item['type'],
                    'amount' => $item['amount'],
                    'description' => $item['description'] ?? null,
                    'department_id' => $item['department_id'] ?? null,
                    'project_id' => $item['project_id'] ?? null,
                    'subsidiary_account_id' => $item['subsidiary_account_id'] ?? null,
                ]);
            }

            $transaction->load('user', 'items.account');

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating transaction: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single transaction with details.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['user', 'items.account']);

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Update a transaction.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        // Only allow updating draft transactions
        if ($transaction->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft transactions can be edited',
            ], 403);
        }

        // Parse items if it comes as JSON string from FormData
        $items = $request->input('items');
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }
        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'reference' => 'sometimes|string|unique:transactions,reference,' . $transaction->id . '|max:100',
            'description' => 'nullable|string|max:1000',
            'payee_description' => 'nullable|string|max:1000',
            'transaction_date' => 'sometimes|date',
            'type' => 'sometimes|in:receipt,payment,journal,transfer,cash_receipt,gcash,bank_transfer,check,check_disbursement,credit_card,debit_card',
            'status' => 'sometimes|in:draft,pending,approved,rejected',
            'amount' => 'sometimes|numeric|min:0.01',
            'notes' => 'nullable|string',
            'check_number' => 'nullable|string|max:100',
            'check_date' => 'nullable|date',
            'bank' => 'nullable|string|max:100',
            'billing_number' => 'nullable|string|max:100',
            'collection_receipt' => 'nullable|string|max:100',
            'delivery_receipt' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'attached_file' => 'nullable|file|max:10240',
            'items' => 'sometimes|array|min:1',
            'items.*.account_id' => 'required_with:items|exists:accounts,id',
            'items.*.type' => 'required_with:items|in:debit,credit',
            'items.*.amount' => 'required_with:items|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
            'items.*.department_id' => 'nullable|exists:departments,id',
            'items.*.project_id' => 'nullable|exists:projects,id',
            'items.*.subsidiary_account_id' => 'nullable|exists:subsidiary_accounts,id',
        ]);

        // Verify double-entry if items are provided
        if (isset($validated['items'])) {
            $debits = collect($validated['items'])
                ->where('type', 'debit')
                ->sum('amount');
            $credits = collect($validated['items'])
                ->where('type', 'credit')
                ->sum('amount');

            if (abs($debits - $credits) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debits must equal credits',
                ], 422);
            }

            // Delete old items and create new ones
            $transaction->items()->delete();
            foreach ($validated['items'] as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $item['account_id'],
                    'type' => $item['type'],
                    'amount' => $item['amount'],
                    'description' => $item['description'] ?? null,
                    'department_id' => $item['department_id'] ?? null,
                    'project_id' => $item['project_id'] ?? null,
                    'subsidiary_account_id' => $item['subsidiary_account_id'] ?? null,
                ]);
            }
            unset($validated['items']);
        }

        // Handle file upload for update
        if ($request->hasFile('receipt_image')) {
            // Delete old image if exists
            if ($transaction->receipt_image && \Storage::disk('public')->exists($transaction->receipt_image)) {
                \Storage::disk('public')->delete($transaction->receipt_image);
            }
            $file = $request->file('receipt_image');
            $validated['receipt_image'] = $file->store('receipts', 'public');
        }
        
        if ($request->hasFile('attached_file')) {
            // Delete old file if exists
            if ($transaction->attached_file && \Storage::disk('public')->exists($transaction->attached_file)) {
                \Storage::disk('public')->delete($transaction->attached_file);
            }
            $file = $request->file('attached_file');
            $validated['attached_file'] = $file->store('attachments', 'public');
        }

        $transaction->update($validated);
        $transaction->load('user', 'items.account');

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => $transaction,
        ]);
    }

    /**
     * Delete a transaction.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        try {
            // Only allow deleting draft or rejected transactions
            if (!in_array($transaction->status, ['draft', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected transactions can be deleted',
                ], 403);
            }

            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting transaction: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a transaction (change status to approved).
     */
    public function approve(Transaction $transaction): JsonResponse
    {
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be approved',
            ], 403);
        }

        $transaction->update(['status' => 'approved']);
        $transaction->load('user', 'items.account');

        return response()->json([
            'success' => true,
            'message' => 'Transaction approved',
            'data' => $transaction,
        ]);
    }

    /**
     * Reject a transaction.
     */
    public function reject(Transaction $transaction, Request $request): JsonResponse
    {
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be rejected',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction->update([
            'status' => 'rejected',
            'notes' => ($transaction->notes ?? '') . "\n\nRejection reason: " . $validated['reason'],
        ]);

        $transaction->load('user', 'items.account');

        return response()->json([
            'success' => true,
            'message' => 'Transaction rejected',
            'data' => $transaction,
        ]);
    }
}
