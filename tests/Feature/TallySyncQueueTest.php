<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_invoice_enqueues_a_pending_tally_sales_voucher(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('invoice.store'), [
            'invoice_id' => 'INV-TALLY-001',
            'invoice_date' => '2026-09-22',
            'placesupply' => 'Gujarat',
            'billing_address_full_name' => 'Tally Test Customer',
            'billing_state' => 'Gujarat',
            'shipping_state' => 'Gujarat',
            'order_summary_cart_total' => 59000,
            'order_summary_cart_amount' => 50000,
            'new_product_obj' => [
                [
                    'product_name' => $product->id,
                    'hsn' => '8456',
                    'unit' => 'Nos',
                    'product_rate' => 50000,
                    'product_qty' => 1,
                    'product_price' => 50000,
                    'gst' => 18,
                    'withtax' => 9000,
                    'total' => 59000,
                ],
            ],
        ]);

        $response->assertOk();

        $invoiceId = \App\Models\Invoice::where('invoice_id', 'INV-TALLY-001')->firstOrFail()->id;

        $this->assertDatabaseHas('tally_sync_queue', [
            'reference_no' => 'SALES-'.$invoiceId,
            'voucher_type' => 'sales',
            'status' => 'pending',
        ]);
    }
}
