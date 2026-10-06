<?php

namespace App\Traits;

use Illuminate\Support\Collection;

/**
 * Логика активации шагов согласования с учётом режима документа:
 *  - parallel   — все согласующие получают документ одновременно (поведение по умолчанию);
 *  - sequential — согласующие обрабатываются по уровням (queue_number 1..N):
 *                 активируется только текущий уровень, следующий — после подписания текущего.
 *
 * Методы намеренно принимают коллекцию шагов и колбэк активации,
 * чтобы не зависеть от конкретного контроллера (веб / API / мобайл).
 * Колбэк активации отвечает за смену статуса на «активен» (2) и уведомления.
 *
 * Статусы шагов: 1 — ожидание, 2 — активен, 5 — подписан/согласован.
 */
trait HandlesAgreementFlow
{
    /** Шаг ожидает своей очереди. */
    private const STEP_WAITING = 1;

    /** Шаг активен: документ лежит у согласующего. */
    private const STEP_ACTIVE = 2;

    /** Шаг подписан. */
    private const STEP_SIGNED = 5;

    private function isSequentialAgreement($journal): bool
    {
        return ($journal->agreement_type ?? 'parallel') === 'sequential';
    }

    /**
     * Начальная активация согласования (при отправке документа на согласование).
     * parallel   → активируем все шаги;
     * sequential → активируем только минимальный уровень (queue_number).
     *
     * @param  callable  $activate  function (Collection $stepsToActivate): void
     */
    public function activateInitialAgreement($journal, $agreementSteps, callable $activate): void
    {
        $steps = $this->asCollection($agreementSteps);

        if ($steps->isEmpty()) {
            return;
        }

        if (!$this->isSequentialAgreement($journal)) {
            $activate($steps);
            return;
        }

        $minLevel = (int) $steps->map(fn ($s) => (int) $s->queue_number)->min();
        $firstLevel = $steps->filter(fn ($s) => (int) $s->queue_number === $minLevel)->values();

        $activate($firstLevel->isNotEmpty() ? $firstLevel : $steps);
    }

    /**
     * Вызывается ПОСЛЕ того, как текущий согласующий подписал (его шаг уже status = 5).
     * Возвращает true, если согласование полностью завершено (можно идти дальше — к резолюции),
     * иначе активирует следующий уровень (для sequential) и возвращает false.
     *
     * ИСПРАВЛЕНО: прежняя версия определяла «текущий уровень» как минимальный
     * среди НЕподписанных и тут же проверяла, все ли шаги этого уровня подписаны.
     * Сразу после подписания последнего шага уровня N минимальным неподписанным
     * становился уровень N+1 — он, разумеется, не подписан, и метод выходил
     * с return false, не активировав его. Ветка активации следующего уровня была
     * недостижима в принципе, и документ зависал: уровень 1 подписан,
     * уровни 2..N навсегда оставались в статусе 1.
     *
     * Теперь уровень-кандидат тот же (минимальный неподписанный), но решение
     * принимается по признаку «работает ли кто-то над ним прямо сейчас»:
     *   - есть активный шаг (status = 2) → ждём, ничего не трогаем;
     *   - активных нет, значит уровень ещё не запускали → активируем его.
     *
     * @param  Collection|array  $agreementSteps  свежая выборка ВСЕХ шагов согласования (со статусами)
     * @param  callable          $activate        function (Collection $stepsToActivate): void
     */
    public function advanceAgreement($journal, $agreementSteps, callable $activate): bool
    {
        $steps = $this->asCollection($agreementSteps);

        if ($steps->isEmpty()) {
            return true;
        }

        $unsigned = $steps->filter(fn ($s) => (int) $s->status !== self::STEP_SIGNED);

        // Все согласовали — согласование завершено.
        if ($unsigned->isEmpty()) {
            return true;
        }

        // Параллельно: ждём, пока подпишут все.
        if (!$this->isSequentialAgreement($journal)) {
            return false;
        }

        // Последовательно: уровень-кандидат — минимальный из неподписанных.
        $currentLevel = (int) $unsigned->map(fn ($s) => (int) $s->queue_number)->min();
        $currentLevelSteps = $steps->filter(fn ($s) => (int) $s->queue_number === $currentLevel);

        // Кто-то из этого уровня уже держит документ — ждём его.
        $active = $currentLevelSteps->filter(fn ($s) => (int) $s->status === self::STEP_ACTIVE);

        if ($active->isNotEmpty()) {
            return false;
        }

        /*
         | Активных нет — уровень ещё не запускали, запускаем сейчас.
         |
         | Берём только шаги в статусе ожидания: на уровне могут оказаться
         | уже подписанные (если согласующих несколько и часть отработала
         | раньше), их повторно активировать нельзя. Отклонённые и
         | возвращённые статусы сюда тоже не попадут — их обрабатывает
         | отдельная ветка возврата документа.
         */
        $toActivate = $currentLevelSteps
            ->filter(fn ($s) => (int) $s->status === self::STEP_WAITING)
            ->values();

        if ($toActivate->isEmpty()) {
            // Шаги уровня в неожиданном состоянии: активировать нечего,
            // но и завершать согласование нельзя — документ зависнет молча.
            // Пишем в лог, чтобы причина была видна.
            \Illuminate\Support\Facades\Log::warning('[agreement] уровень не содержит шагов для активации', [
                'journal_id' => $journal->id ?? null,
                'level'      => $currentLevel,
                'statuses'   => $currentLevelSteps->pluck('status', 'id')->all(),
            ]);

            return false;
        }

        $activate($toActivate);

        return false;
    }

    private function asCollection($steps): Collection
    {
        if ($steps instanceof Collection) {
            return $steps;
        }
        return collect($steps);
    }
}
