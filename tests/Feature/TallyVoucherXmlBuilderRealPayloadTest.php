<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Product;
use App\Services\Integration\TallySyncQueueService;
use App\Services\Integration\TallyVoucherXmlBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Builds the voucher from a payload produced by the REAL
 * TallySyncQueueService (not a hand-written fixture), so any mismatch
 * between what the queue stores and what the builder reads surfaces here.
 */
class TallyVoucherXmlBuilderRealPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_amount_uses_the_pre_tax_line_total_from_a_real_payload(): void
    {
        config([
            'tally.ledgers.sales' => 'Sales Account',
            'tally.ledgers.cgst' => 'CGST',
            'tally.ledgers.sgst' => 'SGST',
            'tally.ledgers.igst' => 'IGST',
        ]);

        $invoice = Invoice::create([
            'invoice_id' => 'INV-001',
            'date' => '2026-09-22',
            'totalamountbeforetax' => 50000,
            'amountwithtax' => 59000,
            'placesupply' => 'Gujarat',
        ]);
        Customer::create([
            'invoice_id' => $invoice->id,
            'name' => 'Test Customer',
            'state' => 'Gujarat',
        ]);
        $product = Product::factory()->create(['name' => 'Widget X']);
        Invoiceproduct::create([
            'invoice_id' => $invoice->id,
            'product_name' => $product->id,
            'hsn' => '8456',
            'unit' => 'Nos',
            'rate' => 50000,
            'quantity' => 1,
            'total' => 50000,
            'gst' => 18,
            'gstamount' => 9000,
            'totalamount' => 59000,
        ]);

        $queue = (new TallySyncQueueService())->enqueueSalesInvoice($invoice->id);

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($queue->payload, 'Oracle Machine Tech');
        $voucher = simplexml_load_string($xml)->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $inventoryEntries = $voucher->{'ALLINVENTORYENTRIES.LIST'};
        $this->assertCount(1, $inventoryEntries);
        $this->assertSame('50000.00', (string) $inventoryEntries[0]->AMOUNT);
        $this->assertNotSame('59000.00', (string) $inventoryEntries[0]->AMOUNT);
    }
}
