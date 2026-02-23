<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';
    protected $description = 'Устанавливает Telegram webhook для получения обновлений';

    public function handle()
    {
        $botToken = config('services.telegram-bot-api.token');
        $webhookUrl = env('TELEGRAM_WEBHOOK_URL');

        if (!$webhookUrl) {
            $this->error('Ошибка: TELEGRAM_WEBHOOK_URL не указан в .env');
            return 1;
        }

        $this->info('Установка Telegram webhook: ' . $webhookUrl);

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/setWebhook", [
                'url' => $webhookUrl,
            ]);

            if ($response->successful() && $response->json()['ok']) {
                $this->info('Webhook успешно установлен!');
                return 0;
            }

            $this->error('Ошибка установки webhook: ' . $response->body());
            return 1;
        } catch (\Exception $e) {
            $this->error('Исключение при установке webhook: ' . $e->getMessage());
            return 1;
        }
    }
}
