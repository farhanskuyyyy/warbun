<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class OperationalNotification extends Notification
{
    public function __construct(public string $event, public int $entityId) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['event' => $this->event, 'entity_id' => $this->entityId];
    }
}
