<?php

namespace App\Observers;

use App\Models\Document\DocumentJournals;
use App\Services\Integration1C\OnecOrderCollector;

/**
 * Автоматическая фиксация кадрового приказа при переходе в подписанный статус.
 *
 * ПОЧЕМУ НАБЛЮДАТЕЛЬ, А НЕ ВЫЗОВ В КОНТРОЛЛЕРЕ.
 * Статус выставляется минимум в четырёх местах — SignDocImzoController,
 * SignLoginController, SignDocImzoApiController, SignDocImzoMobileApiController —
 * и в каждом по несколько точек. Ручные вызовы пришлось бы вставлять больше
 * десяти раз, и любая новая точка подписания молча выпала бы из интеграции.
 *
 * ПОЧЕМУ ЗАХВАТ ОТЛОЖЕН ДО КОНЦА ЗАПРОСА.
 * В SignDocImzoController порядок такой:
 *
 *     $journal->update(['status' => 9]);                  // ← наблюдатель здесь
 *     $this->updateStepStatus($steps['resolution'], 5);   // ← подпись ставится позже
 *
 * То есть в момент срабатывания наблюдателя шаг резолюции ещё не помечен
 * подписанным, и OnecOrderCollector честно пропускает приказ как неподписанный.
 * Именно это и происходило на проде: в логах ORDER SKIPPED, хотя резолюция
 * подписывалась через долю секунды.
 *
 * Поэтому не вызываем capture() сразу, а откладываем через terminating():
 * колбэк выполняется после отправки ответа, когда все записи в рамках запроса
 * уже сделаны. Побочная польза — подписание не ждёт работы интеграции.
 *
 * ВАЖНО: массовые обновления в обход модели
 * (DocumentJournals::where(...)->update(...)) наблюдателей НЕ вызывают.
 * Сейчас таких в проекте нет — при появлении фиксацию нужно вызвать вручную.
 */
class DocumentJournalObserver
{
    /**
     * Статусы, при которых кадровый приказ считается состоявшимся.
     *
     * 9 — резолюция подписана. Именно в этот момент SignDocImzoController
     *     запускает GenerateHrOrderPdfJob и формирует сам приказ. Название
     *     «In process» относится к исполнению, которое идёт дальше.
     * 10 — Tugatilgan (исполнение закрыто)
     * 11 — Ijrosi ta'minlangan (исполнение обеспечено)
     *
     * Тот же набор использует DataController::hrOrders.
     *
     * Статус 9 неоднозначен: его же ставит возврат на доработку
     * (ExecutionController::controlSendToEdit). Различает их OnecOrderCollector
     * по статусу шага резолюции — но только после того, как этот шаг записан,
     * ради чего захват и отложен.
     */
    private const SIGNED_STATUSES = [9, 10, 11];

    /**
     * Уже запланированные к захвату документы текущего запроса.
     *
     * Статус журнала за один запрос может меняться несколько раз
     * (например 9, затем 10) — без этого списка capture() вызвался бы дважды.
     */
    private static array $queued = [];

    public function updated(DocumentJournals $journal): void
    {
        // Реагируем только на смену статуса, а не на любое сохранение
        if (!$journal->wasChanged('status')) {
            return;
        }

        if (!in_array((int) $journal->status, self::SIGNED_STATUSES, true)) {
            return;
        }

        $jId = (int) $journal->id;

        if (isset(self::$queued[$jId])) {
            return;
        }

        self::$queued[$jId] = true;

        // Откладываем до конца запроса: к этому моменту шаг резолюции
        // уже помечен подписанным, и проверка отработает верно
        app()->terminating(function () use ($jId) {
            unset(self::$queued[$jId]);

            // Внутри capture() свой try/catch: интеграция не должна ронять
            // подписание. Нераспознанный журнал просто вернёт null.
            app(OnecOrderCollector::class)->capture($jId);
        });
    }
}
