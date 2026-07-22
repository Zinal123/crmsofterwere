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

    public function test_inventry_page_renders_with_seeded_products(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Fiber Laser Cutting Machine']);
        Invetry::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('Fiber Laser Cutting Machine');
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

        $response->assertRedirect(route('invoice.inventrylist'));
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
