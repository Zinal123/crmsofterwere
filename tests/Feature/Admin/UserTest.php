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

    public function test_users_page_shows_a_toggle_reflecting_each_users_active_state(): void
    {
        // The status badge + separate Activate/Deactivate button were
        // replaced with a single toggle switch - assert structurally, via
        // the real checkbox's checked attribute, that an active user's
        // toggle is on and an inactive user's is off (not just that some
        // "active"-ish text appears somewhere on the page).
        $owner = \App\Models\User::factory()->create();
        $inactiveUser = \App\Models\User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));
        $response->assertOk();

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);

        $ownerToggle = $xpath->query('//input[@type="checkbox"][@aria-label="' . $owner->name . ' active status"]')->item(0);
        $inactiveToggle = $xpath->query('//input[@type="checkbox"][@aria-label="' . $inactiveUser->name . ' active status"]')->item(0);

        $this->assertNotNull($ownerToggle, 'Active-status toggle for the owner not found.');
        $this->assertTrue($ownerToggle->hasAttribute('checked'));
        $this->assertNotNull($inactiveToggle, 'Active-status toggle for the inactive user not found.');
        $this->assertFalse($inactiveToggle->hasAttribute('checked'));
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

    public function test_owner_can_edit_a_users_name_and_email(): void
    {
        $owner = User::factory()->create();
        $target = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

        $response = $this->actingAs($owner)->put(route('admin.users.update-profile', $target->id), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $target->refresh();
        $this->assertSame('New Name', $target->name);
        $this->assertSame('new@example.com', $target->email);
    }

    public function test_owner_can_reset_a_users_password_from_the_edit_modal(): void
    {
        $owner = User::factory()->create();
        $target = User::factory()->create();
        $originalHash = $target->password;

        $response = $this->actingAs($owner)->put(route('admin.users.update-profile', $target->id), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'NewTempPass123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $target->refresh();
        $this->assertNotSame($originalHash, $target->password);
        $this->assertTrue(Hash::check('NewTempPass123', $target->password));
    }

    public function test_leaving_the_password_field_blank_keeps_the_current_password(): void
    {
        $owner = User::factory()->create();
        $target = User::factory()->create();
        $originalHash = $target->password;

        $response = $this->actingAs($owner)->put(route('admin.users.update-profile', $target->id), [
            'name' => $target->name,
            'email' => $target->email,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame($originalHash, $target->fresh()->password);
    }

    public function test_editing_a_user_rejects_an_email_already_used_by_someone_else(): void
    {
        $owner = User::factory()->create();
        $target = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($owner)->putJson(route('admin.users.update-profile', $target->id), [
            'name' => $target->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_editing_a_user_can_keep_their_own_current_email(): void
    {
        $owner = User::factory()->create();
        $target = User::factory()->create(['email' => 'keepme@example.com']);

        $response = $this->actingAs($owner)->put(route('admin.users.update-profile', $target->id), [
            'name' => $target->name,
            'email' => 'keepme@example.com',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasNoErrors();
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
