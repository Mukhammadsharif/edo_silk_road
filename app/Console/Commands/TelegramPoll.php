<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Request;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Poll Telegram updates (fallback for webhook)';

    public function handle()
    {
        $token = config('services.telegram-bot-api.token');

        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN is not configured.');
            return self::FAILURE;
        }

        $offset = 0;

        // ВАЖНО: по умолчанию Telegram getUpdates НЕ возвращает callback_query
        // (нажатия на inline-кнопки). Нужно явно перечислить интересующие типы.
        $allowedUpdates = ['message', 'callback_query', 'my_chat_member'];

        $this->info('Polling started...');

        while (true) {
            try {
                $response = Http::timeout(35)->get(
                    "https://api.telegram.org/bot{$token}/getUpdates",
                    [
                        'offset'          => $offset,
                        'timeout'         => 30,
                        'allowed_updates' => json_encode($allowedUpdates),
                    ]
                );

                if (!$response->successful()) {
                    Log::warning('telegram:poll getUpdates non-2xx', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    sleep(2);
                    continue;
                }

                $updates = $response->json('result', []);

                foreach ($updates as $update) {
                    try {
                        // Проксируем апдейт в TelegramWebhookController.
                        // Тот же код используется и для webhook, и для polling.
                        $request = Request::create(
                            '/telegram/webhook', 'POST',
                            [], [], [], [], json_encode($update)
                        );
                        $request->headers->set('Content-Type', 'application/json');

                        $controller = new TelegramWebhookController();
                        $controller->handle($request);
                    } catch (\Throwable $e) {
                        // Один битый update не должен ронять весь polling
                        Log::error('telegram:poll handler error', [
                            'update_id' => $update['update_id'] ?? null,
                            'error'     => $e->getMessage(),
                        ]);
                    }

                    $offset = ($update['update_id'] ?? 0) + 1;
                }

                if (empty($updates)) {
                    sleep(1);
                }

            } catch (\Throwable $e) {
                // Сетевые/таймаут ошибки — не падаем, просто ждём и повторяем
                Log::warning('telegram:poll exception', [
                    'error' => $e->getMessage(),
                ]);
                sleep(3);
            }
        }
    }
}
