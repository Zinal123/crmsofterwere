<?php

namespace Database\Seeders;

use App\Models\ChartAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * A standard small-business chart of accounts. Codes follow the usual
     * 1000/2000/3000/4000/5000 asset/liability/equity/income/expense blocks.
     * The reports reference these by code, so the codes are stable contract -
     * renaming is fine, re-coding is not.
     */
    private const ACCOUNTS = [
        // Assets
        ['1000', 'Cash & Bank', 'asset'],
        ['1100', 'Accounts Receivable', 'asset'],
        // Liabilities
        ['2000', 'Accounts Payable', 'liability'],
        ['2100', 'GST Payable', 'liability'],
        // Equity
        ['3000', "Owner's Equity", 'equity'],
        // Income
        ['4000', 'Sales Income', 'income'],
        ['4900', 'Other Income', 'income'],
        // Expenses
        ['5000', 'Salaries & Wages', 'expense'],
        ['5100', 'Vendor Purchases', 'expense'],
        ['5900', 'Operating Expenses', 'expense'],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $i => [$code, $name, $type]) {
            ChartAccount::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'sort_order' => $i]
            );
        }
    }
}
