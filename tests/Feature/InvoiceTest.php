<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_renders_with_products_and_banks(): void
    {
        $user = User::factory()->create();
        // Pre-existing bug, preserved: the product dropdown renders
        // $p->product, but Product has no `product` column (only `name`) -
        // so the option text is always blank. Bank fields do render
        // correctly since $bank->bankname etc. are real columns.
        Product::factory()->create(['name' => 'Fiber Laser Cutting Machine']);
        Bank::create(['bankholdername' => 'Oracle Machine Tech', 'bankname' => 'HDFC Bank']);

        $response = $this->actingAs($user)->get(route('invoice.create'));

        $response->assertOk();
        $response->assertDontSee('Fiber Laser Cutting Machine');
        $response->assertSee('HDFC Bank');
    }

    public function test_getproduct_returns_product_list_json(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['name' => 'CO2 Laser Cutting Machine']);

        $response = $this->actingAs($user)->get(route('invoice.product'));

        $response->assertOk();
        $response->assertJson(['isSuccess' => true]);
        $response->assertJsonFragment(['name' => 'CO2 Laser Cutting Machine']);
    }

    public function test_getproductvalue1_returns_pricing_for_a_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['make' => 'FLC-1500', 'rate' => 850000, 'unit' => 'Nos']);

        $response = $this->actingAs($user)->get(route('invoice.product1', ['paymentType1' => $product->id]));

        $response->assertOk();
        $response->assertJson([
            'isSuccess' => true,
            'make' => 'FLC-1500',
            'unit' => 'Nos',
            'rate' => '850000',
        ]);
    }

    public function test_datatable_endpoint_returns_invoice_rows(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 100000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Rajasthan Metal Works']);

        $response = $this->actingAs($user)->get(route('invoice.data'));

        $response->assertOk();
        $response->assertJsonFragment(['recordsTotal' => 1]);
        $response->assertSee('Rajasthan Metal Works');
        $response->assertSee('Paid');
    }

    public function test_invoicestore_creates_invoice_customer_and_products(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('invoice.store'), [
            'invoice_id' => 'INV-TEST-001',
            'invoice_date' => '2026-07-17',
            'placesupply' => 'Gujarat',
            'billing_address_full_name' => 'Test Customer',
            'billing_state' => 'Gujarat',
            'order_summary_cart_total' => 50000,
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
        $response->assertJson(['isSuccess' => true]);
        $this->assertDatabaseHas('invoice', ['invoice_id' => 'INV-TEST-001']);
        $this->assertDatabaseHas('customer', ['name' => 'Test Customer']);
        $this->assertDatabaseHas('invoiceproduct', ['product_name' => $product->id, 'hsn' => '8456']);
    }

    public function test_invoicestore_fails_validation_without_placesupply(): void
    {
        $user = User::factory()->create();

        // postJson to match how the real frontend calls this (jQuery $.ajax
        // with dataType: 'json'), which is what makes Laravel return 422
        // JSON instead of a redirect-back-with-errors.
        $response = $this->actingAs($user)->postJson(route('invoice.store'), [
            'invoice_id' => 'INV-TEST-002',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('invoice', ['invoice_id' => 'INV-TEST-002']);
    }

    public function test_invoice_details_calculates_sgst_cgst_for_gujarat(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertViewHas('sgstamount', 118000 * 0.09);
        $response->assertViewHas('cgstamount', 118000 * 0.09);
        $response->assertViewHas('igsamount', 0);
    }

    public function test_invoice_details_calculates_igst_for_non_gujarat(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'amountwithtax' => 118000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Maharashtra']);

        $response = $this->actingAs($user)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertViewHas('igsamount', 118000 * 0.18);
        $response->assertViewHas('sgstamount', 0);
        $response->assertViewHas('cgstamount', 0);
    }

    public function test_updatepayment_updates_paid_and_remaining_amount(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amount' => 100000, 'paidamount' => 0, 'remaining_amount' => 100000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->post('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
            'paidAmount' => 40000,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertEquals(40000, $invoice->fresh()->paidamount);
        $this->assertEquals(60000, $invoice->fresh()->remaining_amount);
        $this->assertDatabaseHas('paidamount', ['invoice_id' => $invoice->id, 'paidAmount' => 40000]);
    }

    public function test_paymenthistry_page_renders_with_seeded_payments(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Precision CNC Works']);

        $this->actingAs($user)->post('/update-payment', [
            'id' => $invoice->id,
            'customer_id' => Customer::first()->id,
            'paidAmount' => 25000,
        ]);

        $response = $this->actingAs($user)->get(route('invoice.histry'));

        $response->assertOk();
        $response->assertSee('Precision CNC Works');
    }
}
