<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_page_renders_the_branded_shell(): void
    {
        $response = $this->get(route('password.reset', 'some-token'));

        $response->assertOk();
        $response->assertSee('oms-auth-shell', false);
    }
}
