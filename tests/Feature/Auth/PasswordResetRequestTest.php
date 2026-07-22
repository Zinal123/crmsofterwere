<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders_without_template_branding(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertDontSee('Velzon', false);
        $response->assertDontSee('Themesbrand', false);
        $response->assertSee('Forgot password?');
        $response->assertSee('type="submit"', false);
    }

    public function test_submitting_a_registered_email_sends_a_reset_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset-me@example.com']);

        $response = $this->post(route('password.email'), ['email' => 'reset-me@example.com']);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_submitting_an_unregistered_email_does_not_send_a_notification(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => 'nobody@example.com']);

        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }
}
