<?php

namespace App\Http\Controllers\Api\Didox;

use App\Http\Controllers\Controller;
use App\Services\Didox\DidoxClient;
use App\Services\Didox\DidoxSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Служебные эндпоинты Didox:
 *  - timestamp: прикрепление TSA-метки к подписи (проксирование на /v1/dsvs/timestamp);
 *  - webhook: приём уведомления Didox о новом входящем документе.
 */
class DidoxServiceController extends Controller
{
    public function __construct(
        private DidoxClient $client,
        private DidoxSyncService $sync
    ) {}

    /** POST /api/didox/services/timestamp — прикрепить timestamp к подписи. */
    public function timestamp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pkcs7'        => ['required', 'string'],
            'signatureHex' => ['required', 'string'],
        ]);

        $resp = $this->client->timestamp($data['pkcs7'], $data['signatureHex']);

        return response()->json($resp, ($resp['success'] ?? false) ? 200 : 422);
    }

    /**
     * POST /api/didox/webhook — приём уведомления о новом входящем документе.
     * Защита секретом из config('didox.webhook_secrets') (заголовок X-Didox-Secret).
     * Не под sanctum — вызывается внешней системой.
     */
    public function webhook(Request $request): JsonResponse
    {
        if (!$this->verifySecret($request)) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook secret'], 401);
        }

        $ownerTin = (string) $request->input('owner_tin', $request->input('buyertin', ''));
        if ($ownerTin === '') {
            return response()->json(['success' => false, 'message' => 'owner_tin required'], 422);
        }

        try {
            $doc = $this->sync->handleWebhook($ownerTin, $request->all());
            return response()->json(['success' => true, 'id' => $doc->didox_id]);
        } catch (Throwable $e) {
            Log::channel('didox')->error('[Didox] webhook error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function verifySecret(Request $request): bool
    {
        $configured = array_filter(array_map('trim', explode(',', (string) config('didox.webhook_secrets'))));
        if (empty($configured)) {
            return true; // секрет не настроен — не блокируем (dev). В проде задать DIDOX_WEBHOOK_SECRETS.
        }

        $provided = (string) $request->header('X-Didox-Secret', '');
        foreach ($configured as $secret) {
            if (hash_equals($secret, $provided)) {
                return true;
            }
        }
        return false;
    }
}
