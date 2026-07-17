<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The home page requires auth, so a guest gets redirected to login
     * rather than seeing a 200 (this app has no public-facing page).
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
