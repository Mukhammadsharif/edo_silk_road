<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Models\User;
use App\Notifications\TelegramNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TelegramWebhookController extends Controller
{
    // Состояния FSM привязки аккаунта
    private const STATE_WAITING_USERNAME = 'waiting_for_username';
    private const STATE_WAITING_PASSWORD = 'waiting_for_password';
    private const STATE_LINKED           = 'linked';

    /**
     * Telegram webhook entry point.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $update = $request->all();

            // chat_id и Telegram-username (handle вида @user)
            $chatId   = $update['message']['chat']['id']
                ?? $update['my_chat_member']['chat']['id']
                ?? null;
            $tgHandle = $update['message']['from']['username']
                ?? $update['my_chat_member']['from']['username']
                ?? null;

            if (!$chatId) {
                Log::warning('Telegram webhook: no chat_id', ['update' => $update]);
                return response()->json(['status' => 'no_chat_id'], 200);
            }

            // ВАЖНО: не пишем в логи весь payload — иначе пароль попадёт в файл логов.
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
                case self::STATE_WAITING_USERNAME:
                    return $this->handleUsernameInput($telegramUser, $text);

                case self::STATE_WAITING_PASSWORD:
                    return $this->handlePasswordInput($telegramUser, $text, $chatId, $messageId);

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
    //  Обработчики команд
    // ==========================================================

    private function handleStart(string|int $chatId, ?string $tgHandle): JsonResponse
    {
        TelegramUser::updateOrCreate(
            ['telegram_user_id' => $chatId],
            [
                'telegram_username' => $tgHandle,
                'status'            => self::STATE_WAITING_USERNAME,
                'user_identifier'   => null,
            ]
        );

        $this->sendTelegramMessage(
            $chatId,
            "Добро пожаловать!\n\n"
            . "Для привязки аккаунта отправьте ваш логин (username), который вы используете для входа в систему."
        );
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

        $this->sendTelegramMessage($chatId, 'Операция отменена. Для начала привязки отправьте /start.');
        return response()->json(['status' => 'cancelled'], 200);
    }

    private function handleUnlink(string|int $chatId): JsonResponse
    {
        TelegramUser::where('telegram_user_id', $chatId)->delete();
        $this->sendTelegramMessage($chatId, 'Аккаунт отвязан. Для повторной привязки отправьте /start.');
        return response()->json(['status' => 'unlinked'], 200);
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

        // Временно сохраняем username в user_identifier и переходим к запросу пароля.
        // После успешной проверки пароля поле останется тем же.
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
        // Сразу удаляем сообщение с паролем из чата (Telegram deleteMessage API).
        if ($messageId) {
            $this->deleteTelegramMessage($chatId, $messageId);
        }

        $username = $telegramUser->user_identifier;

        if (!$username) {
            $telegramUser->update(['status' => self::STATE_WAITING_USERNAME]);
            $this->sendTelegramMessage($chatId, 'Сессия устарела. Отправьте логин ещё раз.');
            return response()->json(['status' => 'session_lost'], 200);
        }

        // Поиск по username, с fallback на email для обратной совместимости
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

        // Успешная привязка. Гарантированно сохраняем именно username.
        $telegramUser->update([
            'user_identifier' => $user->username,
            'status'          => self::STATE_LINKED,
        ]);

        Log::info('Telegram account linked', [
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
    //  Обновления статуса бота в чате
    // ==========================================================

    private function handleChatMemberUpdate(array $data, string|int $chatId): JsonResponse
    {
        $newStatus = $data['new_chat_member']['status'] ?? null;

        if ($newStatus === 'kicked') {
            TelegramUser::where('telegram_user_id', $chatId)->delete();
            Log::info('Telegram bot kicked, link removed', ['chat_id' => $chatId]);
        }

        return response()->json(['status' => 'chat_member_handled'], 200);
    }

    // ==========================================================
    //  Telegram Bot API helpers
    // ==========================================================

    private function sendTelegramMessage(string|int $chatId, string $message): void
    {
        try {
            $botToken = config('services.telegram-bot-api.token');
            if (!$botToken) {
                Log::error('Telegram bot token is not configured');
                return;
            }

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $response = Http::timeout(10)->post($url, [
                'chat_id' => $chatId,
                'text'    => $message,
            ]);

            if (!$response->successful()) {
                Log::error('Failed to send Telegram message', [
                    'chat_id' => $chatId,
                    'body'    => $response->body(),
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
            // Если удалить не получилось (старое сообщение, нет прав) — просто логируем.
            Log::warning('Failed to delete Telegram message: ' . $e->getMessage());
        }
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

        // Поиск привязок: сначала по username (новый формат),
        // затем по email (старые записи — обратная совместимость).
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
