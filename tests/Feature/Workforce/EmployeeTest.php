<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_role_has_all_six_workforce_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = \Spatie\Permission\Models\Role::findByName('Owner');

        $this->assertTrue($owner->hasPermissionTo('employees.view'));
        $this->assertTrue($owner->hasPermissionTo('employees.manage'));
        $this->assertTrue($owner->hasPermissionTo('attendance.view'));
        $this->assertTrue($owner->hasPermissionTo('attendance.manage'));
        $this->assertTrue($owner->hasPermissionTo('payroll.view'));
        $this->assertTrue($owner->hasPermissionTo('payroll.manage-payments'));
    }

    public function test_worker_role_has_none_of_the_workforce_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = \Spatie\Permission\Models\Role::findByName('Worker');

        $this->assertFalse($worker->hasPermissionTo('employees.view'));
        $this->assertFalse($worker->hasPermissionTo('attendance.manage'));
    }

    public function test_owner_can_create_an_employee(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post(route('employees.store'), [
            'name' => 'Ramesh Kumar',
            'phone' => '9998887770',
            'joining_date' => '2026-01-15',
            'pay_type' => 'daily',
            'pay_rate' => 600,
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', ['name' => 'Ramesh Kumar', 'pay_type' => 'daily', 'is_active' => true]);
    }

    public function test_owner_can_deactivate_an_employee_without_deleting_history(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['is_active' => true]);

        $this->actingAs($owner)->post(route('employees.deactivate', $employee->id));

        $this->assertFalse($employee->fresh()->is_active);
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    public function test_worker_cannot_view_employees(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('employees.index'));

        $response->assertForbidden();
    }
}
