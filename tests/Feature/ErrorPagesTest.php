<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_uses_the_styled_template(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/this-route-does-not-exist-anywhere');

        $response->assertStatus(404);
        $response->assertSee('Sorry, Page not Found');
        $response->assertSee('Back to home');
    }

    public function test_404_page_footer_has_no_leftover_template_branding(): void
    {
        // Every other auth-styled page had already been migrated off the
        // "Velzon. Crafted by Themesbrand" template footer - this one was
        // missed, found during the redesign audit.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/this-route-does-not-exist-anywhere');

        $response->assertStatus(404);
        $response->assertDontSee('Velzon', false);
        $response->assertDontSee('Themesbrand', false);
        $response->assertSee('Oracle Machine Tech');
    }

    public function test_403_page_uses_the_styled_template(): void
    {
        $worker = User::factory()->create();
        Role::findOrCreate('Worker');
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('product'));

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Back to home');
    }
}
