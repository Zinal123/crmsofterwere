<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserModelRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_a_role_and_checked(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('Owner');

        $user->assignRole('Owner');

        $this->assertTrue($user->hasRole('Owner'));
    }

    public function test_user_permission_check_reflects_role_permissions(): void
    {
        // Uses a role other than 'Owner' because Task 2's UserFactory::configure()
        // hook now seeds 'Owner' with every permission; a custom, restricted
        // permission subset needs a role the seeder doesn't touch.
        $user = User::factory()->create();
        $role = Role::findOrCreate('Manager');
        Permission::findOrCreate('invoices.view');
        $role->givePermissionTo('invoices.view');
        $user->syncRoles(['Manager']);

        $this->assertTrue($user->can('invoices.view'));
        $this->assertFalse($user->can('invoices.create'));
    }

    public function test_is_active_defaults_to_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->is_active);
    }
}
