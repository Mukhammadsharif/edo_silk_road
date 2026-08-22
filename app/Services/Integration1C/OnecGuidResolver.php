<?php

namespace App\Services\Integration1C;

use App\Models\Admin\UserWorkHistory;
use App\Models\BitrixHashes;

/**
 * Локальный ID EDOLINE → GUID 1С.
 *
 * Таблица связей — bitrix_hashes, её же наполняет OnecSyncToWorking при приёме
 * справочников из 1С. Типы (менять нельзя, завязано на синк):
 *   C   → organization_units   (компания)
 *   D   → departments          (подразделение)
 *   POS → user_positions       (должность; НЕ 'P' — конфликт с persons)
 *   P   → users                (физлицо)
 *   E   → user_work_histories  (сотрудник = физлицо в конкретной должности)
 *
 * Резолвим на чтении, а не при сохранении приказа: справочник из 1С может
 * доехать позже самого приказа, и тогда GUID, «замороженный» при записи,
 * остался бы null навсегда.
 *
 * Промахи копятся в unresolved() — контроллер отдаёт их в meta ответа,
 * чтобы 1С-сторона видела, каких связей не хватает, а не гадала по null.
 */
class OnecGuidResolver
{
    public const T_COMPANY  = 'C';
    public const T_DIVISION = 'D';
    public const T_POSITION = 'POS';
    public const T_PERSON   = 'P';
    public const T_EMPLOYEE = 'E';

    /** Кэш на время запроса: "C:12" → "fd6f8080-..." */
    private array $cache = [];

    /** Промахи резолва за текущий запрос: ["employee:341", ...] */
    private array $unresolved = [];

    public function company(?int $orgUnitId): ?string
    {
        return $this->resolve(self::T_COMPANY, $orgUnitId, 'company');
    }

    public function division(?int $departmentId): ?string
    {
        return $this->resolve(self::T_DIVISION, $departmentId, 'division');
    }

    public function position(?int $positionId): ?string
    {
        return $this->resolve(self::T_POSITION, $positionId, 'position');
    }

    /** person_ID — физлицо. Используется в приказах о приёме: сотрудника ещё нет. */
    public function person(?int $userId): ?string
    {
        return $this->resolve(self::T_PERSON, $userId, 'person');
    }

    /** employee_ID — запись user_work_histories. */
    public function employee(?int $workHistoryId): ?string
    {
        return $this->resolve(self::T_EMPLOYEE, $workHistoryId, 'employee');
    }

    /**
     * employee_ID по users.id.
     *
     * Нужен там, где EDOLINE знает только пользователя, а контракт требует
     * сотрудника. Берём действующую запись работы: to_date = 9999-12-31 —
     * заглушка «бессрочно», её проставляет OnecSyncToWorking работающим сотрудникам.
     */
    public function employeeByUser(?int $userId): ?string
    {
        if (!$userId) {
            return null;
        }

        $workId = UserWorkHistory::where('user_id', $userId)
            ->orderByRaw("CASE WHEN to_date = '9999-12-31' THEN 0 ELSE 1 END")
            ->orderByDesc('from_date')
            ->value('id');

        if (!$workId) {
            $this->unresolved[] = "employee_by_user:{$userId}";

            return null;
        }

        return $this->employee((int) $workId);
    }

    private function resolve(string $type, ?int $localId, string $label): ?string
    {
        if (!$localId) {
            return null;
        }

        $key = $type . ':' . $localId;

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $guid = BitrixHashes::where('type', $type)
            ->where('local_id', $localId)
            ->value('hash');

        if (!$guid) {
            $this->unresolved[] = "{$label}:{$localId}";
        }

        return $this->cache[$key] = $guid ?: null;
    }

    /** @return string[] Уникальный список несопоставленных сущностей. */
    public function unresolved(): array
    {
        return array_values(array_unique($this->unresolved));
    }

    public function reset(): void
    {
        $this->cache = [];
        $this->unresolved = [];
    }
}
