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

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_flags_and_notifies_owners_when_stock_drops_below_the_threshold(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $part = Product::factory()->create();
        $item = Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 2]);

        $this->artisan('inventory:flag-low-stock')->assertSuccessful();

        $this->assertNotNull($item->fresh()->low_stock_notified_at);
        Notification::assertSentTo($owner, LowStockNotification::class, fn ($n) => $n->item->id === $item->id);
    }

    public function test_does_not_flag_or_notify_when_stock_is_healthy(): void
    {
        Notification::fake();
        User::factory()->create()->assignRole('Owner');
        $part = Product::factory()->create();
        $item = Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 100]);

        $this->artisan('inventory:flag-low-stock')->assertSuccessful();

        $this->assertNull($item->fresh()->low_stock_notified_at);
        Notification::assertNothingSent();
    }

    public function test_running_twice_does_not_notify_again_while_still_low(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $part = Product::factory()->create();
        Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 2]);

        $this->artisan('inventory:flag-low-stock');
        $this->travel(1)->day();
        $this->artisan('inventory:flag-low-stock');

        Notification::assertSentToTimes($owner, LowStockNotification::class, 1);
    }

    public function test_clears_the_flag_and_can_notify_again_after_restock(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $part = Product::factory()->create();
        $item = Invetry::factory()->create(['product_id' => $part->id, 'quantity' => 2]);

        $this->artisan('inventory:flag-low-stock');
        $item->update(['quantity' => 50]);
        $this->artisan('inventory:flag-low-stock');
        $this->assertNull($item->fresh()->low_stock_notified_at);

        $item->update(['quantity' => 1]);
        $this->artisan('inventory:flag-low-stock');

        Notification::assertSentToTimes($owner, LowStockNotification::class, 2);
    }
}
