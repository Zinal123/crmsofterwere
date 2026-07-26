<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TicketStatusChangedNotification extends Notification
{
    public function __construct(public Ticket $ticket)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'status' => $this->ticket->status,
        ];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $label = str_replace('_', ' ', $this->ticket->status);

        return (new WebPushMessage())
            ->title('Ticket Update')
            ->icon('/favicon.ico')
            ->body("Your ticket #{$this->ticket->id} is now " . ucfirst($label) . '.')
            ->action('View Ticket', 'view_ticket')
            ->data(['ticket_id' => $this->ticket->id]);
    }
}
