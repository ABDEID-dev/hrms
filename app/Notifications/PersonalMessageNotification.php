<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PersonalMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Message $message,
        private readonly ?User $sender = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $senderName = $this->sender?->employee_full_name ?: $this->sender?->name ?: __('System');
        $senderEmployeeId = $this->sender?->employee_id ?: $this->message->employee_id;

        return [
            'employee_id' => $senderEmployeeId,
            'user' => $senderName,
            'message' => $this->message->text,
            'message_id' => $this->message->id,
            'type' => 'personal_message',
        ];
    }
}
