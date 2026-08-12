<?php

namespace Tests\Feature\Quotation;

use App\Models\Quation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_inline_print_streams_a_pdf_disposition_inline(): void
    {
        $owner = User::factory()->create(); // Owner role by default → has quotations.download-pdf
        $id = $this->makeQuotation();

        $res = $this->actingAs($owner)->get(route('quation.print', $id));

        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
        $this->assertStringContainsString('inline', (string) $res->headers->get('content-disposition'));
    }

    public function test_print_requires_the_download_permission(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']); // no quotations.download-pdf
        $id = $this->makeQuotation();

        $this->actingAs($worker)->get(route('quation.print', $id))->assertForbidden();
    }

    // Mirrors how the existing QuotationTest PDF cases build a quotation row:
    // a Quation with product_id/bank set, plus one line item via items().
    private function makeQuotation(): int
    {
        $quotation = Quation::create([
            'product_id' => 1,
            'clientname' => 'Print Test Client',
            'companyname' => 'Print Test Company',
            'bank' => 1,
        ]);
        $quotation->items()->create(['description' => 'Machine unit', 'amount' => '250000']);

        return $quotation->id;
    }
}
