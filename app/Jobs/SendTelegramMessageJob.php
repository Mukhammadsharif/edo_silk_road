<?php

namespace App\Jobs;

use App\Models\TelegramUser;
use App\Notifications\TelegramNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Идентификатор пользователя — username или email.
     * Совместимо со старыми записями (где в user_identifier хранится email)
     * и с новыми (где username).
     */
    protected string $userIdentifier;

    protected string $message;
    protected string $documentLink;

    public function __construct(string $userIdentifier, string $message, string $documentLink)
    {
        $this->userIdentifier = $userIdentifier;
        $this->message        = $message;
        $this->documentLink   = $documentLink;
    }

    public function handle(): void
    {
        $this->onQueue('send_telegram');

        $telegramUsers = TelegramUser::where('user_identifier', $this->userIdentifier)
            ->whereNotNull('telegram_user_id')
            ->get();

        if ($telegramUsers->isEmpty()) {
            Log::info("No Telegram account linked for identifier: {$this->userIdentifier}");
            return;
        }

        try {
            Notification::send(
                $telegramUsers,
                new TelegramNotification($this->message, $this->documentLink)
            );

            Log::info("Telegram notification queued for {$this->userIdentifier}", [
                'chat_ids'      => $telegramUsers->pluck('telegram_user_id')->toArray(),
                'document_link' => $this->documentLink,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to queue Telegram notification for {$this->userIdentifier}: {$e->getMessage()}", [
                'exception' => $e->getTraceAsString(),
            ]);
        }
    }
}
