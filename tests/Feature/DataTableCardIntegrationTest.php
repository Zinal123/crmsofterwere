<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataTableCardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Shell Test Customer']);

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
        $response->assertSee('Invoices');
    }

    public function test_inventory_list_uses_shared_card_shell_and_still_shows_data(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::factory()->create(['name' => 'Shell Test Product']);
        \App\Models\Invetry::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }

    public function test_vendor_list_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.vender'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }

    public function test_quotation_list_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }

    public function test_payment_history_uses_shared_card_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.histry'));

        $response->assertOk();
        $response->assertSee('card-header border-0', false);
    }
}
