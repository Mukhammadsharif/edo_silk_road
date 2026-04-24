<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Request;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Poll Telegram updates (fallback for webhook)';

    public function handle()
    {
        $token = config('services.telegram-bot-api.token');
        $offset = 0;

        $this->info('Polling started...');

        while (true) {
            $response = Http::timeout(35)->get(
                "https://api.telegram.org/bot{$token}/getUpdates",
                ['offset' => $offset, 'timeout' => 30]
            );

            if (!$response->successful()) {
                sleep(2);
                continue;
            }

            $updates = $response->json('result', []);

            foreach ($updates as $update) {
                // Controller ga uzatish
                $request = Request::create(
                    '/telegram/webhook', 'POST',
                    [], [], [], [], json_encode($update)
                );
                $request->headers->set('Content-Type', 'application/json');

                $controller = new TelegramWebhookController();
                $controller->handle($request);

                $offset = $update['update_id'] + 1;
            }

            if (empty($updates)) {
                sleep(1);
            }
        }
    }
}
