<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Services\Workforce\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_wage_employee_earnings_with_mixed_attendance(): void
    {
        $user = User::factory()->create();
        // Daily wage 600/day, overtime 50/hour. July 2026 has 31 days.
        $employee = Employee::factory()->create(['pay_type' => 'daily', 'pay_rate' => 600, 'overtime_rate_per_hour' => 50]);
        // 2 present (1200), 1 half-day (300), 1 leave (0), 1 absent (0) = 1500 base.
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-01', 'status' => 'present', 'overtime_hours' => 2, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-02', 'status' => 'present', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-03', 'status' => 'half_day', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-04', 'status' => 'leave', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-05', 'status' => 'absent', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'amount' => 500, 'paid_by' => $user->id]);

        $result = app(PayrollService::class)->calculateMonthlyEarnings($employee, 2026, 7);

        $this->assertEquals(2, $result['days_present']);
        $this->assertEquals(1, $result['days_half']);
        $this->assertEquals(1, $result['days_leave']);
        $this->assertEquals(1, $result['days_absent']);
        $this->assertEquals(600.00, (float) $result['day_rate']);
        $this->assertEquals(1500.00, (float) $result['base_earned']); // (2 * 600) + (1 * 300)
        $this->assertEquals(2.0, (float) $result['overtime_hours']);
        $this->assertEquals(100.00, (float) $result['overtime_earned']); // 2 * 50
        $this->assertEquals(1600.00, (float) $result['total_earned']); // 1500 + 100
        $this->assertEquals(500.00, (float) $result['total_paid']);
        $this->assertEquals(1100.00, (float) $result['balance_due']); // 1600 - 500
    }

    public function test_monthly_salary_employee_day_rate_derived_from_days_in_month(): void
    {
        $user = User::factory()->create();
        // Monthly salary 31000. April 2026 has 30 days -> day rate 1033.333...
        $employee = Employee::factory()->create(['pay_type' => 'monthly', 'pay_rate' => 31000, 'overtime_rate_per_hour' => 0]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-04-01', 'status' => 'present', 'overtime_hours' => 0, 'marked_by' => $user->id]);

        $result = app(PayrollService::class)->calculateMonthlyEarnings($employee, 2026, 4);

        $this->assertEqualsWithDelta(1033.33, (float) $result['day_rate'], 0.01);
        $this->assertEqualsWithDelta(1033.33, (float) $result['base_earned'], 0.01);
    }

    public function test_no_attendance_marked_yields_zero_earnings_not_an_error(): void
    {
        $employee = Employee::factory()->create(['pay_type' => 'daily', 'pay_rate' => 600]);

        $result = app(PayrollService::class)->calculateMonthlyEarnings($employee, 2026, 7);

        $this->assertEquals(0.00, (float) $result['total_earned']);
        $this->assertEquals(0.00, (float) $result['balance_due']);
    }
}
