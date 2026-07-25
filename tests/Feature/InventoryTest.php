<?php

namespace Tests\Feature;

use App\Models\Invetry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_inventory_list_route_redirects_to_the_merged_product_page(): void
    {
        // Product and Inventory were merged into one page - this route is
        // kept only so old bookmarks/links to /inventrylist don't 404.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertRedirect(route('product'));
    }

    public function test_product_page_shows_stock_quantity_for_a_tracked_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Fiber Laser Cutting Machine']);
        Invetry::factory()->create(['product_id' => $product->id, 'quantity' => 10, 'vandername' => 'Acme Supplies']);

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Fiber Laser Cutting Machine');
        $response->assertSee('10');
        $response->assertSee('Acme Supplies');
    }

    public function test_product_page_shows_not_tracked_for_a_product_with_no_inventory(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['name' => 'Untracked Widget']);

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Untracked Widget');
        $response->assertSee('Not tracked');
    }

    public function test_inventrystore_creates_a_new_inventory_row(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('inventrystore'), [
            'product_id' => $product->id,
            'quantity' => 25,
            'vandername' => 'Test Vendor',
            'rate' => 500,
        ]);

        $response->assertRedirect(route('product'));
        $this->assertDatabaseHas('invetry', [
            'product_id' => $product->id,
            'quantity' => 25,
            'vandername' => 'Test Vendor',
        ]);
    }

    public function test_quantityupdate_subtracts_requested_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $item = Invetry::factory()->create(['product_id' => $product->id, 'quantity' => 20]);

        $response = $this->actingAs($user)->post(route('quantityupdate'), [
            'id' => $item->id,
            'quantity' => 5,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertEquals(15, $item->fresh()->quantity);
    }

    public function test_quantityupdate_rejects_a_reduction_that_would_go_negative(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $item = Invetry::factory()->create(['product_id' => $product->id, 'quantity' => 20]);

        $response = $this->actingAs($user)->postJson(route('quantityupdate'), [
            'id' => $item->id,
            'quantity' => 10000,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(20, $item->fresh()->quantity);
    }

    public function test_quantityupdate_rejects_a_negative_quantity_input(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $item = Invetry::factory()->create(['product_id' => $product->id, 'quantity' => 20]);

        $response = $this->actingAs($user)->postJson(route('quantityupdate'), [
            'id' => $item->id,
            'quantity' => -5,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(20, $item->fresh()->quantity);
    }

    public function test_inventrystore_rejects_a_missing_product_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('inventrystore'), [
            'quantity' => 10,
            'vandername' => 'No Product Vendor',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('invetry', ['vandername' => 'No Product Vendor']);
    }
}
