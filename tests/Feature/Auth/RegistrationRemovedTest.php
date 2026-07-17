<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_route_no_longer_exists(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_login_page_has_no_signup_link(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertDontSee('Signup');
    }
}
