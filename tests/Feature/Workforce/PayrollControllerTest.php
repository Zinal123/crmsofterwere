<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_view_shows_earnings_and_balance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['pay_type' => 'daily', 'pay_rate' => 500]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-01', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('500');
    }

    public function test_payroll_page_formats_money_figures_like_every_other_financial_table(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['pay_type' => 'daily', 'pay_rate' => 25000]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-01', 'status' => 'present', 'marked_by' => $owner->id]);
        \App\Models\SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-05', 'amount' => 12500, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee(\App\Support\IndianNumber::format(25000));
        $response->assertSee(\App\Support\IndianNumber::format(12500));
    }

    public function test_payroll_page_has_previous_and_next_month_links(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 6]));
        $response->assertSee(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 8]));
    }

    public function test_payroll_page_links_to_the_attendance_register_for_the_same_month(): void
    {
        // The days present/half/leave/absent stats on this page are computed
        // from attendance for this employee and month - there was no link
        // to see the underlying rows they came from.
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));
    }

    public function test_salary_payments_index_lists_payments_across_all_employees_in_range(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $inRange = Employee::factory()->create(['name' => 'Ramesh Patel']);
        $outOfRange = Employee::factory()->create(['name' => 'Suresh Yadav']);
        \App\Models\SalaryPayment::create(['employee_id' => $inRange->id, 'date' => '2026-07-10', 'amount' => 15000, 'paid_by' => $owner->id]);
        \App\Models\SalaryPayment::create(['employee_id' => $outOfRange->id, 'date' => '2026-06-10', 'amount' => 12000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('payroll.index', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk();
        $response->assertSee('Ramesh Patel');
        $response->assertDontSee('Suresh Yadav');
    }

    public function test_salary_payments_index_links_each_row_to_that_employees_payroll_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        \App\Models\SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'amount' => 15000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('payroll.index', ['from' => '2026-07-01', 'to' => '2026-07-31']));

        $response->assertOk();
        $response->assertSee(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));
    }

    public function test_worker_cannot_view_the_salary_payments_index(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('payroll.index'));

        $response->assertForbidden();
    }

    public function test_owner_adds_a_salary_payment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->post(route('employees.payments.store', $employee->id), [
            'date' => '2026-07-15',
            'amount' => 2000,
            'note' => 'Mid-month advance',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('salary_payments', ['employee_id' => $employee->id, 'amount' => 2000, 'note' => 'Mid-month advance']);
    }

    public function test_owner_can_update_a_salary_payment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $payment = \App\Models\SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-15', 'amount' => 2000, 'note' => 'Old note', 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->put(route('employees.payments.update', [$employee->id, $payment->id]), [
            'date' => '2026-07-16',
            'amount' => 2500,
            'note' => 'Corrected amount',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('salary_payments', ['id' => $payment->id, 'amount' => 2500, 'note' => 'Corrected amount']);
    }

    public function test_owner_can_delete_a_salary_payment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $payment = \App\Models\SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-15', 'amount' => 2000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->delete(route('employees.payments.destroy', [$employee->id, $payment->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('salary_payments', ['id' => $payment->id]);
    }

    public function test_payroll_page_has_edit_and_delete_triggers_per_payment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $payment = \App\Models\SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-15', 'amount' => 2000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('data-bs-target="#editPayment-' . $payment->id . '"', false);
        $response->assertSee(route('employees.payments.destroy', [$employee->id, $payment->id]), false);
    }

    public function test_worker_cannot_view_payroll(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($worker)->get(route('employees.payroll', $employee->id));

        $response->assertForbidden();
    }
}
