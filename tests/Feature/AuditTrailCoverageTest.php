<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\TicketProblemType;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditTrailCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_creating_a_vendor_logs_under_the_clean_vendor_type_not_the_raw_class_name(): void
    {
        $vendor = Vendor::create(['name' => 'QA Vendor', 'category' => 'Raw Material']);

        $this->assertDatabaseHas('audit_logs', ['auditable_type' => 'vendor', 'auditable_id' => $vendor->id, 'action' => 'created']);
        $this->assertDatabaseMissing('audit_logs', ['auditable_type' => Vendor::class]);
    }

    public function test_vendor_audit_trail_is_viewable_with_the_right_permission(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $vendor = Vendor::create(['name' => 'QA Vendor', 'category' => 'Raw Material']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'vendor', 'id' => $vendor->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_creating_a_client_account_logs_under_the_clean_type(): void
    {
        $account = ClientAccount::factory()->create();

        $this->assertDatabaseHas('audit_logs', ['auditable_type' => 'client_account', 'auditable_id' => $account->id, 'action' => 'created']);
    }

    public function test_creating_a_client_machine_logs_under_the_clean_type(): void
    {
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);

        $this->assertDatabaseHas('audit_logs', ['auditable_type' => 'client_machine', 'auditable_id' => $machine->id, 'action' => 'created']);
    }

    public function test_creating_a_ticket_problem_type_logs_under_the_clean_type(): void
    {
        $problemType = TicketProblemType::factory()->create();

        $this->assertDatabaseHas('audit_logs', ['auditable_type' => 'ticket_problem_type', 'auditable_id' => $problemType->id, 'action' => 'created']);
    }

    public function test_role_audit_trail_is_viewable_with_the_admin_permission(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $role = Role::create(['name' => 'QA Role Audit Test', 'guard_name' => 'web']);
        AuditLog::create(['auditable_type' => 'role', 'auditable_id' => $role->id, 'action' => 'created', 'user_id' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'role', 'id' => $role->id]));

        $response->assertOk();
        $response->assertSee('created');
    }
}
