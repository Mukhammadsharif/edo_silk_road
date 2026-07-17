<?php

namespace App\Services\FreeSign;

use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\FreeSignDocumentField;
use App\Models\Journal\JournalTypeControls;
use Illuminate\Support\Collection;

/**
 * Динамическая маршрутизация free-sign по ролям.
 *
 * Каноническая цепочка: AGREE → SIGN → RESOL → EXEC.
 * Отсутствующие роли ПРОПУСКАЮТСЯ — переход ищет следующую присутствующую.
 * Внутри роли участники активируются параллельно; переход дальше — когда
 * подписал последний из роли (как в существующем SignDocImzoController).
 *
 * Статусы шагов:  1 — спит, 2 — активен (ждёт действия), 5 — выполнено.
 * Статусы журнала: 5 Imzolashda | 6 Kelishuvda | 7 Ijro nazoratida
 *                  8 In process | 9 Rezolyutsiyada | 10 Tugatilgan
 */
class FreeSignRouteService
{
    /** Каноническая цепочка ролей (document_execution_roles.doc_code). */
    public const CHAIN = ['AGREE', 'SIGN', 'RESOL', 'EXEC'];

    public const STEP_IDLE   = 1;   // спит, не его очередь
    public const STEP_ACTIVE = 2;   // активен, ждёт действия
    public const STEP_REPORT = 4;   // отчёт отправлен (replyDocument)
    public const STEP_DONE   = 5;   // подписал / выполнил
    public const STEP_EXEC_CONFIRMED = 6;  // исполнение подтверждено (executeWorkDoc)

    /**
     * Статусы, означающие «шаг ЗАВЕРШЁН» (подпись поставлена).
     *
     * ВАЖНО: не только 5! При подтверждении исполнения executeWorkDoc()
     * ставит шагу RESOL статус 6 — это тоже завершение, и такой шаг
     * нельзя считать «не подписанным».
     */
    public const STEPS_COMPLETED = [self::STEP_DONE, self::STEP_EXEC_CONFIRMED];

    /** Шаг завершён? (подписал = 5 ИЛИ подтвердил исполнение = 6) */
    public static function isStepCompleted($status): bool
    {
        return in_array((int) $status, self::STEPS_COMPLETED, true);
    }

    /** Статус журнала по активной роли. */
    /**
     * Статус журнала при активации роли.
     *
     * ВАЖНО — семантика статусов взята из штатного handleInternalDocument():
     *   5  Imzolashda      — активен SIGN (на подписи)
     *   6  Kelishuvda      — активны AGREE (на согласовании)
     *   8  In process      — активен RESOL (резолюция накладывается)
     *   9  Rezolyutsiyada  — резолюция ПОДПИСАНА, активны EXEC
     *
     * Раньше было RESOL=>9 / EXEC=>8 — это ПЕРЕПУТАНО местами:
     *   - при активации RESOL ставился 9 (как будто резолюция уже подписана)
     *   - при активации EXEC ставился 8 (как будто резолюция ещё в работе)
     * Из-за этого документ не попадал в нужный список:
     *   «Резолюция» ищет  whereNotIn status [1,4,5,6,7,13] -> нужен 8+
     *   «Исполнение» ищет шаги EXEC со status > 1 при журнале 9
     */
    private const JOURNAL_STATUS = [
        'AGREE' => 6,   // Kelishuvda
        'SIGN'  => 5,   // Imzolashda
        'RESOL' => 8,   // In process — резолюция в работе
        'EXEC'  => 9,   // Rezolyutsiyada — резолюция подписана, исполнители активны
    ];
    public const JOURNAL_FINISHED = 10;

    /**
     * Запуск маршрута при отправке: активирует первую присутствующую роль.
     *
     * @return string|null doc_code активированной роли (null — шагов нет)
     */
    public function startRoute(DocumentJournals $journal): ?string
    {
        DocumentExecutionSteps::where('j_id', $journal->id)
            ->update(['status' => self::STEP_IDLE]);

        $first = $this->firstPresentRole($journal);

        \Illuminate\Support\Facades\Log::info('[free-sign] startRoute', [
            'j_id'       => $journal->id,
            'first_role' => $first,
            'chain'      => $this->actualChain($journal),
        ]);

        if (!$first) {
            return null;
        }

        $this->activateRole($journal, $first);

        return $first;
    }

    /**
     * Завершение шага участника: шаг → DONE, при завершении роли — переход дальше.
     *
     * @return array{role_done: bool, next_role: string|null, finished: bool}
     */
    public function completeStep(DocumentJournals $journal, DocumentExecutionSteps $step): array
    {
        $step->update(['status' => self::STEP_DONE]);

        $currentRole = $this->roleCodeOfStep($step);
        if (!$currentRole) {
            return ['role_done' => false, 'next_role' => null, 'finished' => false];
        }

        // Все ли в роли завершили? (внутри роли — параллельно)
        // Завершённым считается и 5 (подписал), и 6 (подтвердил исполнение):
        // иначе роль с шагом в статусе 6 никогда бы не закрылась.
        $pending = $this->stepsOfRole($journal, $currentRole)
            ->filter(fn ($s) => !self::isStepCompleted($s->status));

        if ($pending->isNotEmpty()) {
            return ['role_done' => false, 'next_role' => null, 'finished' => false];
        }

        // Роль закрыта → ищем следующую ПРИСУТСТВУЮЩУЮ (пропуская отсутствующие).
        $next = $this->nextPresentRole($journal, $currentRole);

        if (!$next) {
            $journal->update(['status' => self::JOURNAL_FINISHED]);
            return ['role_done' => true, 'next_role' => null, 'finished' => true];
        }

        $this->activateRole($journal, $next);

        return ['role_done' => true, 'next_role' => $next, 'finished' => false];
    }

    /**
     * Активация роли: шаги → ACTIVE, журнал → статус роли.
     * Для RESOL сохранена логика контроля (7 = на контроле / 8 = в процессе).
     */
    public function activateRole(DocumentJournals $journal, string $roleCode): Collection
    {
        $steps = $this->stepsOfRole($journal, $roleCode);

        if ($roleCode === 'RESOL') {
            $hasControl = JournalTypeControls::where('journal_type_id', $journal->j_type)
                ->where('status', 1)
                ->exists();

            if ($hasControl) {
                /* ⚠ ВНИМАНИЕ: статус 7 (Ijro nazoratida) = документ УШЁЛ КОНТРОЛЁРАМ.
                 | Шаг резолюции спит (STEP_IDLE) и ждёт, пока контролёр его активирует.
                 |
                 | ПРОБЛЕМА для free-sign: контролёров в нашем маршруте НЕТ —
                 | их активирует штатный handleSendToResolution(), который мы не зовём.
                 | Плюс список «Резолюция» исключает статус 7:
                 |     ->whereNotIn('status', [1, 4, 5, 6, 7, 13])
                 | => документ станет НЕВИДИМЫМ и зависнет навсегда.
                 |
                 | Поэтому для free-sign контроль ПРОПУСКАЕМ: сразу отдаём резолюции
                 | (статус 8), чтобы документ был виден и двигался дальше.
                 | Если контроль нужен — надо отдельно активировать контролёров.
                 */
                \Illuminate\Support\Facades\Log::info('[free-sign] RESOL: у типа журнала включён контроль, '
                    . 'но в free-sign нет контролёров — пропускаем контроль, '
                    . 'отдаём сразу резолюции (статус 8)', [
                    'j_id'   => $journal->id,
                    'j_type' => $journal->j_type,
                ]);
            }

            // Резолюция активна, документ виден в списке «Резолюция» (статус 8).
            $journal->update(['status' => 8]);
            $steps->each(fn ($s) => $s->update(['status' => self::STEP_ACTIVE]));
            return $steps;
        }

        $newStatus = self::JOURNAL_STATUS[$roleCode] ?? 8;
        $journal->update(['status' => $newStatus]);
        $steps->each(fn ($s) => $s->update(['status' => self::STEP_ACTIVE]));

        \Illuminate\Support\Facades\Log::info('[free-sign] activateRole DONE', [
            'role'        => $roleCode,
            'new_status'  => $newStatus,
            'steps'       => $steps->count(),
            'journal_now' => $journal->fresh()->status,
        ]);

        return $steps;
    }

    /* ───────────── Вспомогательные ───────────── */

    public function firstPresentRole(DocumentJournals $journal): ?string
    {
        foreach (self::CHAIN as $code) {
            if ($this->stepsOfRole($journal, $code)->isNotEmpty()) {
                return $code;
            }
        }
        return null;
    }

    public function nextPresentRole(DocumentJournals $journal, string $afterRole): ?string
    {
        $idx = array_search($afterRole, self::CHAIN, true);
        if ($idx === false) {
            return null;
        }

        for ($i = $idx + 1; $i < count(self::CHAIN); $i++) {
            if ($this->stepsOfRole($journal, self::CHAIN[$i])->isNotEmpty()) {
                return self::CHAIN[$i];
            }
        }
        return null;
    }

    /**
     * Шаги журнала по коду роли.
     * ВНИМАНИЕ: role() в DocumentExecutionSteps — это hasMany(id → exec_role),
     * поэтому фильтруем по exec_role напрямую через список id ролей с этим кодом.
     */
    public function stepsOfRole(DocumentJournals $journal, string $code): Collection
    {
        // Роли с этим кодом. ВНИМАНИЕ: если справочник пуст или status != 1 —
        // шаги не найдутся и маршрут не стартует. Смотри лог [free-sign].
        $roleIds = DocumentExecutionRoles::where('doc_code', $code)
            ->where('status', 1)
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            \Illuminate\Support\Facades\Log::debug('[free-sign] no roles for code', [
                'code' => $code,
                'hint' => 'нет записей в document_execution_roles с doc_code=' . $code . ' и status=1',
            ]);
            return collect();
        }

        $steps = DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereIn('exec_role', $roleIds)
            ->get();

        \Illuminate\Support\Facades\Log::debug('[free-sign] stepsOfRole', [
            'code'     => $code,
            'role_ids' => $roleIds->toArray(),
            'j_id'     => $journal->id,
            'found'    => $steps->count(),
        ]);

        return $steps;
    }

    /** doc_code роли конкретного шага. */
    public function roleCodeOfStep(DocumentExecutionSteps $step): ?string
    {
        return DocumentExecutionRoles::where('id', $step->exec_role)->value('doc_code');
    }

    /** Фактический маршрут (только присутствующие роли, по порядку цепочки). */
    public function actualChain(DocumentJournals $journal): array
    {
        $chain = [];
        foreach (self::CHAIN as $code) {
            if ($this->stepsOfRole($journal, $code)->isNotEmpty()) {
                $chain[] = $code;
            }
        }
        return $chain;
    }

    /**
     * Free-sign ли документ? Признак — наличие расставленных меток.
     * Используется страницами подписи, чтобы слать правильный ecp_doc_type.
     */
    public function isFreeSign(int $documentId): bool
    {
        return FreeSignDocumentField::where('document_id', $documentId)->exists();
    }

    /** Текущая активная роль. */
    public function currentActiveRole(DocumentJournals $journal): ?string
    {
        foreach (self::CHAIN as $code) {
            if ($this->stepsOfRole($journal, $code)->where('status', self::STEP_ACTIVE)->isNotEmpty()) {
                return $code;
            }
        }
        return null;
    }
}
