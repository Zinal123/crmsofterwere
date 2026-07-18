<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class JobDecisionNotification extends Notification
{
    public function __construct(public Job $job, public string $decision)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
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

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $title = $this->decision === 'approved' ? 'Job Approved' : 'Job Rejected';
        $body = $this->decision === 'approved'
            ? "Your job \"{$this->job->title}\" was approved."
            : "Your job \"{$this->job->title}\" was rejected: {$this->job->rejection_reason}";

        return (new WebPushMessage())
            ->title($title)
            ->icon('/favicon.ico')
            ->body($body)
            ->action('View Job', 'view_job')
            ->data(['job_id' => $this->job->id]);
    }
}
