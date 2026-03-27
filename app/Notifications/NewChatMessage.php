<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewChatMessage extends Notification
{
    use Queueable;

    protected string $senderName;
    protected string $messageSnippet;
    protected string $senderType; // 'doctor' or 'user'

    /**
     * Create a new notification instance.
     */
    public function __construct(string $senderName, string $messageSnippet, string $senderType)
    {
        $this->senderName = $senderName;
        $this->messageSnippet = $messageSnippet;
        $this->senderType = $senderType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'رسالة جديدة من ' . $this->senderName,
            'body' => $this->messageSnippet ?: 'أرسل لك ملفاً',
            'type' => 'chat',
            'sender_type' => $this->senderType,
        ];
    }
}
