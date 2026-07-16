<?php

namespace App\Http\Controllers\ECP;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateHrOrderPdfJob;
use App\Models\Admin\Role;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserWorkHistory;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use App\Models\Protocol\ProtocolDocConfigs;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocument;
use App\Models\Resolution\ReplyCommentTypes;
use App\Services\FreeSign\FreeSignRouteService;
use App\Services\FreeSign\SignatureStampService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignLoginController extends Controller
{
    private const STEP_EXEC_CONFIRMED = 6;

    private array $workingDocTypes = [
        'send_to_create_res', 'send_to_agreement', 'send_to_sign',
        'from_signer_to_ag', 'from_signer_to_c_res', 'from_ag_to_res',
        'sign_resolution', 'close_exec_doc',
        'send_to_ag_out_doc', 'send_sign_out_doc', 'sign_out_doc',
        // 'free_sign_doc' СЮДА НЕ ДОБАВЛЯТЬ — обрабатывается отдельно.
        // Если добавить, уйдёт в handleInternalDocument(), где switch его
        // не найдёт → ничего не выполнится → вернётся «успех без подписи».
    ];

    public function signDocuments(Request $request): JsonResponse
    {
        $docId     = $request->ecp_doc_id;
        $docType   = $request->ecp_doc_type;
        $confirmId = $request->ecp_confirm_id ?? null;
        $resText   = $request->ecp_r_text;

        /* Free-sign: свой маршрут по ролям + штамп меток на PDF.
         | Проверка ДО in_array — иначе тип уйдёт в handleInternalDocument().
         |
         | handleFreeSignDocument() возвращает ?JsonResponse:
         |   JsonResponse — ошибка (не твоя очередь / нет резолюции / нет журнала)
         |   null         — успех
         | Поэтому null нельзя вернуть напрямую: сигнатура требует JsonResponse.
         */
        if ($docType === 'free_sign_doc') {
            $result = $this->handleFreeSignDocument($docId, []);

            return $result instanceof JsonResponse
                ? $result                      // ошибка от обработчика
                : $this->successResponse();    // null = успех
        }

        if (in_array($docType, $this->workingDocTypes)) {
            return $this->handleInternalDocument($docId, $docType, $resText);
        }

        if ($docType === 'protocol_document') {
            return $this->handleProtocolDocument($docId, $confirmId, $resText);
        }

        return $this->errorResponse(__('message.document_deleted_system'));
    }
    private function handleFreeSignDocument(int $docId, array $ecpData): ?JsonResponse
    {
        // ДИАГНОСТИКА: метод вызван? Если этой строки нет в логе —
        // значит ecp_doc_type != 'free_sign_doc' и match ушёл в default.
        \Illuminate\Support\Facades\Log::info('[free-sign] ENTER handleFreeSignDocument', [
            'doc_id' => $docId,
        ]);

        $journal = DocumentJournals::where('doc_id', $docId)->first();
        if (!$journal) {
            \Illuminate\Support\Facades\Log::error('[free-sign] журнал не найден по doc_id', ['doc_id' => $docId]);
            return $this->errorResponse(__('message.document_deleted_system'));
        }

        /** @var FreeSignRouteService $route */
        $route = app(FreeSignRouteService::class);

        $userIds = $this->authActiveUserIds();

        /* Шаг текущего пользователя, по которому уместна подпись.
         |
         | Статусы шагов в проекте:
         |   1 — спит (не его очередь)
         |   2 — активен, ждёт действия
         |   4 — отчёт отправлен (replyDocument)
         |   5 — подписал / выполнил
         |   6 — исполнение подтверждено (executeWorkDoc ставит RESOL в 6)
         |
         | Ищем 2, 5 и 6 — потому что у RESOL сценарий двухэтапный:
         | сначала подписывают резолюцию (шаг → 5), потом подтверждают
         | исполнение (шаг → 6). В обоих случаях подпись должна штамповаться.
         |
         | Приоритет — активному шагу (2): если он есть, это обычная
         | очередь подписи и маршрут надо двигать дальше.
         */
        $step = DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereIn('to_user', $userIds)
            ->whereIn('status', [
                FreeSignRouteService::STEP_ACTIVE,   // 2
                FreeSignRouteService::STEP_DONE,     // 5
                self::STEP_EXEC_CONFIRMED,           // 6
            ])
            ->orderByRaw('CASE status WHEN 2 THEN 0 ELSE 1 END')  // сперва активный
            ->first();

        /* ═══════════ СЦЕНАРИЙ 1: АВТОР ПОДТВЕРЖДАЕТ ОТПРАВКУ ═══════════
         | Условие: журнал ещё НЕ отправлен (статус 1/3).
         |
         | Проверяем статус журнала, а НЕ отсутствие шага: с тех пор как
         | поиск ищет шаги в статусах [2,5,6], у автора-участника шаг мог
         | найтись — и он ушёл бы в сценарий 2, не запустив маршрут.
         */
        $journalNotStarted = in_array((int) $journal->status, [1, 3], true);

        if (!$step || $journalNotStarted) {
            // ДИАГНОСТИКА: почему не нашли активный шаг / кто подписывает.
            // Смотри storage/logs/laravel.log по метке [free-sign].
            $allSteps = DocumentExecutionSteps::where('j_id', $journal->id)->get(['id','to_user','exec_role','status']);
            \Illuminate\Support\Facades\Log::info('[free-sign] author-scenario check', [
                'doc_id'          => $docId,
                'journal_id'      => $journal->id,
                'journal_status'  => $journal->status,
                'created_user'    => $journal->created_user,
                'auth_id'         => Auth::id(),
                'auth_work_ids'   => $userIds,
                'steps_count'     => $allSteps->count(),
                'steps'           => $allSteps->toArray(),
            ]);

            // created_user может хранить users.id ЛИБО work_history id —
            // проверяем оба варианта, чтобы не зависеть от схемы.
            $isAuthor = ((int) $journal->created_user === (int) Auth::id())
                || in_array((int) $journal->created_user, array_map('intval', $userIds), true);

            $notStarted = $journalNotStarted; // Yaratilgan / Tahrirlash

            if (!$isAuthor || !$notStarted) {
                \Illuminate\Support\Facades\Log::warning('[free-sign] REJECT: not author or already started', [
                    'is_author'   => $isAuthor,
                    'not_started' => $notStarted,
                    'status'      => $journal->status,
                ]);
                return $this->errorResponse(__('error.not_your_turn_or_already_signed'));
            }

            // Есть кого активировать?
            $activated = $route->startRoute($journal);

            \Illuminate\Support\Facades\Log::info('[free-sign] startRoute result', [
                'activated'    => $activated,
                'chain'        => $route->actualChain($journal),
                'journal_now'  => $journal->fresh()->status,
            ]);

            if (!$activated) {
                return $this->errorResponse(__('error.no_signers_in_route'));
            }

            // Уведомления первой роли.
            $meta = [
                'journalType' => $journal->journal->title ?? '',
                'docName'     => $journal->doc->doc_name ?? '',
                'fromUser'    => $this->authActiveUser()->personal->fullName() ?? '-',
            ];
            $urls = $this->buildUrls($journal);
            $url  = $this->urlForRole($activated, $urls);

            foreach ($route->stepsOfRole($journal, $activated) as $ns) {
                $this->createMailCron($ns->to_user, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']);
            }

            $this->logAction($docId, 5, $meta);

            // Метку автора НЕ штампуем — его подпись без визуального следа.
            return null;
        }

        /* ═══════════ СЦЕНАРИЙ 2: УЧАСТНИК МАРШРУТА ПОДПИСЫВАЕТ ═══════════ */

        $stepStatus = (int) $step->status;

        // 1. Двигаем маршрут ТОЛЬКО если шаг был активен (2).
        //
        //    Если шаг уже 5 (подписал) или 6 (исполнение подтверждено) —
        //    маршрут уже уехал дальше, повторно completeStep() звать НЕЛЬЗЯ:
        //    это закрыло бы роль второй раз и активировало следующую заново.
        //    В таких случаях только доштамповываем подпись на PDF.
        $result = ['role_done' => false, 'next_role' => null, 'finished' => false];

        if ($stepStatus === FreeSignRouteService::STEP_ACTIVE) {
            $result = $route->completeStep($journal, $step);
        } else {
            \Illuminate\Support\Facades\Log::info(
                '[free-sign] шаг уже завершён — маршрут не двигаем, только штампуем', [
                    'step_id'     => $step->id,
                    'step_status' => $stepStatus,
                ]
            );
        }

        // 2. Штампуем метки этого подписанта → НОВАЯ версия PDF.
        //    Ошибка штампа не должна ронять саму подпись.
        try {
            // ВАЖНО: step->to_user — это work_history.id, а метки в
            // free_sign_document_fields хранят signer_id = users.id
            // (редактор строит список подписантов по users).
            // Конвертируем work_history.id → users.id.
            $signerUserId = \App\Models\Admin\UserWorkHistory::where('id', $step->to_user)->value('user_id')
                ?? $step->to_user;

            app(SignatureStampService::class)->stampForSigner(
                $journal->id,
                $docId,
                (int) $signerUserId,
                $ecpData
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('free-sign stamp failed: ' . $e->getMessage(), [
                'doc_id'  => $docId,
                'user_id' => $step->to_user,
            ]);
        }

        // 3. Уведомления следующей роли.
        if ($result['next_role']) {
            $meta = [
                'journalType' => $journal->journal->title ?? '',
                'docName'     => $journal->doc->doc_name ?? '',
                'fromUser'    => $this->authActiveUser()->personal->fullName() ?? '-',
            ];
            $urls = $this->buildUrls($journal);
            $url  = $this->urlForRole($result['next_role'], $urls);

            foreach ($route->stepsOfRole($journal, $result['next_role']) as $ns) {
                $this->createMailCron($ns->to_user, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']);
            }
        }

        $this->logAction($docId, 5, [
            'journalType' => $journal->journal->title ?? '',
            'docName'     => $journal->doc->doc_name ?? '',
            'fromUser'    => $this->authActiveUser()->personal->fullName() ?? '-',
        ]);

        return null;
    }

    /** URL уведомления по коду роли (из buildUrls). */
    private function urlForRole(string $roleCode, array $urls): string
    {
        return match ($roleCode) {
            'AGREE' => $urls['agreement'] ?? '',
            'SIGN'  => $urls['sign'] ?? '',
            'RESOL' => $urls['resolution'] ?? '',
            'EXEC'  => $urls['execution'] ?? '',
            default => '',
        };
    }
    private function handleInternalDocument(int $docId, string $docType, ?string $resText): JsonResponse
    {
        $journal = DocumentJournals::where('doc_id', $docId)->firstOrFail();

        $steps = [
            'sign'       => $this->getStep($journal, 'SIGN'),
            'agreement'  => $this->getSteps($journal, 'AGREE'),
            'resolution' => $this->getStep($journal, 'RESOL'),
            'execution'  => $this->getSteps($journal, 'EXEC'),
        ];

        $currentAgreement = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)
            ->whereIn('to_user', $this->authActiveUserIds())
            ->first();

        $signedAgreements = $steps['agreement']->where('status', 5);

        $urls = $this->buildUrls($journal);
        $meta = [
            'journalType' => $journal->journal->title ?? '',
            'docName'     => $journal->doc->doc_name ?? '',
            'fromUser'    => $journal->fromUser->personal->fullName() ?? '-',
        ];

        $hasControl = JournalTypeControls::where('journal_type_id', $journal->j_type)
            ->where('status', 1)
            ->exists();

        $controllers = $this->getControllers();

        switch ($docType) {
            case 'send_to_sign':
                $journal->update(['status' => 5]);
                $this->updateStepStatus($steps['sign'], 2);
                $this->sendNotifications([$steps['sign']->to_user], $urls['sign'], $meta);
                $this->logAction($docId, 5, $meta);
                break;

            case 'send_to_agreement':
            case 'send_to_ag_out_doc':
                $journal->update(['status' => 6]);
                $this->activateSteps($steps['agreement'], 2, $urls['agreement'], $meta);
                $this->logAction($docId, 4, $meta);
                break;

            case 'send_to_create_res':
            case 'send_sign_out_doc':
                $this->handleSendToResolution($journal, $steps['resolution'], $hasControl, $controllers, $urls, $meta);
                $this->logAction($docId, 28, $meta);
                break;

            case 'from_signer_to_ag':
                $this->updateStepStatus($steps['sign'], 5);
                $journal->update(['status' => 6]);
                $this->activateSteps($steps['agreement'], 2, $urls['agreement'], $meta);
                $this->createReplyComment($steps['sign'], $resText, 'DOC_SIGNED');
                $this->logAction($docId, 4, $meta);
                break;

            case 'from_signer_to_c_res':
                $this->updateStepStatus($steps['sign'], 5);
                $this->handleSendToResolution($journal, $steps['resolution'], $hasControl, $controllers, $urls, $meta);
                $this->createReplyComment($steps['sign'], $resText, 'DOC_SIGNED');
                $this->logAction($docId, 28, $meta);
                break;

            case 'from_ag_to_res':
                $this->updateStepStatus($currentAgreement, 5);
                $this->createReplyComment($currentAgreement, $resText, 'DOC_SIGNED');

                if ($steps['agreement']->count() - $signedAgreements->count() === 1) {
                    $this->handleSendToResolution($journal, $steps['resolution'], $hasControl, $controllers, $urls, $meta);
                    $this->logAction($docId, 28, $meta);
                } else {
                    $this->logAction($docId, 6, $meta);
                }
                break;

            case 'sign_resolution':
                $journal->update(['status' => 9]);
                $this->updateStepStatus($steps['resolution'], 5);

                if ($resText) {
                    $resolution = DocumentResolutions::where('j_id', $journal->id)->first();
                    if ($resolution) {
                        $resolution->update(['res_text' => $resText]);
                    }
                }

                $this->activateSteps($steps['execution'], 2, $urls['execution'], $meta);

                if ($this->isHrOrderType($journal->j_type)) {
                    GenerateHrOrderPdfJob::dispatch($journal->id)->onQueue('pdf');
                }

                $this->logAction($docId, 10, $meta);
                break;

            case 'close_exec_doc':
                $journal->update(['status' => 10]);
                $this->updateStepsStatus($steps['execution'], 5);
                $this->updateStepStatus($steps['resolution'], 5);

                $resolution = DocumentResolutions::where('j_id', $journal->id)->first();
                if ($resolution) {
                    $resolution->update(['status' => 5]);
                }

                $logType = ($steps['resolution']->to_user === $this->authActiveUser()->id) ? 15 : 19;
                $this->logAction($docId, $logType, $meta);
                break;

            case 'sign_out_doc':
                $journal->update(['status' => 10]);
                $this->updateStepStatus($steps['resolution'], 5);
                $this->logAction($docId, 10, $meta);
                break;
        }

        return $this->successResponse();
    }

    private function handleProtocolDocument(int $docId, ?int $confirmId, ?string $resText): JsonResponse
    {
        $document = ProtocolDocument::findOrFail($docId);

        $member = ProtocolDocMember::where('doc_id', $docId)
            ->whereIn('user_id', $this->authActiveUserIds())
            ->where('status', 2)
            ->firstOrFail();

        if (in_array($confirmId, [4, 5, 7]) && empty(trim($resText ?? ''))) {
            return $this->errorResponse(__('message.comment_on_document'));
        }

        $member->update(['status' => $confirmId]);

        $configs = ProtocolDocConfigs::where('protocol_type_id', $document->docType->id)
            ->orderBy('order')
            ->get();

        $signerRoles = $configs->pluck('role_id')->toArray();

        if ($document->docType->sign_type == 1) {
            // Параллельное подписание
            $pending = ProtocolDocMember::where('doc_id', $docId)
                ->whereIn('role_id', $signerRoles)
                ->whereNotIn('status', [3, 5, 7])
                ->count();

            if (!$pending) {
                $document->update(['status' => 3]);
            }
        } else {
            // Последовательное подписание
            $unfinished = ProtocolDocMember::where('doc_id', $docId)
                ->where('role_id', $member->role_id)
                ->whereNotIn('status', [3, 5, 7])
                ->count();

            if (!$unfinished) {
                $currentConfig = $configs->where('role_id', $member->role_id)->first();
                $this->activateNextRole($document, $document->docType, $currentConfig?->order ?? 0);
            }
        }

        ProtocolDocReplyComment::create([
            'doc_id'        => $docId,
            'doc_member_id' => $member->id,
            'user_id'       => $this->authActiveUser()->id,
            'doc_text'      => $resText ?? '',
            'status'        => $confirmId,
        ]);

        $logType = $document->docType->title ?? 'Protocol Document';
        $docName = $document->doc_name ?? $docId;

        $this->logAction($docId, 20, $logType, $docName);

        return $this->successResponse();
    }

    // === Вспомогательные методы ===

    private function getStep($journal, string $code): ?DocumentExecutionSteps
    {
        return DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereHas('role', fn($q) => $q->where('doc_code', $code)->where('status', 1))
            ->first();
    }

    private function getSteps($journal, string $code): \Illuminate\Database\Eloquent\Collection
    {
        return DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereHas('role', fn($q) => $q->where('doc_code', $code)->where('status', 1))
            ->get();
    }

    private function updateStepStatus(?DocumentExecutionSteps $step, int $status): void
    {
        $step?->update(['status' => $status]);
    }

    private function updateStepsStatus($steps, int $status): void
    {
        $steps->each(fn($s) => $s->update(['status' => $status]));
    }

    private function activateSteps($steps, int $status, string $url, array $meta): void
    {
        $steps->each(function ($step) use ($status, $url, $meta) {
            $step->update(['status' => $status]);
            $this->createMailCron($step->to_user, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']);
        });
    }

    private function sendNotifications(array $userIds, string $url, array $meta): void
    {
        foreach ($userIds as $userId) {
            $this->createMailCron($userId, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']);
        }
    }

    private function handleSendToResolution($journal, $resolutionStep, bool $hasControl, $controllers, array $urls, array $meta): void
    {
        if ($hasControl) {
            $journal->update(['status' => 7]);
            $this->updateStepStatus($resolutionStep, 1);
            $this->sendNotifications($controllers->pluck('id')->toArray(), $urls['control'], $meta);
        } else {
            $journal->update(['status' => 8]);
            $this->updateStepStatus($resolutionStep, 2);
            $this->createMailCron($resolutionStep->to_user, $meta['journalType'], $meta['fromUser'], $urls['resolution'], $meta['docName']);
        }
    }

    private function createReplyComment($step, ?string $text, string $code): void
    {
        $type = ReplyCommentTypes::where('doc_code', $code)->firstOrFail();

        DocumentReplyComments::create([
            'doc_exec_step_id'   => $step->id,
            'j_id'               => $journal->id ?? $step->j_id,
            'from_dep'           => $this->authActiveUser()->dep_id,
            'from_user'          => $this->authActiveUser()->id,
            'reply_comment_type' => $type->id,
            'res_text'           => $text ?? '',
            'status'             => 5,
        ]);
    }

    private function getControllers(): \Illuminate\Support\Collection
    {
        $role = Role::whereIn('name', ['Ijro nazorati', 'Control_Emp'])->first();

        return $role
            ? UserWorkHistory::where('org_id', $this->organizationId())->where('status', 1)->where('org_id', $this->organizationId())->whereHas('user.userRoles', fn($q) => $q->where('role_id', $role->id))->get()
            : collect();
    }

    private function isHrOrderType(int $jType): bool
    {
        $hrOrderType = JournalTypes::where('org_id', $this->organizationId())->where('doc_code', 'HR_ORD')->where('status', 1)->first();
        if (!$hrOrderType) return false;

        return JournalTypes::where('org_id', $this->organizationId())
            ->where('parent_id', $hrOrderType->id)
            ->where('status', 1)
            ->pluck('id')
            ->contains($jType);
    }

    private function buildUrls($journal): array
    {
        $base = $journal->id . '/' . $journal->doc->doc_hash;

        return [
            'sign'       => 'documents/view-sign-document/' . $base,
            'agreement'  => 'documents/view-approval-document/' . $base,
            'resolution' => 'documents/view-confirm-document/' . $base,
            'execution'  => 'documents/view-exec-document/' . $base,
            'control'    => 'documents/view-control-document/' . $base,
        ];
    }

    /**
     * Обёртка над parent::createLog для удобной передачи $meta
     */
    private function logAction(int $docId, int $actionId, array|string $logType, ?string $docName = null): void
    {
        $type = is_array($logType) ? ($logType['journalType'] ?? '') : $logType;
        $name = $docName ?? (is_array($logType) ? ($logType['docName'] ?? '') : '');

        parent::createLog($docId, $actionId, $type, $name);
    }

    private function successResponse(): JsonResponse
    {
        return response()->json([
            'success' => 200,
            'title'   => __('message.success_message'),
            'message' => __('message.task_completed_successfully'),
        ]);
    }

    private function errorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => 201,
            'title'   => __('message.error_message'),
            'message' => $message,
        ]);
    }

    private function activateNextRole($document, $protocolType, int $current_order): void
    {
        $configs = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)
            ->orderBy('order')
            ->get();

        $next_config = $configs->where('order', '>', $current_order)
            ->sortBy('order')
            ->first();

        while ($next_config) {
            $has_members = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', $next_config->role_id)
                ->exists();

            if ($has_members) {
                ProtocolDocMember::where('doc_id', $document->id)
                    ->where('role_id', $next_config->role_id)
                    ->update(['status' => 2]);

                $next_members = ProtocolDocMember::where('doc_id', $document->id)
                    ->where('role_id', $next_config->role_id)
                    ->get();

                $url = "protocol/{$protocolType->doc_code}/{$document->id}/{$document->doc_hash}";

                $fromUserName = $document->created_user
                    ? UserPersonalInfo::where('user_id', $document->created_user)->first()?->fullName() ?? '-'
                    : '-';

                foreach ($next_members as $member) {
                    $this->createMailCron(
                        $member->user_id,
                        $document->docType->title ?? '',
                        $fromUserName,
                        $url,
                        $document->doc_name ?? ''
                    );
                }

                return;
            }

            $current_order = $next_config->order;
            $next_config = $configs->where('order', '>', $current_order)
                ->sortBy('order')
                ->first();
        }

        // Все роли пройдены — документ завершён
        $document->update(['status' => 3]);
    }
}
