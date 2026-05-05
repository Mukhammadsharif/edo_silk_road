<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TelegramWebhookController;
use App\Models\Admin\UserPersonalInfo;
use App\Models\TelegramUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Привязка Telegram-аккаунта через E-IMZO ID-Card.
 *
 * Используется существующий E-IMZO mobile-auth flow (/frontend/mobile/auth +
 * /backend/mobile/authenticate/{documentId}) — тот же что для логина в систему
 * через ID-Card на сайте.
 *
 * ВАЖНО про curl: E-IMZO backend endpoints (/backend/*) требуют заголовки
 * X-Real-IP и Host — без них сервер возвращает пустое тело. Поэтому используем
 * курл напрямую с теми же опциями что в SignDocImzoMobileApiController.
 *
 * Flow:
 *  1) Бот генерит link_token, кладёт в cache: tg_eimzo_link:{token} → {chat_id}
 *  2) Бот отправляет inline-кнопку с web_app URL
 *  3) Веб-страница mobile/eimzo-link.blade.php:
 *      - POST /api/telegram/eimzo-link/auth-init  → /frontend/mobile/auth
 *      - polling /api/telegram/eimzo-link/check-auth
 *        → /backend/mobile/authenticate/{documentId}
 *      - на success: backend сразу финализирует привязку
 *  4) Бот шлёт уведомление в чат
 */
class TelegramEimzoLinkController extends Controller
{
    private string $eriSrvIp;

    public function __construct()
    {
        $this->eriSrvIp = config('services.e-imzo-server.url');
    }

    /**
     * POST /api/telegram/eimzo-link/auth-init
     */
    public function authInit(Request $request): JsonResponse
    {
        $request->validate(['link_token' => 'required|string']);
        $token = $request->input('link_token');

        $payload = Cache::get("tg_eimzo_link:{$token}");
        if (!$payload || empty($payload['chat_id'])) {
            return response()->json([
                'success' => false,
                'error'   => 'invalid_token',
                'message' => 'Ссылка устарела. Вернитесь в Telegram и нажмите /start заново.',
            ], 403);
        }

        try {
            // /frontend/mobile/auth — frontend endpoint, не требует X-Real-IP/Host
            $rawResponse = $this->curlPost('/frontend/mobile/auth', null);
            $body = $rawResponse ? json_decode($rawResponse, true) : null;

            $status     = $body['status'] ?? 0;
            $documentId = $body['documentId'] ?? null;
            $siteId     = $body['siteId']     ?? null;
            // E-IMZO API имеет опечатку 'challange' — поддерживаем оба варианта
            $challenge  = $body['challenge'] ?? $body['challange'] ?? null;

            if ((int) $status !== 1 || !$documentId || !$challenge) {
                Log::error('E-IMZO link auth-init failed', [
                    'response_raw' => $rawResponse,
                    'response'     => $body,
                ]);
                return response()->json([
                    'success' => false,
                    'error'   => 'eimzo_error',
                    'message' => $body['message'] ?? 'E-IMZO server error',
                ], 502);
            }

            // Привяжем documentId к token, чтобы /check-auth мог найти chat_id
            Cache::put(
                "tg_eimzo_link_doc:{$documentId}",
                $token,
                now()->addMinutes(15)
            );

            return response()->json([
                'success'    => true,
                'siteId'     => $siteId,
                'documentId' => $documentId,
                'challenge'  => $challenge,
            ]);

        } catch (\Throwable $e) {
            Log::error('E-IMZO link auth-init exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => 'server_error',
                'message' => 'Ошибка соединения с E-IMZO сервером',
            ], 500);
        }
    }

    /**
     * POST /api/telegram/eimzo-link/status
     *
     * Polling-эндпоинт. Дёргает /frontend/mobile/status (НЕ /backend/mobile/authenticate),
     * потому что у E-IMZO backend нет нормального "pending" состояния — он бросает
     * NullPointerException пока юзер не подписал. Frontend status корректно
     * возвращает status=2 в этом случае.
     *
     * Когда status=1 — фронт отдельно зовёт /check-auth для финализации.
     */
    public function pollStatus(Request $request): JsonResponse
    {
        $request->validate([
            'link_token' => 'required|string',
            'documentId' => 'required|string',
        ]);

        $token      = $request->input('link_token');
        $documentId = $request->input('documentId');

        $payload = Cache::get("tg_eimzo_link:{$token}");
        if (!$payload || empty($payload['chat_id'])) {
            return response()->json([
                'success' => false,
                'error'   => 'invalid_token',
                'message' => 'Ссылка устарела',
            ], 403);
        }

        $expectedToken = Cache::get("tg_eimzo_link_doc:{$documentId}");
        if ($expectedToken !== $token) {
            return response()->json([
                'success' => false,
                'error'   => 'document_mismatch',
            ], 403);
        }

        try {
            $rawResponse = $this->curlPostForm(
                '/frontend/mobile/status',
                http_build_query(['documentId' => $documentId])
            );

            if ($rawResponse === null || $rawResponse === false) {
                return response()->json([
                    'success' => true,
                    'state'   => 'pending',
                ]);
            }

            $body = json_decode($rawResponse, true);
            if (!is_array($body)) {
                return response()->json([
                    'success' => true,
                    'state'   => 'pending',
                ]);
            }

            $status = (int) ($body['status'] ?? 0);

            if ($status === 1) {
                return response()->json([
                    'success' => true,
                    'state'   => 'ready',
                    'message' => 'Подпись получена',
                ]);
            }

            if ($status === 2) {
                return response()->json([
                    'success' => true,
                    'state'   => 'pending',
                    'message' => 'Ожидание подписи',
                ]);
            }

            // $status < 0 — реальная ошибка
            Log::warning('E-IMZO link status error', [
                'documentId' => $documentId,
                'status'     => $status,
                'response'   => $body,
            ]);
            return response()->json([
                'success' => false,
                'state'   => 'error',
                'message' => $body['message'] ?? 'Ошибка статуса',
            ]);

        } catch (\Throwable $e) {
            Log::error('E-IMZO link status exception', [
                'documentId' => $documentId,
                'error'      => $e->getMessage(),
            ]);
            return response()->json([
                'success' => true,
                'state'   => 'pending',
                'message' => 'Транзиентная ошибка',
            ]);
        }
    }

    /**
     * POST /api/telegram/eimzo-link/check-auth
     */
    public function checkAuth(Request $request): JsonResponse
    {
        $request->validate([
            'link_token' => 'required|string',
            'documentId' => 'required|string',
        ]);

        $token      = $request->input('link_token');
        $documentId = $request->input('documentId');

        $payload = Cache::get("tg_eimzo_link:{$token}");
        if (!$payload || empty($payload['chat_id'])) {
            return response()->json([
                'success' => false,
                'error'   => 'invalid_token',
                'message' => 'Ссылка устарела',
            ], 403);
        }

        // Cross-check: documentId должен соответствовать сохранённому token
        $expectedToken = Cache::get("tg_eimzo_link_doc:{$documentId}");
        if ($expectedToken !== $token) {
            return response()->json([
                'success' => false,
                'error'   => 'document_mismatch',
                'message' => 'documentId не соответствует токену',
            ], 403);
        }

        $chatId = (string) $payload['chat_id'];

        try {
            // /backend/mobile/authenticate/{documentId} — backend endpoint,
            // ОБЯЗАТЕЛЬНО нужны X-Real-IP и Host заголовки
            $clientIp = $request->ip();
            $host     = $request->getHost();

            $rawResponse = $this->curlGet(
                '/backend/mobile/authenticate/' . urlencode($documentId),
                $clientIp,
                $host
            );

            if ($rawResponse === null || $rawResponse === false) {
                Log::warning('E-IMZO link check-auth: empty response from backend', [
                    'documentId' => $documentId,
                    'host'       => $host,
                ]);
                // Транзиентная ошибка — пусть фронт попробует ещё раз
                return response()->json([
                    'success' => true,
                    'state'   => 'pending',
                    'message' => 'Ожидание (повторная попытка)',
                ]);
            }

            $body = json_decode($rawResponse, true);
            if (!is_array($body)) {
                Log::warning('E-IMZO link check-auth: non-JSON response', [
                    'documentId'   => $documentId,
                    'response_raw' => mb_substr((string) $rawResponse, 0, 500),
                ]);
                return response()->json([
                    'success' => true,
                    'state'   => 'pending',
                    'message' => 'Ожидание (некорректный ответ сервера)',
                ]);
            }

            $status = $body['status'] ?? 0;

            // 2 — pending (ещё не подписано пользователем)
            // 1 — готово
            // < 0 — ошибка
            if ((int) $status === 2) {
                return response()->json([
                    'success' => true,
                    'state'   => 'pending',
                    'message' => 'Ожидание подписи',
                ]);
            }

            if ((int) $status !== 1 || empty($body['subjectCertificateInfo'])) {
                $errMsg = $body['message'] ?? 'Ошибка идентификации';
                Log::warning('E-IMZO link auth failed', [
                    'documentId' => $documentId,
                    'response'   => $body,
                ]);
                return response()->json([
                    'success' => false,
                    'state'   => 'error',
                    'error'   => 'auth_failed',
                    'message' => $errMsg,
                ]);
            }

            // Успех — извлекаем PIN
            $subjectName = $body['subjectCertificateInfo']['subjectName'] ?? [];
            $pin = $subjectName['1.2.860.3.16.1.2'] ?? null;
            $cn  = $subjectName['CN'] ?? null;

            if (!$pin) {
                return response()->json([
                    'success' => false,
                    'state'   => 'error',
                    'error'   => 'no_pin',
                    'message' => 'PIN не найден в сертификате',
                ]);
            }

            // Ищем юзера
            $personal = UserPersonalInfo::where('doc_pin', $pin)->first();
            $user     = $personal ? User::find($personal->user_id) : null;

            $bot = app(TelegramWebhookController::class);

            if (!$user) {
                $bot->notifyEimzoLinkResult(
                    $chatId,
                    false,
                    null,
                    'Пользователь с этим ID-Card не зарегистрирован. Сначала войдите в систему через E-IMZO на сайте.'
                );
                return response()->json([
                    'success' => false,
                    'state'   => 'error',
                    'error'   => 'user_not_found',
                    'message' => 'Пользователь с таким ID-Card не зарегистрирован в системе',
                ], 404);
            }

            if ((int) $user->status !== 1) {
                $bot->notifyEimzoLinkResult($chatId, false, null, 'Аккаунт пользователя не активен.');
                return response()->json([
                    'success' => false,
                    'state'   => 'error',
                    'error'   => 'user_inactive',
                    'message' => 'Аккаунт не активен',
                ], 403);
            }

            // Привязываем TelegramUser
            TelegramUser::updateOrCreate(
                ['telegram_user_id' => $chatId],
                [
                    'user_identifier' => $user->username,
                    'status'          => 'linked',
                ]
            );

            Cache::forget("tg_eimzo_link:{$token}");
            Cache::forget("tg_eimzo_link:{$chatId}");
            Cache::forget("tg_eimzo_link_doc:{$documentId}");

            Log::info('Telegram account linked via E-IMZO', [
                'telegram_user_id' => $chatId,
                'user_id'          => $user->id,
                'username'         => $user->username,
                'doc_pin_last4'    => substr($pin, -4),
            ]);

            $bot->notifyEimzoLinkResult($chatId, true, $user->username);

            return response()->json([
                'success' => true,
                'state'   => 'linked',
                'message' => 'Аккаунт успешно привязан',
                'user'    => [
                    'username' => $user->username,
                    'fullName' => $cn,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('E-IMZO link check-auth exception', [
                'documentId' => $documentId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'state'   => 'error',
                'error'   => 'server_error',
                'message' => 'Серверная ошибка',
            ], 500);
        }
    }

    // ==========================================================
    //  E-IMZO curl helpers
    //  Те же что в SignDocImzoMobileApiController.
    // ==========================================================

    /**
     * POST request to E-IMZO server (для frontend методов)
     */
    private function curlPost(string $path, ?string $postData): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPost (eimzo-link)', [
                'url'   => $url,
                'error' => curl_error($ch),
            ]);
        }

        curl_close($ch);

        return $response !== false ? $response : null;
    }

    /**
     * POST request with form data (для frontend методов)
     */
    private function curlPostForm(string $path, string $postData): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPostForm (eimzo-link)', [
                'url'   => $url,
                'error' => curl_error($ch),
            ]);
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        return $response !== false ? $response : null;
    }

    /**
     * GET request to E-IMZO server (для backend методов).
     * E-IMZO backend ТРЕБУЕТ X-Real-IP и Host заголовки — без них вернёт пусто.
     */
    private function curlGet(string $path, string $clientIp, string $host): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'X-Real-IP: ' . $clientIp,
                'Host: ' . $host,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlGet (eimzo-link)', [
                'url'   => $url,
                'error' => curl_error($ch),
            ]);
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        return $response !== false ? $response : null;
    }
}
