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

    public function test_user_without_permission_cannot_reach_roles_page(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']); // overrides the factory's default Owner role

        $response = $this->actingAs($worker)->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }
}
