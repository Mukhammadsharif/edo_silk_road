<?php

namespace App\Http\Controllers\Api\ECP;

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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignLoginApiController extends Controller
{
    private array $workingDocTypes = [
        'send_to_create_res', 'send_to_agreement', 'send_to_sign',
        'from_signer_to_ag', 'from_signer_to_c_res', 'from_ag_to_res',
        'sign_resolution', 'close_exec_doc',
        'send_to_ag_out_doc', 'send_sign_out_doc', 'sign_out_doc'
    ];

    /**
     * @OA\Post(
     *     path="/api/v1/login-sign/sign-documents",
     *     summary="Подписать документ через логин",
     *     description="Подписывает документ используя обычную авторизацию (без E-IMZO)",
     *     tags={"ECP - Login Sign"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ecp_doc_id", "ecp_doc_type"},
     *             @OA\Property(property="ecp_doc_id", type="integer", description="ID документа"),
     *             @OA\Property(property="ecp_doc_type", type="string", description="Тип документа"),
     *             @OA\Property(property="ecp_confirm_id", type="integer", description="ID подтверждения (опционально)"),
     *             @OA\Property(property="ecp_r_text", type="string", description="Текст резолюции (опционально)")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Документ успешно подписан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="integer", example=200),
     *             @OA\Property(property="title", type="string", example="Успешно"),
     *             @OA\Property(property="message", type="string", example="Задача выполнена успешно")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=500, description="Ошибка подписания")
     * )
     */
    public function signDocuments(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ecp_doc_id' => 'required|integer',
                'ecp_doc_type' => 'required|string',
                'ecp_confirm_id' => 'nullable|integer',
                'ecp_r_text' => 'nullable|string'
            ]);

            $docId     = $request->ecp_doc_id;
            $docType   = $request->ecp_doc_type;
            $confirmId = $request->ecp_confirm_id ?? null;
            $resText   = $request->ecp_r_text;

            if (in_array($docType, $this->workingDocTypes)) {
                return $this->handleInternalDocument($docId, $docType, $resText);
            }

            if ($docType === 'protocol_document') {
                return $this->handleProtocolDocument($docId, $confirmId, $resText);
            }

            return $this->errorResponse(__('message.document_deleted_system'));

        } catch (\Throwable $e) {
            Log::error('Error signing document with login', [
                'error' => $e->getMessage(),
                'doc_id' => $request->ecp_doc_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error signing document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/login-sign/document-types",
     *     summary="Получить список типов документов",
     *     description="Возвращает доступные типы документов для подписания",
     *     tags={"ECP - Login Sign"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован")
     * )
     */
    public function getDocumentTypes(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Document types retrieved successfully',
                'data' => [
                    'working_doc_types' => $this->workingDocTypes,
                    'special_types' => [
                        'protocol_document'
                    ]
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('Error retrieving document types', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document types'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/login-sign/pending-documents",
     *     summary="Получить документы ожидающие подписи",
     *     description="Возвращает список документов, которые требуют подписи текущего пользователя",
     *     tags={"ECP - Login Sign"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Тип документа для фильтрации",
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован")
     * )
     */
    public function getPendingDocuments(Request $request): JsonResponse
    {
        try {
            $type = $request->query('type');
            $userId = auth()->id();

            // Get documents pending signature
            $pendingDocs = DocumentExecutionSteps::where('to_user', $userId)
                ->where('status', 2) // Status 2 = waiting for action
                ->with(['journal.doc', 'journal.fromUser.personal'])
                ->paginate(50);

            // Get protocol documents
            $pendingProtocols = ProtocolDocMember::whereIn('user_id', $this->authActiveUserIds())
                ->where('status', 2)
                ->with(['document.docType'])
                ->paginate(50);

            return response()->json([
                'success' => true,
                'message' => 'Pending documents retrieved successfully',
                'data' => [
                    'internal_documents' => $pendingDocs,
                    'protocol_documents' => $pendingProtocols
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('Error retrieving pending documents', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving pending documents'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/login-sign/document/{doc_id}/details",
     *     summary="Получить детали документа",
     *     description="Возвращает подробную информацию о документе для подписи",
     *     tags={"ECP - Login Sign"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="doc_id",
     *         in="path",
     *         required=true,
     *         description="ID документа",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="doc_type",
     *         in="query",
     *         required=true,
     *         description="Тип документа",
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function getDocumentDetails(int $doc_id, Request $request): JsonResponse
    {
        try {
            $docType = $request->query('doc_type');

            if ($docType === 'protocol_document') {
                $document = ProtocolDocument::with(['docType', 'members.user.personal'])
                    ->findOrFail($doc_id);

                return response()->json([
                    'success' => true,
                    'message' => 'Document details retrieved successfully',
                    'data' => $document
                ]);
            } else {
                $journal = DocumentJournals::with([
                    'doc',
                    'journal',
                    'fromUser.personal',
                    'executionSteps.role',
                    'executionSteps.toUser.personal'
                ])->where('doc_id', $doc_id)->firstOrFail();

                return response()->json([
                    'success' => true,
                    'message' => 'Document details retrieved successfully',
                    'data' => $journal
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Error retrieving document details', [
                'error' => $e->getMessage(),
                'doc_id' => $doc_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document details'
            ], 404);
        }
    }

    // Private helper methods
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
            'j_id'               => $step->j_id,
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
            ? UserWorkHistory::whereHas('user.userRoles', fn($q) => $q->where('role_id', $role->id))->get()
            : collect();
    }

    private function isHrOrderType(int $jType): bool
    {
        $hrOrderType = JournalTypes::where('doc_code', 'HR_ORD')->where('status', 1)->first();
        if (!$hrOrderType) return false;

        return JournalTypes::where('parent_id', $hrOrderType->id)
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

        $document->update(['status' => 3]);
    }
}
