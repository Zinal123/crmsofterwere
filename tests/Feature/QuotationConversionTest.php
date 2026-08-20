<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quation;
use App\Models\QuotationItem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationConversionTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_converting_a_quotation_creates_a_real_invoice_with_customer_and_line_items(): void
    {
        $owner = $this->owner();
        $product = Product::factory()->create();
        $quotation = Quation::create([
            'clientname' => 'Rajesh Fabricators',
            'companyaddress' => '12 Industrial Estate',
            'phone' => '9998887776',
            'email' => 'rajesh@example.com',
            'gstno' => '24AAIFO7039H1Z5',
            'date' => '2026-07-15',
        ]);
        QuotationItem::create(['quotation_id' => $quotation->id, 'description' => 'Fiber laser machine', 'amount' => 850000, 'product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($owner)->post(route('quation.convert-to-invoice', $quotation->id), [
            'state' => 'Gujarat',
        ]);

        $invoice = Invoice::where('invoice_id', 'QTN-' . $quotation->id)->firstOrFail();
        $response->assertRedirect(route('invoice.details', $invoice->id));
        $this->assertEqualsWithDelta(850000, $invoice->amount, 0.01);
        $this->assertDatabaseHas('customer', [
            'invoice_id' => $invoice->id,
            'name' => 'Rajesh Fabricators',
            'state' => 'Gujarat',
            'billinggst' => '24AAIFO7039H1Z5',
        ]);
        $this->assertDatabaseHas('invoiceproduct', [
            'invoice_id' => $invoice->id,
            'product_name' => $product->id,
            'total' => 850000,
        ]);
        $quotation->refresh();
        $this->assertSame('converted', $quotation->status);
        $this->assertSame($invoice->id, $quotation->invoice_id);
    }

    public function test_converting_skips_quotation_items_with_no_linked_product_and_reports_the_count(): void
    {
        $owner = $this->owner();
        $product = Product::factory()->create();
        $quotation = Quation::create(['clientname' => 'Mixed Items Client']);
        QuotationItem::create(['quotation_id' => $quotation->id, 'description' => 'Tracked line', 'amount' => 5000, 'product_id' => $product->id, 'quantity' => 1]);
        QuotationItem::create(['quotation_id' => $quotation->id, 'description' => 'Free-text only line, no product', 'amount' => 2000, 'product_id' => null]);

        $response = $this->actingAs($owner)->post(route('quation.convert-to-invoice', $quotation->id), [
            'state' => 'Gujarat',
        ]);

        $invoice = Invoice::where('invoice_id', 'QTN-' . $quotation->id)->firstOrFail();
        $response->assertRedirect(route('invoice.details', $invoice->id));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('1 quotation line', session('success'));
        // Only the tracked (product-linked) line became an invoice line item.
        $this->assertEqualsWithDelta(5000, $invoice->amount, 0.01);
    }

    public function test_converting_an_already_converted_quotation_is_rejected(): void
    {
        $owner = $this->owner();
        $quotation = Quation::create(['clientname' => 'Already Converted']);

        $this->actingAs($owner)->post(route('quation.convert-to-invoice', $quotation->id), ['state' => 'Gujarat']);
        $response = $this->actingAs($owner)->post(route('quation.convert-to-invoice', $quotation->id), ['state' => 'Gujarat']);

        $response->assertRedirect(route('listqutation'));
        $response->assertSessionHas('error');
        $this->assertSame(1, Invoice::where('invoice_id', 'QTN-' . $quotation->id)->count());
    }

    public function test_convert_requires_a_valid_state(): void
    {
        $owner = $this->owner();
        $quotation = Quation::create(['clientname' => 'No State Client']);

        $response = $this->actingAs($owner)->post(route('quation.convert-to-invoice', $quotation->id), [
            'state' => 'Not A Real State',
        ]);

        $response->assertSessionHasErrors('state');
        $this->assertDatabaseMissing('invoice', ['invoice_id' => 'QTN-' . $quotation->id]);
    }

    public function test_quotation_list_shows_convert_button_for_an_unconverted_quotation(): void
    {
        $owner = $this->owner();
        $quotation = Quation::create(['clientname' => 'Not Yet Converted']);

        $response = $this->actingAs($owner)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee(route('quation.convert-to-invoice', $quotation->id), false);
    }

    public function test_quotation_list_shows_view_invoice_link_for_an_already_converted_quotation(): void
    {
        $owner = $this->owner();
        $quotation = Quation::create(['clientname' => 'Converted Client']);
        $this->actingAs($owner)->post(route('quation.convert-to-invoice', $quotation->id), ['state' => 'Gujarat']);
        $invoice = Invoice::where('invoice_id', 'QTN-' . $quotation->id)->firstOrFail();

        $response = $this->actingAs($owner)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee(route('invoice.details', $invoice->id), false);
        $response->assertDontSee(route('quation.convert-to-invoice', $quotation->id), false);
    }

    public function test_worker_cannot_convert_a_quotation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $quotation = Quation::create(['clientname' => 'Blocked Client']);

        $response = $this->actingAs($worker)->post(route('quation.convert-to-invoice', $quotation->id), ['state' => 'Gujarat']);

        $response->assertStatus(403);
    }
}
