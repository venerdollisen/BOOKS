<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ASSETS
        $assets = Account::create([
            'code' => '1000',
            'name' => 'Assets',
            'account_type' => 'Asset',
            'description' => 'Current and fixed assets',
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1100',
            'name' => 'Current Assets',
            'account_type' => 'Asset',
            'parent_id' => $assets->id,
            'is_active' => true,
        ]);

        $cash = Account::create([
            'code' => '1110',
            'name' => 'Cash',
            'account_type' => 'Asset',
            'parent_id' => $assets->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1111',
            'name' => 'Cash - Operating Account',
            'account_type' => 'Asset',
            'parent_id' => $cash->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1112',
            'name' => 'Cash - Savings Account',
            'account_type' => 'Asset',
            'parent_id' => $cash->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1200',
            'name' => 'Accounts Receivable',
            'account_type' => 'Asset',
            'parent_id' => $assets->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1300',
            'name' => 'Inventory',
            'account_type' => 'Asset',
            'parent_id' => $assets->id,
            'is_active' => true,
        ]);

        $fixedAssets = Account::create([
            'code' => '1400',
            'name' => 'Fixed Assets',
            'account_type' => 'Asset',
            'parent_id' => $assets->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1410',
            'name' => 'Property, Plant & Equipment',
            'account_type' => 'Asset',
            'parent_id' => $fixedAssets->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1420',
            'name' => 'Accumulated Depreciation',
            'account_type' => 'Asset',
            'parent_id' => $fixedAssets->id,
            'is_active' => true,
        ]);

        // LIABILITIES
        $liabilities = Account::create([
            'code' => '2000',
            'name' => 'Liabilities',
            'account_type' => 'Liability',
            'description' => 'Current and long-term liabilities',
            'is_active' => true,
        ]);

        Account::create([
            'code' => '2100',
            'name' => 'Accounts Payable',
            'account_type' => 'Liability',
            'parent_id' => $liabilities->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '2200',
            'name' => 'Short-term Debt',
            'account_type' => 'Liability',
            'parent_id' => $liabilities->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '2300',
            'name' => 'Long-term Debt',
            'account_type' => 'Liability',
            'parent_id' => $liabilities->id,
            'is_active' => true,
        ]);

        // EQUITY
        $equity = Account::create([
            'code' => '3000',
            'name' => 'Equity',
            'account_type' => 'Equity',
            'description' => 'Owner\'s equity and retained earnings',
            'is_active' => true,
        ]);

        Account::create([
            'code' => '3100',
            'name' => 'Capital Stock',
            'account_type' => 'Equity',
            'parent_id' => $equity->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '3200',
            'name' => 'Retained Earnings',
            'account_type' => 'Equity',
            'parent_id' => $equity->id,
            'is_active' => true,
        ]);

        // INCOME
        $income = Account::create([
            'code' => '4000',
            'name' => 'Revenue',
            'account_type' => 'Income',
            'description' => 'Sales and service revenue',
            'is_active' => true,
        ]);

        Account::create([
            'code' => '4100',
            'name' => 'Product Sales',
            'account_type' => 'Income',
            'parent_id' => $income->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '4200',
            'name' => 'Service Revenue',
            'account_type' => 'Income',
            'parent_id' => $income->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '4300',
            'name' => 'Other Income',
            'account_type' => 'Income',
            'parent_id' => $income->id,
            'is_active' => true,
        ]);

        // EXPENSES
        $expenses = Account::create([
            'code' => '5000',
            'name' => 'Operating Expenses',
            'account_type' => 'Expense',
            'description' => 'Cost of goods and operating expenses',
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5100',
            'name' => 'Cost of Goods Sold',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);

        $salaries = Account::create([
            'code' => '5200',
            'name' => 'Salaries & Wages',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5210',
            'name' => 'Salary Expense',
            'account_type' => 'Expense',
            'parent_id' => $salaries->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5300',
            'name' => 'Rent Expense',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5400',
            'name' => 'Utilities',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5500',
            'name' => 'Office Supplies',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5600',
            'name' => 'Depreciation Expense',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5700',
            'name' => 'Interest Expense',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '5800',
            'name' => 'Travel & Meals',
            'account_type' => 'Expense',
            'parent_id' => $expenses->id,
            'is_active' => true,
        ]);
    }
}
