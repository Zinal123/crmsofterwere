<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class FlagOverdueJobs extends Command
{
    protected $signature = 'jobs:flag-overdue';

    protected $description = 'Flag open jobs past their due date, and clear the flag on jobs that are no longer overdue.';

    public function handle(): int
    {
        $newlyFlagged = Job::overdue()
            ->whereNull('overdue_flagged_at')
            ->update(['overdue_flagged_at' => now()]);

        $unflagged = Job::whereNotNull('overdue_flagged_at')
            ->whereNotIn('id', Job::overdue()->pluck('id'))
            ->update(['overdue_flagged_at' => null]);

        $this->info("Flagged {$newlyFlagged} newly overdue job(s), cleared {$unflagged} job(s) no longer overdue.");

        return self::SUCCESS;
    }
}
