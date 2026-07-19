<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class JobAssignedNotification extends Notification
{
    public function __construct(public Job $job, public string $type)
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
            'decision' => $this->type,
        ];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $title = $this->type === 'reassigned' ? 'Job Reassigned To You' : 'Job Assigned To You';

        return (new WebPushMessage())
            ->title($title)
            ->icon('/favicon.ico')
            ->body("You've been assigned the job \"{$this->job->title}\".")
            ->action('View Job', 'view_job')
            ->data(['job_id' => $this->job->id]);
    }
}
