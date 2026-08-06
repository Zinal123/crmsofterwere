<?php

namespace App\Console\Commands;

use App\Models\Invetry;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;

class FlagLowStockInventory extends Command
{
    protected $signature = 'inventory:flag-low-stock';

    protected $description = 'Notify Owners about inventory items that have dropped below the low-stock threshold, and clear the flag once restocked.';

    public function handle(): int
    {
        $newlyLow = Invetry::where('quantity', '<', Invetry::LOW_STOCK_THRESHOLD)
            ->whereNull('low_stock_notified_at')
            ->get();

        if ($newlyLow->isNotEmpty()) {
            $owners = User::role('Owner')->get();

            foreach ($newlyLow as $item) {
                $item->update(['low_stock_notified_at' => now()]);
                foreach ($owners as $owner) {
                    $owner->notify(new LowStockNotification($item));
                }
            }
        }

        $restocked = Invetry::whereNotNull('low_stock_notified_at')
            ->where('quantity', '>=', Invetry::LOW_STOCK_THRESHOLD)
            ->update(['low_stock_notified_at' => null]);

        $this->info("Flagged {$newlyLow->count()} newly low-stock item(s), cleared {$restocked} restocked item(s).");

        return self::SUCCESS;
    }
}
