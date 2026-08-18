<?php

namespace Tests\Feature;

use App\Models\DailyTransaction;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use App\Services\Reporting\AccountingService;
use Database\Seeders\ChartOfAccountsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_owner_can_view_the_accounting_pages(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get(route('accounting.chart'))->assertOk();
        $this->actingAs($owner)->get(route('accounting.profit-loss'))->assertOk();
        $this->actingAs($owner)->get(route('accounting.trial-balance'))->assertOk();
    }

    public function test_worker_cannot_view_accounting_pages(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $this->actingAs($worker)->get(route('accounting.chart'))->assertForbidden();
        $this->actingAs($worker)->get(route('accounting.profit-loss'))->assertForbidden();
        $this->actingAs($worker)->get(route('accounting.trial-balance'))->assertForbidden();
    }

    public function test_profit_and_loss_nets_income_against_expenses(): void
    {
        $owner = $this->owner();
        $fuel = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);
        $misc = ExpenseCategory::create(['name' => 'Other Income', 'type' => 'receipt', 'party_model' => null]);

        Invoice::factory()->create(['date' => now(), 'totalamountbeforetax' => 100000, 'amountwithtax' => 118000, 'remaining_amount' => 0, 'paidamount' => 118000]);
        DailyTransaction::create(['type' => 'receipt', 'expense_category_id' => $misc->id, 'amount' => 5000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);
        DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $fuel->id, 'amount' => 3000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $report = app(AccountingService::class)->profitAndLoss(now()->startOfMonth(), now()->endOfMonth());

        $this->assertEqualsWithDelta(105000, $report['total_income'], 0.01);
        $this->assertEqualsWithDelta(3000, $report['total_expense'], 0.01);
        $this->assertEqualsWithDelta(102000, $report['net_profit'], 0.01);
    }

    public function test_wages_are_counted_once_not_double_from_the_mirror_row(): void
    {
        $owner = $this->owner();
        $employee = Employee::factory()->create();
        $wages = ExpenseCategory::create(['name' => 'Worker Wages', 'type' => 'payment', 'party_model' => 'employee']);

        // The real payment, plus the linked cash-book mirror the Daily
        // Expenses module writes alongside it. Only the SalaryPayment should
        // count toward Salaries & Wages; the mirror must be ignored.
        $salary = SalaryPayment::create(['employee_id' => $employee->id, 'date' => now(), 'amount' => 20000, 'note' => null, 'paid_by' => $owner->id]);
        DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $wages->id, 'party_type' => 'employee', 'party_id' => $employee->id, 'amount' => 20000, 'date' => now(), 'payment_mode' => 'cash', 'linked_type' => 'salary_payment', 'linked_id' => $salary->id, 'created_by' => $owner->id]);

        $report = app(AccountingService::class)->profitAndLoss(now()->startOfMonth(), now()->endOfMonth());

        // 20000 once (from payroll), not 40000.
        $this->assertEqualsWithDelta(20000, $report['total_expense'], 0.01);
        $salaryLine = collect($report['expenses'])->firstWhere('account', 'Salaries & Wages');
        $this->assertEqualsWithDelta(20000, $salaryLine['amount'], 0.01);
    }

    public function test_profit_and_loss_lines_link_to_the_matching_filtered_list(): void
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $report = app(AccountingService::class)->profitAndLoss($from, $to);

        $salesLine = collect($report['income'])->firstWhere('account', 'Sales Income');
        $otherIncomeLine = collect($report['income'])->firstWhere('account', 'Other Income');
        $salaryLine = collect($report['expenses'])->firstWhere('account', 'Salaries & Wages');
        $vendorLine = collect($report['expenses'])->firstWhere('account', 'Vendor Purchases');
        $operatingLine = collect($report['expenses'])->firstWhere('account', 'Operating Expenses');

        $this->assertSame(route('invoice', ['from' => $from->toDateString(), 'to' => $to->toDateString()]), $salesLine['link']);
        $this->assertSame(route('expenses.index', ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'type' => 'receipt', 'exclude_mirrors' => 1]), $otherIncomeLine['link']);
        $this->assertSame(route('payroll.index', ['from' => $from->toDateString(), 'to' => $to->toDateString()]), $salaryLine['link']);
        $this->assertSame(route('vendor-payments.index', ['from' => $from->toDateString(), 'to' => $to->toDateString()]), $vendorLine['link']);
        $this->assertSame(route('expenses.index', ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'type' => 'payment', 'exclude_mirrors' => 1]), $operatingLine['link']);
    }

    public function test_trial_balance_rows_link_to_the_matching_filtered_list_where_one_exists(): void
    {
        $asOf = now()->endOfMonth();

        $report = app(AccountingService::class)->trialBalance($asOf);
        $rows = $report['rows']->keyBy('code');

        $this->assertSame(route('invoice', ['to' => $asOf->toDateString()]), $rows['4000']['link']); // Sales
        $this->assertSame(route('expenses.index', ['to' => $asOf->toDateString(), 'type' => 'receipt', 'exclude_mirrors' => 1]), $rows['4900']['link']); // Other Income
        $this->assertSame(route('payroll.index', ['to' => $asOf->toDateString()]), $rows['5000']['link']); // Salaries
        $this->assertSame(route('vendor-payments.index', ['to' => $asOf->toDateString()]), $rows['5100']['link']); // Vendor Purchases
        $this->assertSame(route('expenses.index', ['to' => $asOf->toDateString(), 'type' => 'payment', 'exclude_mirrors' => 1]), $rows['5900']['link']); // Operating

        // Cash, Receivable, Payable, GST Payable, and Owner's Equity are not
        // sums of one record type (Equity is explicitly the balancing figure,
        // GST is a computed difference) - no link for these.
        $this->assertArrayNotHasKey('link', $rows['1000']);
        $this->assertArrayNotHasKey('link', $rows['1100']);
        $this->assertArrayNotHasKey('link', $rows['2000']);
        $this->assertArrayNotHasKey('link', $rows['2100']);
        $this->assertArrayNotHasKey('link', $rows['3000']);
    }

    public function test_profit_and_loss_page_renders_the_drill_down_links(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->get(route('accounting.profit-loss'));

        $response->assertOk();
        $response->assertSee(route('payroll.index', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString()]));
    }

    public function test_trial_balance_page_renders_a_drill_down_link_and_the_info_icon_for_non_drillable_rows(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->get(route('accounting.trial-balance'));

        $response->assertOk();
        $response->assertSee(route('vendor-payments.index', ['to' => now()->toDateString()]));
        $response->assertSee('ri-information-line', false);
    }

    public function test_trial_balance_debits_equal_credits(): void
    {
        $owner = $this->owner();
        $vendor = Vendor::create(['name' => 'Acme Supplies', 'category' => 'Accessories', 'is_active' => true]);
        $fuel = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);

        Invoice::factory()->create(['date' => now(), 'totalamountbeforetax' => 100000, 'amountwithtax' => 118000, 'remaining_amount' => 50000, 'paidamount' => 68000]);
        VendorBill::create(['vendor_id' => $vendor->id, 'amount' => 45000, 'date' => now(), 'created_by' => $owner->id]);
        VendorPayment::create(['vendor_id' => $vendor->id, 'amount' => 10000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);
        DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $fuel->id, 'amount' => 3000, 'date' => now(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $report = app(AccountingService::class)->trialBalance(now()->endOfMonth());

        $this->assertEqualsWithDelta($report['total_debit'], $report['total_credit'], 0.01);
        $this->assertGreaterThan(0, $report['total_debit']);
    }
}
