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
