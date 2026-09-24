<?php

namespace App\Services\Integration;

class TallyVoucherXmlBuilder
{
    /**
     * Build the Tally XML envelope to import a Sales voucher from a
     * tally_sync_queue payload (see TallySyncQueueService::enqueueSalesInvoice()).
     *
     * Ledger entry signs (ISDEEMEDPOSITIVE / AMOUNT) follow Tally's
     * documented Sales-voucher-with-inventory convention, but have not yet
     * been confirmed against this project's real Tally server - see the
     * "Open questions" section of
     * docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md.
     * The one structural property that IS guaranteed regardless of sign
     * convention: all ALLLEDGERENTRIES.LIST amounts sum to zero (Tally
     * requires this for any voucher to balance).
     */
    public function buildSalesVoucher(array $payload, string $companyName): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        $envelope = $dom->createElement('ENVELOPE');
        $dom->appendChild($envelope);

        $header = $dom->createElement('HEADER');
        $header->appendChild($dom->createElement('TALLYREQUEST', 'Import Data'));
        $envelope->appendChild($header);

        $requestDesc = $dom->createElement('REQUESTDESC');
        $requestDesc->appendChild($dom->createElement('REPORTNAME', 'Vouchers'));
        $staticVariables = $dom->createElement('STATICVARIABLES');
        $staticVariables->appendChild($dom->createElement('SVCURRENTCOMPANY', htmlspecialchars($companyName, ENT_XML1)));
        $requestDesc->appendChild($staticVariables);

        $importData = $dom->createElement('IMPORTDATA');
        $importData->appendChild($requestDesc);

        $tallyMessage = $dom->createElement('TALLYMESSAGE');
        $tallyMessage->setAttribute('xmlns:UDF', 'TallyUDF');
        $tallyMessage->appendChild($this->buildVoucher($dom, $payload));

        $requestData = $dom->createElement('REQUESTDATA');
        $requestData->appendChild($tallyMessage);
        $importData->appendChild($requestData);

        $body = $dom->createElement('BODY');
        $body->appendChild($importData);
        $envelope->appendChild($body);

        return $dom->saveXML();
    }

    private function buildVoucher(\DOMDocument $dom, array $payload): \DOMElement
    {
        $voucher = $dom->createElement('VOUCHER');
        $voucher->setAttribute('VCHTYPE', 'Sales');
        $voucher->setAttribute('ACTION', 'Create');

        $voucher->appendChild($dom->createElement('DATE', $this->tallyDate($payload['invoice_date'])));
        $voucher->appendChild($dom->createElement('VOUCHERTYPENAME', 'Sales'));
        $voucher->appendChild($dom->createElement('VOUCHERNUMBER', htmlspecialchars((string) $payload['invoice_number'], ENT_XML1)));
        $voucher->appendChild($dom->createElement('REFERENCE', 'SALES-'.$payload['invoice_id']));
        $customerName = (string) ($payload['customer']['name'] ?? '');
        $voucher->appendChild($dom->createElement('PARTYLEDGERNAME', htmlspecialchars($customerName, ENT_XML1)));

        $totalWithTax = (float) $payload['total_with_tax'];
        $totalBeforeTax = (float) $payload['total_before_tax'];

        $voucher->appendChild($this->ledgerEntry($dom, $customerName, true, -$totalWithTax));
        $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.sales'), false, $totalBeforeTax));

        if ((float) $payload['sgst_amount'] > 0) {
            $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.sgst'), false, (float) $payload['sgst_amount']));
        }
        if ((float) $payload['cgst_amount'] > 0) {
            $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.cgst'), false, (float) $payload['cgst_amount']));
        }
        if ((float) $payload['igst_amount'] > 0) {
            $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.igst'), false, (float) $payload['igst_amount']));
        }

        foreach ($payload['line_items'] as $line) {
            $voucher->appendChild($this->inventoryEntry($dom, $line));
        }

        return $voucher;
    }

    private function ledgerEntry(\DOMDocument $dom, string $ledgerName, bool $isDeemedPositive, float $amount): \DOMElement
    {
        $entry = $dom->createElement('ALLLEDGERENTRIES.LIST');
        $entry->appendChild($dom->createElement('LEDGERNAME', htmlspecialchars($ledgerName, ENT_XML1)));
        $entry->appendChild($dom->createElement('ISDEEMEDPOSITIVE', $isDeemedPositive ? 'Yes' : 'No'));
        $entry->appendChild($dom->createElement('AMOUNT', number_format($amount, 2, '.', '')));

        return $entry;
    }

    private function inventoryEntry(\DOMDocument $dom, array $line): \DOMElement
    {
        $unit = htmlspecialchars((string) ($line['unit'] ?? ''), ENT_XML1);
        $quantity = (float) $line['quantity'];
        $rate = (float) $line['rate'];

        $entry = $dom->createElement('ALLINVENTORYENTRIES.LIST');
        $entry->appendChild($dom->createElement('STOCKITEMNAME', htmlspecialchars((string) $line['product_name'], ENT_XML1)));
        $entry->appendChild($dom->createElement('ISDEEMEDPOSITIVE', 'No'));
        $entry->appendChild($dom->createElement('RATE', number_format($rate, 2, '.', '').'/'.$unit));
        $entry->appendChild($dom->createElement('AMOUNT', number_format((float) $line['total_amount'], 2, '.', '')));

        $batchAllocation = $dom->createElement('BATCHALLOCATIONS.LIST');
        $batchAllocation->appendChild($dom->createElement('ACTUALQTY', number_format($quantity, 2, '.', '').' '.$unit));
        $batchAllocation->appendChild($dom->createElement('BILLEDQTY', number_format($quantity, 2, '.', '').' '.$unit));
        $entry->appendChild($batchAllocation);

        return $entry;
    }

    private function tallyDate(string $date): string
    {
        return date('Ymd', strtotime($date));
    }
}
