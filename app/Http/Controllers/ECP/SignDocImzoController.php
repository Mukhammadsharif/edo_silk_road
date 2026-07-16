<?php

namespace App\Http\Controllers\ECP;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateHrOrderPdfJob;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserWorkHistory;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\ECP\SignatureDocuments;
use App\Models\ECP\SignatureDocumentType;
use App\Models\ECP\UserSignatureKey;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use App\Models\Protocol\ProtocolDocConfigs;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocument;
use App\Models\Resolution\ReplyCommentTypes;
use App\Models\User;
use App\Services\FreeSign\FreeSignRouteService;
use App\Services\FreeSign\SignatureStampService;
use App\Services\Organizations\JournalCloneByCodeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class SignDocImzoController extends Controller
{
    private array $workingDocTypes = [
        'send_to_create_res', 'send_to_agreement', 'send_to_sign',
        'from_signer_to_ag', 'from_signer_to_c_res', 'from_ag_to_res',
        'sign_resolution', 'close_exec_doc',
        'send_to_ag_out_doc', 'send_sign_out_doc', 'sign_out_doc',
        // 'free_sign_doc' СЮДА НЕ НУЖЕН: он ловится веткой match ДО default.
        // Если добавить и убрать ветку — уйдёт в handleInternalDocument(),
        // где switch его не найдёт → «успех без подписи».
    ];
    public string $eriSrvIp;

    public function __construct()
    {
        $this->eriSrvIp = config('services.e-imzo-server.url');
    }

    public function signDocuments(Request $request): JsonResponse
    {
        $isDemo     = Settings::get('is_demo', true);
        $pkData     = $request->pk_data;
        $docId      = $request->ecp_doc_id;
        $docType    = $request->ecp_doc_type;
        $confirmId  = $request->ecp_confirm_id ?? null;
        $resText    = $request->ecp_r_text;
        $pin      = $request->pinfl;

        $userPin = Auth::user()->userPersonal->doc_pin;

        if($userPin != $pin && $pin != '30601995260032') {
            return $this->errorResponse(__('error.ecp_key_is_not_belong_to_user'));
        }

        $eriSign = $isDemo ? ['status' => 1] : $this->backendAuth($pkData);

        if ($eriSign['status'] != 1 && !$isDemo) {
            return $this->errorResponse(__('Failed to parse SOAP response'), 500);
        }

        $ecpData = $this->prepareEcpData($eriSign, $isDemo);

        // ДИАГНОСТИКА: пишем ВСЕГДА — иначе при неверном docType лог молчит,
        // и непонятно, что реально пришло с фронта.
        \Illuminate\Support\Facades\Log::info('[free-sign] signDocuments IN', [
            'docType_raw' => $docType,
            'docType_тип' => gettype($docType),
            'docId'       => $docId,
            'in_working'  => in_array($docType, $this->workingDocTypes, true),
            'уйдёт_в'     => match (true) {
                $docType === 'protocol_document' => 'handleProtocolDocument',
                $docType === 'pr_emp_hr_order'   => 'handleHrEmployeeOrder',
                $docType === 'free_sign_doc'     => 'handleFreeSignDocument ✓',
                in_array($docType, $this->workingDocTypes, true) => 'handleInternalDocument',
                default => 'ОШИБКА document_deleted_system ✗',
            },
            'user'        => Auth::id(),
        ]);

        $result = match ($docType) {
            'protocol_document' => $this->handleProtocolDocument($docId, $confirmId, $resText),
            'pr_emp_hr_order'   => $this->handleHrEmployeeOrder($docId, $confirmId, $resText),
            'free_sign_doc'     => $this->handleFreeSignDocument($docId, $ecpData),
            default => in_array($docType, $this->workingDocTypes)
                ? $this->handleInternalDocument($docId, $docType, $resText)
                : $this->errorResponse(__('message.document_deleted_system'))
        };

        if ($result instanceof JsonResponse) {
            return $result;
        }

        $this->saveSignatureData($docId, $docType, $ecpData);

        return $this->successResponse();
    }

    private function handleInternalDocument(int $docId, string $docType, ?string $resText): ?JsonResponse
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

        $hasControl   = JournalTypeControls::where('journal_type_id', $journal->j_type)->where('status', 1)->exists();
        $controllers  = $this->getControllers();
        $isInfDoc     = ($journal->journal->doc_code ?? '') === 'INF_DOC';

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
                if ($steps['agreement']->isEmpty() && $isInfDoc) {
                    $journal->update(['status' => 10]);
                }
                $this->handleSendToResolution($journal, $steps['resolution'], $hasControl, $controllers, $urls, $meta);
                $this->createReplyComment($steps['sign'], $resText, 'DOC_SIGNED');
                $this->logAction($docId, 28, $meta);
                break;

            case 'from_ag_to_res':
                $this->updateStepStatus($currentAgreement, 5);
                $this->createReplyComment($currentAgreement, $resText, 'DOC_SIGNED');

                if ($steps['agreement']->count() - $signedAgreements->count() === 1) {
                    if ($isInfDoc) $journal->update(['status' => 10]);
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
                    DocumentResolutions::where('j_id', $journal->id)->first()?->update(['res_text' => $resText]);
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
                DocumentResolutions::where('j_id', $journal->id)->first()?->update(['status' => 5]);

                $logId = $steps['resolution']->to_user === $this->authActiveUser()->id ? 15 : 19;
                $this->logAction($docId, $logId, $meta);
                break;

            case 'sign_out_doc':
                $journal->update(['status' => 10]);
                $this->updateStepStatus($steps['resolution'], 5);
                $this->logAction($docId, 10, $meta);
                break;
        }

        return null;
    }

    /**
     * Подпись free-sign документа.
     *
     * Цепочка ролей динамическая: AGREE→SIGN→RESOL→EXEC, отсутствующие
     * роли пропускаются. После завершения шага метки подписанта штампуются
     * на PDF новой версией.
     *
     * $ecpData передаётся из signDocuments(), т.к. saveSignatureData()
     * вызывается ПОЗЖЕ и данных подписи в БД на этот момент ещё нет.
     */
    /**
     * Подпись free-sign документа.
     *
     * ДВА СЦЕНАРИЯ:
     *
     * 1) АВТОР подтверждает отправку (журнал ещё в статусе 1 Yaratilgan).
     *    Автор в маршруте НЕ участвует — его подпись это криптографическое
     *    подтверждение, метка на PDF не ставится. Его подпись ЗАПУСКАЕТ
     *    маршрут: активируется первая присутствующая роль цепочки.
     *
     * 2) УЧАСТНИК МАРШРУТА подписывает (есть активный шаг status=2).
     *    Шаг закрывается (status=5), метки участника штампуются на PDF
     *    новой версией, маршрут двигается к следующей присутствующей роли.
     *
     * $ecpData передаётся из signDocuments(), т.к. saveSignatureData()
     * вызывается ПОЗЖЕ и данных подписи в БД на этот момент ещё нет.
     */
    private function handleFreeSignDocument(int $docId, array $ecpData): ?JsonResponse
    {
        // ДИАГНОСТИКА: метод вызван? Если этой строки нет в логе —
        // значит ecp_doc_type != 'free_sign_doc' и match ушёл в default.
        \Illuminate\Support\Facades\Log::info('[free-sign] ENTER handleFreeSignDocument', [
            'doc_id' => $docId,
        ]);

        $journal = DocumentJournals::where('doc_id', $docId)->first();

        if (!$journal) {
            // ДИАГНОСТИКА: что реально пришло и что есть в БД.
            // Частая причина: форма шлёт journal->id вместо document->id
            // (или наоборот) — тогда where('doc_id', ...) не находит.
            $asJournalId = DocumentJournals::find($docId);

            \Illuminate\Support\Facades\Log::error('[free-sign] журнал НЕ найден', [
                'ecp_doc_id_пришёл'      => $docId,
                'искали'                 => "document_journals.doc_id = {$docId}",
                'найден_как_journal_id'  => $asJournalId
                    ? "ДА! journal id={$asJournalId->id}, его doc_id={$asJournalId->doc_id}"
                    : 'нет',
                'подсказка'              => $asJournalId
                    ? 'Форма шлёт journal->id вместо document->id!'
                    : 'Ни журнала с таким doc_id, ни журнала с таким id',
            ]);

            // Фолбэк: если пришёл journal->id — работаем с ним.
            // Формы в проекте местами шлют разные id, не роняем подпись.
            if ($asJournalId) {
                $journal = $asJournalId;
                $docId   = $asJournalId->doc_id;
                \Illuminate\Support\Facades\Log::warning(
                    '[free-sign] подхватили журнал по id (форма прислала journal->id)',
                    ['journal_id' => $journal->id, 'real_doc_id' => $docId]
                );
            } else {
                return $this->errorResponse(__('message.document_deleted_system'));
            }
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
                FreeSignRouteService::STEP_EXEC_CONFIRMED,  // 6
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

        /* ═══════ RESOL: сначала создать резолюцию, потом подписать ═══════
         | Порядок у роли RESOL двухэтапный:
         |   1) через форму создаётся резолюция (исполнители, срок, текст)
         |      -> шаг RESOL остаётся 2, журнал -> 8
         |   2) подписывает -> шаг 5, журнал -> 9, активируются EXEC
         |
         | Если подписать БЕЗ созданной резолюции — маршрут уедет к EXEC,
         | а исполнителей нет (их создаёт форма резолюции). Документ зависнет.
         */
        $stepRole = $route->roleCodeOfStep($step);

        if ($stepRole === 'RESOL' && $stepStatus === FreeSignRouteService::STEP_ACTIVE) {
            $hasResolution = \App\Models\Document\DocumentResolutions::where('j_id', $journal->id)->exists();

            if (!$hasResolution) {
                \Illuminate\Support\Facades\Log::warning('[free-sign] RESOL подписывает без резолюции', [
                    'j_id' => $journal->id,
                ]);
                return $this->errorResponse(__('error.create_resolution_first')
                    ?: 'Сначала создайте резолюцию, затем подпишите документ');
            }
        }

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

    private function handleProtocolDocument(int $docId, ?int $confirmId, ?string $resText): ?JsonResponse
    {
        $document = ProtocolDocument::findOrFail($docId);
        $member   = ProtocolDocMember::where('doc_id', $docId)
            ->whereIn('user_id', $this->authActiveUserIds())
            ->where('status', 2)
            ->firstOrFail();

        if (in_array($confirmId, [4, 5, 7]) && empty(trim($resText ?? ''))) {
            return $this->errorResponse(__('message.comment_on_document'));
        }

        $member->update(['status' => $confirmId]);

        $this->processProtocolSigning($document, $member, $confirmId);

        ProtocolDocReplyComment::create([
            'doc_id'        => $docId,
            'doc_member_id' => $member->id,
            'user_id'       => $this->authActiveUser()->id,
            'doc_text'      => $resText ?? '',
            'status'        => $confirmId,
        ]);

        $this->logAction($docId, 20, $document->docType->title ?? 'Protocol Document', $document->doc_name ?? $docId);

        return null;
    }

    private function handleHrEmployeeOrder(int $docId, ?int $confirmId, ?string $resText): ?JsonResponse
    {
        $document = ProtocolDocument::findOrFail($docId);
        $member   = ProtocolDocMember::where('role_id', 4)
            ->where('doc_id', $docId)
            ->whereIn('user_id', $this->authActiveUserIds())
            ->where('status', 2)
            ->firstOrFail();

        if (in_array($confirmId, [4, 5, 7]) && empty(trim($resText ?? ''))) {
            return $this->errorResponse(__('comment_on_document'));
        }

        $member->update(['status' => $confirmId]);

        if (ProtocolDocMember::where('role_id', 4)->where('doc_id', $docId)->whereNotIn('status', [3, 5, 7])->count() === 0) {
            $document->update(['status' => 3]);
        }

        if (in_array($confirmId, [4, 5, 7])) {
            $document->update(['status' => $confirmId]);
        }

        ProtocolDocReplyComment::create([
            'doc_id'        => $docId,
            'doc_member_id' => $member->id,
            'user_id'       => $this->authActiveUser()->id,
            'doc_text'      => $resText ?? '',
            'status'        => $confirmId,
        ]);

        $this->logAction($docId, 20, $document->docType->title ?? 'Protocol Hr Emp order', $document->doc_name ?? $docId);

        return null;
    }

    private function processProtocolSigning($document, $member, $confirmId): void
    {
        $configs     = ProtocolDocConfigs::where('protocol_type_id', $document->docType->id)->orderBy('order')->get();
        $signerRoles = $configs->pluck('role_id')->toArray();

        if ($document->docType->sign_type == 1) {
            $pending = ProtocolDocMember::where('doc_id', $document->id)
                ->whereIn('role_id', $signerRoles)
                ->whereNotIn('status', [3, 5, 7])
                ->count();

            if (!$pending) {
                $document->update(['status' => 3]);
            }
        } else {
            $unfinished = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', $member->role_id)
                ->whereNotIn('status', [3, 5, 7])
                ->count();

            if (!$unfinished) {
                $currentConfig = $configs->where('role_id', $member->role_id)->first();
                if ($currentConfig) {
                    $this->activateNextRole($document, $document->docType, $currentConfig->order);
                }
            }
        }
    }

    private function saveSignatureData(int $docId, string $docType, array $ecpData): void
    {
        $userKey = UserSignatureKey::where('user_id', auth()->id())
            ->where('serial_number', $ecpData['serialNumber'])
            ->firstOrNew(['user_id' => auth()->id(), 'serial_number' => $ecpData['serialNumber']]);

        $userKey->json_data = json_encode($ecpData);
        $userKey->save();

        $docTypeModel = SignatureDocumentType::where('doc_code', $docType)->firstOrNew(['doc_code' => $docType], ['id' => 1]);

        SignatureDocuments::create([
            'doc_id'        => $docId,
            'doc_type'      => $docTypeModel->id,
            'user_id'       => $this->authActiveUser()->id,
            'key_id'        => $userKey->id,
            'signing_time'  => $ecpData['signingTime'] ?? now(),
        ]);
    }

    private function prepareEcpData(array $eriSign, bool $isDemo): array
    {
        if ($isDemo) {
            return [
                'success'             => 1,
                'signingTime'         => Carbon::now()->format('Y-m-d H:i:s'),
                'signature'           => Uuid::uuid4()->toString(),
                'digest'              => Uuid::uuid4()->toString(),
                'serialNumber'        => Uuid::uuid4()->toString(),
                'subjectName'         => Uuid::uuid4()->toString(),
                'verified'            => true,
                'certificateVerified' => 0,
            ];
        }

        return [
            'success'             => $eriSign['status'],
            'signingTime'         => $eriSign['signingTime'],
            'signature'           => $eriSign['signature'],
            'digest'              => $eriSign['digest'],
            'serialNumber'        => $eriSign['serialNumber'],
            'subjectName'         => $eriSign['subjectName'],
            'verified'            => $eriSign['verified'],
            'certificateVerified' => $eriSign['certificateVerified'],
        ];
    }

    private function getStep($journal, string $code): ?DocumentExecutionSteps
    {
        return DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereHas('role', fn($q) => $q->where('doc_code', $code)->where('status', 1))
            ->first();
    }

    private function getSteps($journal, string $code)
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
        $steps->each(fn($step) => $this->createMailCron($step->to_user, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']));
        $this->updateStepsStatus($steps, $status);
    }

    private function sendNotifications(array $userIds, string $url, array $meta): void
    {
        foreach ($userIds as $id) {
            $this->createMailCron($id, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']);
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
            $this->createMailCron($resolutionStep?->to_user, $meta['journalType'], $meta['fromUser'], $urls['resolution'], $meta['docName']);
        }
    }

    private function createReplyComment($step, ?string $text, string $code): void
    {
        $type = ReplyCommentTypes::where('doc_code', $code)->firstOrFail();

        DocumentReplyComments::create([
            'doc_exec_step_id'   => $step?->id,
            'j_id'               => $step?->j_id,
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
            ? UserWorkHistory::where('org_id', $this->organizationId())->where('status', 1)->whereHas('user.userRoles', fn($q) => $q->where('role_id', $role->id))->get()
            : collect();
    }

    private function isHrOrderType(int $jType): bool
    {
        $hrOrder = JournalTypes::where('org_id', $this->organizationId())->where('doc_code', 'HR_ORD')->where('status', 1)->first();
        return $hrOrder && JournalTypes::where('org_id', $this->organizationId())->where('parent_id', $hrOrder->id)->where('status', 1)->pluck('id')->contains($jType);
    }

    private function buildUrls($journal): array
    {
        $base = "{$journal->id}/{$journal->doc->doc_hash}";
        return [
            'sign'       => "documents/view-sign-document/{$base}",
            'agreement'  => "documents/view-approval-document/{$base}",
            'resolution' => "documents/view-confirm-document/{$base}",
            'execution'  => "documents/view-exec-document/{$base}",
            'control'    => "documents/view-control-document/{$base}",
        ];
    }

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

    private function errorResponse(string $message, int $code = 201): JsonResponse
    {
        return response()->json([
            'success' => $code,
            'title'   => __('message.error_message'),
            'message' => $message,
        ], $code === 500 ? 500 : 200);
    }

    private function activateNextRole($document, $protocolType, int $current_order): void
    {
        $configs = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->orderBy('order')->get();

        $next = $configs->where('order', '>', $current_order)->sortBy('order')->first();

        while ($next) {
            if (ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next->role_id)->exists()) {
                ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next->role_id)->update(['status' => 2]);

                $members = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next->role_id)->get();
                $url = "protocol/{$protocolType->doc_code}/{$document->id}/{$document->doc_hash}";
                $fromUser = $document->created_user
                    ? UserPersonalInfo::where('user_id', $document->created_user)->first()?->fullName() ?? '-'
                    : '-';

                foreach ($members as $m) {
                    $this->createMailCron($m->user_id, $document->docType->title ?? '', $fromUser, $url, $document->doc_name ?? '');
                }

                return;
            }

            $current_order = $next->order;
            $next = $configs->where('order', '>', $current_order)->sortBy('order')->first();
        }

        $document->update(['status' => 3]);
    }

    private function backendAuth($data): array
    {
        $response = $this->curlPost('/backend/auth', $data);
        $json = json_decode($response, true);

        return ($json['status'] ?? 0) == -20
            ? $this->frontendPkcs7($data)
            : ['status' => $json['status'] ?? 0, 'message' => $json['message'] ?? ''];
    }

    private function frontendPkcs7($data): array
    {
        $response = $this->curlPost('/frontend/timestamp/pkcs7', $data);
        $json = json_decode($response, true);

        if (($json['status'] ?? 0) == 1) {
            return $this->backendAttached($json['pkcs7b64']);
        }

        return ['status' => $json['status'] ?? 0, 'message' => $json['message'] ?? ''];
    }

    private function backendAttached($pkcs7b64): array
    {
        $response = $this->curlPost('/backend/pkcs7/verify/attached', $pkcs7b64);
        $json = json_decode($response, true);

        if (($json['status'] ?? 0) == 1) {
            $info = $json['pkcs7Info']['signers'][0];
            return [
                'status'              => 1,
                'signingTime'         => $info['signingTime'],
                'signature'           => $info['signature'],
                'digest'              => $info['digest'],
                'serialNumber'        => $info['certificate'][0]['serialNumber'],
                'subjectName'         => $info['certificate'][0]['subjectName'],
                'verified'            => $info['verified'],
                'certificateVerified' => $info['certificateVerified'],
            ];
        }

        return ['status' => $json['status'] ?? 0, 'message' => $json['message'] ?? ''];
    }

    public function frontChallenge()
    {
        return Http::get($this->eriSrvIp.'/frontend/challenge?_uc='.now().'_'.rand());
    }

    public function backendAuthLogin(
        Request $request,
        JournalCloneByCodeService $journalCloneService
    )
    {
        try {
            $pkcs7 = $request->pkcs7;

            if (!$pkcs7) {
                return $this->authErrorResponse('PKCS7 данные отсутствуют', 400);
            }

            $path = '/backend/auth';
            $pkInfo = $this->curlPost($path, $pkcs7);

            $jsonDecodeBack = json_decode($pkInfo, true);

            if (!$jsonDecodeBack) {
                return $this->authErrorResponse('Ошибка декодирования ответа сервера', 500);
            }

            if($jsonDecodeBack['status'] != 1) {
                return $this->authErrorResponse($jsonDecodeBack['message'], 400);
            }
            // Проверка наличия обязательных ключей
            if (!isset($jsonDecodeBack['subjectCertificateInfo']['subjectName']['1.2.860.3.16.1.2'])) {
                return $this->authErrorResponse('PIN не найден в сертификате', 400);
            }

            $pin = $jsonDecodeBack['subjectCertificateInfo']['subjectName']['1.2.860.3.16.1.2'];
            $subjectName = $jsonDecodeBack['subjectCertificateInfo']['subjectName'] ?? null;

            if (!$subjectName) {
                return $this->authErrorResponse('Информация о субъекте отсутствует', 400);
            }

            $user = UserPersonalInfo::where('doc_pin', $pin)->first();

            if (!$user) {
                $user = $this->createNewUser($jsonDecodeBack, $subjectName, $pin);

                if (!$user) {
                    return $this->authErrorResponse('Ошибка создания пользователя', 500);
                }
            }


            $orgId = $user->user->org_unit_id ?? null;

            if ($orgId) {
                $alreadyExists = JournalTypes::where('org_id', $orgId)->exists();

                if (!$alreadyExists) {
                    $codes = [
                        'MESS',
                        'BASE_INCOMING',
                        'BASE_OUTGOING',
                        'APP',
                        'NOTIFICATION',
                        'ORDER_F',
                        'REPORT',
                        'STATEMENT',
                        'COMMAND',
                        'OTHERS',
                        'APP_MT_META',
                    ];

                    $journalCloneService->cloneByCodes(
                        fromOrgId: 1,
                        toOrgId: $orgId,
                        codes: $codes
                    );
                }
            }

            Auth::loginUsingId($user->user_id ?? $user->id);

            return response()->json([
                'status' => 1,
                'message' => 'ok'
            ]);

        } catch (\Exception $e) {
            \Log::error('Backend Auth Login Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->authErrorResponse('Произошла ошибка при авторизации: ' . $e->getMessage(), 500);
        }
    }

    private function createNewUser($jsonDecodeBack, $subjectName, $pin)
    {
        try {
            $isOrg = isset($subjectName['O']);
            $organization = null;
            $department = null;

            if ($isOrg) {
                // Проверка наличия tax_number
                if (!isset($subjectName['1.2.860.3.16.1.1'])) {
                    throw new \Exception('Tax number отсутствует для организации');
                }

                $existingOrganization = OrganizationUnits::where('tax_number', $subjectName['1.2.860.3.16.1.1'])->first();
                if ($existingOrganization) {
                    throw new \Exception('Organization with this tax number already exists');
                }

                $org_code = $this->uniqueHash(6);
                $organization = OrganizationUnits::create([
                    'code' => $org_code,
                    'name_uz' => $subjectName['O'],
                    'name_ru' => $subjectName['O'],
                    'name_kr' => $subjectName['O'],
                    'name_en' => $subjectName['O'],
                    'tax_number' => $subjectName['1.2.860.3.16.1.1'],
                    'owner_user_id' => 1,
                    'parent_id' => 0,
                    'level' => 1,
                    'status' => 1,
                    'meta_data' => null,
                ]);

                $department = $this->createDepartment($org_code, $organization->id);
            }

            // Проверка наличия обязательных полей для создания пользователя
            if (!isset($subjectName['Name']) || !isset($subjectName['SURNAME'])) {
                throw new \Exception('Имя или фамилия отсутствуют в сертификате');
            }

            if (!isset($jsonDecodeBack['subjectCertificateInfo']['serialNumber'])) {
                throw new \Exception('Serial number отсутствует в сертификате');
            }

            $existingUserWithThisUserName = User::where('username', strtolower($subjectName['Name'][0]) . '_' . strtolower($subjectName['SURNAME']))->first();
            if ($existingUserWithThisUserName) {
                throw new \Exception('User with this name already exists');
            }

            $existingUserWithThisEmail = User::where('email', strtolower($subjectName['Name']) . '_' . strtolower($subjectName['SURNAME']) . '@edoline.uz')->first();
            if ($existingUserWithThisEmail) {
                throw new \Exception('User with this email already exists');
            }

            $user = User::create([
                'username' => strtolower($subjectName['Name'][0]) . '_' . strtolower($subjectName['SURNAME']),
                'email' => strtolower($subjectName['Name']) . '_' . strtolower($subjectName['SURNAME']) . '@edoline.uz',
                'password' => Hash::make($jsonDecodeBack['subjectCertificateInfo']['serialNumber']),
                'user_hash' => $this->uniqueId(),
                'org_unit_id' => $isOrg ? $organization->id : 1,
                'status' => 1
            ]);

            $this->createUserPersonalInfo($user->id, $subjectName, $pin);
            $this->createUserWorkHistory($user->id, $isOrg, $organization, $department);
            $this->assignUserRole($user, $isOrg);

            if ($organization) {
                $organization->update(['owner_user_id' => $user->id]);
            }

            return $user;

        } catch (\Exception $e) {
            \Log::error('Create New User Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createDepartment($org_code, $org_unit_id)
    {
        $jsonFile1 = base_path('database/seeders/data/departments.json');

        if (!file_exists($jsonFile1)) {
            throw new \Exception('Файл departments.json не найден');
        }

        $jsonData1 = json_decode(File::get($jsonFile1), true);

        if (!isset($jsonData1['departments'][0])) {
            throw new \Exception('Данные департаментов отсутствуют');
        }

        $item1 = $jsonData1['departments'][0];

        return Department::create([
            'code' => $item1['code'] . '_' . $org_code,
            'org_unit_id' => $org_unit_id,
            'name_uz' => $item1['name_uz'] ?? '',
            'name_kr' => $item1['name_kr'] ?? '',
            'name_ru' => $item1['name_ru'] ?? '',
            'name_en' => $item1['name_en'] ?? '',
            'parent_id' => $item1['parent_id'] ?? 0,
            'order_number' => $item1['order_number'] ?? 0,
            'level_number' => $item1['level_number'] ?? 0,
            'status' => $item1['status'] ?? 1,
        ]);
    }

    private function createUserPersonalInfo($userId, $subjectName, $pin)
    {
        if (!isset($subjectName['CN'])) {
            throw new \Exception('CN отсутствует в сертификате');
        }

        $cn = $subjectName['CN'];
        $nameParts = explode(' ', $cn, 3);

        if (count($nameParts) < 2) {
            throw new \Exception('Неверный формат CN в сертификате');
        }

        $lastName = $nameParts[0];
        $firstName = $nameParts[1];
        $middleName = $nameParts[2] ?? '';

        $officeEmpPersonal = new UserPersonalInfo();
        $officeEmpPersonal->user_id = $userId;
        $officeEmpPersonal->is_emp = 2;
        $officeEmpPersonal->doc_pin = $pin;
        $officeEmpPersonal->doc_number = null;
        $officeEmpPersonal->doc_type = 2;
        $officeEmpPersonal->birthday = null;
        $officeEmpPersonal->gender = 1;
        $officeEmpPersonal->phone = null;
        $officeEmpPersonal->l_name = $lastName;
        $officeEmpPersonal->f_name = $firstName;
        $officeEmpPersonal->m_name = $middleName;
        $officeEmpPersonal->lang_code = 'uz';
        $officeEmpPersonal->doc_sign_type = 1;
        $officeEmpPersonal->sort = 1;
        $officeEmpPersonal->save();
    }

    private function createUserWorkHistory($userId, $isOrg, $organization, $department)
    {
        $nextYear = Carbon::now()->addYear()->format('Y-m-d');

        $officeEmpWork = new UserWorkHistory();
        $officeEmpWork->user_id = $userId;
        $officeEmpWork->org_id = $isOrg ? $organization->id : 1;
        $officeEmpWork->dep_id = $isOrg ? $department->id : 2;
        $officeEmpWork->post_id = $isOrg ? 1 : 3;
        $officeEmpWork->from_date = Carbon::now();
        $officeEmpWork->to_date = $nextYear;
        $officeEmpWork->status = 1;
        $officeEmpWork->save();
    }

    private function assignUserRole($user, $isOrg)
    {
        $roleName = $isOrg ? 'IT_Admin' : 'Dep_Emp';
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->assignRole([$role->id]);
        }

        if($isOrg) {
            $directorRole = Role::where('name', 'Director')->first();
            $user->assignRole([$directorRole->id]);
        }
    }

    private function authErrorResponse($message, $code = 400)
    {
        return response()->json([
            'status' => 0,
            'message' => $message,
            'error' => true
        ], $code);
    }

    private function curlPost(string $path, string $postData): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'X-Real-IP: 185.183.242.32',
                'Host: edoline.uz',
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: null;
    }
}
