<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class MessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $senderName,
        protected string $subject,
        protected string $message,
        protected string $url,
        protected ?int $messageId = null
    )
    {
    }

    public function via($notifiable)
    {
        $channels = ['database', 'broadcast'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Nouveau message reçu',
            'message' => $this->message,
            'url' => $this->url,
            'icon' => 'fas fa-envelope',
            'entity_type' => 'message',
            'entity_id' => $this->messageId,
            'entity_name' => $this->subject,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view('emails.message-received', [
                'senderName' => $this->senderName,
                'subject' => $this->subject,
                'content' => $this->message,
                'url' => $this->url,
                'notifiable' => $notifiable,
            ])
            ->subject('Nouveau message de ' . $this->senderName);
    }
}
