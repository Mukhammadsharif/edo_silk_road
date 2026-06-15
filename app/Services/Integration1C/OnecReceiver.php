<?php

namespace App\Services\Integration1C;

use App\Models\Integration1C\OnecCompany;
use App\Models\Integration1C\OnecDivision;
use App\Models\Integration1C\OnecEmployee;
use App\Models\Integration1C\OnecPerson;
use App\Models\Integration1C\OnecPosition;
use App\Models\Integration1C\OnecStafflistPosition;
use App\Models\Integration1C\OnecUser;
use App\Models\Integration1C\OnecVocation;

/**
 * Приёмная сторона: 1С → EDOLINE.
 * Принимает payload в формате 1С, маппит и апсертит в локальные таблицы.
 */
class OnecReceiver
{
    /** org_unit_id из токена 1С (определяется в middleware), для синка */
    private ?int $orgUnitId = null;

    public function setOrgUnit(?int $orgUnitId): void
    {
        $this->orgUnitId = $orgUnitId;
    }

    /** Результат последнего upsert: ['record','local_id','action','existed','sync_action'] */
    private array $lastResult = [];

    public function __construct(
        private OnecMapper $mapper,
        private OnecSyncToWorking $sync,
        private OnecPromoter $promoter
    ) {}

    /** Детали последнего upsert (action: created|updated|skipped|error, existed: bool) */
    public function lastResult(): array
    {
        return $this->lastResult;
    }

    /** Сущность => класс модели */
    private const MODELS = [
        'companies'           => OnecCompany::class,
        'divisions'           => OnecDivision::class,
        'positions'           => OnecPosition::class,
        'stafflist_positions' => OnecStafflistPosition::class,
        'persons'             => OnecPerson::class,
        'employees'           => OnecEmployee::class,
        'vocations'           => OnecVocation::class,
        'users'               => OnecUser::class,
    ];

    public function modelClass(string $entity): ?string
    {
        return self::MODELS[$entity] ?? null;
    }

    /** Upsert одной записи */
    public function upsert(string $entity, array $onecData): object
    {
        $modelClass = $this->modelClass($entity);
        if (!$modelClass) {
            throw new \InvalidArgumentException("Unknown 1C entity: {$entity}");
        }

        $local = $this->mapper->toLocal($entity, $onecData);
        $keyCol = $modelClass::upsertKey();

        // Организация из токена 1С (если есть) — для фильтрации в кабинете
        if ($this->orgUnitId) {
            $local['org_unit_id'] = $this->orgUnitId;
        }

        if (empty($local[$keyCol])) {
            throw new \RuntimeException("Missing key '{$keyCol}' for {$entity}");
        }

        $record = $modelClass::updateOrCreate(
            [$keyCol => $local[$keyCol]],
            $local
        );

        // Действие в БУФЕРЕ onec_*
        $bufferAction = $record->wasRecentlyCreated ? 'created' : 'updated';

        // STAGING -> PROMOTE: промоутер проверит зависимости и атомарно (в транзакции)
        // перенесёт в рабочую таблицу. Если не готово — запись останется pending в буфере.
        // Возвращает: synced | pending | error
        $syncAction = null;
        $onecId = (string) $local[$keyCol];
        if (in_array($entity, ['companies', 'divisions', 'positions', 'persons', 'employees'], true)) {
            $syncAction = $this->promoter->promote($entity, $onecId, $this->orgUnitId);
        } elseif ($entity === 'users') {
            // users привязывают логин/пароль к существующему person — отдельно
            $syncAction = $this->sync->syncUser($onecData);
        }
        // stafflist_positions, vocations — остаются только в буфере onec_*

        $this->lastResult = [
            'record'      => $record,
            'local_id'    => $record->getKey(),
            'action'      => $syncAction ?? $bufferAction, // synced|pending|error (или created/updated если синка нет)
            'existed'     => $bufferAction === 'updated', // запись уже была в буфере
            'sync_action' => $syncAction, // synced|pending|error
        ];

        return $record;
    }

    /** Пакетный upsert */
    public function upsertList(string $entity, array $items): array
    {
        $ok = 0; $errors = [];
        foreach ($items as $i => $item) {
            try {
                $this->upsert($entity, $item);
                $ok++;
            } catch (\Throwable $e) {
                $errors[] = "#{$i}: " . $e->getMessage();
            }
        }
        return ['upserted' => $ok, 'total' => count($items), 'errors' => $errors];
    }

    /** type-коды BitrixHashes для рабочих сущностей */
    private const HASH_TYPES = [
        'companies' => 'C',
        'divisions' => 'D',
        'employees' => 'E',
        'persons'   => 'P',
    ];

    /** Удаление по ключу: чистит буфер onec_* и (для рабочих) связь BitrixHashes */
    public function delete(string $entity, string $key, ?string $extra = null): int
    {
        $modelClass = $this->modelClass($entity);
        if (!$modelClass) {
            throw new \InvalidArgumentException("Unknown 1C entity: {$entity}");
        }

        // Удаляем из буфера onec_*
        $keyCol = $modelClass::upsertKey();
        $deleted = (int) $modelClass::where($keyCol, $key)->delete();

        // Для рабочих сущностей помечаем/чистим связь BitrixHashes
        if (isset(self::HASH_TYPES[$entity]) && class_exists(\App\Models\BitrixHashes::class)) {
            try {
                \App\Models\BitrixHashes::where('type', self::HASH_TYPES[$entity])
                    ->where('hash', $key)
                    ->delete();
            } catch (\Throwable $e) {
                // не критично — связь могла отсутствовать
            }
        }

        return $deleted;
    }
}
