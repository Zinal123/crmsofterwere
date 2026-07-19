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
