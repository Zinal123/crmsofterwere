<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\TallySyncQueue;
use App\Services\Integration\TallySyncQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncQueueServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_enqueues_a_sales_voucher_with_gujarat_customer_as_sgst_cgst_split(): void
    {
        config(['tally.company.name' => 'Oracle Machine Tech', 'tally.company.gstin' => '24AAAAA0000A1Z5', 'tally.company.state' => 'Gujarat']);

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
            'address' => '123 Main St',
            'state' => 'Gujarat',
            'billinggst' => '24BBBBB1111B1Z5',
        ]);
        Invoiceproduct::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Widget',
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

        $this->assertSame('sales', $queue->voucher_type);
        $this->assertSame('pending', $queue->status);
        $this->assertSame('SALES-'.$invoice->id, $queue->reference_no);
        $this->assertSame('App\\Models\\Invoice', $queue->source_type);
        $this->assertSame($invoice->id, $queue->source_id);
        $this->assertSame('Test Customer', $queue->payload['customer']['name']);
        $this->assertSame('Oracle Machine Tech', $queue->payload['company']['name']);
        $this->assertSame(4500.0, $queue->payload['sgst_amount']);
        $this->assertSame(4500.0, $queue->payload['cgst_amount']);
        $this->assertSame(0.0, $queue->payload['igst_amount']);
        $this->assertCount(1, $queue->payload['line_items']);
        $this->assertSame('8456', $queue->payload['line_items'][0]['hsn']);

        $this->assertDatabaseHas('tally_sync_queue', ['reference_no' => 'SALES-'.$invoice->id]);
    }

    public function test_it_splits_out_of_state_customer_as_igst_only(): void
    {
        $invoice = Invoice::create(['invoice_id' => 'INV-002', 'date' => '2026-09-22', 'totalamountbeforetax' => 1000, 'amountwithtax' => 1180, 'placesupply' => 'Maharashtra']);
        Customer::create(['invoice_id' => $invoice->id, 'name' => 'Out Of State', 'state' => 'Maharashtra']);
        Invoiceproduct::create(['invoice_id' => $invoice->id, 'product_name' => 'Widget', 'gst' => 18, 'gstamount' => 180, 'totalamount' => 1180]);

        $queue = (new TallySyncQueueService())->enqueueSalesInvoice($invoice->id);

        $this->assertSame(0.0, $queue->payload['sgst_amount']);
        $this->assertSame(0.0, $queue->payload['cgst_amount']);
        $this->assertSame(180.0, $queue->payload['igst_amount']);
    }
}
