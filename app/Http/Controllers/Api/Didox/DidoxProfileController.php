<?php

namespace App\Http\Controllers\Api\Didox;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Профиль/аккаунт в Didox.
 * Эндпоинты Didox: GET /v1/account, POST /v1/profile/update.
 */
class DidoxProfileController extends Controller
{
    use ResolvesDidoxClient;

    /** GET /api/didox/profile — данные текущего аккаунта. */
    public function show(Request $request): JsonResponse
    {
        return $this->run($request, fn ($client) => $client->account());
    }

    /** POST /api/didox/profile/update — обновить mobile/email/notifications/password. */
    public function update(Request $request): JsonResponse
    {
        $payload = $request->only(['mobile', 'email', 'password', 'notifications']);
        return $this->run($request, fn ($client) => $client->updateProfile($payload));
    }

    private function run(Request $request, callable $call): JsonResponse
    {
        try {
            $resp = $call($this->authorizedClient($request));
            return response()->json($resp, ($resp['success'] ?? false) ? 200 : ($resp['status'] ?? 422));
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
