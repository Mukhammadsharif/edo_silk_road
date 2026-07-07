<?php

namespace App\Services\Didox;

use App\Models\Admin\OrganizationUnits;
use App\Models\Didox\DidoxDocType;
use App\Models\Didox\DidoxDocument;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Синхронизация входящих документов Didox во вкладку «Внешний документооборот».
 *
 * По бизнес-процессу:
 *   Документ поступил в Didox → (webhook «новый документ» ИЛИ периодический pull)
 *   → upsert записи didox_documents → пользователь видит в списке входящих.
 *
 * Локальные статусы (list_status / approval_status) считаем здесь на основе
 * числового статуса Didox и состояния внутреннего согласования в EDOLINE.
 */
class DidoxSyncService
{
    public function __construct(
        private DidoxClient $client,
        private DidoxTokenService $tokens
    ) {}

    /**
     * Подтянуть входящие из Didox для указанного ИНН и сохранить локально.
     * Проходит по страницам (limit=100) до конца списка, но не более $maxPages.
     * Возвращает количество созданных/обновлённых записей.
     */
    public function pullIncoming(int $userId, int $orgUnitId, array $filters = [], int $maxPages = 20): int
    {
        $ownerTin = $this->tokens->resolveOrgTin($orgUnitId);
        $token    = $this->tokens->getFreshToken($userId, $orgUnitId);
        $client   = $this->client->withUserKey($token);

        $limit = (int) ($filters['limit'] ?? 100);
        $page  = (int) ($filters['page'] ?? 1);
        $count = 0;

        // owner=0 → только входящие документы (вкладка «Внешний документооборот»).
        for ($i = 0; $i < $maxPages; $i++, $page++) {
            $query = array_merge($filters, ['owner' => 0, 'limit' => $limit, 'page' => $page]);

            $resp = $client->documents($query);

            if (empty($resp['success'])) {
                Log::channel('didox')->error('[Didox] pullIncoming failed', [
                    'org' => $orgUnitId, 'tin' => $ownerTin, 'page' => $page,
                    'error' => $resp['error'] ?? null,
                ]);
                break;
            }

            $data  = $resp['data'];
            $items = $this->extractList($data);

            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                $this->upsertFromList($orgUnitId, $ownerTin, $item);
                $count++;
            }

            // Пагинация по next_page_url из ответа Didox (надёжнее, чем счёт элементов).
            if (!is_array($data) || empty($data['next_page_url'])) {
                break;
            }
        }

        Log::channel('didox')->info('[Didox] pullIncoming done', ['org' => $orgUnitId, 'tin' => $ownerTin, 'saved' => $count]);

        return $count;
    }

    /**
     * Обработать webhook «новый входящий документ». Payload — то, что шлёт Didox/прокси.
     * Минимально нужен didox_id; остальное можно дотянуть отдельным show-запросом.
     */
    public function handleWebhook(string $ownerTin, array $payload): DidoxDocument
    {
        // Мультиорганизационность: определяем организацию по ИНН получателя.
        $org = OrganizationUnits::where('tax_number', $ownerTin)->first();

        if (!$org) {
            throw new \RuntimeException("Организация с ИНН {$ownerTin} не найдена в EDOLINE — webhook отклонён.");
        }

        $doc = $this->upsertFromList($org->id, $ownerTin, $payload); // кинет исключение, если id отсутствует

        Log::channel('didox')->info('[Didox] webhook incoming', ['org' => $org->id, 'tin' => $ownerTin, 'didox_id' => $doc->didox_id]);

        return $doc;
    }

    /**
     * Обновить статус локальной записи после действия (подписал/отклонил/отправил на согласование).
     * Тянет свежие данные документа из Didox и пересчитывает list_status.
     */
    public function refreshStatus(int $userId, int $orgUnitId, string $didoxId): ?DidoxDocument
    {
        $doc = DidoxDocument::where('didox_id', $didoxId)->first();
        if (!$doc) {
            return null;
        }

        $token = $this->tokens->getFreshToken($userId, $orgUnitId);
        // owner из локальной записи — направление документа в Didox.
        $resp  = $this->client->withUserKey($token)
            ->document($didoxId, ['owner' => (int) ($doc->owner ?? 0)]);

        if (empty($resp['success'])) {
            return null;
        }

        $data   = $resp['data']['data'] ?? $resp['data'] ?? [];
        $status = $this->readStatus($data);

        $doc->didox_status = $status;
        // list_status от подписи не трогает approval_status — это независимые поля из легенды.
        $doc->list_status  = DidoxDocType::toListStatus($status);
        $doc->raw          = $data ?: $doc->raw;
        $doc->save();

        return $doc;
    }

    /* --- Локальные переходы внутреннего согласования (без обращения к Didox) --- */

    public function markSentToInternalReview(DidoxDocument $doc): void
    {
        $doc->approval_status = DidoxDocument::APPROVAL_ON_REVIEW;
        $doc->list_status     = DidoxDocument::LIST_ON_INTERNAL;
        $doc->save();
    }

    public function markInternalApproved(DidoxDocument $doc): void
    {
        $doc->approval_status = DidoxDocument::APPROVAL_APPROVED;
        $doc->save();
    }

    /* ------------------------------------------------------------------ */

    /**
     * Имена полей отличаются между списком и карточкой Didox:
     *   список  (GET /v2/documents):      doc_id, doc_status, name, partnerTin, doctype
     *   карточка (GET /v1/documents/:id): _id/documentid, status, documentno, sellertin/buyertin
     * Метод устойчив к обоим форматам.
     */
    private function upsertFromList(int $orgUnitId, string $ownerTin, array $item): DidoxDocument
    {
        $didoxId = Arr::get($item, 'doc_id')          // список v2
            ?? Arr::get($item, '_id')                 // карточка/webhook
            ?? Arr::get($item, 'documentid')
            ?? Arr::get($item, 'id');

        if (empty($didoxId)) {
            throw new \RuntimeException('Didox document id is missing in payload.');
        }

        $status = $this->readStatus($item);

        return DidoxDocument::updateOrCreate(
            ['didox_id' => (string) $didoxId],
            [
                'owner'        => Arr::get($item, 'owner'), // 0 входящий / 1 исходящий
                'doc_type'     => Arr::get($item, 'doctype') ?? Arr::get($item, 'doc_type'),
                'doc_number'   => Arr::get($item, 'name') ?? Arr::get($item, 'doc_number') ?? Arr::get($item, 'documentno'),
                'doc_date'     => $this->readDate($item),
                'partner_tin'  => Arr::get($item, 'partnerTin') ?? Arr::get($item, 'partner_tin'),
                'seller_tin'   => Arr::get($item, 'sellertin') ?? Arr::get($item, 'seller_tin'),
                'buyer_tin'    => Arr::get($item, 'buyertin') ?? Arr::get($item, 'buyer_tin'),
                'org_unit_id'  => $orgUnitId,
                'owner_tin'    => $ownerTin,
                'didox_status' => $status,
                'list_status'  => DidoxDocType::toListStatus($status),
                'raw'          => $item,
                'received_at'  => now(),
            ]
        );
    }

    private function extractList(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }
        // Обёртки Didox: {data:[...], total, next_page_url} | {documents:[...]} | прямо [...]
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }
        if (isset($data['documents']) && is_array($data['documents'])) {
            return $data['documents'];
        }
        return Arr::isList($data) ? $data : [];
    }

    private function readStatus(array $item): ?int
    {
        // список v2 → doc_status; карточка v1 → status
        $status = Arr::get($item, 'doc_status')
            ?? Arr::get($item, 'status')
            ?? Arr::get($item, 'didox_status');
        return is_null($status) ? null : (int) $status;
    }

    private function readDate(array $item): ?string
    {
        $date = Arr::get($item, 'doc_date') ?? Arr::get($item, 'documentdate') ?? Arr::get($item, 'facturadate');
        return $date ?: null;
    }
}
