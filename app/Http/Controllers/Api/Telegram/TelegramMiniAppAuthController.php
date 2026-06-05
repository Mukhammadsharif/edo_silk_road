<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserWorkHistory;
use App\Models\TelegramMiniAppToken;
use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramMiniAppAuthController extends Controller
{
    /**
     * Авторизация через Telegram WebApp initData
     * POST /api/telegram/auth
     *
     * Telegram передаёт initData — HMAC-подписанные данные пользователя.
     * Проверяем подпись, находим привязанного пользователя, выдаём Bearer token.
     */
    public function auth(Request $request): JsonResponse
    {
        $request->validate([
            'init_data' => 'required|string',
        ]);

        $initData = $request->input('init_data');
        $botToken  = config('services.telegram-bot-api.token');

        // Верификация HMAC подписи Telegram
        if (!$this->verifyTelegramInitData($initData, $botToken)) {
            return response()->json(['error' => 'Invalid Telegram signature'], 401);
        }

        // Парсим initData (URL-encoded)
        parse_str($initData, $params);
        $userJson = $params['user'] ?? null;

        if (!$userJson) {
            return response()->json(['error' => 'User data not found in initData'], 400);
        }

        $telegramUser = json_decode($userJson, true);
        $telegramId   = (string) ($telegramUser['id'] ?? null);

        if (!$telegramId) {
            return response()->json(['error' => 'Telegram user ID not found'], 400);
        }

        // Ищем привязанного пользователя
        $linkedUser = TelegramUser::where('telegram_user_id', $telegramId)
            ->where('status', 'linked')
            ->whereNotNull('user_identifier')
            ->first();

        if (!$linkedUser) {
            return response()->json([
                'error'   => 'not_linked',
                'message' => 'Telegram аккаунт не привязан. Отправьте /start боту для привязки.',
            ], 403);
        }

        // Поиск по username (новый формат привязки), с fallback на email
        // для обратной совместимости со старыми записями TelegramUser.
        $user = User::where('username', $linkedUser->user_identifier)
            ->orWhere('email', $linkedUser->user_identifier)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Убедимся что org_unit_id указывает на организацию где у юзера есть
        // активная work_history. Иначе списки документов будут пустыми
        // (всё фильтруется через org_unit_id). В Mini App нет переключателя
        // организаций, поэтому делаем это автоматически.
        $hasActiveInCurrent = $user->org_unit_id && UserWorkHistory::where('user_id', $user->id)
                ->where('org_id', $user->org_unit_id)
                ->where('status', 1)
                ->exists();

        if (!$hasActiveInCurrent) {
            $firstWork = UserWorkHistory::where('user_id', $user->id)
                ->where('status', 1)
                ->orderByDesc('id')
                ->first();
            if ($firstWork) {
                $user->org_unit_id = $firstWork->org_id;
                $user->save();
                Log::info('Telegram auth: org_unit_id auto-set', [
                    'user_id' => $user->id,
                    'org_id'  => $firstWork->org_id,
                ]);
            }
        }

        // Создаём auth token
        $tokenRecord = TelegramMiniAppToken::createAuthToken($user->id, $telegramId);

        Log::info('Telegram Mini App auth', [
            'telegram_id' => $telegramId,
            'user_id'     => $user->id,
        ]);

        return response()->json([
            'token'      => $tokenRecord->auth_token,
            'expires_at' => $tokenRecord->auth_token_expires_at->toIso8601String(),
            'user'       => [
                'name'  => $user->username,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Верификация HMAC подписи Telegram WebApp
     * https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
     */
    private function verifyTelegramInitData(string $initData, string $botToken): bool
    {
        parse_str($initData, $params);
        $hash = $params['hash'] ?? '';
        unset($params['hash']);

        // Сортируем и формируем data-check-string
        ksort($params);
        $dataCheckString = implode("\n", array_map(
            fn($k, $v) => "$k=$v",
            array_keys($params),
            array_values($params)
        ));

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $expected   = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($expected, $hash);
    }
}
