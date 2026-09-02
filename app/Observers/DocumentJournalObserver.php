<?php

namespace App\Observers;

use App\Models\Document\DocumentJournals;
use App\Services\Integration1C\OnecOrderCollector;

/**
 * Автоматическая фиксация кадрового приказа при переходе в подписанный статус.
 *
 * ПОЧЕМУ НАБЛЮДАТЕЛЬ, А НЕ ВЫЗОВ В КОНТРОЛЛЕРЕ.
 * Статус 10 («подписан») выставляется минимум в четырёх местах — SignDocImzoController,
 * SignLoginController, SignDocImzoApiController, SignDocImzoMobileApiController —
 * и в каждом по несколько точек. Ручные вызовы пришлось бы вставлять больше десяти раз,
 * и любая новая точка подписания молча выпала бы из интеграции.
 * Наблюдатель ловит все случаи разом, потому что везде используется
 * $journal->update(['status' => ...]) на модели.
 *
 * ВАЖНО: массовые обновления в обход модели
 * (DocumentJournals::where(...)->update(...)) наблюдателей НЕ вызывают.
 * Сейчас таких в проекте нет — при появлении фиксацию нужно будет вызвать вручную.
 */
class DocumentJournalObserver
{
    /**
     * Статусы, при которых кадровый приказ считается состоявшимся.
     *
     * 9 — резолюция подписана. Именно в этот момент SignDocImzoController
     *     (ветка sign_resolution) запускает GenerateHrOrderPdfJob и формирует
     *     сам приказ. Название «In process» относится к исполнению, которое
     *     идёт дальше, а приказ уже подписан.
     * 10 — Tugatilgan (исполнение закрыто)
     * 11 — Ijrosi ta'minlangan (исполнение обеспечено)
     *
     * Тот же набор использует DataController::hrOrders.
     *
     * ВАЖНО: статус 9 неоднозначен — его же ставит возврат на доработку
     * (ExecutionController::controlSendToEdit). Поэтому OnecOrderCollector
     * дополнительно проверяет, что шаг резолюции в статусе 5, то есть
     * действительно подписан.
     */
    private const SIGNED_STATUSES = [9, 10, 11];

    public function updated(DocumentJournals $journal): void
    {
        // Реагируем только на смену статуса, а не на любое сохранение
        if (!$journal->wasChanged('status')) {
            return;
        }

        if (!in_array((int) $journal->status, self::SIGNED_STATUSES, true)) {
            return;
        }

        // Внутри capture() свой try/catch: интеграция не должна ронять подписание.
        // Нераспознанный журнал просто вернёт null и ничего не запишет.
        app(OnecOrderCollector::class)->capture($journal->id);
    }
}
