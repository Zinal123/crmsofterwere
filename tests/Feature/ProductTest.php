<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_validation_failure_shows_a_visible_error_and_reopens_the_modal(): void
    {
        // Regression test: the server always rejected a blank name correctly
        // (422/redirect-back-with-errors), but the Blade view had no
        // @error markup at all, so a real user saw no indication anything
        // went wrong. Now asserts both the inline error text and the JS
        // that reopens the modal so the error is actually visible.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('product'))->post(route('productstore'), []);

        $response->assertRedirect(route('product'));
        $response->assertSessionHasErrors('name');

        $followUp = $this->actingAs($user)->get(route('product'));
        $followUp->assertSee('is-invalid', false);
        $followUp->assertSee('invalid-feedback', false);
        $followUp->assertSee("new bootstrap.Modal(document.getElementById('exampleModalgrid')).show();", false);
    }

    public function test_create_form_disables_the_submit_button_to_prevent_duplicate_submissions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee("this.querySelector('button[type=submit]').disabled = true;", false);
    }
}
