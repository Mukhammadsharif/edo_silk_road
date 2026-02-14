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

    protected $userEmail;

    protected $message;
    protected $documentLink;

    /**
     * Create a new job instance.
     *
     * @param string $userEmail
     * @param string $message
     * @param string $documentLink
     * @return void
     */
    public function __construct(string $userEmail, string $message, string $documentLink)
    {
        $this->userEmail = $userEmail;
        $this->message = $message;
        $this->documentLink = $documentLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->onQueue('send_telegram');

        $telegramUsers = TelegramUser::where('user_identifier', $this->userEmail)
            ->whereNotNull('telegram_user_id')
            ->get();

        if ($telegramUsers->isNotEmpty()) {
            try {
                Notification::send($telegramUsers, new TelegramNotification($this->message, $this->documentLink));
                Log::info("Telegram notification queued for {$this->userEmail}", [
                    'chat_ids' => $telegramUsers->pluck('telegram_user_id')->toArray(),
                    'document_link' => $this->documentLink,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to queue Telegram notification for {$this->userEmail}: {$e->getMessage()}", [
                    'exception' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info("No Telegram account linked for user: {$this->userEmail}");
        }
    }
}
