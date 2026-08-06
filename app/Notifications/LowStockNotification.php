<?php

namespace App\Notifications;

use App\Models\Invetry;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LowStockNotification extends Notification
{
    public function __construct(public Invetry $item)
    {
    }

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'invetry_id' => $this->item->id,
            'product_name' => $this->item->product->name ?? 'Unknown part',
            'quantity' => $this->item->quantity,
        ];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $name = $this->item->product->name ?? 'A part';

        return (new WebPushMessage())
            ->title('Low Stock')
            ->icon('/favicon.ico')
            ->body("{$name} is down to {$this->item->quantity} in stock.")
            ->action('View Inventory', 'view_inventory')
            ->data(['invetry_id' => $this->item->id]);
    }
}
