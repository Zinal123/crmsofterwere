<?php

namespace Tests\Feature;

use App\Services\Integration\TallyVoucherXmlBuilder;
use Tests\TestCase;

class TallyVoucherXmlBuilderTest extends TestCase
{
    private function payload(): array
    {
        return [
            'invoice_id' => 1,
            'invoice_number' => 'INV-001',
            'invoice_date' => '2026-09-22',
            'customer' => ['name' => 'Test Customer'],
            'total_before_tax' => 50000.0,
            'total_with_tax' => 59000.0,
            'sgst_amount' => 4500.0,
            'cgst_amount' => 4500.0,
            'igst_amount' => 0.0,
            'line_items' => [
                [
                    'product_name' => 'Widget X',
                    'hsn' => '8456',
                    'unit' => 'Nos',
                    'quantity' => 1,
                    'rate' => 50000,
                    'gst_percent' => 18,
                    'gst_amount' => 9000,
                    'total_amount' => 59000,
                    'taxable_amount' => 50000.0,
                ],
            ],
        ];
    }

    private function configureLedgers(): void
    {
        config([
            'tally.ledgers.sales' => 'Sales Account',
            'tally.ledgers.cgst' => 'CGST',
            'tally.ledgers.sgst' => 'SGST',
            'tally.ledgers.igst' => 'IGST',
        ]);
    }

    public function test_it_builds_a_voucher_with_the_right_party_and_reference(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $this->assertSame('Sales', (string) $voucher['VCHTYPE']);
        $this->assertSame('SALES-1', (string) $voucher['REMOTEID']);
        $this->assertSame('INV-001', (string) $voucher->VOUCHERNUMBER);
        $this->assertSame('SALES-1', (string) $voucher->REFERENCE);
        $this->assertSame('Test Customer', (string) $voucher->PARTYLEDGERNAME);
    }

    public function test_ledger_entries_balance_to_zero(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $total = 0.0;
        foreach ($voucher->{'LEDGERENTRIES.LIST'} as $entry) {
            $total += (float) $entry->AMOUNT;
        }
        // The Sales ledger is posted inside each stock line.
        foreach ($voucher->{'ALLINVENTORYENTRIES.LIST'} as $item) {
            $total += (float) $item->{'ACCOUNTINGALLOCATIONS.LIST'}->AMOUNT;
        }

        $this->assertEqualsWithDelta(0.0, $total, 0.001);
    }

    public function test_it_includes_one_inventory_entry_per_line_item_with_the_real_product_name(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $inventoryEntries = $voucher->{'ALLINVENTORYENTRIES.LIST'};
        $this->assertCount(1, $inventoryEntries);
        $this->assertSame('Widget X', (string) $inventoryEntries[0]->STOCKITEMNAME);
        $this->assertSame('50000.00', (string) $inventoryEntries[0]->AMOUNT);
    }

    public function test_it_falls_back_to_quantity_times_rate_when_taxable_amount_is_missing(): void
    {
        $this->configureLedgers();

        $payload = $this->payload();
        unset($payload['line_items'][0]['taxable_amount']);
        $payload['line_items'][0]['quantity'] = 2;
        $payload['line_items'][0]['rate'] = 25000;

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($payload, 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $this->assertSame('50000.00', (string) $voucher->{'ALLINVENTORYENTRIES.LIST'}[0]->AMOUNT);
    }

    public function test_it_omits_the_igst_ledger_entry_when_the_amount_is_zero(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $ledgerNames = [];
        foreach ($voucher->{'LEDGERENTRIES.LIST'} as $entry) {
            $ledgerNames[] = (string) $entry->LEDGERNAME;
        }

        $this->assertContains('SGST', $ledgerNames);
        $this->assertNotContains('IGST', $ledgerNames);
    }
}
