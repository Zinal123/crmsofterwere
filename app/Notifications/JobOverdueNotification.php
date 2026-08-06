<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class JobOverdueNotification extends Notification
{
    public function __construct(public Job $job)
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
            'due_date' => $this->job->due_date?->toDateString(),
        ];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Job Overdue')
            ->icon('/favicon.ico')
            ->body("\"{$this->job->title}\" is now overdue (was due {$this->job->due_date?->format('d M Y')}).")
            ->action('View Job', 'view_job')
            ->data(['job_id' => $this->job->id]);
    }
}
