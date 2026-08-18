<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_view_their_own_profile_page(): void
    {
        $user = User::factory()->create(['name' => 'Meet Patel', 'email' => 'meet@example.com']);
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk();
        $response->assertSee('My Profile');
        $response->assertSee('Meet Patel');
        $response->assertSee('meet@example.com');
        $response->assertSee('Personal Information');
        $response->assertSee('Change Password');
    }

    public function test_profile_is_reachable_by_a_worker_role(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        // Profile is auth-only (no permission gate) so every staff role reaches it.
        $this->actingAs($worker)->get(route('profile'))->assertOk();
    }

    public function test_worker_views_profile_inside_the_kiosk_shell_not_admin_chrome(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        // Worker's root route sends them to the kiosk specifically to avoid
        // ever seeing the admin sidebar/command-palette chrome - the Profile
        // page they reach from the kiosk topbar must stay inside that same
        // shell, not drop them back into the admin layout.
        $response = $this->actingAs($worker)->get(route('profile'));

        $response->assertOk();
        $response->assertSee('worker-kiosk', false);
        $response->assertDontSee('commandPalette');
        // The kiosk layout didn't yield a 'script' section until this fix -
        // without it the password-change form's fetch() handler never
        // attaches and the button falls back to a raw JSON page load.
        $response->assertSee('changePasswordForm', false);
    }

    public function test_password_change_returns_success_json(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass123')]);

        $response = $this->actingAs($user)->postJson(route('updatePassword', $user->id), [
            'current_password' => 'oldpass123',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertOk();
        $response->assertJson(['isSuccess' => true]);
        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpass123')]);

        $response = $this->actingAs($user)->postJson(route('updatePassword', $user->id), [
            'current_password' => 'wrongpass',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertOk();
        $response->assertJson(['isSuccess' => false]);
        $this->assertTrue(Hash::check('oldpass123', $user->fresh()->password));
    }
}
