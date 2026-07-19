<?php

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_all_25_permissions(): void
    {
        (new RolesAndPermissionsSeeder())->run();

        $this->assertCount(31, Permission::all());
        $this->assertTrue(Permission::where('name', 'invoices.view')->exists());
        $this->assertTrue(Permission::where('name', 'admin.manage-roles')->exists());
    }

    public function test_owner_role_gets_every_permission(): void
    {
        (new RolesAndPermissionsSeeder())->run();

        $owner = Role::findByName('Owner');

        $this->assertCount(31, $owner->permissions);
    }

    public function test_worker_role_gets_only_its_two_job_permissions(): void
    {
        (new RolesAndPermissionsSeeder())->run();

        $worker = Role::findByName('Worker');

        $this->assertCount(2, $worker->permissions);
    }

    public function test_seeder_is_idempotent(): void
    {
        (new RolesAndPermissionsSeeder())->run();
        (new RolesAndPermissionsSeeder())->run();

        $this->assertCount(31, Permission::all());
        $this->assertCount(2, Role::all());
    }

    public function test_new_factory_user_has_owner_role_and_full_permissions(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->assertTrue($user->hasRole('Owner'));
        $this->assertTrue($user->can('invoices.view'));
        $this->assertTrue($user->can('admin.manage-users'));
    }
}
