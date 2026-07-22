<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_users_page_renders_with_existing_users(): void
    {
        $owner = User::factory()->create(['name' => 'Existing Owner']);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Existing Owner');
    }

    public function test_users_page_shows_status_badge_with_icon(): void
    {
        $owner = \App\Models\User::factory()->create();
        \App\Models\User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('ri-checkbox-circle-line', false);
        $response->assertSee('ri-close-circle-line', false);
    }

    public function test_owner_can_create_a_worker_with_a_temporary_password(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('admin.users.store'), [
            'name' => 'New Worker',
            'email' => 'worker@example.com',
            'password' => 'TempPass123',
            'role' => 'Worker',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'worker@example.com', 'is_active' => 1]);

        $newUser = User::where('email', 'worker@example.com')->first();
        $this->assertTrue($newUser->hasRole('Worker'));
        $this->assertTrue(Hash::check('TempPass123', $newUser->password));
    }

    public function test_owner_can_change_a_users_role_and_deactivate_them(): void
    {
        $owner = User::factory()->create();
        $target = User::factory()->create();
        $target->syncRoles(['Worker']);

        $response = $this->actingAs($owner)->put(route('admin.users.update', $target->id), [
            'role' => 'Worker',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_worker_cannot_reach_user_management(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_the_last_active_owner_cannot_be_demoted(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->put(route('admin.users.update', $owner->id), [
            'role' => 'Worker',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertTrue($owner->fresh()->hasRole('Owner'));
    }

    public function test_the_last_active_owner_rejection_is_actually_visible_on_the_page(): void
    {
        // Regression test: the guard was always correctly enforced server-side,
        // but layouts/master.blade.php had no @if(session('error')) block
        // anywhere, so the rejection reason was silently dropped - the page
        // just appeared to do nothing. Follows the same 302 redirect the
        // browser would, then checks the message is actually in the HTML.
        $owner = User::factory()->create();

        $this->actingAs($owner)->put(route('admin.users.update', $owner->id), [
            'role' => 'Worker',
            'is_active' => '1',
        ]);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('At least one active Owner must remain', false);
    }

    public function test_the_last_active_owner_cannot_be_deactivated(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->put(route('admin.users.update', $owner->id), [
            'role' => 'Owner',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertTrue($owner->fresh()->is_active);
    }

    public function test_an_owner_can_be_demoted_when_another_active_owner_remains(): void
    {
        $ownerOne = User::factory()->create();
        $ownerTwo = User::factory()->create();

        $response = $this->actingAs($ownerOne)->put(route('admin.users.update', $ownerTwo->id), [
            'role' => 'Worker',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasNoErrors();
        $this->assertFalse($ownerTwo->fresh()->hasRole('Owner'));
    }
}
