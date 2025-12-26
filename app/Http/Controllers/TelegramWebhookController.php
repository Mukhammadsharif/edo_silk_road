<?php
//
//namespace App\Http\Controllers;
//
//use App\Models\TelegramUser;
//use App\Models\User;
//use App\Notifications\TelegramNotification;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\Notification;
//
//class TelegramWebhookController extends Controller
//{
//    public function handle(Request $request)
//    {
//        try {
//            Log::info('Telegram Webhook Update:', $request->all());
//
//            $update = $request->all();
//
//            // Extract chat_id from message or my_chat_member
//            $chatId = $update['message']['chat']['id'] ?? $update['my_chat_member']['chat']['id'] ?? null;
//            $username = $update['message']['from']['username'] ?? $update['my_chat_member']['from']['username'] ?? null;
//
//            if (!$chatId) {
//                Log::error('No chat ID found in update: ' . json_encode($update));
//                return response()->json(['status' => 'no chat_id'], 200);
//            }
//
//            // Handle my_chat_member updates
//            if (isset($update['my_chat_member'])) {
//                $newStatus = $update['my_chat_member']['new_chat_member']['status'];
//                Log::info("Chat member status changed: chat_id={$chatId}, status={$newStatus}");
//
//                if ($newStatus === 'kicked') {
//                    TelegramUser::where('telegram_user_id', $chatId)->delete();
//                    Log::info("Bot was kicked from chat_id={$chatId}");
//                }
//
//                return response()->json(['status' => 'success'], 200);
//            }
//
//            // Handle message updates
//            $text = $update['message']['text'] ?? null;
//
//            // Handle /start command
//            if ($text === '/start') {
//                TelegramUser::updateOrCreate(
//                    ['telegram_user_id' => $chatId],
//                    [
//                        'telegram_user_id' => $chatId,
//                        'telegram_username' => $username,
//                        'status' => 'waiting_for_email', // New status to track email input
//                        'user_identifier' => null,
//                    ]
//                );
//                $this->sendTelegramMessage($chatId, 'Добро пожаловать! Пожалуйста, отправьте ваш email для привязки аккаунта.');
//                return response()->json(['status' => 'success'], 200);
//            }
//
//            // Handle /help command
//            if ($text === '/help') {
//                $this->sendTelegramMessage($chatId, 'Доступные команды:\n/start - Начать процесс привязки\n/help - Показать эту справку');
//                return response()->json(['status' => 'success'], 200);
//            }
//
//            // Handle email input (non-command message)
//            $telegramUser = TelegramUser::where('telegram_user_id', $chatId)->first();
//
//            if ($telegramUser && $telegramUser->status === 'waiting_for_email' && !str_starts_with($text, '/')) {
//                $userIdentifier = trim($text);
//
//                $user = User::where('email', $userIdentifier)->first();
//
//                if (!$user) {
//                    $this->sendTelegramMessage($chatId, 'Email не найден. Проверьте и попробуйте снова.');
//                    return response()->json(['status' => 'invalid_email'], 200);
//                }
//
//                $telegramUser->update([
//                    'user_identifier' => $userIdentifier,
//                    'status' => 'linked', // Update status to linked
//                ]);
//
//                $this->sendTelegramMessage($chatId, 'Успех! Ваш Telegram аккаунт привязан.');
//                return response()->json(['status' => 'linked'], 200);
//            }
//
//            // Default response for unrecognized input
//            $this->sendTelegramMessage($chatId, 'Используйте /start для начала или /help для справки.');
//            return response()->json(['status' => 'success'], 200);
//        } catch (\Exception $e) {
//            Log::error('Webhook Error: ' . $e->getMessage());
//            return response()->json(['status' => 'error'], 500);
//        }
//    }
//
//    private function sendTelegramMessage($chatId, $message)
//    {
//        Log::info('Sending Telegram Message: chat_id=' . $chatId . ', text=' . $message);
//        $botToken = config('services.telegram-bot-api.token');
//        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
//
//        $data = [
//            'chat_id' => $chatId,
//            'text' => $message,
//            'parse_mode' => 'MarkdownV2',
//        ];
//
//        $response = \Illuminate\Support\Facades\Http::post($url, $data);
//        Log::info('Send Telegram Message Response: ' . $response->body());
//    }
//
//    public function sendTelegramNotification($user_id)
//    {
//        $user = User::find($user_id);
//        if (!$user) {
//            return 'Пользователь не найден.';
//        }
//
//        $telegramUsers = TelegramUser::where('user_identifier', $user->email)
//            ->whereNotNull('telegram_user_id')
//            ->get();
//
//        if ($telegramUsers->isNotEmpty()) {
//            Notification::send($telegramUsers, new TelegramNotification("Привет, {$user->name}! Это тестовое уведомление."));
//            return 'Уведомление отправлено!';
//        }
//
//        return 'Нет привязанных Telegram аккаунтов.';
//    }
//}

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Models\User;
use App\Notifications\TelegramNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            Log::info('Telegram Webhook Update:', $request->all());

            $update = $request->all();

            // Extract chat_id and username
            $chatId = $update['message']['chat']['id'] ?? $update['my_chat_member']['chat']['id'] ?? null;
            $username = $update['message']['from']['username'] ?? $update['my_chat_member']['from']['username'] ?? null;

            if (!$chatId) {
                Log::error('No chat ID found in update: ' . json_encode($update));
                return response()->json(['status' => 'no chat_id'], 200);
            }

            // Handle my_chat_member updates
            if (isset($update['my_chat_member'])) {
                $newStatus = $update['my_chat_member']['new_chat_member']['status'];
                Log::info("Chat member status changed: chat_id={$chatId}, status={$newStatus}");

                if ($newStatus === 'kicked') {
                    TelegramUser::where('telegram_user_id', $chatId)->delete();
                    Log::info("Bot was kicked from chat_id={$chatId}");
                }

                return response()->json(['status' => 'success'], 200);
            }

            // Handle message updates
            $text = $update['message']['text'] ?? null;
            if (!$text) {
                Log::warning('No text in message: ' . json_encode($update));
                $this->sendTelegramMessage($chatId, 'Пожалуйста, отправьте текстовое сообщение.');
                return response()->json(['status' => 'no_text'], 200);
            }

            // Handle /start command
            if ($text === '/start') {
                TelegramUser::updateOrCreate(
                    ['telegram_user_id' => $chatId],
                    [
                        'telegram_user_id' => $chatId,
                        'telegram_username' => $username,
                        'status' => 'waiting_for_email',
                        'user_identifier' => null,
                    ]
                );
                $this->sendTelegramMessage($chatId, 'Добро пожаловать! Отправьте ваш email для привязки аккаунта.');
                return response()->json(['status' => 'success'], 200);
            }

            // Handle /help command
            if ($text === '/help') {
                $this->sendTelegramMessage($chatId, 'Команды:\n/start - Начать привязку\n/help - Показать справку');
                return response()->json(['status' => 'success'], 200);
            }

            // Handle email input
            $telegramUser = TelegramUser::where('telegram_user_id', $chatId)->first();

            if ($telegramUser && $telegramUser->status === 'waiting_for_email' && !str_starts_with($text, '/')) {
                $userIdentifier = trim($text);

                // Basic email validation
                if (!filter_var($userIdentifier, FILTER_VALIDATE_EMAIL)) {
                    $this->sendTelegramMessage($chatId, 'Неверный формат email. Пожалуйста, отправьте корректный email.');
                    return response()->json(['status' => 'invalid_email_format'], 200);
                }

                $user = User::where('email', $userIdentifier)->first();

                if (!$user) {
                    $this->sendTelegramMessage($chatId, 'Email не найден. Проверьте и попробуйте снова.');
                    return response()->json(['status' => 'invalid_email'], 200);
                }

                $telegramUser->update([
                    'user_identifier' => $userIdentifier,
                    'status' => 'linked',
                ]);

                $this->sendTelegramMessage($chatId, 'Успех! Ваш Telegram аккаунт привязан.');
                return response()->json(['status' => 'linked'], 200);
            }

            // Default response
            $this->sendTelegramMessage($chatId, 'Используйте /start или /help.');
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    private function sendTelegramMessage($chatId, $message)
    {
        try {
            // Escape MarkdownV2 reserved characters
            $reservedChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
            $escapedMessage = str_replace($reservedChars, array_map(fn($char) => '\\' . $char, $reservedChars), $message);

            Log::info('Sending Telegram Message: chat_id=' . $chatId . ', text=' . $escapedMessage);
            $botToken = config('services.telegram-bot-api.token');
            if (!$botToken) {
                Log::error('Telegram bot token not configured');
                return;
            }

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $data = [
                'chat_id' => $chatId,
                'text' => $escapedMessage,
                'parse_mode' => 'MarkdownV2',
            ];

            $response = \Illuminate\Support\Facades\Http::timeout(10)->post($url, $data);
            if ($response->successful()) {
                Log::info('Telegram Message Sent: ' . $response->body());
            } else {
                Log::error('Failed to send Telegram message: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Error sending Telegram message: ' . $e->getMessage());
        }
    }

    public function sendTelegramNotification($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return 'Пользователь не найден.';
        }

        $telegramUsers = TelegramUser::where('user_identifier', $user->email)
            ->whereNotNull('telegram_user_id')
            ->get();

        if ($telegramUsers->isNotEmpty()) {
            Notification::send($telegramUsers, new TelegramNotification("Привет, {$user->name}! Это тестовое уведомление."));
            return 'Уведомление отправлено!';
        }

        return 'Нет привязанных Telegram аккаунтов.';
    }
}
