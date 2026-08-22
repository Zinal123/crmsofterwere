<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConfirmDialogConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_product_delete_uses_the_shared_confirm_modal_not_a_browser_popup(): void
    {
        Product::factory()->create();

        $response = $this->actingAs($this->owner())->get(route('product'));

        $response->assertOk();
        $response->assertSee('data-confirm-delete', false);
        $response->assertSee('id="deleteOrder"', false);
        $response->assertDontSee("onclick=\"return confirm('Delete this product?", false);
    }

    public function test_vendor_delete_uses_the_shared_confirm_modal_not_a_browser_popup(): void
    {
        Vendor::create(['name' => 'Test Vendor', 'category' => 'Raw Material']);

        $response = $this->actingAs($this->owner())->get(route('admin.vendors.index'));

        $response->assertOk();
        $response->assertSee('data-confirm-delete', false);
        $response->assertSee('id="deleteOrder"', false);
        $response->assertDontSee("onsubmit=\"return confirm('Delete this vendor?", false);
    }

    public function test_quotation_delete_uses_the_shared_confirm_modal_not_a_browser_popup(): void
    {
        $response = $this->actingAs($this->owner())->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee('data-confirm-delete', false);
        $response->assertSee('id="deleteOrder"', false);
        $response->assertDontSee("onclick=\"return confirm('Delete this quotation?", false);
    }

    public function test_role_delete_uses_the_shared_confirm_modal_not_a_browser_popup(): void
    {
        $this->owner();
        Role::create(['name' => 'Test Role', 'guard_name' => 'web']);

        $response = $this->actingAs(User::role('Owner')->first())->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('data-confirm-delete', false);
        $response->assertSee('id="deleteOrder"', false);
        $response->assertDontSee("onsubmit=\"return confirm('Delete this role?", false);
    }

    public function test_invoice_bulk_delete_uses_the_shared_confirm_modal_not_a_browser_popup(): void
    {
        $invoice = Invoice::factory()->create();
        Customer::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($this->owner())->get(route('invoice'));

        $response->assertOk();
        $response->assertSee('data-confirm-delete', false);
        $response->assertSee('id="deleteOrder"', false);
    }
}
