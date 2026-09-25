<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserWorkHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Вход и выход мобильного приложения.
 *
 * Отдельно от /api/auth/login намеренно, по двум причинам.
 *
 * 1. Несколько устройств. Старый login выполняет $user->tokens()->delete(),
 *    то есть держит ОДИН активный токен на пользователя: вход с телефона
 *    выбивал бы человека из веб-клиента и наоборот. Здесь токены именуются
 *    по устройству и живут параллельно.
 *
 * 2. Срок жизни. Старые токены бессрочны (config/sanctum.php → expiration
 *    равен null). Для приложения на телефоне это рискованно: утерянный
 *    телефон означает вечный доступ. Здесь срок задаётся явно.
 */
class MobileAuthController extends Controller
{
    /** Сколько живёт токен приложения. */
    private const TOKEN_TTL_DAYS = 30;

    /**
     * POST /api/v1/mobile/login
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username'    => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('username', $data['username'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Одинаковый ответ на неверный логин и неверный пароль:
            // иначе по разнице ответов можно перебирать существующие логины
            return response()->json([
                'error'   => 'invalid_credentials',
                'message' => 'Login yoki parol noto\'g\'ri',
            ], 401);
        }

        if ((int) $user->status !== 1) {
            return response()->json([
                'error'   => 'inactive',
                'message' => 'Hisob faol emas',
            ], 403);
        }

        $this->ensureActiveOrganization($user);

        $deviceName = $data['device_name'] ?? 'mobile';

        // Токен того же устройства перевыпускаем, чужие не трогаем —
        // так человек может держать телефон и планшет одновременно
        $user->tokens()->where('name', $deviceName)->delete();

        $expiresAt = now()->addDays(self::TOKEN_TTL_DAYS);
        $token = $user->createToken($deviceName, ['*'], $expiresAt);

        Log::info('Mobile login', ['user_id' => $user->id, 'device' => $deviceName]);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $expiresAt->toIso8601String(),
            'user'         => $this->userPayload($user),
        ]);
    }

    /**
     * POST /api/v1/mobile/logout — гасит токен текущего устройства.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/v1/mobile/logout-all — гасит токены всех устройств.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $count = $request->user()->tokens()->count();
        $request->user()->tokens()->delete();

        return response()->json(['success' => true, 'revoked' => $count]);
    }

    /**
     * GET /api/v1/mobile/me — кто я сейчас.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    /**
     * Организация пользователя должна совпадать с той, где у него есть
     * активная запись работы.
     *
     * Все списки документов фильтруются по org_unit_id, а переключателя
     * организаций в приложении нет. Без этой проверки человек с org_unit_id
     * от старого места работы видел бы пустые списки и считал это поломкой.
     * Та же логика работает при входе в мини-апп.
     */
    private function ensureActiveOrganization(User $user): void
    {
        $hasActive = $user->org_unit_id && UserWorkHistory::where('user_id', $user->id)
                ->where('org_id', $user->org_unit_id)
                ->where('status', 1)
                ->exists();

        if ($hasActive) {
            return;
        }

        $work = UserWorkHistory::where('user_id', $user->id)
            ->where('status', 1)
            ->orderByDesc('id')
            ->first();

        if ($work) {
            $user->org_unit_id = $work->org_id;
            $user->save();

            Log::info('Mobile login: org_unit_id auto-set', [
                'user_id' => $user->id,
                'org_id'  => $work->org_id,
            ]);
        }
    }

    private function userPayload(User $user): array
    {
        return [
            'id'            => $user->id,
            'username'      => $user->username,
            'email'         => $user->email,
            'org_unit_id'   => $user->org_unit_id,
        ];
    }
}
