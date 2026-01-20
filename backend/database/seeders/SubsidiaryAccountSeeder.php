<?php

namespace Database\Seeders;

use App\Models\SubsidiaryAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubsidiaryAccountSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subsidiaryAccounts = [
            // For Sales Revenue Account (1000)
            [
                'account_id' => 1,
                'code' => 'SALES-DOMESTIC',
                'name' => 'Domestic Sales Revenue',
                'description' => 'Revenue from domestic customers',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            [
                'account_id' => 1,
                'code' => 'SALES-EXPORT',
                'name' => 'Export Sales Revenue',
                'description' => 'Revenue from international exports',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            // For Cost of Goods Sold (2000)
            [
                'account_id' => 2,
                'code' => 'COGS-PRODUCT-A',
                'name' => 'Product A - COGS',
                'description' => 'Cost of goods sold for Product A',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            [
                'account_id' => 2,
                'code' => 'COGS-PRODUCT-B',
                'name' => 'Product B - COGS',
                'description' => 'Cost of goods sold for Product B',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            // For Operating Expenses (5000)
            [
                'account_id' => 5,
                'code' => 'EXP-SALARIES-ADMIN',
                'name' => 'Admin Salaries Expense',
                'description' => 'Administrative department salaries',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            [
                'account_id' => 5,
                'code' => 'EXP-SALARIES-SALES',
                'name' => 'Sales Salaries Expense',
                'description' => 'Sales department salaries and commissions',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            [
                'account_id' => 5,
                'code' => 'EXP-UTILITIES',
                'name' => 'Utilities Expense',
                'description' => 'Electricity, water, and gas expenses',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            [
                'account_id' => 5,
                'code' => 'EXP-RENT-OFFICE',
                'name' => 'Office Rent Expense',
                'description' => 'Office building rental expenses',
                'type' => 'cost_center',
                'status' => 'active',
            ],
            // For Bank Account (7000) - Branch locations
            [
                'account_id' => 7,
                'code' => 'BANK-MAIN-BRANCH',
                'name' => 'Main Branch Bank Account',
                'description' => 'Primary bank account - Main office',
                'type' => 'branch',
                'status' => 'active',
            ],
            [
                'account_id' => 7,
                'code' => 'BANK-REGIONAL-BRANCH',
                'name' => 'Regional Branch Bank Account',
                'description' => 'Bank account for regional operations',
                'type' => 'branch',
                'status' => 'active',
            ],
        ];

        foreach ($subsidiaryAccounts as $account) {
            SubsidiaryAccount::create($account);
        }
    }
}
