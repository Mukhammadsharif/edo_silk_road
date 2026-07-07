<?php

namespace App\Http\Controllers\Api\Didox;

use App\Http\Controllers\Controller;
use App\Services\Didox\DidoxClient;
use App\Services\Didox\DidoxTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Авторизация EDOLINE в Didox.
 *
 * Поддержаны оба способа (по выбору пользователя):
 *  - ЭЦП: клиент присылает готовый signature (timeStampTokenB64), см. E-IMZO.
 *  - пароль: пароль регистрации Didox.
 *
 * Приватный ключ ЭЦП НИКОГДА не приходит на сервер — подпись формируется
 * на стороне клиента через E-IMZO, сюда попадает лишь результат.
 */
class DidoxAuthController extends Controller
{
    public function __construct(
        private DidoxTokenService $tokens,
        private DidoxClient $client
    ) {}

    use ResolvesDidoxClient;

    /** POST /api/didox/auth/login-ecp — логин по ЭЦП (ИНН активной организации). */
    public function loginEcp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'signature' => ['required', 'string'],   // timeStampTokenB64 (подписан ИНН организации)
            'locale'    => ['nullable', 'in:ru,uz'],
        ]);

        try {
            $this->tokens->loginByEcp(
                $request->user()->id,
                $this->currentOrgId($request),
                $data['signature'],
                $data['locale'] ?? null
            );

            return response()->json(['success' => true, 'authorized' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /api/didox/auth/login-password — логин по паролю (ИНН активной организации). */
    public function loginPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
            'locale'   => ['nullable', 'in:ru,uz'],
        ]);

        try {
            $this->tokens->loginByPassword(
                $request->user()->id,
                $this->currentOrgId($request),
                $data['password'],
                $data['locale'] ?? null
            );

            return response()->json(['success' => true, 'authorized' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/didox/auth/login-company — вход в компанию под физлицом.
     * Требует токен физлица (из login-password/login-ecp по ИНН физлица).
     */
    public function loginCompany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_token' => ['required', 'string'],
            'locale'     => ['nullable', 'in:ru,uz'],
        ]);

        try {
            $this->tokens->loginCompany(
                $request->user()->id,
                $this->currentOrgId($request),
                $data['user_token'],
                $data['locale'] ?? null
            );

            return response()->json(['success' => true, 'authorized' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /api/didox/auth/register — регистрация в Didox по ЭЦП. */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'     => ['nullable', 'email'],
            'mobile'    => ['required', 'string'],
            'password'  => ['required', 'string', 'min:6'],
            'accept'    => ['required', 'boolean'],
            'signature' => ['required', 'string'], // подписанный ИНН + timestamp (base64)
        ]);

        $resp = $this->client->signup($data);

        return response()->json($resp, $resp['success'] ? 200 : 422);
    }

    /** GET /api/didox/auth/status — есть ли живой токен для активной организации. */
    public function status(Request $request): JsonResponse
    {
        try {
            $fresh = $this->tokens->hasFreshToken($request->user()->id, $this->currentOrgId($request));
            return response()->json(['success' => true, 'authorized' => $fresh]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'authorized' => false, 'message' => $e->getMessage()]);
        }
    }
}
