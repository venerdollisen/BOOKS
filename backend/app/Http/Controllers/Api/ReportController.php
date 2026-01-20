<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get Trial Balance Report
     * Returns all accounts with debit and credit balances
     */
    public function trialBalance(Request $request)
    {
        $accounts = Account::with(['items'])
            ->whereIn('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])
            ->orderBy('code')
            ->get();

        $trialBalanceData = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $debits = 0;
            $credits = 0;

            foreach ($account->items as $item) {
                if ($item->type === 'debit') {
                    $debits += $item->amount;
                } else {
                    $credits += $item->amount;
                }
            }

            // Only show accounts with activity
            if ($debits > 0 || $credits > 0) {
                $trialBalanceData[] = [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debits' => round($debits, 2),
                    'credits' => round($credits, 2),
                    'balance' => round($debits - $credits, 2),
                ];

                $totalDebits += $debits;
                $totalCredits += $credits;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $trialBalanceData,
            'totals' => [
                'debits' => round($totalDebits, 2),
                'credits' => round($totalCredits, 2),
                'difference' => round($totalDebits - $totalCredits, 2),
                'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
            ],
        ]);
    }

    /**
     * Get General Ledger Report by Account
     * Returns detailed transactions for a specific account with running balance
     */
    public function generalLedger(Request $request, Account $account)
    {
        $items = $account->items()
            ->with(['transaction'])
            ->orderBy('created_at')
            ->get();

        $runningBalance = 0;
        $ledgerData = [];

        foreach ($items as $item) {
            if ($item->type === 'debit') {
                $runningBalance += $item->amount;
            } else {
                $runningBalance -= $item->amount;
            }

            $ledgerData[] = [
                'id' => $item->id,
                'date' => $item->transaction->transaction_date,
                'reference' => $item->transaction->reference,
                'description' => $item->description,
                'debit' => $item->type === 'debit' ? round($item->amount, 2) : null,
                'credit' => $item->type === 'credit' ? round($item->amount, 2) : null,
                'balance' => round($runningBalance, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'account' => [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
            ],
            'data' => $ledgerData,
            'final_balance' => round($runningBalance, 2),
        ]);
    }

    /**
     * Get GL Report summary (all accounts with balances)
     */
    public function glSummary(Request $request)
    {
        $accounts = Account::with(['items'])
            ->whereIn('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])
            ->orderBy('code')
            ->get();

        $glData = [];
        $balancesByType = [
            'asset' => 0,
            'liability' => 0,
            'equity' => 0,
            'revenue' => 0,
            'expense' => 0,
        ];

        foreach ($accounts as $account) {
            $balance = 0;

            foreach ($account->items as $item) {
                if ($item->type === 'debit') {
                    $balance += $item->amount;
                } else {
                    $balance -= $item->amount;
                }
            }

            // Only show accounts with activity
            if ($balance != 0) {
                $glData[] = [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => round($balance, 2),
                ];

                $balancesByType[$account->type] += $balance;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $glData,
            'totals' => [
                'assets' => round($balancesByType['asset'], 2),
                'liabilities' => round($balancesByType['liability'], 2),
                'equity' => round($balancesByType['equity'], 2),
                'revenue' => round($balancesByType['revenue'], 2),
                'expense' => round($balancesByType['expense'], 2),
            ],
        ]);
    }
}
