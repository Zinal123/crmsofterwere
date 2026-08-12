<?php
namespace App\Services\Job;

use App\Models\ChecklistTemplate;
use App\Models\Job;

class ChecklistTemplateService
{
    public function applyToJob(ChecklistTemplate $template, Job $job): int
    {
        $items = $template->items; // ordered by position

        return \Illuminate\Support\Facades\DB::transaction(function () use ($items, $job) {
            foreach ($items as $item) {
                $job->checklistItems()->create(['description' => $item->description]);
            }

            return $items->count();
        });
    }
}
