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

    public function test_owner_can_update_a_product(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::create(['name' => 'Old Name', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'OLD-1']);

        $response = $this->actingAs($user)->put(route('product.update', $product->id), [
            'name' => 'New Laser Cutter Name',
            'rate' => 2000,
            'unit' => 'Pcs',
            'make' => 'NEW-2',
        ]);

        $response->assertRedirect(route('product'));
        $this->assertDatabaseHas('product', ['id' => $product->id, 'name' => 'New Laser Cutter Name', 'rate' => 2000, 'unit' => 'Pcs', 'make' => 'NEW-2']);
    }

    public function test_updating_a_product_requires_a_name(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::create(['name' => 'Old Name', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'OLD-1']);

        $response = $this->actingAs($user)->put(route('product.update', $product->id), ['name' => '']);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('product', ['id' => $product->id, 'name' => 'Old Name']);
    }

    public function test_product_list_page_has_an_edit_trigger_per_row(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::create(['name' => 'Editable Product', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'OLD-1']);

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#editProduct-' . $product->id . '"', false);
    }

    public function test_product_list_page_shows_bulk_select_checkboxes_and_bulk_bar(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::create(['name' => 'Bulk Candidate', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'OLD-1']);

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('class="form-check-input bulk-select-row" value="' . $product->id . '"', false);
        $response->assertSee('id="products-bulk-bar"', false);
        $response->assertSee(route('product.bulk-delete'), false);
    }

    public function test_owner_can_bulk_delete_products(): void
    {
        $user = User::factory()->create();
        $keep = \App\Models\Product::create(['name' => 'Keep Me', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'KEEP-1']);
        $deleteOne = \App\Models\Product::create(['name' => 'Delete Me 1', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'DEL-1']);
        $deleteTwo = \App\Models\Product::create(['name' => 'Delete Me 2', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'DEL-2']);

        $response = $this->actingAs($user)->post(route('product.bulk-delete'), [
            'ids' => [$deleteOne->id, $deleteTwo->id],
        ]);

        $response->assertRedirect(route('product'));
        $this->assertDatabaseMissing('product', ['id' => $deleteOne->id]);
        $this->assertDatabaseMissing('product', ['id' => $deleteTwo->id]);
        $this->assertDatabaseHas('product', ['id' => $keep->id]);
    }

    public function test_worker_cannot_bulk_delete_products(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $product = \App\Models\Product::create(['name' => 'Protected', 'rate' => 1000, 'unit' => 'Nos', 'make' => 'OLD-1']);

        $response = $this->actingAs($worker)->post(route('product.bulk-delete'), ['ids' => [$product->id]]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('product', ['id' => $product->id]);
    }
}
