<?php

namespace App\Services\Integration1C;

use App\Models\BitrixHashes;
use App\Models\Integration1C\OnecCompany;
use App\Models\Integration1C\OnecDivision;
use App\Models\Integration1C\OnecEmployee;
use App\Models\Integration1C\OnecPerson;
use App\Models\Integration1C\OnecPosition;
use Illuminate\Support\Facades\DB;

/**
 * STAGING -> VALIDATE -> PROMOTE с откатом.
 *
 * 1С пишет ТОЛЬКО в буфер onec_* (staging). Промоутер проверяет готовность
 * всей цепочки зависимостей и переносит запись в рабочие таблицы В ТРАНЗАКЦИИ.
 *
 * Если зависимость отсутствует:
 *   - запись остаётся в буфере: sync_status='pending' + sync_error=причина
 *   - при приходе недостающей зависимости retryDependents() до-синхронизирует.
 *
 * Статусы буфера: staged -> synced | pending | error
 */
class OnecPromoter
{
    public function __construct(
        private OnecSyncToWorking $sync,
        private OnecLogger $logger
    ) {}

    /** Промоут одной буферной записи. Возвращает: synced|pending|error. */
    public function promote(string $entity, string $onecId, ?int $orgUnitId = null): string
    {
        $buffer = $this->bufferModel($entity, $onecId);
        if (!$buffer) return 'error';

        $payload   = $this->payload($buffer);
        $orgUnitId = $orgUnitId ?? ($buffer->org_unit_id ?? null);

        // 1) Готовность зависимостей
        $missing = $this->checkDependencies($entity, $payload, $orgUnitId);
        if (!empty($missing)) {
            $this->markPending($buffer, $missing);
            $this->logger->syncSkipped($entity, $onecId, 'missing_dependencies', ['missing' => $missing]);
            return 'pending';
        }

        // 2) Промоут в транзакции
        try {
            $action = DB::transaction(fn () => $this->doSync($entity, $payload, $orgUnitId));

            if ($action === 'error') {
                $this->markError($buffer, 'sync returned error');
                return 'error';
            }
            if ($action === 'skipped') {
                $this->markPending($buffer, ['синк вернул skipped (проверьте обязательные поля)']);
                return 'pending';
            }

            $this->markSynced($buffer);
            $this->logger->synced($entity, $onecId, $this->targetTable($entity), $buffer->id);

            // 3) Досинхрон зависимых
            $this->retryDependents($entity, $onecId, $orgUnitId);
            return 'synced';
        } catch (\Throwable $e) {
            $this->markError($buffer, $e->getMessage());
            $this->logger->error("promote {$entity}", $e, ['onec_id' => $onecId]);
            return 'error';
        }
    }

    /** Какие связи отсутствуют для промоута (пустой массив = всё готово). */
    private function checkDependencies(string $entity, array $data, ?int $orgUnitId): array
    {
        $missing = [];

        switch ($entity) {
            case 'companies':
                if (!empty($data['parentOrganizationID']) && $data['parentOrganizationID'] !== ($data['ID'] ?? null)) {
                    if (!$this->hashExists('C', $data['parentOrganizationID'])) {
                        $missing[] = "родительская организация {$data['parentOrganizationID']} ещё не пришла";
                    }
                }
                break;

            case 'divisions':
                if (empty($orgUnitId) && empty($data['companyID'])) {
                    $missing[] = 'не определена организация (нет токена и companyID)';
                }
                if (!empty($data['parentDivisionID']) && $data['parentDivisionID'] !== ($data['ID'] ?? null)) {
                    if (!$this->hashExists('D', $data['parentDivisionID'])) {
                        $missing[] = "родительский отдел {$data['parentDivisionID']} ещё не пришёл";
                    }
                }
                break;

            case 'positions':
                break;

            case 'persons':
                if (empty($orgUnitId)) {
                    $missing[] = 'не определена организация (нужен org-токен)';
                }
                break;

            case 'employees':
                if (empty($data['personID']) || !$this->hashExists('P', $data['personID'])) {
                    $missing[] = "физлицо (person) " . ($data['personID'] ?? '?') . " ещё не синхронизировано";
                }
                $hasOrg = (!empty($data['companyID']) && $this->hashExists('C', $data['companyID'])) || !empty($orgUnitId);
                if (!$hasOrg) {
                    $missing[] = "организация не определена (нет company-хэша и токена)";
                }
                if (!empty($data['divisionID']) && !$this->hashExists('D', $data['divisionID'])) {
                    $missing[] = "отдел {$data['divisionID']} ещё не пришёл";
                }
                if (!empty($data['positionID']) && !$this->hashExists('POS', $data['positionID'])) {
                    $missing[] = "должность {$data['positionID']} ещё не пришла";
                }
                break;
        }

        return $missing;
    }

    /** Синк в рабочую таблицу (уже внутри транзакции). */
    private function doSync(string $entity, array $data, ?int $orgUnitId): string
    {
        return match ($entity) {
            'companies' => $this->sync->syncCompany($data),
            'divisions' => $this->sync->syncDivision($data, $orgUnitId),
            'positions' => $this->sync->syncPosition($data, $orgUnitId),
            'persons'   => $this->sync->syncPerson($data, $orgUnitId),
            'employees' => $this->sync->syncEmployee($data, $orgUnitId),
            default     => 'skipped',
        };
    }

    /** После прихода новой записи пробуем промоутнуть тех, кто её ждал. */
    public function retryDependents(string $entity, string $onecId, ?int $orgUnitId = null): void
    {
        $dependents = match ($entity) {
            'companies' => ['divisions', 'employees'],
            'divisions' => ['employees'],
            'positions' => ['employees'],
            'persons'   => ['employees'],
            default     => [],
        };

        foreach ($dependents as $depEntity) {
            $model = $this->bufferClass($depEntity);
            if (!$model) continue;

            $pending = $model::where('sync_status', 'pending')
                ->where('sync_attempts', '<', 20)
                ->limit(200)->get();

            foreach ($pending as $row) {
                $this->promote($depEntity, $row->onec_id, $row->org_unit_id ?? $orgUnitId);
            }
        }
    }

    /** Промоут всех pending/staged/error (кнопка "повторить" / cron). */
    public function promoteAllPending(?int $orgUnitId = null): array
    {
        $order = ['companies', 'positions', 'divisions', 'persons', 'employees'];
        $result = [];
        foreach ($order as $entity) {
            $model = $this->bufferClass($entity);
            if (!$model) continue;

            $q = $model::whereIn('sync_status', ['pending', 'staged', 'error'])
                ->where('sync_attempts', '<', 20);
            if ($orgUnitId) $q->where('org_unit_id', $orgUnitId);

            $counts = ['synced' => 0, 'pending' => 0, 'error' => 0];
            foreach ($q->limit(500)->get() as $row) {
                $status = $this->promote($entity, $row->onec_id, $row->org_unit_id ?? $orgUnitId);
                $counts[$status] = ($counts[$status] ?? 0) + 1;
            }
            $result[$entity] = $counts;
        }
        return $result;
    }

    // ───────── helpers ─────────

    private function hashExists(string $type, string $hash): bool
    {
        return BitrixHashes::where('type', $type)->where('hash', $hash)->exists();
    }

    private function payload($buffer): array
    {
        $p = $buffer->payload;
        if (is_array($p)) return $p;
        if (is_string($p)) {
            $d = json_decode($p, true);
            return is_array($d) ? $d : [];
        }
        return [];
    }

    private function markPending($buffer, array $missing): void
    {
        $buffer->forceFill([
            'sync_status'   => 'pending',
            'sync_error'    => implode('; ', $missing),
            'sync_attempts' => ($buffer->sync_attempts ?? 0) + 1,
        ])->save();
    }

    private function markError($buffer, string $error): void
    {
        $buffer->forceFill([
            'sync_status'   => 'error',
            'sync_error'    => mb_substr($error, 0, 1000),
            'sync_attempts' => ($buffer->sync_attempts ?? 0) + 1,
        ])->save();
    }

    private function markSynced($buffer): void
    {
        $buffer->forceFill([
            'sync_status'   => 'synced',
            'sync_error'    => null,
            'promoted_at'   => now(),
            'sync_attempts' => ($buffer->sync_attempts ?? 0) + 1,
        ])->save();
    }

    private function bufferClass(string $entity): ?string
    {
        return match ($entity) {
            'companies' => OnecCompany::class,
            'divisions' => OnecDivision::class,
            'positions' => OnecPosition::class,
            'persons'   => OnecPerson::class,
            'employees' => OnecEmployee::class,
            default     => null,
        };
    }

    private function bufferModel(string $entity, string $onecId)
    {
        $class = $this->bufferClass($entity);
        return $class ? $class::where('onec_id', $onecId)->first() : null;
    }

    private function targetTable(string $entity): string
    {
        return match ($entity) {
            'companies' => 'organization_units',
            'divisions' => 'departments',
            'positions' => 'user_positions',
            'persons'   => 'users',
            'employees' => 'user_work_history',
            default     => '?',
        };
    }
}
