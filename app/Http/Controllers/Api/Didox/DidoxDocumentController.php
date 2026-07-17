<?php

namespace App\Http\Controllers\Api\Didox;

use App\Http\Controllers\Controller;
use App\Models\Didox\DidoxDocument;
use App\Services\Didox\DidoxSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Документы Didox: список, детали, создание/подпись/отклонение, печать,
 * синхронизация входящих (webhook/pull) и переходы внутреннего согласования.
 */
class DidoxDocumentController extends Controller
{
    use ResolvesDidoxClient;

    public function __construct(private DidoxSyncService $sync) {}

    /** GET /api/didox/documents — список из Didox (сквозной прокси с фильтрами). */
    public function index(Request $request): JsonResponse
    {
        // Имена фильтров — строго из документации GET /v2/documents
        return $this->proxy($request, fn ($client) => $client->documents($request->only([
            'owner', 'page', 'limit', 'status', 'doctype', 'name', 'sum', 'partner',
            'dateFromCreated', 'dateToCreated', 'dateFromUpdated', 'dateToUpdated',
            'signDateFrom', 'signDateTo', 'docDateFromCreated', 'docDateToCreated',
            'contractName', 'contractDate', 'hasCommittent', 'hasLgota', 'hasMarks', 'oneside',
        ])));
    }

    /** GET /api/didox/documents/statistics — счётчики. */
    public function statistics(Request $request): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->statistics());
    }

    /** GET /api/didox/documents/{id} — детали. */
    public function show(Request $request, string $id): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->document($id, $request->only(['owner'])));
    }

    /** POST /api/didox/documents/{docType}/create — создать черновик. */
    public function store(Request $request, string $docType): JsonResponse
    {
        $payload = $request->input('data', $request->except(['locale']));
        return $this->proxy($request, fn ($client) => $client->createDocument($docType, $payload, $request->input('locale')));
    }

    /** POST /api/didox/documents/{id}/update/{docType} — обновить черновик. */
    public function updateDraft(Request $request, string $id, string $docType): JsonResponse
    {
        $payload = $request->input('data', $request->except(['locale']));
        return $this->proxy($request, fn ($client) => $client->updateDocument($id, $docType, $payload));
    }

    /** POST /api/didox/documents/{id}/sign — подписать (signature = timeStampTokenB64). */
    public function sign(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['signature' => ['required', 'string']]);

        $resp = $this->proxyRaw($request, fn ($client) => $client->signDocument($id, $data['signature']));

        // После успешной подписи обновляем локальный статус списка (🟢 Подписан).
        if (($resp['success'] ?? false)) {
            try {
                $this->sync->refreshStatus($request->user()->id, $this->currentOrgId($request), $id);
            } catch (Throwable $e) { /* не критично для ответа */ }
        }

        return response()->json($resp, ($resp['success'] ?? false) ? 200 : 422);
    }

    /** POST /api/didox/documents/{id}/reject — отклонить. */
    public function reject(Request $request, string $id): JsonResponse
    {
        // По разделу 8 документации отказ подписывается: tosign(reject) → подпись → timestamp → сюда
        $data = $request->validate(['comment' => ['required', 'string'], 'signature' => ['nullable', 'string']]);
        return $this->proxy($request, fn ($client) => $client->rejectDocument($id, $data['comment'], $data['signature'] ?? null));
    }

    /** POST /api/didox/documents/{id}/delete/draft — удалить черновик. */
    public function destroyDraft(Request $request, string $id): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->deleteDraft($id));
    }

    /** POST /api/didox/documents/{id}/delete — аннулировать/удалить. */
    public function destroy(Request $request, string $id): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->deleteDocument($id));
    }

    /** POST /api/didox/documents/{id}/tosign — данные к подписанию (accept/cancel/...). */
    public function toSign(Request $request, string $id): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->toSign($id, $request->only(['action'])));
    }

    /** GET /api/didox/documents/{id}/html/{locale} — печатная форма HTML. */
    public function html(Request $request, string $id, string $locale = 'ru'): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->html($id, $locale));
    }

    /** GET /api/didox/documents/{id}/pdf/{locale} — печатная форма PDF. */
    public function pdf(Request $request, string $id, string $locale = 'ru'): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->pdf($id, $locale));
    }

    /** GET /api/didox/documents/{id}/archive — zip-архив (ссылка живёт 5 мин). */
    public function archive(Request $request, string $id): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->archive($id));
    }

    /** GET /api/didox/documents/exchange — биржевые лоты. */
    public function exchange(Request $request): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->exchange($request->only(['buyerTin', 'lotId'])));
    }

    /** GET /api/didox/documents/exchange/types — типы биржевых операций. */
    public function exchangeTypes(Request $request): JsonResponse
    {
        return $this->proxy($request, fn ($client) => $client->exchangeTypes());
    }

    /* ---------------- Синхронизация входящих (наша сторона) ---------------- */

    /** POST /api/didox/documents/sync/pull — подтянуть входящие в локальный список. */
    public function syncPull(Request $request): JsonResponse
    {
        $data = $request->validate(['filters' => ['nullable', 'array']]);

        try {
            $count = $this->sync->pullIncoming($request->user()->id, $this->currentOrgId($request), $data['filters'] ?? []);
            return response()->json(['success' => true, 'synced' => $count]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** GET /api/didox/documents/incoming — локальный список входящих (вкладка «Внешний документооборот»). */
    public function incoming(Request $request): JsonResponse
    {
        $data = $request->validate(['list_status' => ['nullable', 'string']]);

        $query = DidoxDocument::where('org_unit_id', $this->currentOrgId($request));
        if (!empty($data['list_status'])) {
            $query->where('list_status', $data['list_status']);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderByDesc('received_at')->paginate(20),
        ]);
    }

    /** POST /api/didox/documents/{id}/to-internal-review — отправить на внутреннее согласование. */
    public function toInternalReview(Request $request, string $id): JsonResponse
    {
        $doc = DidoxDocument::where('didox_id', $id)
            ->where('org_unit_id', $this->currentOrgId($request))
            ->firstOrFail();
        $this->sync->markSentToInternalReview($doc);

        return response()->json(['success' => true, 'data' => $doc]);
    }

    /* ------------------------------- helpers ------------------------------- */

    private function proxy(Request $request, callable $call): JsonResponse
    {
        $resp = $this->proxyRaw($request, $call);
        return response()->json($resp, ($resp['success'] ?? false) ? 200 : ($resp['status'] ?? 422));
    }

    private function proxyRaw(Request $request, callable $call): array
    {
        try {
            $client = $this->authorizedClient($request);
            return $call($client);
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
