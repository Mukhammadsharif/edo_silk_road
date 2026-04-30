<?php

namespace App\Http\Controllers\Api\Office;

use App\Http\Controllers\Controller;
use App\Models\Comment\DocumentComment;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\Document\Documents;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\QrCodeKeys;
use App\Services\DocumentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class JournalApiController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/work-documents",
     *     summary="Получить список рабочих документов",
     *     description="Возвращает список рабочих документов текущего пользователя с возможностью фильтрации",
     *     tags={"User Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="doc_name",
     *         in="query",
     *         description="Поиск по названию или тексту документа",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="doc_type",
     *         in="query",
     *         description="ID типа документа или 'all'",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Статус документа (12 - просроченные, 14 - с просроченной резолюцией)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Дата от (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_till",
     *         in="query",
     *         description="Дата до (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество записей на странице (max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Work documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=4),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="doc_id", type="integer", example=123),
     *                         @OA\Property(property="j_type", type="integer", example=5),
     *                         @OA\Property(property="status", type="integer", example=2),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="doc",
     *                             type="object",
     *                             @OA\Property(property="doc_name", type="string", example="Приказ о назначении"),
     *                             @OA\Property(property="doc_number", type="string", example="123/2024"),
     *                             @OA\Property(property="doc_date", type="string", format="date"),
     *                             @OA\Property(property="doc_text", type="string")
     *                         ),
     *                         @OA\Property(
     *                             property="journal",
     *                             type="object",
     *                             @OA\Property(property="title", type="string", example="Внутренние документы"),
     *                             @OA\Property(property="doc_code", type="string", example="INTERNAL")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="date_from",
     *                     type="array",
     *                     @OA\Items(type="string", example="Неверный формат даты")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error retrieving work documents"),
     *             @OA\Property(property="error", type="string", example="Database connection error")
     *         )
     *     )
     * )
     */
    public function indexWork(Request $request): JsonResponse
    {
        try {
            // Валидация входных данных
            $validated = $request->validate([
                'doc_name' => 'nullable|string|max:255',
                'doc_type' => 'nullable|string|max:50',
                'status' => 'nullable|string|in:2,9,10,11,12,14',
                'date_from' => 'nullable|date_format:d.m.Y',
                'date_till' => 'nullable|date_format:d.m.Y',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $user = Auth::user();
            $userWorkIds = $user->userWorkIDs();

            $doc_name = $validated['doc_name'] ?? null;
            $doc_type = $validated['doc_type'] ?? null;
            $status = $validated['status'] ?? null;
            $date_from = $validated['date_from'] ?? null;
            $date_till = $validated['date_till'] ?? null;
            $per_page = $validated['per_page'] ?? 25;

            $search = DocumentJournals::whereHas('journal', function ($query) {
                $query->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING']);
            })->whereIn('created_user', $userWorkIds);

            $today = Carbon::now()->format('Y-m-d');

            // Применение фильтров по статусу
            if ($status == '12') {
                $search->whereHas('doc', function ($query) use ($today) {
                    $query->where(function ($docQuery) use ($today) {
                        $docQuery->where('doc_term', '<', $today)
                            ->whereNotIn('status', [2, 9, 10, 11]);
                    });
                });
            } elseif ($status == '14') {
                $search->whereNotIn('status', [2, 10, 11])
                    ->whereHas('directorResolution', function ($directorResolutionQuery) use ($today) {
                        $directorResolutionQuery->where('res_term', '<', $today);
                    });
            } elseif ($status) {
                $search->where('status', $status);
            } else {
                $search->where('status', '!=', 2);
            }

            // Фильтр по типу документа
            if ($doc_type && $doc_type != 'all') {
                $search->where('j_type', $doc_type);
            }

            // Поиск по названию документа
            if (trim($doc_name)) {
                $search->whereHas('doc', function ($query) use ($doc_name) {
                    $query->where('doc_name', 'ILIKE', '%' . $doc_name . '%')
                        ->orWhere('doc_text', 'ILIKE', '%' . $doc_name . '%');
                });
            }

            // Фильтр по датам
            if ($date_from && $date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
                    $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
                });
            } elseif ($date_from && !$date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_from) {
                    $query->where('doc_date', '>=', $tmp_date_from);
                });
            } elseif (!$date_from && $date_till) {
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_till) {
                    $query->where('doc_date', '<=', $tmp_date_till);
                });
            }

            $models = $search->where('status', '!=', 13)
                ->with(['doc', 'journal'])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            Log::info('Work documents retrieved successfully', [
                'user_id' => Auth::id(),
                'total_records' => $models->total(),
                'filters' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work documents retrieved successfully',
                'data' => $models
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Validation failed for work documents', [
                'user_id' => Auth::id(),
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Error retrieving work documents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving work documents',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/work-documents/{id}/{hash}",
     *     summary="Просмотр рабочего документа",
     *     description="Возвращает детальную информацию о рабочем документе со всеми связанными данными",
     *     tags={"User Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Хеш документа для безопасности",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="journal", type="object"),
     *                 @OA\Property(property="document", type="object"),
     *                 @OA\Property(property="html_doc", type="string"),
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="director_document", type="object"),
     *                 @OA\Property(property="director_resolution", type="object"),
     *                 @OA\Property(property="performers", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="additional_performers", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="signer", type="object"),
     *                 @OA\Property(property="agreement_users", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="reply_documents", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="qr_key", type="object"),
     *                 @OA\Property(property="comments", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Access denied to this document")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Документ не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Document not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function viewWork($id, $hash): JsonResponse
    {
        try {
            $user = Auth::user();
            $userWorkIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($id);
            $document = Documents::where('id', $journal->doc_id)
                ->where('doc_hash', $hash)
                ->firstOrFail();

            // Проверка доступа пользователя к документу
            if (!in_array($journal->created_user, $userWorkIds)) {
                Log::warning('Access denied to work document', [
                    'user_id' => Auth::id(),
                    'journal_id' => $id,
                    'document_id' => $document->id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getResolutionRole()->id)
                ->with(['role', 'toUser.personal'])
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getExecutiveRole()->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->with(['role', 'toUser.personal'])
                ->get();

            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getExecutiveRole()->id)
                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
                ->with(['role', 'toUser.personal'])
                ->get();

            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getSignerRole()->id)
                ->with(['role', 'toUser.personal'])
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getAgreementRole()->id)
                ->with(['role', 'toUser.personal'])
                ->get();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

            $qrKey = QrCodeKeys::where('doc_type', 'work_doc')
                ->where('doc_id', $document->id)
                ->first();

            $documentComments = DocumentComment::where('doc_type', 1)
                ->where('doc_id', $id)
                ->with(['createdUser.personal'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            Log::info('Work document viewed successfully', [
                'user_id' => Auth::id(),
                'journal_id' => $id,
                'document_id' => $document->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'performers' => $selectedPerformers,
                    'additional_performers' => $selectedAddPerformers,
                    'signer' => $signer,
                    'agreement_users' => $agreement_users,
                    'reply_documents' => $replyDocuments,
                    'qr_key' => $qrKey,
                    'comments' => $documentComments
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            Log::warning('Work document not found', [
                'user_id' => Auth::id(),
                'journal_id' => $id,
                'hash' => $hash
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing work document', [
                'error' => $e->getMessage(),
                'journal_id' => $id,
                'hash' => $hash,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }




    /**
     * @OA\Get(
     *     path="/api/v1/inbox-documents",
     *     summary="Получить список входящих документов",
     *     description="Возвращает список входящих документов с возможностью фильтрации",
     *     tags={"User Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="doc_name",
     *         in="query",
     *         description="Поиск по названию или номеру документа",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="doc_org",
     *         in="query",
     *         description="ID организации или 'all'",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Статус документа",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Дата от (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_till",
     *         in="query",
     *         description="Дата до (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество записей на странице (max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inbox documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=4),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="doc_id", type="integer"),
     *                         @OA\Property(property="status", type="integer"),
     *                         @OA\Property(
     *                             property="doc",
     *                             type="object",
     *                             @OA\Property(property="doc_name", type="string"),
     *                             @OA\Property(property="doc_number", type="string"),
     *                             @OA\Property(property="doc_date", type="string", format="date")
     *                         ),
     *                         @OA\Property(
     *                             property="journal",
     *                             type="object",
     *                             @OA\Property(property="title", type="string"),
     *                             @OA\Property(property="doc_code", type="string", example="BASE_INCOMING")
     *                         ),
     *                         @OA\Property(
     *                             property="doc_org",
     *                             type="object",
     *                             @OA\Property(property="org_name", type="string")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function indexInbox(Request $request): JsonResponse
    {
        try {
            // Валидация входных данных
            $validated = $request->validate([
                'doc_name' => 'nullable|string|max:255',
                'doc_org' => 'nullable|string|max:50',
                'status' => 'nullable|string',
                'date_from' => 'nullable|date_format:d.m.Y',
                'date_till' => 'nullable|date_format:d.m.Y',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $user = Auth::user();
            $orgUnitId = $user->org_unit_id;

            $doc_name = $validated['doc_name'] ?? null;
            $doc_org = $validated['doc_org'] ?? null;
            $status = $validated['status'] ?? null;
            $date_from = $validated['date_from'] ?? null;
            $date_till = $validated['date_till'] ?? null;
            $per_page = $validated['per_page'] ?? 25;

            $search = DocumentJournals::whereHas('journal', function ($query) use ($orgUnitId) {
                $query->where('doc_code', 'BASE_INCOMING')
                    ->where('org_id', $orgUnitId);
            })->where('org_unit_id', $orgUnitId);

            // Фильтр по статусу
            if ($status) {
                $search->where('status', $status);
            }

            // Фильтр по организации
            if ($doc_org && $doc_org != 'all') {
                $search->whereHas('docOrg', function ($query) use ($doc_org) {
                    $query->where('doc_org_id', $doc_org);
                });
            }

            // Поиск по названию или номеру документа
            if (trim($doc_name)) {
                $search->whereHas('doc', function ($query) use ($doc_name) {
                    $query->where('doc_name', 'ILIKE', '%' . $doc_name . '%')
                        ->orWhere('doc_number', 'ILIKE', '%' . $doc_name . '%');
                });
            }

            // Фильтр по датам
            if ($date_from && $date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
                    $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
                });
            } elseif ($date_from && !$date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_from) {
                    $query->where('doc_date', '>=', $tmp_date_from);
                });
            } elseif (!$date_from && $date_till) {
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_till) {
                    $query->where('doc_date', '<=', $tmp_date_till);
                });
            }

            $models = $search
                ->with(['doc', 'journal', 'docOrg'])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            Log::info('Inbox documents retrieved successfully', [
                'user_id' => Auth::id(),
                'total_records' => $models->total(),
                'filters' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inbox documents retrieved successfully',
                'data' => $models
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Validation failed for inbox documents', [
                'user_id' => Auth::id(),
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Error retrieving inbox documents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving inbox documents',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inbox-documents/{id}/{hash}",
     *     summary="Просмотр входящего документа",
     *     description="Возвращает детальную информацию о входящем документе",
     *     tags={"User Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Хеш документа для безопасности",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inbox document retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="journal", type="object"),
     *                 @OA\Property(property="document", type="object"),
     *                 @OA\Property(property="html_doc", type="string"),
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="director_document", type="object"),
     *                 @OA\Property(property="director_resolution", type="object"),
     *                 @OA\Property(property="performers", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="additional_performers", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="signer", type="object"),
     *                 @OA\Property(property="agreement_users", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="reply_documents", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="qr_key", type="object"),
     *                 @OA\Property(property="comments", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Документ не найден"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function viewInbox($id, $hash): JsonResponse
    {
        try {
            $user = Auth::user();
            $orgUnitId = $user->org_unit_id;

            $journal = DocumentJournals::findOrFail($id);
            $document = Documents::where('id', $journal->doc_id)
                ->where('doc_hash', $hash)
                ->firstOrFail();

            // Проверка, что это действительно входящий документ
            if ($journal->journal->doc_code !== 'BASE_INCOMING') {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not an inbox document'
                ], 400);
            }

            // Проверка принадлежности документа к организации пользователя
            if ((int) $journal->org_unit_id !== (int) $orgUnitId) {
                Log::warning('Access denied to inbox document (org mismatch)', [
                    'user_id'        => Auth::id(),
                    'user_org'       => $orgUnitId,
                    'document_org'   => $journal->org_unit_id,
                    'journal_id'     => $id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getResolutionRole()->id)
                ->with(['role', 'toUser.personal'])
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getExecutiveRole()->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->with(['role', 'toUser.personal'])
                ->get();

            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getExecutiveRole()->id)
                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
                ->with(['role', 'toUser.personal'])
                ->get();

            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getSignerRole()->id)
                ->with(['role', 'toUser.personal'])
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getAgreementRole()->id)
                ->with(['role', 'toUser.personal'])
                ->get();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

            $qrKey = QrCodeKeys::where('doc_type', 'work_doc')
                ->where('doc_id', $document->id)
                ->first();

            $documentComments = DocumentComment::where('doc_type', 1)
                ->where('doc_id', $id)
                ->with(['createdUser.personal'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            Log::info('Inbox document viewed successfully', [
                'user_id' => Auth::id(),
                'journal_id' => $id,
                'document_id' => $document->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inbox document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'performers' => $selectedPerformers,
                    'additional_performers' => $selectedAddPerformers,
                    'signer' => $signer,
                    'agreement_users' => $agreement_users,
                    'reply_documents' => $replyDocuments,
                    'qr_key' => $qrKey,
                    'comments' => $documentComments
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            Log::warning('Inbox document not found', [
                'user_id' => Auth::id(),
                'journal_id' => $id,
                'hash' => $hash
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing inbox document', [
                'error' => $e->getMessage(),
                'journal_id' => $id,
                'hash' => $hash,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving inbox document',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }





    /**
     * @OA\Get(
     *     path="/api/v1/outgoing-documents",
     *     summary="Получить список исходящих документов",
     *     description="Возвращает список исходящих документов текущего пользователя с возможностью фильтрации",
     *     tags={"User Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="doc_name",
     *         in="query",
     *         description="Поиск по названию или тексту документа",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="doc_org",
     *         in="query",
     *         description="ID организации или 'all'",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="doc_number",
     *         in="query",
     *         description="Номер документа",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Статус документа",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Дата от (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_till",
     *         in="query",
     *         description="Дата до (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество записей на странице (max: 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Outgoing documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=4),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="doc_id", type="integer"),
     *                         @OA\Property(property="status", type="integer"),
     *                         @OA\Property(
     *                             property="doc",
     *                             type="object",
     *                             @OA\Property(property="doc_name", type="string"),
     *                             @OA\Property(property="doc_number", type="string"),
     *                             @OA\Property(property="doc_date", type="string", format="date"),
     *                             @OA\Property(property="doc_text", type="string")
     *                         ),
     *                         @OA\Property(
     *                             property="journal",
     *                             type="object",
     *                             @OA\Property(property="title", type="string"),
     *                             @OA\Property(property="doc_code", type="string", example="BASE_OUTGOING")
     *                         ),
     *                         @OA\Property(
     *                             property="doc_org",
     *                             type="object",
     *                             @OA\Property(property="org_name", type="string")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function indexOutgoing(Request $request): JsonResponse
    {
        try {
            // Валидация входных данных
            $validated = $request->validate([
                'doc_name' => 'nullable|string|max:255',
                'doc_org' => 'nullable|string|max:50',
                'doc_number' => 'nullable|string|max:100',
                'status' => 'nullable|string',
                'date_from' => 'nullable|date_format:d.m.Y',
                'date_till' => 'nullable|date_format:d.m.Y',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $user = Auth::user();
            $userWorkIds = $user->userWorkIDs();

            $doc_name = $validated['doc_name'] ?? null;
            $doc_org = $validated['doc_org'] ?? null;
            $doc_number = $validated['doc_number'] ?? null;
            $status = $validated['status'] ?? null;
            $date_from = $validated['date_from'] ?? null;
            $date_till = $validated['date_till'] ?? null;
            $per_page = $validated['per_page'] ?? 25;

            $search = DocumentJournals::whereHas('journal', function ($query) {
                $query->where('doc_code', 'BASE_OUTGOING');
            })->whereIn('created_user', $userWorkIds);

            // Фильтр по статусу
            if ($status) {
                $search->where('status', $status);
            }

            // Фильтр по организации
            if ($doc_org && $doc_org != 'all') {
                $search->whereHas('docOrg', function ($query) use ($doc_org) {
                    $query->where('doc_org_id', $doc_org);
                });
            }

            // Поиск по названию или тексту документа
            if (trim($doc_name)) {
                $search->whereHas('doc', function ($query) use ($doc_name) {
                    $query->where('doc_name', 'ILIKE', '%' . $doc_name . '%')
                        ->orWhere('doc_text', 'ILIKE', '%' . $doc_name . '%');
                });
            }

            // Фильтр по номеру документа
            if ($doc_number) {
                $search->whereHas('doc', function ($query) use ($doc_number) {
                    $query->where('doc_number', $doc_number);
                });
            }

            // Фильтр по датам
            if ($date_from && $date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
                    $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
                });
            } elseif ($date_from && !$date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_from) {
                    $query->where('doc_date', '>=', $tmp_date_from);
                });
            } elseif (!$date_from && $date_till) {
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_till) {
                    $query->where('doc_date', '<=', $tmp_date_till);
                });
            }

            $models = $search
                ->with(['doc', 'journal', 'docOrg'])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            Log::info('Outgoing documents retrieved successfully', [
                'user_id' => Auth::id(),
                'total_records' => $models->total(),
                'filters' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Outgoing documents retrieved successfully',
                'data' => $models
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Validation failed for outgoing documents', [
                'user_id' => Auth::id(),
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Error retrieving outgoing documents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving outgoing documents',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/outgoing-documents/{id}/{hash}",
     *     summary="Просмотр исходящего документа",
     *     description="Возвращает детальную информацию об исходящем документе",
     *     tags={"User Documents"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Хеш документа для безопасности",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Outgoing document retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="journal", type="object"),
     *                 @OA\Property(property="document", type="object"),
     *                 @OA\Property(property="html_doc", type="string"),
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="director_document", type="object"),
     *                 @OA\Property(property="director_resolution", type="object"),
     *                 @OA\Property(property="agreement_users", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="reply_documents", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="qr_key", type="object"),
     *                 @OA\Property(property="comments", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Доступ запрещен"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Документ не найден"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function viewOutgoing($id, $hash): JsonResponse
    {
        try {
            $user = Auth::user();
            $userWorkIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($id);
            $document = Documents::where('id', $journal->doc_id)
                ->where('doc_hash', $hash)
                ->firstOrFail();

            // Проверка, что это действительно исходящий документ
            if ($journal->journal->doc_code !== 'BASE_OUTGOING') {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not an outgoing document'
                ], 400);
            }

            // Проверка доступа пользователя к документу
            if (!in_array($journal->created_user, $userWorkIds)) {
                Log::warning('Access denied to outgoing document', [
                    'user_id' => Auth::id(),
                    'journal_id' => $id,
                    'document_id' => $document->id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getResolutionRole()->id)
                ->with(['role', 'toUser.personal'])
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getAgreementRole()->id)
                ->with(['role', 'toUser.personal'])
                ->get();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

            $qrKey = QrCodeKeys::where('doc_type', 'out_doc')
                ->where('doc_id', $document->id)
                ->first();

            $documentComments = DocumentComment::where('doc_type', 1)
                ->where('doc_id', $id)
                ->with(['createdUser.personal'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            Log::info('Outgoing document viewed successfully', [
                'user_id' => Auth::id(),
                'journal_id' => $id,
                'document_id' => $document->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Outgoing document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'agreement_users' => $agreement_users,
                    'reply_documents' => $replyDocuments,
                    'qr_key' => $qrKey,
                    'comments' => $documentComments
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            Log::warning('Outgoing document not found', [
                'user_id' => Auth::id(),
                'journal_id' => $id,
                'hash' => $hash
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing outgoing document', [
                'error' => $e->getMessage(),
                'journal_id' => $id,
                'hash' => $hash,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving outgoing document',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
