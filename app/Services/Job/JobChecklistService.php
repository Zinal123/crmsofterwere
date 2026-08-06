<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobChecklistItem;
use App\Models\User;

class JobChecklistService
{
    public function addItem(Job $job, string $description): JobChecklistItem
    {
        return $job->checklistItems()->create(['description' => $description]);
    }

    public function toggleItem(JobChecklistItem $item, User $user): JobChecklistItem
    {
        if ($item->is_completed) {
            $item->update(['is_completed' => false, 'completed_by' => null, 'completed_at' => null]);
        } else {
            $item->update(['is_completed' => true, 'completed_by' => $user->id, 'completed_at' => now()]);
        }

        return $item;
    }
}
