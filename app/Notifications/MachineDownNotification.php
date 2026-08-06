<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class MachineDownNotification extends Notification
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
            'machine_id' => $this->job->machine_id,
            'reported_by' => $this->job->creator?->name,
        ];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Machine Down')
            ->icon('/favicon.ico')
            ->body("{$this->job->creator?->name} flagged \"{$this->job->machine?->name}\" as down.")
            ->action('View Job', 'view_job')
            ->data(['job_id' => $this->job->id]);
    }
}
