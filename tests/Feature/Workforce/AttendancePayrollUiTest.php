<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePayrollUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_page_lists_active_employees_for_bulk_entry(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Employee::factory()->create(['name' => 'Active Worker', 'is_active' => true]);
        Employee::factory()->create(['name' => 'Inactive Worker', 'is_active' => false]);

        $response = $this->actingAs($owner)->get(route('attendance.mark'));

        $response->assertOk();
        $response->assertSee('Active Worker');
        $response->assertDontSee('Inactive Worker');
    }

    public function test_mark_page_links_each_employee_to_their_attendance_register(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['is_active' => true]);

        $response = $this->actingAs($owner)->get(route('attendance.mark', ['date' => '2026-07-15']));

        $response->assertOk();
        $response->assertSee(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));
    }

    public function test_register_page_shows_the_months_marked_days(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('present');
    }

    public function test_payroll_page_shows_ledger_and_add_payment_form(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'amount' => 1500, 'note' => 'Advance', 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('1500');
        $response->assertSee('Advance');
        $response->assertSee(route('employees.payments.store', $employee->id), false);
    }
}
