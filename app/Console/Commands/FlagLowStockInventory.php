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
        // Per-item thresholds can override the global default in either
        // direction, so this can't be a single DB-side comparison - filter
        // in PHP against each item's effective threshold instead.
        $newlyLow = Invetry::whereNull('low_stock_notified_at')
            ->get()
            ->filter(fn (Invetry $item) => $item->quantity < $item->effectiveLowStockThreshold());

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
            ->get()
            ->filter(fn (Invetry $item) => $item->quantity >= $item->effectiveLowStockThreshold());
        Invetry::whereIn('id', $restocked->pluck('id'))->update(['low_stock_notified_at' => null]);

        $this->info("Flagged {$newlyLow->count()} newly low-stock item(s), cleared {$restocked->count()} restocked item(s).");

        return self::SUCCESS;
    }
}
