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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class SignDocImzoMobileApiController extends Controller
{
    public string $eriSrvIp;

    public function __construct()
    {
        $this->eriSrvIp = config('services.e-imzo-server.url');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/auth",
     *     summary="Генерация challenge для идентификации через ID-Card",
     *     description="Создает случайное значение challenge для подписания пользователем",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Challenge успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="siteId", type="string", example="0000"),
     *             @OA\Property(property="documentId", type="string", example="2944F1F2"),
     *             @OA\Property(property="challenge", type="string", example="F8D2181DC6C02EA819B88FF3EF49BE0C"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function mobileAuth(): JsonResponse
    {
        try {
            $path = '/frontend/mobile/auth';
            $response = $this->curlPost($path, null);

            $status = 0;
            $siteId = '';
            $documentId = '';
            $challenge = '';
            $message = null;

            if ($response) {
                $jsonDecode = json_decode($response, true);
                $status = $jsonDecode['status'] ?? 0;
                $siteId = $jsonDecode['siteId'] ?? '';
                $documentId = $jsonDecode['documentId'] ?? '';
                $challenge = $jsonDecode['challange'] ?? ''; // Note: API uses 'challange' with typo
                $message = $jsonDecode['message'] ?? null;
            }

            return response()->json([
                'status' => $status,
                'siteId' => $siteId,
                'documentId' => $documentId,
                'challenge' => $challenge,
                'message' => $message
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in mobile auth', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Server communication error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/status",
     *     summary="Проверка статуса подписания",
     *     description="Проверяет загружен ли PKCS#7 документ",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId"},
     *             @OA\Property(property="documentId", type="string", example="BBF3E8C3")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Статус получен",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function mobileStatus(Request $request): JsonResponse
    {
        try {
            $documentId = $request->input('documentId');

            if (!$documentId) {
                Log::warning('Missing documentId in request', ['request' => $request->all()]);
                return response()->json([
                    'status' => 0,
                    'message' => 'Document ID is required'
                ], 400);
            }

            $path = '/frontend/mobile/status';
            $response = $this->curlPostForm($path, http_build_query(['documentId' => $documentId]));

            $status = 0;
            $message = 'Status retrieved';

            if ($response === false) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Server communication error'
                ], 500);
            }

            $jsonDecode = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $status = $jsonDecode['status'] ?? 0;
                $message = $jsonDecode['message'] ?? 'Status retrieved';
            } else {
                $message = 'Invalid response from server: ' . json_last_error_msg();
            }

            return response()->json([
                'status' => $status,
                'message' => $message
            ]);

        } catch (\Throwable $e) {
            Log::error('Error checking mobile status', [
                'error' => $e->getMessage(),
                'documentId' => $request->documentId ?? null
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/upload",
     *     summary="Endpoint для загрузки PKCS#7 от мобильного приложения",
     *     description="Принимает PKCS#7 документ от E-IMZO ID-CARD приложения",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId", "pkcs7"},
     *             @OA\Property(property="documentId", type="string"),
     *             @OA\Property(property="pkcs7", type="string"),
     *             @OA\Property(property="serialNumber", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PKCS#7 загружен"
     *     )
     * )
     */
    public function mobileUpload(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            $documentId = $data['document_id'];
            $pkcs7 = $data['pkcs7_b64'];
            $serialNumber = $data['serial_number'];

            $path = '/frontend/mobile/upload';
            $postData = http_build_query([
                'document_id' => $documentId,
                'pkcs7_b64' => $pkcs7,
                'serial_number' => $serialNumber
            ]);

            sleep(1);

//            $this->mobileVerify($request, $data['document_id']);

            $this->curlPostForm($path, $postData);

            return response()->json([
                'status' => 1,
                'message' => 'PKCS#7 uploaded successfully'
            ]);

        } catch (\Throwable $e) {
            Log::error('Error uploading mobile PKCS7', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Upload failed'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/ecp/mobile/authenticate/{documentId}",
     *     summary="Проверка результата идентификации",
     *     description="Возвращает информацию о сертификате после идентификации",
     *     tags={"ECP - E-IMZO Mobile"},
     *
     *     @OA\Parameter(
     *         name="documentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Информация о сертификате"
     *     )
     * )
     */
    public function mobileAuthenticate(Request $request, string $documentId): JsonResponse
    {
        try {
            $clientIp = $request->ip();
            $host = $request->getHost();

            $path = '/backend/mobile/authenticate/' . $documentId;
            $response = $this->curlGet($path, $clientIp, $host);

            $responseJson = json_decode($response, true);
            if ($responseJson['status'] === 1 && !empty($responseJson['subjectCertificateInfo'])) {
                $pin = $responseJson['subjectCertificateInfo']['subjectName']['1.2.860.3.16.1.2'] ?? null;
                $userPersonalInfo = UserPersonalInfo::where('doc_pin', $pin)->first();
                if ($userPersonalInfo) {
                    $user = User::where('id', $userPersonalInfo->user_id)->where('status', 1)->first();
                    if ($user) {
                        $token = $user->createToken('ecp-auth')->plainTextToken;
                        $responseJson['bearer'] = $token;
                        $responseJson['userStatus'] = true;
                    } else {
                        $responseJson['bearer'] = 'Not authenticated';
                        $responseJson['userStatus'] = false;
                    }
                } else {
                    $responseJson['bearer'] = 'Not authenticated';
                    $responseJson['userStatus'] = false;
                }
            }

            if ($response === false) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Server communication error'
                ], 500);
            }

//            $jsonData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid response from server'
                ], 500);
            }

            return response()->json($responseJson);

        } catch (\Throwable $e) {
            Log::error('Error in mobile authenticate', [
                'error' => $e->getMessage(),
                'documentId' => $documentId
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Authentication failed'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/sign",
     *     summary="Генерация документа для подписания",
     *     description="Создает DocumentID для подписания",
     *     tags={"ECP - E-IMZO Mobile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="DocumentID создан"
     *     )
     * )
     */
    public function mobileSign(): JsonResponse
    {
        try {
            $path = '/frontend/mobile/sign';
            $response = $this->curlPost($path, null);

            $status = 0;
            $siteId = '';
            $documentId = '';
            $message = null;

            if ($response) {
                $jsonDecode = json_decode($response, true);
                $status = $jsonDecode['status'] ?? 0;
                $siteId = $jsonDecode['siteId'] ?? '';
                $documentId = $jsonDecode['documentId'] ?? '';
                $message = $jsonDecode['message'] ?? null;
            }

            return response()->json([
                'status' => $status,
                'siteId' => $siteId,
                'documentId' => $documentId,
                'message' => $message
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in mobile sign', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Failed to generate document ID'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ecp/mobile/verify",
     *     summary="Проверка подписи документа",
     *     description="Проверяет подпись документа",
     *     tags={"ECP - E-IMZO Mobile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"documentId", "document"},
     *             @OA\Property(property="documentId", type="string"),
     *             @OA\Property(property="document", type="string", description="Base64 encoded document")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Результат проверки подписи"
     *     )
     * )
     */
    public function mobileVerify(Request $request): JsonResponse
    {
        try {
            $documentId = $request->documentId;
            $document = $request->document;
            $docType = $request->type ?? '';
            $docId = $request->doc_id ?? 0;
            $confirmId  = $request->ecp_confirm_id ?? null;
            $resText    = $request->ecp_r_text;

            $clientIp = $request->ip();
            $host = $request->getHost();

            $path = '/backend/mobile/verify';
            $postData = 'documentId=' . urlencode($documentId) . '&document=' . urlencode(base64_encode($document));

            $response = $this->curlPostFormBack($path, $postData, $clientIp, $host);

            if ($response === false) {
                Log::error('cURL Request Failed', ['documentId' => $documentId]);
                return response()->json([
                    'status' => 0,
                    'message' => 'Server communication error'
                ], 500);
            }

            $jsonData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid response from server'
                ], 500);
            }

            Log::info('Mobile verify response', ['jsonData' => $jsonData]);

//            return response()->json($jsonData);

            $ecpData = $this->prepareEcpData($jsonData, false);

            $result = match ($docType) {
                'protocol_document' => $this->handleProtocolDocument($docId, $confirmId, $resText),
                'pr_emp_hr_order'   => $this->handleHrEmployeeOrder($docId, $confirmId, $resText),
                default => in_array($docType, $this->workingDocTypes)
                    ? $this->handleInternalDocument($docId, $docType, $resText)
                    : $this->errorResponse(__('message.document_deleted_system'))
            };

            if ($result instanceof JsonResponse) {
                return $result;
            }

            $this->saveSignatureData($docId, $docType, $ecpData);

            return $this->successResponse();

        } catch (\Throwable $e) {
            Log::error('Error in mobile verify', [
                'error' => $e->getMessage(),
                'documentId' => $request->documentId ?? null
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Verification failed'
            ], 500);
        }
    }

    /**
     * POST request to E-IMZO server (для frontend методов)
     */
    private function curlPost(string $path, ?string $postData): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPost', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
        }

        curl_close($ch);

        return $response ?: null;
    }

    /**
     * POST request with form data (для frontend методов)
     */
    private function curlPostForm(string $path, string $postData): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPostForm', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $response ?: null;
    }

    /**
     * GET request to E-IMZO server (для backend методов)
     */
    private function curlGet(string $path, string $clientIp, string $host): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'X-Real-IP: ' . $clientIp,
                'Host: ' . $host,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlGet', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $response ?: null;
    }

    /**
     * POST request with form data to backend (для backend методов)
     */
    private function curlPostFormBack(string $path, string $postData, string $clientIp, string $host): ?string
    {
        $url = $this->eriSrvIp . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'X-Real-IP: ' . $clientIp,
                'Host: ' . $host,
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error in curlPostFormBack', [
                'url' => $url,
                'error' => curl_error($ch)
            ]);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $response ?: null;
    }


    // Private helper methods from original controller
    private array $workingDocTypes = [
        'send_to_create_res', 'send_to_agreement', 'send_to_sign',
        'from_signer_to_ag', 'from_signer_to_c_res', 'from_ag_to_res',
        'sign_resolution', 'close_exec_doc',
        'send_to_ag_out_doc', 'send_sign_out_doc', 'sign_out_doc'
    ];
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
            'signingTime'         => $eriSign['verificationInfo']['signingTime'],
            'signature'           => "",
            'digest'              => "",
            'serialNumber'        => $eriSign['subjectCertificateInfo']['serialNumber'],
            'subjectName'         => $eriSign['subjectCertificateInfo']['subjectName'],
            'verified'            => "",
            'certificateVerified' => "",
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
            ? UserWorkHistory::whereHas('user.userRoles', fn($q) => $q->where('role_id', $role->id))->get()
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
}
