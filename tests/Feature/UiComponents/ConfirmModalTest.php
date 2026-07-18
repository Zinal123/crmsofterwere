<?php

namespace Tests\Feature\UiComponents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_page_still_has_delete_modal_with_correct_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('id="deleteOrder"', false);
        $response->assertSee('id="delete-record"', false);
        $response->assertSee('id="deleteRecord-close"', false);
    }

    public function test_inventory_list_page_still_has_delete_modal_with_correct_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('id="deleteOrder"', false);
        $response->assertSee('id="delete-record"', false);
    }

    public function test_product_list_page_still_has_delete_modal_with_correct_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product'));

        $response->assertOk();
        $response->assertSee('id="deleteOrder"', false);
        $response->assertSee('id="delete-record"', false);
    }
}
