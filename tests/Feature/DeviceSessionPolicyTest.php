<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Worker Kiosk is used on both company-owned shared tablets and personal
 * phones. A "Remember this device" checkbox at login distinguishes them:
 * unchecked (default) = untrusted/shared device, session dies after a short
 * idle period; checked = trusted personal device, no forced idle logout.
 */
class DeviceSessionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeOwner(): User
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $user->assignRole('Owner');

        return $user;
    }

    private function login(User $user, bool $remember = false): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => $remember ? '1' : null,
        ]);
    }

    public function test_login_without_remember_checkbox_is_treated_as_an_untrusted_device(): void
    {
        $user = $this->makeOwner();

        $this->login($user, remember: false);

        $this->assertFalse(session('device_trusted'));
    }

    public function test_login_with_remember_checkbox_is_treated_as_a_trusted_device(): void
    {
        $user = $this->makeOwner();

        $this->login($user, remember: true);

        $this->assertTrue(session('device_trusted'));
    }

    public function test_untrusted_device_is_logged_out_after_15_minutes_idle(): void
    {
        $user = $this->makeOwner();
        $this->login($user, remember: false);
        $this->get(route('root')); // establishes last_activity_at

        $this->travel(16)->minutes();
        $response = $this->get(route('root'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_untrusted_device_stays_logged_in_within_the_idle_window(): void
    {
        $user = $this->makeOwner();
        $this->login($user, remember: false);
        $this->get(route('root'));

        $this->travel(5)->minutes();
        $response = $this->get(route('root'));

        $response->assertOk();
        $this->assertAuthenticated();
    }

    public function test_trusted_device_survives_past_the_idle_window(): void
    {
        $user = $this->makeOwner();
        $this->login($user, remember: true);
        $this->get(route('root'));

        $this->travel(30)->minutes();
        $response = $this->get(route('root'));

        $response->assertOk();
        $this->assertAuthenticated();
    }
}
