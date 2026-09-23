<?php

namespace App\Services\Integration;

use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\TallySyncQueue;
use App\Support\IndianStates;

class TallySyncQueueService
{
    /** Snapshot an invoice into a pending Tally Sales-voucher queue row. */
    public function enqueueSalesInvoice(int $invoiceId): TallySyncQueue
    {
        $invoice = Invoice::with('customer')->findOrFail($invoiceId);
        $customer = $invoice->customer;
        $lineItems = Invoiceproduct::where('invoice_id', $invoiceId)->get();

        $totalGst = (float) $lineItems->sum('gstamount');
        $isHomeState = $customer?->state === IndianStates::HOME_STATE;

        $payload = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_id,
            'invoice_date' => $invoice->date,
            'company' => [
                'name' => config('tally.company.name'),
                'gstin' => config('tally.company.gstin'),
                'state' => config('tally.company.state'),
            ],
            'customer' => [
                'name' => $customer?->name,
                'address' => $customer?->address,
                'state' => $customer?->state,
                'gstin' => $customer?->billinggst,
                'pan' => $customer?->billingpan,
            ],
            'total_before_tax' => (float) $invoice->totalamountbeforetax,
            'total_with_tax' => (float) $invoice->amountwithtax,
            'sgst_amount' => $isHomeState ? round($totalGst / 2, 2) : 0.0,
            'cgst_amount' => $isHomeState ? round($totalGst / 2, 2) : 0.0,
            'igst_amount' => $isHomeState ? 0.0 : round($totalGst, 2),
            'line_items' => $lineItems->map(fn (Invoiceproduct $line) => [
                'product_name' => $line->product_name,
                'hsn' => $line->hsn,
                'unit' => $line->unit,
                'quantity' => $line->quantity,
                'rate' => $line->rate,
                'gst_percent' => $line->gst,
                'gst_amount' => $line->gstamount,
                'total_amount' => $line->totalamount,
            ])->values()->all(),
        ];

        return TallySyncQueue::create([
            'source_type' => Invoice::class,
            'source_id' => $invoice->id,
            'voucher_type' => 'sales',
            'reference_no' => 'SALES-'.$invoice->id,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }
}
