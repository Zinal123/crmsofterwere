<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;

class JobDecisionNotification extends Notification
{
    public function __construct(public Job $job, public string $decision)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'decision' => $this->decision,
            'reason' => $this->decision === 'rejected' ? $this->job->rejection_reason : null,
        ];
    }
}
