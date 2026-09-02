<?php

namespace App\Http\Controllers\Api\Integration1C;

use App\Http\Controllers\Controller;
use App\Models\BitrixHashes;
use App\Models\Document\DocumentFiles;
use App\Models\Document\Documents;
use App\Models\Integration1C\OnecOrderPayload;
use App\Services\Integration1C\OnecGuidResolver;
use App\Services\Integration1C\OnecLogger;
use App\Services\Integration1C\OnecOrderExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Отдача кадровых приказов в 1С (EDOLINE → 1С, pull-модель).
 *
 * Справочники ходят в обратную сторону (1С → EDOLINE, PUT create_update) —
 * это OnecInboundController. Здесь 1С сама забирает подписанные приказы.
 *
 * Инкрементальность — через подтверждение приёма, а не только по датам:
 * 1С забирает only_new=1, разбирает, затем шлёт mark_exported со списком
 * order_ID. Пока подтверждения нет — приказ отдаётся снова. Так ничего
 * не теряется, если 1С упала между ответом и записью в свою базу.
 */
class OnecOrdersController extends Controller
{
    private const DEFAULT_PER_PAGE = 50;
    private const MAX_PER_PAGE     = 200;

    /** Диск пользовательских вложений — тот же, куда их кладёт DocumentController. */
    private const FILES_DISK = 'ftp_13';

    public function __construct(
        private OnecOrderExporter $exporter,
        private OnecLogger $logger
    ) {}

    /**
     * Берём резолвер у экспортёра, а не инжектим свой: класс не singleton,
     * и отдельный экземпляр не увидел бы промахи, накопленные при сборке ответа.
     */
    private function guid(): OnecGuidResolver
    {
        return $this->exporter->guidResolver();
    }

    /**
     * GET /api/main/api-1c/v1/orders/{type}/get_list
     *
     * Параметры:
     *   company_ID      GUID организации (иначе — организация из токена)
     *   date_from       фильтр по дате подписания, Y-m-d
     *   date_to         фильтр по дате подписания, Y-m-d
     *   modified_since  выгрузка изменённых с момента, Y-m-d H:i:s
     *   only_new        1 (по умолчанию) — только неподтверждённые
     *   with_files      1 — инлайнить файлы в base64
     *   page, per_page  пагинация (per_page ≤ 200)
     */
    public function getList(Request $request, string $type): JsonResponse
    {
        if (!OnecOrderPayload::isValidType($type)) {
            return $this->badType($type);
        }

        $this->guid()->reset();

        $orgUnitId = $this->resolveOrgUnit($request);

        $perPage = min(
            max((int) $request->input('per_page', self::DEFAULT_PER_PAGE), 1),
            self::MAX_PER_PAGE
        );

        $query = OnecOrderPayload::query()
            ->ofType($type)
            ->signedBetween($request->input('date_from'), $request->input('date_to'))
            ->modifiedSince($request->input('modified_since'))
            ->orderBy('sign_date')
            ->orderBy('id');

        if ($orgUnitId) {
            $query->where('org_unit_id', $orgUnitId);
        }

        // По умолчанию только неподтверждённые.
        // only_new=0 — полная перевыгрузка (первичная загрузка, сверка).
        if ($request->boolean('only_new', true)) {
            $query->notExported();
        }

        $page      = $query->paginate($perPage, ['*'], 'page', (int) $request->input('page', 1));
        $withFiles = $request->boolean('with_files', false);

        $data = $page->getCollection()
            ->map(fn (OnecOrderPayload $p) => $this->exporter->export($p, $withFiles))
            ->values();

        $this->logger->event('ORDERS GET_LIST', [
            'type'        => $type,
            'org_unit_id' => $orgUnitId,
            'returned'    => $data->count(),
            'total'       => $page->total(),
            'with_files'  => $withFiles,
        ]);

        $response = [
            'success'   => true,
            'data'      => $data,
            'page'      => $page->currentPage(),
            'per_page'  => $page->perPage(),
            'total'     => $page->total(),
            'last_page' => $page->lastPage(),
        ];

        // Несопоставленные справочники — чтобы 1С видела причину null в GUID-полях,
        // а не считала это ошибкой формата
        if ($unresolved = $this->guid()->unresolved()) {
            $response['meta']['unresolved_references'] = $unresolved;
        }

        return response()->json($response);
    }

    /**
     * POST /api/main/api-1c/v1/orders/{type}/mark_exported
     * Body: { "order_IDs": ["142/2026", "143/2026"] }
     *
     * Подтверждение приёма. Без него приказ попадёт в следующую выборку снова.
     */
    public function markExported(Request $request, string $type): JsonResponse
    {
        if (!OnecOrderPayload::isValidType($type)) {
            return $this->badType($type);
        }

        $ids = array_filter((array) $request->input('order_IDs', []));

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Передайте непустой массив order_IDs',
            ], 422);
        }

        $orgUnitId = $this->resolveOrgUnit($request);

        $query = OnecOrderPayload::query()
            ->ofType($type)
            ->whereIn('order_number', $ids);

        if ($orgUnitId) {
            $query->where('org_unit_id', $orgUnitId);
        }

        $found   = $query->get();
        $updated = 0;

        foreach ($found as $payload) {
            $payload->markExported();
            $updated++;
        }

        $notFound = array_values(array_diff($ids, $found->pluck('order_number')->all()));

        $this->logger->event('ORDERS MARK_EXPORTED', [
            'type'      => $type,
            'requested' => count($ids),
            'updated'   => $updated,
            'not_found' => $notFound,
        ]);

        return response()->json([
            'success'   => true,
            'updated'   => $updated,
            'not_found' => $notFound,
        ]);
    }

    /**
     * GET /api/main/api-1c/v1/orders/document/{jId}
     *
     * Скачивание самого приказа — PDF, сформированного при подписании
     * резолюции (GenerateHrOrderPdfJob). Лежит отдельно от пользовательских
     * вложений: storage/app/public/hr_orders/{doc_code}.pdf
     */
    public function orderDocument(int $jId)
    {
        $payload = OnecOrderPayload::where('j_id', $jId)->first();

        if (!$payload) {
            return response()->json(['success' => false, 'message' => 'Приказ не найден'], 404);
        }

        // Организация из токена: чужой приказ отдавать нельзя
        $orgUnitId = $this->resolveOrgUnit(request());

        if ($orgUnitId && (int) $payload->org_unit_id !== $orgUnitId) {
            return response()->json(['success' => false, 'message' => 'Приказ другой организации'], 403);
        }

        $docCode = $payload->data['doc_code']
            ?? Documents::where('id', $payload->doc_id)->value('doc_code');

        $path = 'public/hr_orders/' . $docCode . '.pdf';

        if (!$docCode || !Storage::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF приказа ещё не сформирован',
            ], 404);
        }

        return response()->streamDownload(
            fn () => print(Storage::get($path)),
            'prikaz_' . str_replace(['/', ' '], '-', (string) $payload->order_number) . '.pdf'
        );
    }

    /**
     * GET /api/main/api-1c/v1/orders/attachments/{hash}
     *
     * Скачивание вложения бинарём — основной способ забрать файлы,
     * чтобы не раздувать get_list base64-телами.
     */
    public function attachment(string $hash)
    {
        $file = DocumentFiles::where('file_hash', $hash)->first();

        if (!$file) {
            return response()->json(['success' => false, 'message' => 'Файл не найден'], 404);
        }

        $path = '/' . $file->file_path . $file->file_hash;
        $disk = Storage::disk(self::FILES_DISK);

        if (!$disk->exists($path)) {
            $this->logger->event('ORDER ATTACHMENT MISSING', ['hash' => $hash, 'path' => $path], 'warning');

            return response()->json(['success' => false, 'message' => 'Файл отсутствует в хранилище'], 404);
        }

        return response()->streamDownload(
            fn () => print($disk->get($path)),
            $file->file_name
        );
    }

    /* ─────────────────────────────── Служебное ─────────────────────────────── */

    /**
     * Организация запроса: сначала явный company_ID (GUID → local_id),
     * иначе — организация, привязанная к токену 1С (проставляет OnecAuth).
     */
    private function resolveOrgUnit(Request $request): ?int
    {
        if ($companyGuid = $request->input('company_ID')) {
            $localId = BitrixHashes::where('type', OnecGuidResolver::T_COMPANY)
                ->where('hash', $companyGuid)
                ->value('local_id');

            if ($localId) {
                return (int) $localId;
            }

            $this->logger->event('ORDERS UNKNOWN company_ID', ['company_ID' => $companyGuid], 'warning');
        }

        $fromToken = $request->attributes->get('onec_org_unit_id');

        return $fromToken !== null ? (int) $fromToken : null;
    }

    private function badType(string $type): JsonResponse
    {
        return response()->json([
            'success'       => false,
            'message'       => "Неизвестный тип приказа: {$type}",
            'allowed_types' => OnecOrderPayload::TYPES,
        ], 400);
    }
}
