<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Models\User;
use App\Notifications\TelegramNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    // Состояния FSM
    private const STATE_CHOOSING_METHOD  = 'choosing_method';
    private const STATE_WAITING_USERNAME = 'waiting_for_username';
    private const STATE_WAITING_PASSWORD = 'waiting_for_password';
    private const STATE_WAITING_EIMZO    = 'waiting_for_eimzo';
    private const STATE_LINKED           = 'linked';

    // Callback data для inline-keyboard
    private const CB_METHOD_PASSWORD = 'auth:method:password';
    private const CB_METHOD_EIMZO    = 'auth:method:eimzo';

    /**
     * Telegram webhook entry point.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $update = $request->all();

            // Кнопка inline-keyboard нажата — это callback_query
            if (isset($update['callback_query'])) {
                return $this->handleCallbackQuery($update['callback_query']);
            }

            // chat_id и Telegram-username (handle вида @user)
            $chatId   = $update['message']['chat']['id']
                ?? $update['my_chat_member']['chat']['id']
                ?? null;
            $tgHandle = $update['message']['from']['username']
                ?? $update['my_chat_member']['from']['username']
                ?? null;

            if (!$chatId) {
                Log::warning('Telegram webhook: no chat_id', ['type' => array_keys($update)]);
                return response()->json(['status' => 'no_chat_id'], 200);
            }

            // ВАЖНО: не пишем в логи весь payload — пароль попадёт в логи.
            Log::info('Telegram webhook', [
                'chat_id' => $chatId,
                'type'    => isset($update['my_chat_member']) ? 'my_chat_member' : 'message',
            ]);

            // Изменение статуса бота в чате (kicked / left / member …)
            if (isset($update['my_chat_member'])) {
                return $this->handleChatMemberUpdate($update['my_chat_member'], $chatId);
            }

            // Текстовое сообщение
            $text      = $update['message']['text'] ?? null;
            $messageId = $update['message']['message_id'] ?? null;

            if (!is_string($text) || $text === '') {
                $this->sendTelegramMessage($chatId, 'Пожалуйста, отправьте текстовое сообщение.');
                return response()->json(['status' => 'no_text'], 200);
            }

            // Команды
            switch ($text) {
                case '/start':
                    return $this->handleStart($chatId, $tgHandle);
                case '/help':
                    return $this->handleHelp($chatId);
                case '/cancel':
                    return $this->handleCancel($chatId);
                case '/unlink':
                    return $this->handleUnlink($chatId);
            }

            // Не команда — обрабатываем как ввод по текущему состоянию
            $telegramUser = TelegramUser::where('telegram_user_id', $chatId)->first();

            if (!$telegramUser) {
                $this->sendTelegramMessage($chatId, 'Для начала привязки отправьте /start.');
                return response()->json(['status' => 'no_state'], 200);
            }

            switch ($telegramUser->status) {
                case self::STATE_CHOOSING_METHOD:
                    // Текст вместо нажатия кнопки — напоминаем пользователю
                    $this->sendChooseMethodKeyboard($chatId);
                    return response()->json(['status' => 'still_choosing'], 200);

                case self::STATE_WAITING_USERNAME:
                    return $this->handleUsernameInput($telegramUser, $text);

                case self::STATE_WAITING_PASSWORD:
                    return $this->handlePasswordInput($telegramUser, $text, $chatId, $messageId);

                case self::STATE_WAITING_EIMZO:
                    // Юзер уже на этапе E-IMZO — никакого текстового ввода не ожидаем,
                    // только результат веб-flow. Подсказываем нажать кнопку.
                    $this->sendTelegramMessage(
                        $chatId,
                        'Откройте E-IMZO авторизацию через кнопку выше или отправьте /cancel.'
                    );
                    return response()->json(['status' => 'awaiting_eimzo'], 200);

                case self::STATE_LINKED:
                    $this->sendTelegramMessage(
                        $chatId,
                        'Ваш аккаунт уже привязан. Чтобы привязать другой — отправьте /unlink, затем /start.'
                    );
                    return response()->json(['status' => 'already_linked'], 200);
            }

            $this->sendTelegramMessage($chatId, 'Используйте /start или /help.');
            return response()->json(['status' => 'unknown_state'], 200);

        } catch (\Throwable $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    // ==========================================================
    //  Команды бота
    // ==========================================================

    private function handleStart(string|int $chatId, ?string $tgHandle): JsonResponse
    {
        TelegramUser::updateOrCreate(
            ['telegram_user_id' => $chatId],
            [
                'telegram_username' => $tgHandle,
                'status'            => self::STATE_CHOOSING_METHOD,
                'user_identifier'   => null,
            ]
        );

        $this->sendChooseMethodKeyboard($chatId);
        return response()->json(['status' => 'started'], 200);
    }

    private function handleHelp(string|int $chatId): JsonResponse
    {
        $msg = "Доступные команды:\n"
            . "/start — начать привязку аккаунта\n"
            . "/cancel — отменить текущую операцию\n"
            . "/unlink — отвязать аккаунт\n"
            . "/help — показать эту справку";
        $this->sendTelegramMessage($chatId, $msg);
        return response()->json(['status' => 'help'], 200);
    }

    private function handleCancel(string|int $chatId): JsonResponse
    {
        $tgUser = TelegramUser::where('telegram_user_id', $chatId)->first();

        if ($tgUser && $tgUser->status !== self::STATE_LINKED) {
            $tgUser->delete();
        }

        // Чистим возможный link_token из кеша
        Cache::forget("tg_eimzo_link:{$chatId}");

        $this->sendTelegramMessage($chatId, 'Операция отменена. Для начала привязки отправьте /start.');
        return response()->json(['status' => 'cancelled'], 200);
    }

    private function handleUnlink(string|int $chatId): JsonResponse
    {
        TelegramUser::where('telegram_user_id', $chatId)->delete();
        Cache::forget("tg_eimzo_link:{$chatId}");
        $this->sendTelegramMessage($chatId, 'Аккаунт отвязан. Для повторной привязки отправьте /start.');
        return response()->json(['status' => 'unlinked'], 200);
    }

    // ==========================================================
    //  Inline keyboard — выбор метода авторизации
    // ==========================================================

    private function sendChooseMethodKeyboard(string|int $chatId): void
    {
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🔑 Логин и пароль',     'callback_data' => self::CB_METHOD_PASSWORD]],
                [['text' => '🆔 E-IMZO ID-Card',     'callback_data' => self::CB_METHOD_EIMZO]],
            ],
        ];

        $this->sendTelegramMessage(
            $chatId,
            "Добро пожаловать!\n\n"
            . "Выберите способ авторизации:\n\n"
            . "🔑 Логин и пароль — введите свои учётные данные\n"
            . "🆔 E-IMZO ID-Card — авторизация через ID-карту E-IMZO",
            $keyboard
        );
    }

    private function handleCallbackQuery(array $callback): JsonResponse
    {
        $callbackId = $callback['id']               ?? null;
        $chatId     = $callback['message']['chat']['id'] ?? null;
        $messageId  = $callback['message']['message_id'] ?? null;
        $data       = $callback['data']             ?? '';

        if (!$chatId || !$callbackId) {
            return response()->json(['status' => 'bad_callback'], 200);
        }

        // Подтверждаем callback (убирает loading-индикатор у кнопки)
        $this->answerCallbackQuery($callbackId);

        $telegramUser = TelegramUser::where('telegram_user_id', $chatId)->first();

        if (!$telegramUser || $telegramUser->status !== self::STATE_CHOOSING_METHOD) {
            $this->sendTelegramMessage($chatId, 'Этот выбор уже устарел. Отправьте /start заново.');
            return response()->json(['status' => 'stale_callback'], 200);
        }

        if ($data === self::CB_METHOD_PASSWORD) {
            $telegramUser->update(['status' => self::STATE_WAITING_USERNAME]);

            // Убираем кнопки из исходного сообщения
            if ($messageId) {
                $this->editMessageReplyMarkup($chatId, $messageId, null);
            }

            $this->sendTelegramMessage(
                $chatId,
                "🔑 Способ выбран: Логин и пароль\n\n"
                . "Отправьте ваш логин (username), который вы используете для входа в систему."
            );
            return response()->json(['status' => 'method_password'], 200);
        }

        if ($data === self::CB_METHOD_EIMZO) {
            return $this->startEimzoLink($telegramUser, $chatId, $messageId);
        }

        $this->sendTelegramMessage($chatId, 'Неизвестный выбор. Отправьте /start.');
        return response()->json(['status' => 'unknown_callback'], 200);
    }

    // ==========================================================
    //  E-IMZO link flow — кнопка с веб-страницей привязки
    // ==========================================================

    private function startEimzoLink(TelegramUser $telegramUser, string|int $chatId, ?int $messageId): JsonResponse
    {
        // Генерируем одноразовый short-lived link_token (5 минут).
        // Веб-страница использует его для идентификации chat_id при возврате PKCS#7.
        $linkToken = Str::random(64);

        Cache::put(
            "tg_eimzo_link:{$linkToken}",
            ['chat_id' => (string) $chatId],
            now()->addMinutes(5)
        );

        // Также сохраняем обратную ссылку chat_id → token, чтобы /cancel мог её зачистить.
        Cache::put(
            "tg_eimzo_link:{$chatId}",
            $linkToken,
            now()->addMinutes(5)
        );

        $telegramUser->update(['status' => self::STATE_WAITING_EIMZO]);

        // Убираем старую клавиатуру с выбором метода
        if ($messageId) {
            $this->editMessageReplyMarkup($chatId, $messageId, null);
        }

        // BASE_URL для Mini App
        $baseUrl = rtrim(config('app.url') ?: 'https://edoline.uz', '/');
        $webUrl  = $baseUrl . '/telegram-link/eimzo?token=' . urlencode($linkToken);

        // Inline keyboard с web_app кнопкой
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🆔 Открыть E-IMZO авторизацию', 'web_app' => ['url' => $webUrl]]],
                [['text' => '↩️ Назад к выбору',           'callback_data' => 'auth:back']],
            ],
        ];

        $this->sendTelegramMessage(
            $chatId,
            "🆔 Способ выбран: E-IMZO ID-Card\n\n"
            . "Нажмите кнопку ниже, чтобы открыть страницу авторизации.\n\n"
            . "Вам потребуется мобильное приложение E-IMZO ID-Card.\n\n"
            . "⏱ Ссылка действительна 5 минут.",
            $keyboard
        );

        return response()->json(['status' => 'eimzo_started'], 200);
    }

    // ==========================================================
    //  Обработчики ввода (логин / пароль)
    // ==========================================================

    private function handleUsernameInput(TelegramUser $telegramUser, string $text): JsonResponse
    {
        $login = trim($text);

        if ($login === '' || mb_strlen($login) > 100) {
            $this->sendTelegramMessage(
                $telegramUser->telegram_user_id,
                'Неверный формат логина. Попробуйте ещё раз или отправьте /cancel для отмены.'
            );
            return response()->json(['status' => 'invalid_username'], 200);
        }

        // По аналогии с web-логином: можно ввести username или email
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user  = User::where($field, $login)->first();

        if (!$user) {
            $this->sendTelegramMessage(
                $telegramUser->telegram_user_id,
                'Пользователь с таким логином не найден. Попробуйте ещё раз или отправьте /cancel.'
            );
            return response()->json(['status' => 'user_not_found'], 200);
        }

        if ((int) $user->status !== 1) {
            $this->sendTelegramMessage(
                $telegramUser->telegram_user_id,
                'Аккаунт не активен. Обратитесь к администратору.'
            );
            return response()->json(['status' => 'user_inactive'], 200);
        }

        $telegramUser->update([
            'user_identifier' => $user->username,
            'status'          => self::STATE_WAITING_PASSWORD,
        ]);

        $this->sendTelegramMessage(
            $telegramUser->telegram_user_id,
            "Логин принят. Теперь отправьте ваш пароль.\n\n"
            . "🔒 Сообщение с паролем будет автоматически удалено в целях безопасности."
        );
        return response()->json(['status' => 'awaiting_password'], 200);
    }

    private function handlePasswordInput(
        TelegramUser $telegramUser,
        string $text,
        string|int $chatId,
        ?int $messageId
    ): JsonResponse {
        // Сразу удаляем сообщение с паролем из чата
        if ($messageId) {
            $this->deleteTelegramMessage($chatId, $messageId);
        }

        $username = $telegramUser->user_identifier;

        if (!$username) {
            $telegramUser->update(['status' => self::STATE_WAITING_USERNAME]);
            $this->sendTelegramMessage($chatId, 'Сессия устарела. Отправьте логин ещё раз.');
            return response()->json(['status' => 'session_lost'], 200);
        }

        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user || (int) $user->status !== 1) {
            $telegramUser->delete();
            $this->sendTelegramMessage($chatId, 'Аккаунт не найден или не активен. Начните заново с /start.');
            return response()->json(['status' => 'user_gone'], 200);
        }

        if (!Hash::check($text, $user->password)) {
            $this->sendTelegramMessage(
                $chatId,
                'Неверный пароль. Отправьте пароль ещё раз или /cancel для отмены.'
            );
            return response()->json(['status' => 'wrong_password'], 200);
        }

        // Успешная привязка
        $telegramUser->update([
            'user_identifier' => $user->username,
            'status'          => self::STATE_LINKED,
        ]);

        Log::info('Telegram account linked via password', [
            'telegram_user_id' => $chatId,
            'user_id'          => $user->id,
            'username'         => $user->username,
        ]);

        $this->sendTelegramMessage(
            $chatId,
            "✅ Аккаунт успешно привязан!\nТеперь вы будете получать уведомления о документах в этом чате."
        );
        return response()->json(['status' => 'linked'], 200);
    }

    // ==========================================================
    //  Обновления статуса бота
    // ==========================================================

    private function handleChatMemberUpdate(array $data, string|int $chatId): JsonResponse
    {
        $newStatus = $data['new_chat_member']['status'] ?? null;

        if ($newStatus === 'kicked') {
            TelegramUser::where('telegram_user_id', $chatId)->delete();
            Cache::forget("tg_eimzo_link:{$chatId}");
            Log::info('Telegram bot kicked, link removed', ['chat_id' => $chatId]);
        }

        return response()->json(['status' => 'chat_member_handled'], 200);
    }

    // ==========================================================
    //  Telegram Bot API helpers
    // ==========================================================

    private function sendTelegramMessage(string|int $chatId, string $message, ?array $keyboard = null): void
    {
        try {
            $botToken = config('services.telegram-bot-api.token');
            if (!$botToken) {
                Log::error('Telegram bot token is not configured');
                return;
            }

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $payload = [
                'chat_id' => $chatId,
                'text'    => $message,
            ];
            // ВАЖНО: при JSON body (Laravel Http::post по умолчанию) reply_markup
            // должен быть ОБЪЕКТОМ, а не json_encode-строкой. Иначе Telegram
            // молча проигнорирует разметку и пришлёт просто текст без кнопок.
            if ($keyboard !== null) {
                $payload['reply_markup'] = $keyboard;
            }

            $response = Http::timeout(10)->acceptJson()->post($url, $payload);

            if (!$response->successful()) {
                Log::error('Failed to send Telegram message', [
                    'chat_id'       => $chatId,
                    'has_keyboard'  => $keyboard !== null,
                    'http_status'   => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error sending Telegram message: ' . $e->getMessage());
        }
    }

    private function deleteTelegramMessage(string|int $chatId, int $messageId): void
    {
        try {
            $botToken = config('services.telegram-bot-api.token');
            if (!$botToken) {
                return;
            }

            $url = "https://api.telegram.org/bot{$botToken}/deleteMessage";

            Http::timeout(5)->post($url, [
                'chat_id'    => $chatId,
                'message_id' => $messageId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete Telegram message: ' . $e->getMessage());
        }
    }

    private function answerCallbackQuery(string $callbackQueryId, ?string $text = null): void
    {
        try {
            $botToken = config('services.telegram-bot-api.token');
            if (!$botToken) {
                return;
            }

            $url = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";

            $payload = ['callback_query_id' => $callbackQueryId];
            if ($text) {
                $payload['text'] = $text;
            }

            Http::timeout(5)->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('Failed to answer callback query: ' . $e->getMessage());
        }
    }

    private function editMessageReplyMarkup(string|int $chatId, int $messageId, ?array $keyboard): void
    {
        try {
            $botToken = config('services.telegram-bot-api.token');
            if (!$botToken) {
                return;
            }

            $url = "https://api.telegram.org/bot{$botToken}/editMessageReplyMarkup";

            // То же что и в sendTelegramMessage — reply_markup как объект, не строка.
            $payload = [
                'chat_id'      => $chatId,
                'message_id'   => $messageId,
                'reply_markup' => $keyboard ?? ['inline_keyboard' => []],
            ];

            $response = Http::timeout(5)->acceptJson()->post($url, $payload);

            if (!$response->successful()) {
                Log::warning('editMessageReplyMarkup failed', [
                    'chat_id'       => $chatId,
                    'message_id'    => $messageId,
                    'response_body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to edit message reply markup: ' . $e->getMessage());
        }
    }

    /**
     * Публичный метод — вызывается из E-IMZO link complete-эндпоинта,
     * чтобы уведомить юзера в Telegram об успехе/ошибке привязки.
     */
    public function notifyEimzoLinkResult(string|int $chatId, bool $success, ?string $username = null, ?string $errorMessage = null): void
    {
        if ($success) {
            $msg = "✅ Аккаунт успешно привязан через E-IMZO!\n\n"
                . "Пользователь: {$username}\n"
                . "Теперь вы будете получать уведомления о документах в этом чате.";
        } else {
            $msg = "❌ Не удалось привязать аккаунт через E-IMZO.\n\n"
                . ($errorMessage ?: 'Попробуйте /start заново или используйте логин/пароль.');
        }

        $this->sendTelegramMessage($chatId, $msg);
    }

    // ==========================================================
    //  Тестовое уведомление (вызывается с web-роута)
    // ==========================================================

    public function sendTelegramNotification($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return 'Пользователь не найден.';
        }

        $telegramUsers = TelegramUser::where('status', self::STATE_LINKED)
            ->whereNotNull('telegram_user_id')
            ->where(function ($q) use ($user) {
                $q->where('user_identifier', $user->username)
                    ->orWhere('user_identifier', $user->email);
            })
            ->get();

        if ($telegramUsers->isNotEmpty()) {
            Notification::send(
                $telegramUsers,
                new TelegramNotification("Привет, {$user->username}! Это тестовое уведомление.")
            );
            return 'Уведомление отправлено!';
        }

        return 'Нет привязанных Telegram аккаунтов.';
    }
}
