<?php

namespace Tests\Feature\UiComponents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateButtonConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('btn-success', false);
        $response->assertDontSee('btn-danger" href="' . route('invoice.create'), false);
    }

    public function test_inventory_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('Create Inventry');
    }

    public function test_product_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Create Product');
    }

    public function test_bulk_delete_buttons_have_aria_label(): void
    {
        $user = User::factory()->create();

        $invoiceResponse = $this->actingAs($user)->get(route('invoice'));
        $invoiceResponse->assertSee('aria-label="Delete selected invoices"', false);

        $inventoryResponse = $this->actingAs($user)->get(route('invoice.inventrylist'));
        $inventoryResponse->assertSee('aria-label="Delete selected items"', false);

        $productResponse = $this->actingAs($user)->get(route('product'));
        $productResponse->assertSee('aria-label="Delete selected products"', false);
    }
}
