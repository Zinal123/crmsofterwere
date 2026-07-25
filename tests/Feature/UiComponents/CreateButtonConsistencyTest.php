<?php

namespace Tests\Feature\UiComponents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateButtonConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private const REMOVED_BULK_DELETE_ONCLICK = 'deleteMultiple()';

    public function test_invoice_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('btn-success', false);
        $response->assertDontSee('btn-danger" href="' . route('invoice.create'), false);
    }

    // Inventory management was merged into the product page - the standalone
    // "Create Inventory" button no longer exists (stock is now added inline
    // per product row, or via the Create Product form's optional fields).

    public function test_product_create_button_uses_success_variant(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Create Product');
    }

    /**
     * The bulk-delete buttons (Invoices/Inventory/Product) and their shared
     * <x-ui.confirm-modal> were removed rather than "fixed": deleteMultiple()
     * only ever operated on a fake in-memory list.js array via Velzon's demo
     * invoiceslist.init.js, never a real backend call, and no page had actual
     * per-row checkboxes to select from - it could never have worked. See
     * git history for the investigation. This test now locks in their absence
     * instead of asserting their (non-functional) presence.
     */
    public function test_bulk_delete_buttons_are_absent(): void
    {
        $user = User::factory()->create();

        $invoiceResponse = $this->actingAs($user)->get(route('invoice'));
        $invoiceResponse->assertDontSee(self::REMOVED_BULK_DELETE_ONCLICK, false);

        // route('invoice.inventrylist') now just redirects into route('product') -
        // covered by the productResponse assertion below.
        $productResponse = $this->actingAs($user)->get(route('product'));
        $productResponse->assertDontSee(self::REMOVED_BULK_DELETE_ONCLICK, false);
    }
}
