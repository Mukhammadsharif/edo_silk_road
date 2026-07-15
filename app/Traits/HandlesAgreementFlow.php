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
     * @param  Collection|array  $agreementSteps  свежая выборка ВСЕХ шагов согласования (со статусами)
     * @param  callable          $activate        function (Collection $stepsToActivate): void
     */
    public function advanceAgreement($journal, $agreementSteps, callable $activate): bool
    {
        $steps = $this->asCollection($agreementSteps);

        if ($steps->isEmpty()) {
            return true;
        }

        $unsigned = $steps->filter(fn ($s) => (int) $s->status !== 5);

        // Все согласовали — согласование завершено.
        if ($unsigned->isEmpty()) {
            return true;
        }

        // Параллельно: ждём, пока подпишут все.
        if (!$this->isSequentialAgreement($journal)) {
            return false;
        }

        // Последовательно: определяем текущий (минимальный неполностью подписанный) уровень.
        $currentLevel = (int) $unsigned->map(fn ($s) => (int) $s->queue_number)->min();
        $currentLevelSteps = $steps->filter(fn ($s) => (int) $s->queue_number === $currentLevel);
        $currentLevelUnsigned = $currentLevelSteps->filter(fn ($s) => (int) $s->status !== 5);

        // Текущий уровень ещё не весь подписан — ждём остальных на этом уровне.
        if ($currentLevelUnsigned->isNotEmpty()) {
            return false;
        }

        // Текущий уровень завершён — ищем следующий.
        $nextSteps = $steps->filter(fn ($s) => (int) $s->queue_number > $currentLevel);
        if ($nextSteps->isEmpty()) {
            return true; // следующих уровней нет — согласование завершено
        }

        $nextLevel = (int) $nextSteps->map(fn ($s) => (int) $s->queue_number)->min();
        $toActivate = $steps->filter(fn ($s) => (int) $s->queue_number === $nextLevel)->values();

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
