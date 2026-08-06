<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Notifications\JobOverdueNotification;
use Illuminate\Console\Command;

class FlagOverdueJobs extends Command
{
    protected $signature = 'jobs:flag-overdue';

    protected $description = 'Flag open jobs past their due date, notify the assigned worker, and clear the flag on jobs that are no longer overdue.';

    public function handle(): int
    {
        $newlyOverdue = Job::overdue()->whereNull('overdue_flagged_at')->get();

        foreach ($newlyOverdue as $job) {
            $job->update(['overdue_flagged_at' => now()]);
            $job->assignee?->notify(new JobOverdueNotification($job));
        }

        $unflagged = Job::whereNotNull('overdue_flagged_at')
            ->whereNotIn('id', Job::overdue()->pluck('id'))
            ->update(['overdue_flagged_at' => null]);

        $this->info("Flagged {$newlyOverdue->count()} newly overdue job(s), cleared {$unflagged} job(s) no longer overdue.");

        return self::SUCCESS;
    }
}
