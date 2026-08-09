<?php

namespace App\Services\Reporting;

use App\Models\ChartAccount;
use App\Models\DailyTransaction;
use App\Models\Invoice;
use App\Models\SalaryPayment;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Cash-basis reporting layer on top of the existing financial records. It
 * does NOT post journal entries or change how anything is saved - it derives
 * account balances from the DailyTransaction / Invoice / VendorBill /
 * SalaryPayment rows that already exist.
 *
 * The one rule it enforces, inherited from the Daily Expenses design: wages
 * and vendor payments logged through the Cash Book create a real
 * SalaryPayment / VendorPayment plus a *linked* daily_transactions mirror
 * row. To avoid double-counting, every expense/cash figure here reads the
 * real payment as its source of truth and excludes the linked mirror rows
 * (linked_type IS NULL) from the daily-transaction totals.
 */
class AccountingService
{
    /** All accounts grouped by type, in chart order. */
    public function chartOfAccounts(): Collection
    {
        return ChartAccount::orderBy('sort_order')->get()->groupBy('type');
    }

    /**
     * Income vs. expense for each of the last N months, oldest first - the
     * series behind the dashboard trend chart.
     */
    public function monthlyTrend(int $months = 6): array
    {
        $trend = [];
        $cursor = Carbon::now()->startOfMonth()->subMonths($months - 1);

        for ($i = 0; $i < $months; $i++) {
            $pl = $this->profitAndLoss($cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth());
            $trend[] = [
                'label' => $cursor->format('M'),
                'income' => round($pl['total_income'], 2),
                'expense' => round($pl['total_expense'], 2),
                'profit' => $pl['net_profit'],
            ];
            $cursor->addMonth();
        }

        return $trend;
    }

    /**
     * Profit & Loss for a date range. Income and expense lines only - this is
     * the statement of what was earned vs. spent over the period.
     */
    public function profitAndLoss(Carbon $from, Carbon $to): array
    {
        $income = [
            ['account' => 'Sales Income', 'amount' => $this->invoiceSales($from, $to)],
            ['account' => 'Other Income', 'amount' => $this->dailyTotal('receipt', $from, $to)],
        ];

        $expenses = [
            ['account' => 'Salaries & Wages', 'amount' => $this->moneySum(SalaryPayment::query(), 'amount', 'date', $from, $to)],
            ['account' => 'Vendor Purchases', 'amount' => $this->moneySum(VendorPayment::query(), 'amount', 'date', $from, $to)],
            ['account' => 'Operating Expenses', 'amount' => $this->dailyTotal('payment', $from, $to)],
        ];

        $totalIncome = array_sum(array_column($income, 'amount'));
        $totalExpense = array_sum(array_column($expenses, 'amount'));

        return [
            'from' => $from,
            'to' => $to,
            'income' => $income,
            'expenses' => $expenses,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => round($totalIncome - $totalExpense, 2),
            'expense_breakdown' => $this->operatingExpenseBreakdown($from, $to),
        ];
    }

    /**
     * A derived trial balance as of a date. Cumulative account balances shown
     * on their natural side. Owner's Equity is the balancing figure (retained
     * earnings are not tracked separately on a cash book), so the statement
     * balances by construction - the honest caveat, surfaced in the view.
     */
    public function trialBalance(Carbon $asOf): array
    {
        $sales = $this->invoiceSales(null, $asOf);
        $otherIncome = $this->dailyTotal('receipt', null, $asOf);
        $salaries = $this->moneySum(SalaryPayment::query(), 'amount', 'date', null, $asOf);
        $vendorPurchases = $this->moneySum(VendorPayment::query(), 'amount', 'date', null, $asOf);
        $operating = $this->dailyTotal('payment', null, $asOf);

        $receivable = $this->invoiceOutstanding($asOf);
        $payable = $this->vendorPayable($asOf);
        $gstPayable = $this->invoiceGst($asOf);
        $cash = $this->cashPosition($asOf);

        // Known balances first; Owner's Equity absorbs the remainder so total
        // debits equal total credits.
        $debits = $cash + $receivable + $salaries + $vendorPurchases + $operating;
        $knownCredits = $payable + $gstPayable + $sales + $otherIncome;
        $equity = round($debits - $knownCredits, 2);

        $balances = [
            '1000' => $cash,
            '1100' => $receivable,
            '2000' => $payable,
            '2100' => $gstPayable,
            '3000' => $equity,
            '4000' => $sales,
            '4900' => $otherIncome,
            '5000' => $salaries,
            '5100' => $vendorPurchases,
            '5900' => $operating,
        ];

        $rows = ChartAccount::orderBy('sort_order')->get()->map(function (ChartAccount $account) use ($balances) {
            $balance = round($balances[$account->code] ?? 0, 2);
            $side = $account->normalBalanceSide();

            // A negative natural balance flips sides (e.g. a net loss makes
            // Owner's Equity a debit) so the columns still foot.
            return [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'debit' => ($side === 'debit') === ($balance >= 0) ? abs($balance) : 0.0,
                'credit' => ($side === 'credit') === ($balance >= 0) ? abs($balance) : 0.0,
            ];
        });

        return [
            'as_of' => $asOf,
            'rows' => $rows,
            'total_debit' => round($rows->sum('debit'), 2),
            'total_credit' => round($rows->sum('credit'), 2),
        ];
    }

    /** Payment-type daily transactions per category (excludes linked mirror rows). */
    private function operatingExpenseBreakdown(Carbon $from, Carbon $to): Collection
    {
        return DailyTransaction::query()
            ->where('daily_transactions.type', 'payment')
            ->whereNull('linked_type')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->join('expense_categories', 'expense_categories.id', '=', 'daily_transactions.expense_category_id')
            ->selectRaw('expense_categories.name as category, sum(daily_transactions.amount) as total')
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['category' => $row->category, 'total' => (float) $row->total]);
    }

    /** Standalone (non-mirror) daily transactions of a type, optionally within a range. */
    private function dailyTotal(string $type, ?Carbon $from, Carbon $to): float
    {
        $query = DailyTransaction::query()->where('type', $type)->whereNull('linked_type');

        return $this->applyDateRange($query, 'date', $from, $to)->sum('amount');
    }

    /** Net cash recorded: cash in (receipts + invoice payments) less cash out. */
    private function cashPosition(Carbon $asOf): float
    {
        $in = $this->dailyTotal('receipt', null, $asOf)
            + $this->moneySum(Invoice::query(), 'paidamount', 'date', null, $asOf);

        $out = $this->dailyTotal('payment', null, $asOf)
            + $this->moneySum(SalaryPayment::query(), 'amount', 'date', null, $asOf)
            + $this->moneySum(VendorPayment::query(), 'amount', 'date', null, $asOf);

        return round($in - $out, 2);
    }

    /** Net sales (before tax) from invoices in the range. */
    private function invoiceSales(?Carbon $from, Carbon $to): float
    {
        return $this->moneySum(Invoice::query(), 'totalamountbeforetax', 'date', $from, $to);
    }

    /** GST collected = amount-with-tax minus amount-before-tax, to date. */
    private function invoiceGst(Carbon $asOf): float
    {
        $withTax = $this->moneySum(Invoice::query(), 'amountwithtax', 'date', null, $asOf);

        return round($withTax - $this->invoiceSales(null, $asOf), 2);
    }

    /** Outstanding customer balance = unpaid invoice remainder, to date. */
    private function invoiceOutstanding(Carbon $asOf): float
    {
        return $this->moneySum(Invoice::query(), 'remaining_amount', 'date', null, $asOf);
    }

    /** Unpaid vendor bill balances (bill total less payments), to date. */
    private function vendorPayable(Carbon $asOf): float
    {
        $billed = $this->moneySum(VendorBill::query(), 'amount', 'date', null, $asOf);
        $paid = $this->moneySum(VendorPayment::query(), 'amount', 'date', null, $asOf);

        return round(max($billed - $paid, 0), 2);
    }

    /**
     * Sum a money column, tolerating columns stored as strings (the invoice
     * table keeps its amounts as varchar). Summed in PHP so the cast behaves
     * identically on MySQL and the SQLite test database.
     */
    private function moneySum($query, string $column, string $dateColumn, ?Carbon $from, Carbon $to): float
    {
        $this->applyDateRange($query, $dateColumn, $from, $to);

        return round($query->pluck($column)->sum(fn ($value) => (float) $value), 2);
    }

    private function applyDateRange($query, string $dateColumn, ?Carbon $from, Carbon $to)
    {
        if ($from !== null) {
            $query->whereBetween($dateColumn, [$from->toDateString(), $to->toDateString()]);
        } else {
            $query->whereDate($dateColumn, '<=', $to->toDateString());
        }

        return $query;
    }
}
