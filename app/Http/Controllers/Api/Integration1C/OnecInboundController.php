<?php

namespace App\Http\Controllers\Api\Integration1C;

use App\Http\Controllers\Controller;
use App\Services\Integration1C\OnecReceiver;
use App\Services\Integration1C\OnecValidator;
use App\Services\Integration1C\OnecLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(name="1C Inbound", description="Приём данных из 1С (push) с валидацией и понятными ошибками")
 */
class OnecInboundController extends Controller
{
    public function __construct(
        private OnecReceiver $receiver,
        private OnecValidator $validator,
        private OnecLogger $logger
    ) {}

    /** Прокинуть org_unit_id (из токена 1С, проставлен middleware) в receiver */
    private function applyOrgContext(Request $request): void
    {
        $orgUnitId = $request->attributes->get('onec_org_unit_id');
        $resolved = $orgUnitId !== null ? (int) $orgUnitId : null;
        $this->receiver->setOrgUnit($resolved);
        $this->logger->event('ORG CONTEXT', [
            'org_unit_id' => $resolved,
            'source'      => $resolved !== null ? 'org_token' : 'none (static token or dev)',
        ]);
    }

    /** Ключ-идентификатор записи по сущности (для сообщений и ответа) */
    private function keyField(string $entity): string
    {
        return $entity === 'users' ? 'personID' : 'ID';
    }

    /**
     * @OA\Put(
     *     path="/api/main/api-1c/v1/{entity}/create_update/",
     *     tags={"1C Inbound"},
     *     summary="Upsert одной записи из 1С",
     *     security={{"onec_token":{}}},
     *     @OA\Parameter(name="entity", in="path", required=true, @OA\Schema(type="string",
     *         enum={"companies","divisions","positions","stafflist_positions","persons","employees","vocations","users"})),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Сохранено"),
     *     @OA\Response(response=400, description="Неизвестная сущность"),
     *     @OA\Response(response=422, description="Ошибка валидации с указанием поля")
     * )
     */
    public function createUpdate(Request $request, string $entity): JsonResponse
    {
        $this->logger->inboundReceived($entity, 'create_update', [
            'key' => $request->input($this->keyField($entity)),
        ]);
        $this->applyOrgContext($request);

        if (!$this->validator->hasEntity($entity)) {
            $this->logger->event("UNKNOWN ENTITY {$entity}", [], 'warning');
            return $this->badEntity($entity);
        }

        $data = $request->all();

        // Тело должно быть объектом, не массивом
        if (!$this->isAssoc($data)) {
            $this->logger->event("BAD BODY {$entity} (array instead of object)", [], 'warning');
            return response()->json([
                'success' => false,
                'message' => 'Ожидается объект записи, получен массив. Для списка используйте /create_update_list/',
            ], 422);
        }

        // Валидация
        $errors = $this->validator->validate($entity, $data);
        if (!empty($errors)) {
            $this->logger->validationFailed($entity, $data[$this->keyField($entity)] ?? null, $errors);
            return response()->json([
                'success' => false,
                'message' => $this->validator->entityName($entity) . ': ошибка данных',
                'errors'  => $errors,
            ], 422);
        }

        try {
            $record = $this->receiver->upsert($entity, $data);
            $result = $this->receiver->lastResult();
            $action = $result['action'] ?? ($record->wasRecentlyCreated ? 'created' : 'updated');
            $existed = $result['existed'] ?? !$record->wasRecentlyCreated;

            $this->logger->upserted($entity, $data[$this->keyField($entity)] ?? null, $record->getKey(), $action === 'created');

            $name = $this->validator->entityName($entity);
            $msg = match ($action) {
                'created' => "{$name} создан(а)",
                'updated' => "{$name} обновлён(а) (уже существовал(а))",
                'skipped' => "{$name}: синхронизация пропущена (не хватает зависимостей)",
                default   => "{$name} сохранён(а)",
            };

            return response()->json([
                'success'  => true,
                'message'  => $msg,
                'action'   => $action,   // created | updated | skipped
                'existed'  => $existed,  // true если запись уже была
                'key'      => $data[$this->keyField($entity)] ?? null,
                'local_id' => $record->getKey(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error("{$entity} create_update", $e, ['key' => $data[$this->keyField($entity)] ?? null]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка сохранения: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/main/api-1c/v1/{entity}/create_update_list/",
     *     tags={"1C Inbound"},
     *     summary="Пакетный upsert из 1С (массив в обёртке {entity:[...]})",
     *     security={{"onec_token":{}}},
     *     @OA\Parameter(name="entity", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Обработано (с детализацией по каждой записи)"),
     *     @OA\Response(response=422, description="Список не найден / ошибки в записях")
     * )
     */
    public function createUpdateList(Request $request, string $entity): JsonResponse
    {
        $this->logger->inboundReceived($entity, 'create_update_list');
        $this->applyOrgContext($request);

        if (!$this->validator->hasEntity($entity)) {
            $this->logger->event("UNKNOWN ENTITY {$entity}", [], 'warning');
            return $this->badEntity($entity);
        }

        $items = $this->extractList($request->all(), $entity);
        if ($items === null) {
            $this->logger->event("LIST NOT FOUND {$entity}", [], 'warning');
            return response()->json([
                'success' => false,
                'message' => "Не найден массив '{$entity}' в теле запроса. Ожидается {\"{$entity}\": [...]}",
            ], 422);
        }
        if (empty($items)) {
            $this->logger->event("EMPTY LIST {$entity}", [], 'warning');
            return response()->json(['success' => false, 'message' => 'Список пуст'], 422);
        }

        $keyField = $this->keyField($entity);
        $results = ['ok' => 0, 'failed' => 0, 'items' => []];

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                $results['failed']++;
                $results['items'][] = ['index' => $idx, 'success' => false, 'message' => 'Элемент не является объектом'];
                continue;
            }

            // Валидация каждого элемента
            $errors = $this->validator->validate($entity, $item);
            if (!empty($errors)) {
                $this->logger->validationFailed($entity, $item[$keyField] ?? null, $errors);
                $results['failed']++;
                $results['items'][] = [
                    'index'   => $idx,
                    'key'     => $item[$keyField] ?? null,
                    'success' => false,
                    'errors'  => $errors,
                ];
                continue;
            }

            try {
                $record = $this->receiver->upsert($entity, $item);
                $r = $this->receiver->lastResult();
                $action = $r['action'] ?? ($record->wasRecentlyCreated ? 'created' : 'updated');
                $this->logger->upserted($entity, $item[$keyField] ?? null, $record->getKey(), $action === 'created');
                $results['ok']++;
                $results['items'][] = [
                    'index'    => $idx,
                    'key'      => $item[$keyField] ?? null,
                    'success'  => true,
                    'action'   => $action,
                    'existed'  => ($r['existed'] ?? !$record->wasRecentlyCreated),
                    'local_id' => $record->getKey(),
                ];
            } catch (\Throwable $e) {
                $this->logger->error("{$entity} create_update_list item", $e, ['key' => $item[$keyField] ?? null, 'index' => $idx]);
                $results['failed']++;
                $results['items'][] = [
                    'index'   => $idx,
                    'key'     => $item[$keyField] ?? null,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // 207 если частично, 200 если все ок, 422 если все упали
        $status = $results['failed'] === 0 ? 200 : ($results['ok'] === 0 ? 422 : 207);

        $this->logger->event("LIST DONE {$entity}", [
            'ok'     => $results['ok'],
            'failed' => $results['failed'],
            'total'  => count($items),
            'status' => $status,
        ], $results['failed'] === 0 ? 'info' : 'warning');

        return response()->json([
            'success' => $results['failed'] === 0,
            'message' => "Обработано: {$results['ok']} ок, {$results['failed']} с ошибкой",
            'summary' => ['ok' => $results['ok'], 'failed' => $results['failed'], 'total' => count($items)],
            'items'   => $results['items'],
        ], $status);
    }

    /**
     * @OA\Delete(
     *     path="/api/main/api-1c/v1/{entity}/delete/",
     *     tags={"1C Inbound"},
     *     summary="Удаление записи из 1С",
     *     security={{"onec_token":{}}},
     *     @OA\Parameter(name="entity", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="ID", in="query", @OA\Schema(type="string"), description="UUID (для большинства сущностей)"),
     *     @OA\Parameter(name="personID", in="query", @OA\Schema(type="string"), description="Для users"),
     *     @OA\Response(response=200, description="Удалено / нечего удалять"),
     *     @OA\Response(response=422, description="Не передан ключ")
     * )
     */
    public function delete(Request $request, string $entity): JsonResponse
    {
        $this->logger->inboundReceived($entity, 'delete', [
            'ID'       => $request->query('ID'),
            'personID' => $request->query('personID'),
        ]);

        if (!$this->validator->hasEntity($entity)) {
            $this->logger->event("UNKNOWN ENTITY {$entity}", [], 'warning');
            return $this->badEntity($entity);
        }

        $keyField = $this->keyField($entity);
        $key = $request->query($keyField) ?? $request->query('ID') ?? $request->query('personID');

        if (!$key) {
            $this->logger->event("DELETE NO KEY {$entity}", [], 'warning');
            return response()->json([
                'success' => false,
                'message' => "Не передан параметр '{$keyField}' в query (?{$keyField}=...)",
            ], 422);
        }

        try {
            $deleted = $this->receiver->delete($entity, $key);
            $this->logger->deleted($entity, (string) $key, $deleted);
            return response()->json([
                'success' => true,
                'message' => $deleted > 0
                    ? ($this->validator->entityName($entity) . ' удалён(а)')
                    : 'Запись не найдена (уже удалена или не существовала)',
                'deleted' => $deleted,
                'key'     => $key,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error("{$entity} delete", $e, ['key' => $key]);
            return response()->json(['success' => false, 'message' => 'Ошибка удаления: ' . $e->getMessage()], 500);
        }
    }

    // ── helpers ──

    private function badEntity(string $entity): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "Неизвестная сущность: '{$entity}'. Доступны: companies, divisions, positions, stafflist_positions, persons, employees, vocations, users",
        ], 400);
    }

    /** Извлечь список из тела: {entity:[...]} или первый встретившийся массив-список */
    private function extractList(array $body, string $entity): ?array
    {
        if (isset($body[$entity]) && is_array($body[$entity])) {
            return $body[$entity];
        }
        foreach ($body as $v) {
            if (is_array($v) && (empty($v) || array_is_list($v))) {
                return $v;
            }
        }
        return null;
    }

    /** Ассоциативный массив (объект), а не список */
    private function isAssoc(array $arr): bool
    {
        if (empty($arr)) return true;
        return !array_is_list($arr);
    }
}
