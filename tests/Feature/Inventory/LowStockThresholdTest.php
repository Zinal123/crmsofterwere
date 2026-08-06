<?php

namespace Tests\Feature\Inventory;

use App\Models\Invetry;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LowStockThresholdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_effective_threshold_falls_back_to_the_global_default_when_unset(): void
    {
        $item = Invetry::factory()->create(['low_stock_threshold' => null]);

        $this->assertSame(Invetry::LOW_STOCK_THRESHOLD, $item->effectiveLowStockThreshold());
    }

    public function test_effective_threshold_uses_the_per_item_override_when_set(): void
    {
        $item = Invetry::factory()->create(['low_stock_threshold' => 20]);

        $this->assertSame(20, $item->effectiveLowStockThreshold());
    }

    public function test_owner_can_set_a_custom_threshold(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $item = Invetry::factory()->create(['low_stock_threshold' => null]);

        $response = $this->actingAs($owner)->post(route('inventory.set-low-stock-threshold', $item->id), [
            'low_stock_threshold' => 15,
        ]);

        $response->assertRedirect();
        $this->assertSame(15, $item->fresh()->low_stock_threshold);
    }

    public function test_low_stock_command_respects_a_higher_custom_threshold(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $part = Product::factory()->create();
        // 8 is above the global default (5) but below this item's custom threshold (10).
        $item = Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 8, 'low_stock_threshold' => 10]);

        $this->artisan('inventory:flag-low-stock');

        $this->assertNotNull($item->fresh()->low_stock_notified_at);
        Notification::assertSentTo($owner, LowStockNotification::class);
    }

    public function test_low_stock_command_respects_a_lower_custom_threshold(): void
    {
        Notification::fake();
        User::factory()->create()->assignRole('Owner');
        $part = Product::factory()->create();
        // 3 is below the global default (5) but above this item's custom threshold (2).
        $item = Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 3, 'low_stock_threshold' => 2]);

        $this->artisan('inventory:flag-low-stock');

        $this->assertNull($item->fresh()->low_stock_notified_at);
        Notification::assertNothingSent();
    }
}
