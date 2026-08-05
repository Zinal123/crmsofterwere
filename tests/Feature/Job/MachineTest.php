<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_role_gets_view_own_and_create_job_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = \Spatie\Permission\Models\Role::findByName('Worker');

        $this->assertTrue($worker->hasPermissionTo('jobs.view-own'));
        $this->assertTrue($worker->hasPermissionTo('jobs.create'));
        $this->assertFalse($worker->hasPermissionTo('jobs.manage-machines'));
    }

    public function test_user_with_permission_can_list_and_create_machines(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->post(route('machines.store'), ['name' => 'CNC Lathe #3']);

        $response->assertRedirect(route('machines.index'));
        $this->assertDatabaseHas('machines', ['name' => 'CNC Lathe #3', 'is_active' => true]);
    }

    public function test_machine_can_be_created_with_coordinates(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->post(route('machines.store'), [
            'name' => 'CNC Lathe #4',
            'latitude' => 19.0759837,
            'longitude' => 72.8776559,
        ]);

        $response->assertRedirect(route('machines.index'));
        $this->assertDatabaseHas('machines', [
            'name' => 'CNC Lathe #4',
            'latitude' => 19.0759837,
            'longitude' => 72.8776559,
        ]);
    }

    public function test_machine_can_still_be_created_without_coordinates(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->post(route('machines.store'), ['name' => 'CNC Lathe #5']);

        $response->assertRedirect(route('machines.index'));
        $this->assertDatabaseHas('machines', ['name' => 'CNC Lathe #5', 'latitude' => null, 'longitude' => null]);
    }

    public function test_machine_coordinates_are_rejected_when_out_of_range(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->post(route('machines.store'), [
            'name' => 'CNC Lathe #6',
            'latitude' => 200,
            'longitude' => 72.8776559,
        ]);

        $response->assertSessionHasErrors('latitude');
        $this->assertDatabaseMissing('machines', ['name' => 'CNC Lathe #6']);
    }

    public function test_toggle_flips_active_state(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');
        $machine = Machine::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('machines.toggle', $machine->id));

        $this->assertFalse($machine->fresh()->is_active);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->syncRoles(['Worker']);

        $response = $this->actingAs($user)->get(route('machines.index'));

        $response->assertForbidden();
    }
}
