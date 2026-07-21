<?php

namespace Tests\Feature\Auditing;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_a_records_history(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Audit Target']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'employee', 'id' => $employee->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($worker)->get(route('audit-logs.for-record', ['type' => 'employee', 'id' => $employee->id]));

        $response->assertForbidden();
    }

    public function test_unknown_type_is_forbidden_not_a_server_error(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'not-a-real-type', 'id' => 1]));

        $response->assertForbidden();
    }
}
