<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_roles_page_renders_with_the_matrix(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('Owner');
        $response->assertSee('Worker');
        $response->assertSee('invoices.view');
    }

    public function test_owner_can_create_a_new_role(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('admin.roles.store'), ['name' => 'Manager']);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'Manager']);
    }

    public function test_owner_can_toggle_a_permission_on_a_role(): void
    {
        $owner = User::factory()->create();
        $worker = Role::findByName('Worker');

        $response = $this->actingAs($owner)->postJson(route('admin.roles.togglePermission', $worker->id), [
            'permission' => 'invoices.view',
        ]);

        $response->assertOk();
        $response->assertJson(['granted' => true]);
        $this->assertTrue($worker->fresh()->hasPermissionTo('invoices.view'));

        $response2 = $this->actingAs($owner)->postJson(route('admin.roles.togglePermission', $worker->id), [
            'permission' => 'invoices.view',
        ]);
        $response2->assertJson(['granted' => false]);
        $this->assertFalse($worker->fresh()->hasPermissionTo('invoices.view'));
    }

    public function test_owner_can_delete_a_role_with_no_users_assigned(): void
    {
        $owner = User::factory()->create();
        Role::create(['name' => 'Temp']);

        $response = $this->actingAs($owner)->delete(route('admin.roles.destroy', Role::findByName('Temp')->id));

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['name' => 'Temp']);
    }

    public function test_owner_role_cannot_be_deleted(): void
    {
        $owner = User::factory()->create();
        $ownerRole = Role::findByName('Owner');

        $response = $this->actingAs($owner)->delete(route('admin.roles.destroy', $ownerRole->id));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Owner']);
    }

    public function test_role_with_assigned_users_cannot_be_deleted(): void
    {
        $owner = User::factory()->create();
        Role::create(['name' => 'Temp']);
        $assignedUser = User::factory()->create();
        $assignedUser->syncRoles(['Temp']);

        $response = $this->actingAs($owner)->delete(route('admin.roles.destroy', Role::findByName('Temp')->id));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Temp']);
    }

    public function test_toggle_permission_with_nonexistent_role_returns_422(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->postJson(route('admin.roles.togglePermission', 999999), [
            'permission' => 'invoices.view',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    public function test_toggle_permission_with_invalid_permission_name_returns_422(): void
    {
        $owner = User::factory()->create();
        $worker = Role::findByName('Worker');
        $countBefore = \Spatie\Permission\Models\Permission::count();

        $response = $this->actingAs($owner)->postJson(route('admin.roles.togglePermission', $worker->id), [
            'permission' => 'bogus.permission',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
        $this->assertEquals($countBefore, \Spatie\Permission\Models\Permission::count());
        $this->assertDatabaseMissing('permissions', ['name' => 'bogus.permission']);
    }

    public function test_user_without_permission_cannot_reach_roles_page(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']); // overrides the factory's default Owner role

        $response = $this->actingAs($worker)->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }

    public function test_owner_can_rename_a_role(): void
    {
        $owner = User::factory()->create();
        $role = Role::create(['name' => 'QA Old Name', 'guard_name' => 'web']);

        $response = $this->actingAs($owner)->put(route('admin.roles.update', $role->id), ['name' => 'QA New Name']);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'QA New Name']);
    }

    public function test_the_owner_role_cannot_be_renamed(): void
    {
        $owner = User::factory()->create();
        $ownerRole = Role::findByName('Owner');

        $response = $this->actingAs($owner)->put(route('admin.roles.update', $ownerRole->id), ['name' => 'Not Owner Anymore']);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $ownerRole->id, 'name' => 'Owner']);
    }

    public function test_roles_page_has_a_rename_trigger_per_role(): void
    {
        $owner = User::factory()->create();
        $worker = Role::findByName('Worker');

        $response = $this->actingAs($owner)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#renameRole-' . $worker->id . '"', false);
    }

    public function test_roles_page_has_a_history_trigger_per_role(): void
    {
        $owner = User::factory()->create();
        $worker = Role::findByName('Worker');

        $response = $this->actingAs($owner)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#auditTrailModal-role"', false);
        $response->assertSee('data-audit-id="' . $worker->id . '"', false);
    }

    public function test_renaming_a_role_shows_up_in_its_history(): void
    {
        $owner = User::factory()->create();
        $role = Role::create(['name' => 'QA History Role', 'guard_name' => 'web']);

        $this->actingAs($owner)->put(route('admin.roles.update', $role->id), ['name' => 'QA Renamed Role']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'role', 'id' => $role->id]));

        $response->assertOk();
        $response->assertSee('updated');
    }
}
