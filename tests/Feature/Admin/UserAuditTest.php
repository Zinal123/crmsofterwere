<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_a_user_logs_it(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $target = User::factory()->create();
        $target->syncRoles(['Owner']);

        $this->actingAs($owner)->put(route('admin.users.update', $target->id), [
            'role' => 'Owner',
            'is_active' => '0',
        ]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'user', 'id' => $target->id]));

        $response->assertOk();
        $response->assertSee('deactivated');
    }

    public function test_changing_a_users_role_logs_the_old_and_new_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $target = User::factory()->create();
        $target->syncRoles(['Worker']);

        $this->actingAs($owner)->put(route('admin.users.update', $target->id), [
            'role' => 'Owner',
            'is_active' => '1',
        ]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'user', 'id' => $target->id]));

        $response->assertOk();
        $response->assertSee('Worker');
        $response->assertSee('Owner');
    }

    public function test_users_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $owner->id . '"', false);
    }
}
