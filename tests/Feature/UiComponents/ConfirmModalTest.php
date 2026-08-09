<?php

namespace Tests\Feature\UiComponents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmModalTest extends TestCase
{
    use RefreshDatabase;

    private const REMOVED_DELETE_MODAL_ID = 'id="deleteOrder"';

    /**
     * <x-ui.confirm-modal> (#deleteOrder / #delete-record / #deleteRecord-close)
     * was removed from Invoices/Inventory/Product: nothing on any of these
     * pages ever opened it (no data-bs-target="#deleteOrder" trigger existed;
     * single-item delete is a plain unconfirmed GET link, bulk-delete was
     * removed separately - see CreateButtonConsistencyTest). It was dead
     * markup on all three pages since it was first added. This test now
     * locks in its absence.
     *
     * Product was later re-added deliberately (see ConfirmDialogConsistencyTest)
     * once the component became a real, wired data-confirm-delete trigger
     * instead of dead markup - it's no longer covered by this "stays absent"
     * check.
     */
    public function test_invoice_list_page_has_no_dead_delete_modal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertDontSee(self::REMOVED_DELETE_MODAL_ID, false);
    }
}
