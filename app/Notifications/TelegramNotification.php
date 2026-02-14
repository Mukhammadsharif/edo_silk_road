<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Facades\Log;

class TelegramNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;
    protected $documentLink;

    public function __construct($message, $documentLink = null)
    {
        $this->message = $message;
        $this->documentLink = $documentLink;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $telegramMessage = TelegramMessage::create()
            ->to($notifiable->telegram_user_id)
            ->content("<b>{$this->message}</b>")
            ->parseMode('HTML');

        if ($this->documentLink) {
            $telegramMessage->button('View Document', $this->documentLink);
        } else {
            Log::warning('No document link provided for Telegram notification', [
                'chat_id' => $notifiable->telegram_user_id,
                'message' => $this->message,
            ]);
        }

        Log::debug('Sending Telegram notification', [
            'chat_id' => $notifiable->telegram_user_id,
            'message' => $this->message,
            'document_link' => $this->documentLink,
        ]);

        return $telegramMessage;
    }
}
