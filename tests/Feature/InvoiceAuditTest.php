<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Paidamount;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_details_page_shows_the_invoices_audit_trail(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $invoice = Invoice::factory()->create(['amount' => 50000, 'amountwithtax' => 59000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);
        $invoice->update(['paidamount' => 20000]);

        $response = $this->actingAs($owner)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee('paidamount');
        $response->assertSee('d-print-none', false);
    }

    public function test_invoice_details_history_includes_sub_record_events(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $invoice = Invoice::factory()->create(['amount' => 50000, 'amountwithtax' => 59000]);
        $customer = Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Rajasthan Metal Works', 'state' => 'Gujarat']);
        $product = Product::factory()->create();
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id, 'product_name' => $product->id]);
        Paidamount::create(['invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'paidAmount' => 20000]);

        $response = $this->actingAs($owner)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        // Each sub-record's own "created" audit entry should now appear
        // alongside the invoice's, not just the top-level Invoice record's -
        // one "created" row each for invoice, customer, invoiceproduct, paidamount.
        $this->assertEquals(4, substr_count($response->getContent(), '<td>created</td>'));
    }
}
