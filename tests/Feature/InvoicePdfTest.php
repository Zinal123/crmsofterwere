<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_pdf_downloads_with_real_invoice_data(): void
    {
        $user = User::factory()->create();
        $part = Product::factory()->create(['name' => 'Fiber Laser Cutting Machine 1500W']);
        $invoice = Invoice::factory()->create([
            'invoice_id' => 'OMT/2026/007',
            'amount' => 500000,
            'amountwithtax' => 590000,
            'paidamount' => 250000,
            'remaining_amount' => 340000,
            'placesupply' => 'Gujarat',
        ]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'name' => 'Bhavya Laser Works', 'state' => 'Gujarat']);
        Invoiceproduct::factory()->create([
            'invoice_id' => $invoice->id,
            'product_name' => $part->id,
            'quantity' => 1,
            'rate' => 500000,
            'gst' => 18,
            'gstamount' => 90000,
            'totalamount' => 590000,
        ]);

        $response = $this->actingAs($user)->get(route('invoice.pdf', $invoice->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename=Invoice-OMT-2026-007.pdf');

        $text = strtolower($this->extractPdfText($response->getContent()));
        $this->assertStringContainsString('bhavya laser works', $text);
        $this->assertStringContainsString('fiber laser cutting machine 1500w', $text);
        $this->assertStringContainsString('omt/2026/007', $text);
    }

    public function test_invoice_pdf_page_returns_404_for_a_missing_invoice(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.pdf', 999999));

        $response->assertNotFound();
    }

    /**
     * dompdf compresses each content stream with zlib (FlateDecode) - pulls
     * every stream/endstream block out of the PDF and inflates the
     * flate-compressed ones back to plain text so tests can assert on what
     * actually ended up on the page. Same helper as QuotationTest.
     */
    private function extractPdfText(string $pdf): string
    {
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdf, $matches);

        $text = '';
        foreach ($matches[1] as $stream) {
            $inflated = @gzuncompress($stream);
            if ($inflated !== false) {
                $text .= $inflated;
            }
        }

        return $text;
    }
}
