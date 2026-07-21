<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_a_payment_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $payment = SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-15', 'amount' => 2000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'salary_payment', 'id' => $payment->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_payroll_page_has_a_history_trigger_per_payment_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $payment = SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-15', 'amount' => 2000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $payment->id . '"', false);
    }
}
