<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users and accounts
        $users = User::all();
        if ($users->isEmpty()) {
            return; // Skip if no users
        }

        $user = $users->first();

        // Get account codes for transaction entries
        $bankAccount = Account::where('code', '10100')->first() ?? Account::where('account_type', 'asset')->first();
        $incomeAccount = Account::where('code', '40100')->first() ?? Account::where('account_type', 'income')->first();
        $expenseAccount = Account::where('code', '60100')->first() ?? Account::where('account_type', 'expense')->first();
        $arAccount = Account::where('code', '10200')->first() ?? Account::where('account_type', 'asset')->first();
        $apAccount = Account::where('code', '20100')->first() ?? Account::where('account_type', 'liability')->first();

        if (!$bankAccount || !$incomeAccount || !$expenseAccount) {
            return; // Skip if required accounts don't exist
        }

        // Sample Transaction 1: Income Receipt
        $txn1 = Transaction::create([
            'user_id' => $user->id,
            'reference' => 'REC-2025-001',
            'description' => 'Sales Invoice #INV-001',
            'transaction_date' => Carbon::now()->subDays(30),
            'type' => 'receipt',
            'status' => 'approved',
            'amount' => 5000.00,
            'notes' => 'Payment received from ABC Corp for services',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn1->id,
            'account_id' => $bankAccount->id,
            'type' => 'debit',
            'amount' => 5000.00,
            'description' => 'Bank deposit',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn1->id,
            'account_id' => $incomeAccount->id,
            'type' => 'credit',
            'amount' => 5000.00,
            'description' => 'Service income',
        ]);

        // Sample Transaction 2: Expense Payment
        $txn2 = Transaction::create([
            'user_id' => $user->id,
            'reference' => 'PAY-2025-001',
            'description' => 'Supplier Payment',
            'transaction_date' => Carbon::now()->subDays(25),
            'type' => 'payment',
            'status' => 'approved',
            'amount' => 1200.00,
            'notes' => 'Payment for office supplies',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn2->id,
            'account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 1200.00,
            'description' => 'Office supplies expense',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn2->id,
            'account_id' => $bankAccount->id,
            'type' => 'credit',
            'amount' => 1200.00,
            'description' => 'Bank payment',
        ]);

        // Sample Transaction 3: Journal Entry (Accounts Receivable)
        if ($arAccount) {
            $txn3 = Transaction::create([
                'user_id' => $user->id,
                'reference' => 'JNL-2025-001',
                'description' => 'Month-end accrual entry',
                'transaction_date' => Carbon::now()->subDays(20),
                'type' => 'journal',
                'status' => 'approved',
                'amount' => 3500.00,
                'notes' => 'Accruing estimated income',
            ]);

            TransactionItem::create([
                'transaction_id' => $txn3->id,
                'account_id' => $arAccount->id,
                'type' => 'debit',
                'amount' => 3500.00,
                'description' => 'Accounts receivable - accrual',
            ]);

            TransactionItem::create([
                'transaction_id' => $txn3->id,
                'account_id' => $incomeAccount->id,
                'type' => 'credit',
                'amount' => 3500.00,
                'description' => 'Accrued service income',
            ]);
        }

        // Sample Transaction 4: Draft Transaction
        $txn4 = Transaction::create([
            'user_id' => $user->id,
            'reference' => 'DRF-2025-001',
            'description' => 'Draft payment pending approval',
            'transaction_date' => Carbon::now()->subDays(5),
            'type' => 'payment',
            'status' => 'draft',
            'amount' => 850.00,
            'notes' => 'Awaiting manager approval',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn4->id,
            'account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 850.00,
            'description' => 'Utilities expense',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn4->id,
            'account_id' => $bankAccount->id,
            'type' => 'credit',
            'amount' => 850.00,
            'description' => 'Bank payment',
        ]);

        // Sample Transaction 5: Pending Transaction
        $txn5 = Transaction::create([
            'user_id' => $user->id,
            'reference' => 'PND-2025-001',
            'description' => 'Invoice payment waiting approval',
            'transaction_date' => Carbon::now()->subDays(10),
            'type' => 'payment',
            'status' => 'pending',
            'amount' => 2500.00,
            'notes' => 'Vendor invoice due within 10 days',
        ]);

        if ($apAccount) {
            TransactionItem::create([
                'transaction_id' => $txn5->id,
                'account_id' => $apAccount->id,
                'type' => 'debit',
                'amount' => 2500.00,
                'description' => 'Accounts payable settlement',
            ]);
        } else {
            TransactionItem::create([
                'transaction_id' => $txn5->id,
                'account_id' => $expenseAccount->id,
                'type' => 'debit',
                'amount' => 2500.00,
                'description' => 'Vendor payment',
            ]);
        }

        TransactionItem::create([
            'transaction_id' => $txn5->id,
            'account_id' => $bankAccount->id,
            'type' => 'credit',
            'amount' => 2500.00,
            'description' => 'Bank payment',
        ]);

        // Sample Transaction 6: Another Income Receipt
        $txn6 = Transaction::create([
            'user_id' => $user->id,
            'reference' => 'REC-2025-002',
            'description' => 'Sales Invoice #INV-002',
            'transaction_date' => Carbon::now()->subDays(15),
            'type' => 'receipt',
            'status' => 'approved',
            'amount' => 7500.00,
            'notes' => 'Payment from XYZ Corporation',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn6->id,
            'account_id' => $bankAccount->id,
            'type' => 'debit',
            'amount' => 7500.00,
            'description' => 'Bank deposit',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn6->id,
            'account_id' => $incomeAccount->id,
            'type' => 'credit',
            'amount' => 7500.00,
            'description' => 'Service income',
        ]);

        // Sample Transaction 7: Rejected Transaction
        $txn7 = Transaction::create([
            'user_id' => $user->id,
            'reference' => 'RJC-2025-001',
            'description' => 'Rejected vendor payment',
            'transaction_date' => Carbon::now()->subDays(8),
            'type' => 'payment',
            'status' => 'rejected',
            'amount' => 1500.00,
            'notes' => 'Rejected due to incorrect vendor account. Rejection reason: Vendor account mismatch, needs correction',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn7->id,
            'account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 1500.00,
            'description' => 'Equipment repair',
        ]);

        TransactionItem::create([
            'transaction_id' => $txn7->id,
            'account_id' => $bankAccount->id,
            'type' => 'credit',
            'amount' => 1500.00,
            'description' => 'Bank payment',
        ]);
    }
}
