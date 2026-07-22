<?php

namespace App\Services\Didox;

use App\Models\Didox\DidoxDocument;
use App\Models\Didox\DidoxDocumentApprover;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Внутреннее согласование входящих документов Didox.
 *
 * Реализует правую ветку бизнес-процесса:
 *   Отправить на внутреннее согласование → Выбор согласующих →
 *   Процесс внутреннего согласования → Обновление статуса в системе.
 *
 * Правила статусов (легенда схемы):
 *   - отправили на согласование → list_status = on_internal_review 🟡,
 *     approval_status = on_review 🟡
 *   - ВСЕ согласующие approved → approval_status = approved 🟢
 *   - хотя бы один rejected → approval_status остаётся on_review,
 *     документ требует внимания (реджект виден в списке согласующих)
 */
class DidoxApprovalService
{
    /**
     * Отправить документ на внутреннее согласование выбранным пользователям.
     *
     * @param int[] $userIds согласующие
     */
    public function sendToReview(DidoxDocument $doc, array $userIds, int $assignedBy): DidoxDocument
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));

        if (empty($userIds)) {
            throw new InvalidArgumentException('Не выбран ни один согласующий.');
        }

        DB::transaction(function () use ($doc, $userIds, $assignedBy) {
            foreach ($userIds as $uid) {
                DidoxDocumentApprover::updateOrCreate(
                    ['didox_document_id' => $doc->id, 'user_id' => $uid],
                    [
                        'assigned_by' => $assignedBy,
                        'status'      => DidoxDocumentApprover::PENDING,
                        'comment'     => null,
                        'decided_at'  => null,
                    ]
                );
            }

            $doc->approval_status = DidoxDocument::APPROVAL_ON_REVIEW;
            $doc->list_status     = DidoxDocument::LIST_ON_INTERNAL;
            $doc->save();
        });

        // Точка расширения: пуш в общий движок согласования EDOLINE
        // (DocumentJournals + DocumentExecutionSteps), если потребуется
        // единый список задач у согласующих.
        // $this->pushToExecutionEngine($doc, $userIds, $assignedBy);

        return $doc->refresh();
    }

    /**
     * Решение согласующего: approve/reject (+ комментарий).
     * Решение подписывается ЭЦП согласующего (E-IMZO) — pkcs7 сохраняется
     * как юридически значимое подтверждение.
     */
    public function decide(
        DidoxDocument $doc,
        int $userId,
        bool $approved,
        ?string $comment = null,
        ?string $pkcs7 = null,
        ?string $signatureHex = null,
        ?string $signerSerial = null
    ): DidoxDocument {
        $row = DidoxDocumentApprover::where('didox_document_id', $doc->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $row->update([
            'status'        => $approved ? DidoxDocumentApprover::APPROVED : DidoxDocumentApprover::REJECTED,
            'comment'       => $comment,
            'decided_at'    => now(),
            'pkcs7'         => $pkcs7,
            'signature_hex' => $signatureHex,
            'signer_serial' => $signerSerial,
            'signed_at'     => $pkcs7 ? now() : null,
        ]);

        $this->recalculate($doc);

        return $doc->refresh();
    }

    /** Пересчитать approval_status по состоянию согласующих. */
    public function recalculate(DidoxDocument $doc): void
    {
        $statuses = DidoxDocumentApprover::where('didox_document_id', $doc->id)
            ->pluck('status');

        if ($statuses->isEmpty()) {
            $doc->approval_status = DidoxDocument::APPROVAL_NOT_SENT;
        } elseif ($statuses->every(fn ($s) => $s === DidoxDocumentApprover::APPROVED)) {
            // Все согласовали → 🟢 «Прошёл внутреннее согласование»
            $doc->approval_status = DidoxDocument::APPROVAL_APPROVED;
        } else {
            // Есть pending или rejected → остаёмся «На согласовании»
            $doc->approval_status = DidoxDocument::APPROVAL_ON_REVIEW;
        }

        $doc->save();
    }

    /**
     * ЗАГОТОВКА: интеграция с общим движком согласования EDOLINE.
     * Создание DocumentJournals + DocumentExecutionSteps требует журнала,
     * ролей (DocumentExecutionRoles) и отдела — подключается, когда
     * входящие Didox решено регистрировать в общем журнале.
     */
    // private function pushToExecutionEngine(DidoxDocument $doc, array $userIds, int $assignedBy): void {}
}
