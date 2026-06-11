<?php

namespace App\Http\Controllers\Api\Integration1C;

use App\Http\Controllers\Controller;
use App\Services\Integration1C\OnecClient;
use App\Services\Integration1C\OnecMapper;
use App\Services\Integration1C\OnecLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="1C Outbound", description="Отправка данных EDOLINE → 1С")
 *
 * @OA\SecurityScheme(securityScheme="onec", type="http", scheme="bearer",
 *     description="Токен интеграции 1С (входящие) или Sanctum (исходящие)")
 */
class OnecOutboundController extends Controller
{
    public function __construct(
        private OnecClient $client,
        private OnecMapper $mapper,
        private OnecLogger $logger
    ) {}

    private const WRAPPER = [
        'companies'           => 'companies',
        'divisions'           => 'divisions',
        'positions'           => 'positions',
        'stafflist_positions' => 'stafflist_positions',
        'persons'             => 'persons',
        'employees'           => 'employees',
        'vocations'           => 'vocations',
        'users'               => 'users',
    ];

    /**
     * @OA\Post(
     *     path="/api/onec/push/{entity}",
     *     tags={"1C Outbound"},
     *     summary="Отправить одну запись EDOLINE → 1С",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="entity", in="path", required=true, @OA\Schema(type="string",
     *         enum={"companies","divisions","positions","stafflist_positions","persons","employees","vocations","users"})),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         description="Данные в формате 1С (camelCase). Либо EDOLINE-формат — будет смаппен.")),
     *     @OA\Response(response=200, description="Отправлено в 1С"),
     *     @OA\Response(response=502, description="Ошибка 1С")
     * )
     */
    public function push(Request $request, string $entity): JsonResponse
    {
        if (!isset(self::WRAPPER[$entity])) {
            return response()->json(['success' => false, 'message' => "Unknown entity: {$entity}"], 400);
        }

        // Если пришли наши snake_case данные — маппим в формат 1С; если уже camelCase — отправляем как есть
        $payload = $this->normalizeToOnec($entity, $request->all());

        try {
            $this->ensureAuth();
            $resp = $this->client->createUpdate($entity, $payload);
            $this->logger->outbound($entity, 'push/create_update', 200, ['key' => $payload['ID'] ?? $payload['personID'] ?? null]);
            return response()->json(['success' => true, 'onec' => $resp]);
        } catch (\Throwable $e) {
            $this->logger->error("outbound push {$entity}", $e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/onec/push-list/{entity}",
     *     tags={"1C Outbound"},
     *     summary="Отправить пакет записей EDOLINE → 1С",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="entity", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object",
     *         @OA\Property(property="items", type="array", @OA\Items(type="object")))),
     *     @OA\Response(response=200, description="Отправлено в 1С")
     * )
     */
    public function pushList(Request $request, string $entity): JsonResponse
    {
        if (!isset(self::WRAPPER[$entity])) {
            return response()->json(['success' => false, 'message' => "Unknown entity: {$entity}"], 400);
        }

        $items = $request->input('items', []);
        if (!is_array($items) || empty($items)) {
            return response()->json(['success' => false, 'message' => 'items пуст'], 422);
        }

        $mapped = array_map(fn($it) => $this->normalizeToOnec($entity, $it), $items);

        try {
            $this->ensureAuth();
            $resp = $this->client->createUpdateList($entity, self::WRAPPER[$entity], $mapped);
            $this->logger->outbound($entity, 'push-list/create_update_list', 200, ['count' => count($mapped)]);
            return response()->json(['success' => true, 'onec' => $resp]);
        } catch (\Throwable $e) {
            $this->logger->error("outbound push-list {$entity}", $e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/onec/push/{entity}",
     *     tags={"1C Outbound"},
     *     summary="Удалить запись в 1С EDOLINE → 1С",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="entity", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="ID", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="personID", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Удалено в 1С")
     * )
     */
    public function pushDelete(Request $request, string $entity): JsonResponse
    {
        if (!isset(self::WRAPPER[$entity])) {
            return response()->json(['success' => false, 'message' => "Unknown entity: {$entity}"], 400);
        }
        $query = array_filter([
            'ID'       => $request->query('ID'),
            'personID' => $request->query('personID'),
        ]);
        if (empty($query)) {
            return response()->json(['success' => false, 'message' => 'Не передан ID/personID'], 422);
        }

        try {
            $this->ensureAuth();
            $resp = $this->client->delete($entity, $query);
            $this->logger->outbound($entity, 'push/delete', 200, $query);
            return response()->json(['success' => true, 'onec' => $resp]);
        } catch (\Throwable $e) {
            $this->logger->error("outbound delete {$entity}", $e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        }
    }

    /** Если данные пришли в snake_case (наши колонки) — конвертируем в формат 1С */
    private function normalizeToOnec(string $entity, array $data): array
    {
        // эвристика: если есть onec_id/snake_case ключи — маппим
        $looksLocal = isset($data['onec_id']) || isset($data['person_id']) || isset($data['company_id']);
        return $looksLocal ? $this->mapper->toOnec($entity, $data) : $data;
    }

    private function ensureAuth(): void
    {
        // Если задан логин/пароль — авторизуемся, иначе предполагаем токен в конфиге/окружении
        if (config('onec.username')) {
            $this->client->login();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/onec/export/persons",
     *     tags={"1C Outbound"},
     *     summary="Выгрузить персон EDOLINE → 1С по user_id (из рабочих таблиц + BitrixHashes)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="user_ids", type="array", @OA\Items(type="integer"), example={12,15,18}))),
     *     @OA\Response(response=200, description="Выгружено в 1С"),
     *     @OA\Response(response=502, description="Ошибка 1С")
     * )
     */
    public function exportPersons(Request $request, \App\Services\Integration1C\OnecExporter $exporter): JsonResponse
    {
        $ids = $request->input('user_ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'user_ids пуст'], 422);
        }
        try {
            $resp = $exporter->exportPersons(array_map('intval', $ids));
            $this->logger->outbound('persons', 'export/persons', 200, ['count' => count($ids)]);
            return response()->json(['success' => true, 'onec' => $resp]);
        } catch (\Throwable $e) {
            $this->logger->error('outbound export persons', $e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 502);
        }
    }
}
