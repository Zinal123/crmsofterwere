<?php

namespace Tests\Feature;

use App\Models\ClientAccount;
use App\Models\ClientMachine;
use App\Models\Invetry;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductInventoryMergeTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_creating_a_product_with_quantity_also_creates_its_inventory_row(): void
    {
        $response = $this->actingAs($this->owner())->post(route('productstore'), [
            'name' => 'Precision Nozzle',
            'rate' => 750,
            'quantity' => 40,
            'vandername' => 'Precitec India',
        ]);

        $response->assertRedirect(route('product'));
        $product = Product::where('name', 'Precision Nozzle')->firstOrFail();
        $this->assertDatabaseHas('invetry', [
            'product_id' => $product->id,
            'quantity' => 40,
            'vandername' => 'Precitec India',
        ]);
    }

    public function test_creating_a_product_can_link_its_inventory_to_a_real_vendor(): void
    {
        $vendor = Vendor::create(['name' => 'Precitec India', 'category' => 'Accessories', 'is_active' => true]);

        $response = $this->actingAs($this->owner())->post(route('productstore'), [
            'name' => 'Precision Nozzle',
            'quantity' => 40,
            'vendor_id' => $vendor->id,
        ]);

        $response->assertRedirect(route('product'));
        $product = Product::where('name', 'Precision Nozzle')->firstOrFail();
        $this->assertDatabaseHas('invetry', ['product_id' => $product->id, 'vendor_id' => $vendor->id]);
    }

    public function test_the_product_page_links_to_a_tracked_vendors_own_record(): void
    {
        $vendor = Vendor::create(['name' => 'Precitec India', 'category' => 'Accessories', 'is_active' => true]);
        $owner = $this->owner();
        $product = Product::factory()->create(['name' => 'Linked Vendor Product']);
        Invetry::factory()->create(['product_id' => $product->id, 'vendor_id' => $vendor->id, 'vandername' => null]);

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee(route('admin.vendors.show', $vendor->id), false);
        $response->assertSee('Precitec India');
    }

    public function test_the_product_page_still_shows_free_text_vendor_name_when_not_linked(): void
    {
        $owner = $this->owner();
        $product = Product::factory()->create(['name' => 'Untracked Vendor Product']);
        Invetry::factory()->create(['product_id' => $product->id, 'vendor_id' => null, 'vandername' => 'One-Off Supplier']);

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee('One-Off Supplier');
    }

    public function test_creating_a_product_without_quantity_creates_no_inventory_row(): void
    {
        $response = $this->actingAs($this->owner())->post(route('productstore'), [
            'name' => 'Fiber Laser Cutting Machine 2000W',
            'rate' => 900000,
        ]);

        $response->assertRedirect(route('product'));
        $product = Product::where('name', 'Fiber Laser Cutting Machine 2000W')->firstOrFail();
        $this->assertDatabaseMissing('invetry', ['product_id' => $product->id]);
    }

    public function test_product_page_shows_add_stock_button_only_for_untracked_products(): void
    {
        $owner = $this->owner();
        $trackedProduct = Product::factory()->create(['name' => 'Tracked Product']);
        Invetry::factory()->create(['product_id' => $trackedProduct->id]);
        $untrackedProduct = Product::factory()->create(['name' => 'Untracked Product']);

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee('data-product-id="' . $untrackedProduct->id . '"', false);
        $response->assertDontSee('data-product-id="' . $trackedProduct->id . '"', false);
    }

    public function test_product_page_shows_update_qty_button_only_for_tracked_products(): void
    {
        $owner = $this->owner();
        $trackedProduct = Product::factory()->create();
        $item = Invetry::factory()->create(['product_id' => $trackedProduct->id]);
        Product::factory()->create();

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee('open-update-qty-modal', false);
        $response->assertSee('data-id="' . $item->id . '"', false);
    }

    public function test_product_page_shows_reserved_and_available_stock_for_a_spare_part(): void
    {
        $owner = $this->owner();
        $part = Product::factory()->create(['name' => 'Ceramic Nozzle Ring', 'is_spare_part' => true]);
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 10]);
        $account = ClientAccount::factory()->create();
        $machine = ClientMachine::factory()->create(['client_account_id' => $account->id]);
        SparePartRequest::create([
            'client_machine_id' => $machine->id,
            'client_account_id' => $account->id,
            'product_id' => $part->id,
            'quantity' => 3,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Reserved: 3');
        $response->assertSee('Available: 7');
    }

    public function test_product_page_shows_low_stock_badge_using_the_effective_threshold(): void
    {
        $owner = $this->owner();
        $part = Product::factory()->create(['name' => 'Precitec Focusing Lens', 'is_spare_part' => true]);
        // 8 is above the global default (5) but below this item's custom threshold (10).
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 8, 'low_stock_threshold' => 10]);

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee('Low Stock');
    }

    public function test_sidebar_no_longer_shows_a_separate_inventory_menu_item(): void
    {
        $response = $this->actingAs($this->owner())->get(route('root'));

        $response->assertOk();
        $response->assertDontSee('Invtery managemnet');
    }
}
