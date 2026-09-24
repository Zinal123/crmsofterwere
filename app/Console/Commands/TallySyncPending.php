<?php

namespace App\Console\Commands;

use App\Models\TallySyncQueue;
use App\Services\Integration\TallyPostingService;
use Illuminate\Console\Command;

class TallySyncPending extends Command
{
    protected $signature = 'tally:sync-pending';

    protected $description = 'Retry posting pending and under-cap failed tally_sync_queue rows to Tally.';

    public function handle(TallyPostingService $service): int
    {
        $rows = TallySyncQueue::whereIn('status', ['pending', 'failed'])->get();

        foreach ($rows as $row) {
            $service->attempt($row);
        }

        $this->info("Processed {$rows->count()} Tally sync queue row(s).");

        return self::SUCCESS;
    }
}
