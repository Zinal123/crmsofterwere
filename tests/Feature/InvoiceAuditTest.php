<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
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
}
