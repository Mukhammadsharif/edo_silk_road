<?php
//
//namespace App\Http\Controllers\Api\Office;
//
//use App\Http\Controllers\Controller;
//use App\Models\Admin\StatusList;
//use App\Models\Comment\DocumentComment;
//use App\Models\Comment\DocumentCommentUser;
//use App\Models\Document\DocumentExecutionSteps;
//use App\Models\Document\DocumentFiles;
//use App\Models\Document\DocumentJournals;
//use App\Models\Document\DocumentReplyComments;
//use App\Models\Document\DocumentResolutions;
//use App\Models\Document\Documents;
//use App\Models\Journal\JournalTypes;
//use App\Models\Messages\OnlyOfficeDoc;
//use App\Models\Protocol\ProtocolDocFile;
//use App\Models\Protocol\ProtocolDocMember;
//use App\Models\Protocol\ProtocolDocReplyComment;
//use App\Models\Protocol\ProtocolDocText;
//use App\Models\Protocol\ProtocolDocType;
//use App\Models\Protocol\ProtocolDocument;
//use App\Models\QrCodeKeys;
//use Carbon\Carbon;
//use Exception;
//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Http\JsonResponse;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\Storage;
//use Illuminate\Validation\ValidationException;
//
///**
// * @OA\Tag(
// *     name="DocContoller(Documents)",
// *     description="API для работы с документами"
// * )
// *
// * @OA\Tag(
// *     name="DocContoller(Protocols)",
// *     description="API для работы с протоколами"
// * )
// */
//class DocApiController extends Controller
//{
//    /**
//     * @OA\Get(
//     *     path="/api/v1/doc-controller/documents/work-docs",
//     *     summary="Получить список рабочих документов",
//     *     description="Возвращает список рабочих документов с возможностью фильтрации",
//     *     tags={"DocContoller(Documents)"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="doc_name",
//     *         in="query",
//     *         description="Поиск по названию или номеру документа",
//     *         required=false,
//     *         @OA\Schema(type="string", example="Договор")
//     *     ),
//     *     @OA\Parameter(
//     *         name="doc_type",
//     *         in="query",
//     *         description="Тип документа (ID или 'all')",
//     *         required=false,
//     *         @OA\Schema(type="string", example="1")
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_from",
//     *         in="query",
//     *         description="Дата от (формат: dd.mm.YYYY)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date", example="01.01.2024")
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_till",
//     *         in="query",
//     *         description="Дата до (формат: dd.mm.YYYY)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date", example="31.12.2024")
//     *     ),
//     *     @OA\Parameter(
//     *         name="status",
//     *         in="query",
//     *         description="Статус документа (12 - просроченные, 14 - просроченные резолюции)",
//     *         required=false,
//     *         @OA\Schema(type="string", example="2")
//     *     ),
//     *     @OA\Parameter(
//     *         name="created_user",
//     *         in="query",
//     *         description="ID пользователя создавшего документ",
//     *         required=false,
//     *         @OA\Schema(type="integer", example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="page",
//     *         in="query",
//     *         description="Номер страницы",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=1, example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="per_page",
//     *         in="query",
//     *         description="Количество записей на странице",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=25, example=25)
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Успешный ответ",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string", example="Work documents retrieved successfully"),
//     *             @OA\Property(
//     *                 property="data",
//     *                 type="object",
//     *                 @OA\Property(property="current_page", type="integer", example=1),
//     *                 @OA\Property(property="per_page", type="integer", example=25),
//     *                 @OA\Property(property="total", type="integer", example=100),
//     *                 @OA\Property(property="last_page", type="integer", example=4),
//     *                 @OA\Property(
//     *                     property="data",
//     *                     type="array",
//     *                     @OA\Items(
//     *                         @OA\Property(property="id", type="integer", example=1),
//     *                         @OA\Property(property="doc_id", type="integer", example=5),
//     *                         @OA\Property(property="j_type", type="integer", example=1),
//     *                         @OA\Property(property="status", type="integer", example=2),
//     *                         @OA\Property(property="created_at", type="string", format="date-time"),
//     *                         @OA\Property(
//     *                             property="doc",
//     *                             type="object",
//     *                             @OA\Property(property="doc_name", type="string", example="Служебная записка"),
//     *                             @OA\Property(property="doc_number", type="string", example="№123"),
//     *                             @OA\Property(property="doc_date", type="string", format="date")
//     *                         )
//     *                     )
//     *                 )
//     *             ),
//     *             @OA\Property(
//     *                 property="meta",
//     *                 type="object",
//     *                 @OA\Property(property="journal_types", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="status_list", type="array", @OA\Items(type="object"))
//     *             )
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=401,
//     *         description="Неавторизован",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=false),
//     *             @OA\Property(property="message", type="string", example="Unauthenticated")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=422,
//     *         description="Ошибка валидации",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=false),
//     *             @OA\Property(property="message", type="string", example="Validation failed"),
//     *             @OA\Property(property="errors", type="object")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=false),
//     *             @OA\Property(property="message", type="string", example="Error retrieving work documents"),
//     *             @OA\Property(property="error", type="string")
//     *         )
//     *     )
//     * )
//     */
//    public function workDocsIndex(Request $request): JsonResponse
//    {
//        try {
//            $validated = $request->validate([
//                'doc_name' => 'nullable|string|max:255',
//                'doc_type' => 'nullable|string|max:50',
//                'date_from' => 'nullable|date_format:d.m.Y',
//                'date_till' => 'nullable|date_format:d.m.Y',
//                'status' => 'nullable|string|max:50',
//                'created_user' => 'nullable|integer|exists:users,id',
//                'per_page' => 'nullable|integer|min:1|max:100'
//            ]);
//
//            $doc_name = $validated['doc_name'] ?? null;
//            $doc_type = $validated['doc_type'] ?? null;
//            $date_from = $validated['date_from'] ?? null;
//            $date_till = $validated['date_till'] ?? null;
//            $status = $validated['status'] ?? null;
//            $created_user = $validated['created_user'] ?? null;
//            $per_page = $validated['per_page'] ?? 25;
//
//            $search = DocumentJournals::whereNotIn('status', [1, 4, 13]);
//
//            $today = Carbon::now()->format('Y-m-d');
//
//            if ($status == '12') {
//                $search->whereHas('doc', function ($query) use ($today) {
//                    $query->where(function ($docQuery) use ($today) {
//                        $docQuery->where('doc_term', '<', $today)
//                            ->whereNotIn('status', [2, 9, 10, 11]);
//                    });
//                });
//            } elseif ($status == '14') {
//                $search->whereNotIn('status', [2, 10, 11])
//                    ->whereHas('directorResolution', function ($directorResolutionQuery) use ($today) {
//                        $directorResolutionQuery->where('res_term', '<', $today);
//                    });
//            } elseif ($status) {
//                $search->where('status', $status);
//            }
//
//            if ($doc_type && $doc_type != 'all') {
//                $search->where('j_type', $doc_type);
//            }
//
//            if (trim($doc_name ?? '')) {
//                $search->whereHas('doc', function ($query) use ($doc_name) {
//                    $query->where('doc_name', 'LIKE', '%' . $doc_name . '%')
//                        ->orWhere('doc_number', 'LIKE', '%' . $doc_name . '%');
//                });
//            }
//
//            if ($created_user) {
//                $workUserIds = $this->getWorkUserIds($created_user);
//                $search->whereIn('created_user', $workUserIds);
//            }
//
//            if ($date_from && $date_till) {
//                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
//                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
//                $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
//                    $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
//                });
//            } elseif ($date_from) {
//                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
//                $search->whereHas('doc', function ($query) use ($tmp_date_from) {
//                    $query->where('doc_date', '>=', $tmp_date_from);
//                });
//            } elseif ($date_till) {
//                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
//                $search->whereHas('doc', function ($query) use ($tmp_date_till) {
//                    $query->where('doc_date', '<=', $tmp_date_till);
//                });
//            }
//
//            $models = $search->with(['doc', 'journal'])
//                ->orderBy('id', 'DESC')
//                ->paginate($per_page);
//
//            $journal_types = JournalTypes::whereDoesntHave('childs')
//                ->where('status', 1)
//                ->orderBy('order_number')
//                ->get();
//
//            $statusNames = StatusList::where('type', 'work_document')
//                ->whereNotIn('code', [1, 4, 13])
//                ->orderBy('order_number')
//                ->get();
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Work documents retrieved successfully',
//                'data' => $models,
//                'meta' => [
//                    'journal_types' => $journal_types,
//                    'status_list' => $statusNames
//                ]
//            ], 200);
//
//        } catch (ValidationException $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Validation failed',
//                'errors' => $e->errors()
//            ], 422);
//
//        } catch (Exception $e) {
//            Log::error('Error retrieving work documents', [
//                'error' => $e->getMessage(),
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error retrieving work documents',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
//
//
//    /**
//     * @OA\Get(
//     *     path="/api/v1/doc-controller/documents/{id}/view/{hash}",
//     *     summary="Просмотр рабочего документа",
//     *     description="Возвращает детальную информацию о рабочем документе",
//     *     tags={"DocContoller(Documents)"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="id",
//     *         in="path",
//     *         description="ID журнала документа",
//     *         required=true,
//     *         @OA\Schema(type="integer", example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="hash",
//     *         in="path",
//     *         description="Хеш документа",
//     *         required=true,
//     *         @OA\Schema(type="string", example="abc123def456")
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Успешный ответ",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string", example="Document retrieved successfully"),
//     *             @OA\Property(
//     *                 property="data",
//     *                 type="object",
//     *                 @OA\Property(property="journal", type="object"),
//     *                 @OA\Property(property="document", type="object"),
//     *                 @OA\Property(property="document_files", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="director_document", type="object"),
//     *                 @OA\Property(property="director_resolution", type="object"),
//     *                 @OA\Property(property="performers", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="additional_performers", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="signer", type="object"),
//     *                 @OA\Property(property="agreement_users", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="reply_documents", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="comments", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="qr_key", type="object")
//     *             )
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=404,
//     *         description="Документ не найден",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=false),
//     *             @OA\Property(property="message", type="string", example="Document not found")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=401,
//     *         description="Неавторизован"
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера"
//     *     )
//     * )
//     */
//    public function workView($id, $hash): JsonResponse
//    {
//        try {
//            $journal = DocumentJournals::findOrFail($id);
//
//            $document = Documents::where('id', $journal->doc_id)
//                ->where('doc_hash', $hash)
//                ->firstOrFail();
//
//            $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();
//
//            $user = Auth::user();
//            $resolutionRole = $this->getResolutionRole();
//            $executiveRole = $this->getExecutiveRole();
//            $signerRole = $this->getSignerRole();
//            $agreementRole = $this->getAgreementRole();
//
//            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
//                ->where('exec_role', $resolutionRole->id ?? 0)
//                ->with(['fromUser.personal', 'toUser.personal', 'role'])
//                ->first();
//
//            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
//                ->where('from_user', $directorDocument->to_user ?? 0)
//                ->first();
//
//            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
//                ->where('exec_role', $executiveRole->id ?? 0)
//                ->where('from_user', $directorDocument->to_user ?? 0)
//                ->with(['fromUser.personal', 'toUser.personal', 'role'])
//                ->get();
//
//            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
//                ->where('exec_role', $executiveRole->id ?? 0)
//                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
//                ->with(['fromUser.personal', 'toUser.personal', 'role'])
//                ->get();
//
//            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
//                ->where('exec_role', $signerRole->id ?? 0)
//                ->with(['fromUser.personal', 'toUser.personal', 'role'])
//                ->first();
//
//            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
//                ->where('exec_role', $agreementRole->id ?? 0)
//                ->with(['fromUser.personal', 'toUser.personal', 'role'])
//                ->get();
//
//            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();
//
//            $qrKey = QrCodeKeys::where('doc_type', 'work_doc')
//                ->where('doc_id', $document->id)
//                ->first();
//
//            $documentComments = DocumentComment::where('doc_type', 1)
//                ->where('doc_id', $journal->id)
//                ->with(['user.personal', 'replies.user.personal'])
//                ->get();
//
//            // Mark comments as read
//            $authUserIds = $user->userWorkIDs();
//            DocumentCommentUser::leftJoin('document_comments', function ($join) {
//                $join->on('document_comments.id', '=', 'document_comment_users.comment_id')
//                    ->where('document_comments.doc_type', 1);
//            })
//                ->where('document_comment_users.doc_id', $journal->id)
//                ->whereIn('document_comment_users.user_id', $authUserIds)
//                ->where('document_comment_users.is_read', 1)
//                ->update([
//                    'document_comment_users.is_read' => 2,
//                    'document_comment_users.is_read_date' => Carbon::now()
//                ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Document retrieved successfully',
//                'data' => [
//                    'journal' => $journal->load('journal'),
//                    'document' => $document,
//                    'document_files' => $documentFiles,
//                    'director_document' => $directorDocument,
//                    'director_resolution' => $directorResolution,
//                    'performers' => $selectedPerformers,
//                    'additional_performers' => $selectedAddPerformers,
//                    'signer' => $signer,
//                    'agreement_users' => $agreement_users,
//                    'reply_documents' => $replyDocuments,
//                    'comments' => $documentComments,
//                    'qr_key' => $qrKey
//                ]
//            ], 200);
//
//        } catch (ModelNotFoundException $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Document not found'
//            ], 404);
//
//        } catch (Exception $e) {
//            Log::error('Error viewing work document', [
//                'error' => $e->getMessage(),
//                'id' => $id,
//                'hash' => $hash,
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error retrieving document',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
//
//    /**
//     * @OA\Get(
//     *     path="/api/v1/doc-controller/protocol/{id}/{doc_hash}",
//     *     summary="Получить данные протокола",
//     *     description="Возвращает полную информацию по протоколу, включая текст, участников, файлы и комментарии",
//     *     tags={"DocContoller(Protocols)"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="id",
//     *         in="path",
//     *         description="ID участника протокола",
//     *         required=true,
//     *         @OA\Schema(type="integer")
//     *     ),
//     *     @OA\Parameter(
//     *         name="doc_hash",
//     *         in="path",
//     *         description="Хэш документа",
//     *         required=true,
//     *         @OA\Schema(type="string")
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Успешный ответ",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string", example="Protocol retrieved successfully"),
//     *             @OA\Property(
//     *                 property="data",
//     *                 type="object",
//     *                 @OA\Property(property="document", type="object"),
//     *                 @OA\Property(property="document_member", type="object", nullable=true),
//     *                 @OA\Property(property="document_text", type="object", nullable=true),
//     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="members", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="hr_members", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="member_roles", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="reply_comments", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="comments", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="qr_key", type="object", nullable=true),
//     *                 @OA\Property(property="auth_roles", type="array", @OA\Items(type="string")),
//     *                 @OA\Property(property="html_doc", type="string", nullable=true)
//     *             )
//     *         )
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=404,
//     *         description="Документ не найден"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера"
//     *     )
//     * )
//     */
//    public function protocolView(int $id, string $doc_hash)
//    {
//        try {
//
//            $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();
//
//            $documentMember = ProtocolDocMember::where('id', $id)
//                ->where('doc_id', $document->id)
//                ->first();
//
//            $documentText = ProtocolDocText::where('doc_id', $document->id)->first();
//
//            $authRoles = $this->authActiveUserRoleNames();
//
//            $documentFiles = ProtocolDocFile::where('doc_id', $document->id)
//                ->orderBy('file_ext', 'DESC')
//                ->get();
//
//            $documentMembers = ProtocolDocMember::where('doc_id', $document->id)
//                ->where('role_id', '!=', 4)
//                ->orderBy('order_number')
//                ->get();
//
//            $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)
//                ->where('role_id', 4)
//                ->orderBy('order_number')
//                ->get();
//
//            $documentMemberRoles = StatusList::where('type', 'prt_role_type')
//                ->orderBy('code')
//                ->get();
//
//            $documentReplyComments = ProtocolDocReplyComment::where('doc_id', $document->id)->get();
//
//            $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')
//                ->where('doc_id', $document->id)
//                ->first();
//
//            // Комментарии
//            $documentComments = DocumentComment::where('doc_type', 2)
//                ->where('doc_id', $id)
//                ->get();
//
//            // Отметить комментарии как прочитанные
//            DocumentCommentUser::leftJoin('document_comments', function ($join) {
//                $join->on('document_comments.id', '=', 'document_comment_users.comment_id')
//                    ->where('document_comments.doc_type', 2);
//            })
//                ->where('document_comment_users.doc_id', $id)
//                ->whereIn('document_comment_users.user_id', $this->authActiveUserIds())
//                ->where('document_comment_users.is_read', 1)
//                ->update([
//                    'document_comment_users.is_read' => 2,
//                    'document_comment_users.is_read_date' => Carbon::now()
//                ]);
//
//            // HTML-документ (OnlyOffice)
//            $html_doc = null;
//            $onlyOffice = OnlyOfficeDoc::where('doc_type', 'PRT')
//                ->where('doc_id', $document->id)
//                ->first();
//
//            if ($onlyOffice) {
//                $path = $onlyOffice->doc_path . $onlyOffice->doc_key . '.html';
//                if (Storage::disk('public')->exists($path)) {
//                    $html_doc = Storage::disk('public')->get($path);
//                }
//            }
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Protocol retrieved successfully',
//                'data' => [
//                    'document' => $document,
//                    'document_member' => $documentMember,
//                    'document_text' => $documentText,
//                    'files' => $documentFiles,
//                    'members' => $documentMembers,
//                    'hr_members' => $documentHrEmp,
//                    'member_roles' => $documentMemberRoles,
//                    'reply_comments' => $documentReplyComments,
//                    'comments' => $documentComments,
//                    'qr_key' => $qrKey,
//                    'auth_roles' => $authRoles,
//                    'html_doc' => $html_doc,
//                ]
//            ], 200);
//
//        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Protocol not found'
//            ], 404);
//
//        } catch (\Exception $e) {
//
//            Log::error('Error retrieving protocol', [
//                'error' => $e->getMessage(),
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error retrieving protocol',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
//
//
//    /**
//     * @OA\Get(
//     *     path="/api/v1/doc-controller/public-protocols",
//     *     summary="Получить список протоколов",
//     *     description="Возвращает список протоколов с возможностью фильтрации",
//     *     tags={"DocContoller(Protocols)"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="s_text",
//     *         in="query",
//     *         description="Поиск по названию или номеру протокола",
//     *         required=false,
//     *         @OA\Schema(type="string", example="Протокол совещания")
//     *     ),
//     *     @OA\Parameter(
//     *         name="s_status",
//     *         in="query",
//     *         description="Статус протокола",
//     *         required=false,
//     *         @OA\Schema(type="string", example="2")
//     *     ),
//     *     @OA\Parameter(
//     *         name="s_type",
//     *         in="query",
//     *         description="ID типа протокола",
//     *         required=false,
//     *         @OA\Schema(type="integer", example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_from",
//     *         in="query",
//     *         description="Дата от (формат: dd.mm.YYYY)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date", example="01.01.2024")
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_till",
//     *         in="query",
//     *         description="Дата до (формат: dd.mm.YYYY)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date", example="31.12.2024")
//     *     ),
//     *     @OA\Parameter(
//     *         name="created_user",
//     *         in="query",
//     *         description="ID пользователя создавшего протокол",
//     *         required=false,
//     *         @OA\Schema(type="integer", example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="page",
//     *         in="query",
//     *         description="Номер страницы",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=1, example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="per_page",
//     *         in="query",
//     *         description="Количество записей на странице",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=25, example=25)
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Успешный ответ",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string", example="Protocols retrieved successfully"),
//     *             @OA\Property(
//     *                 property="data",
//     *                 type="object",
//     *                 @OA\Property(property="current_page", type="integer"),
//     *                 @OA\Property(property="per_page", type="integer"),
//     *                 @OA\Property(property="total", type="integer"),
//     *                 @OA\Property(
//     *                     property="data",
//     *                     type="array",
//     *                     @OA\Items(
//     *                         @OA\Property(property="id", type="integer"),
//     *                         @OA\Property(property="doc_name", type="string"),
//     *                         @OA\Property(property="doc_number", type="string"),
//     *                         @OA\Property(property="doc_date", type="string", format="date"),
//     *                         @OA\Property(property="status", type="integer"),
//     *                         @OA\Property(property="doc_type_id", type="integer")
//     *                     )
//     *                 )
//     *             ),
//     *             @OA\Property(
//     *                 property="meta",
//     *                 type="object",
//     *                 @OA\Property(property="protocol_types", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="status_list", type="array", @OA\Items(type="object"))
//     *             )
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=401,
//     *         description="Неавторизован"
//     *     ),
//     *     @OA\Response(
//     *         response=422,
//     *         description="Ошибка валидации"
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера"
//     *     )
//     * )
//     */
//
//    public function publicProtocolsIndex(Request $request): JsonResponse
//    {
//        try {
//            $validated = $request->validate([
//                's_text' => 'nullable|string|max:255',
//                's_status' => 'nullable|string|max:50',
//                's_type' => 'nullable|integer|exists:protocol_doc_types,id',
//                'per_page' => 'nullable|integer|min:1|max:100'
//            ]);
//
//            $s_text = $validated['s_text'] ?? null;
//            $s_status = $validated['s_status'] ?? null;
//            $s_type = $validated['s_type'] ?? null;
//            $per_page = $validated['per_page'] ?? 25;
//
//            $search = ProtocolDocument::whereNotIn('status', [1])
//                ->where('visibility', 2);
//
//            if (trim($s_text ?? '')) {
//                $search->where(function ($query) use ($s_text) {
//                    $query->where('doc_name', 'LIKE', '%' . $s_text . '%')
//                        ->orWhere('doc_number', 'LIKE', '%' . $s_text . '%');
//                });
//            }
//
//            if ($s_type) {
//                $search->where('doc_type_id', $s_type);
//            }
//
//            if ($s_status) {
//                $search->where('status', $s_status);
//            }
//
//            $models = $search->where('status', '!=', 6)
//                ->with(['docType'])
//                ->orderBy('id', 'DESC')
//                ->paginate($per_page);
//
//            $protocolTypes = ProtocolDocType::where('status', 1)->get();
//
//            $statusList = StatusList::where('type', 'prt_status')
//                ->whereNotIn('code', [1, 5, 6, 7])
//                ->orderBy('order_number')
//                ->get();
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Public protocols retrieved successfully',
//                'data' => $models,
//                'meta' => [
//                    'protocol_types' => $protocolTypes,
//                    'status_list' => $statusList
//                ]
//            ], 200);
//
//        } catch (ValidationException $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Validation failed',
//                'errors' => $e->errors()
//            ], 422);
//
//        } catch (Exception $e) {
//            Log::error('Error retrieving public protocols', [
//                'error' => $e->getMessage(),
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error retrieving public protocols',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
//
//
//    /**
//     * @OA\Get(
//     *     path="/api/v1/doc-controller/protocols",
//     *     summary="Получить список протоколов",
//     *     description="Возвращает список протоколов с возможностью фильтрации",
//     *     tags={"DocContoller(Protocols)"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="s_text",
//     *         in="query",
//     *         description="Поиск по названию или номеру протокола",
//     *         required=false,
//     *         @OA\Schema(type="string", example="Протокол совещания")
//     *     ),
//     *     @OA\Parameter(
//     *         name="s_status",
//     *         in="query",
//     *         description="Статус протокола",
//     *         required=false,
//     *         @OA\Schema(type="string", example="2")
//     *     ),
//     *     @OA\Parameter(
//     *         name="s_type",
//     *         in="query",
//     *         description="ID типа протокола",
//     *         required=false,
//     *         @OA\Schema(type="integer", example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_from",
//     *         in="query",
//     *         description="Дата от (формат: dd.mm.YYYY)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date", example="01.01.2024")
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_till",
//     *         in="query",
//     *         description="Дата до (формат: dd.mm.YYYY)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date", example="31.12.2024")
//     *     ),
//     *     @OA\Parameter(
//     *         name="created_user",
//     *         in="query",
//     *         description="ID пользователя создавшего протокол",
//     *         required=false,
//     *         @OA\Schema(type="integer", example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="page",
//     *         in="query",
//     *         description="Номер страницы",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=1, example=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="per_page",
//     *         in="query",
//     *         description="Количество записей на странице",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=25, example=25)
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Успешный ответ",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string", example="Protocols retrieved successfully"),
//     *             @OA\Property(
//     *                 property="data",
//     *                 type="object",
//     *                 @OA\Property(property="current_page", type="integer"),
//     *                 @OA\Property(property="per_page", type="integer"),
//     *                 @OA\Property(property="total", type="integer"),
//     *                 @OA\Property(
//     *                     property="data",
//     *                     type="array",
//     *                     @OA\Items(
//     *                         @OA\Property(property="id", type="integer"),
//     *                         @OA\Property(property="doc_name", type="string"),
//     *                         @OA\Property(property="doc_number", type="string"),
//     *                         @OA\Property(property="doc_date", type="string", format="date"),
//     *                         @OA\Property(property="status", type="integer"),
//     *                         @OA\Property(property="doc_type_id", type="integer")
//     *                     )
//     *                 )
//     *             ),
//     *             @OA\Property(
//     *                 property="meta",
//     *                 type="object",
//     *                 @OA\Property(property="protocol_types", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="status_list", type="array", @OA\Items(type="object"))
//     *             )
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=401,
//     *         description="Неавторизован"
//     *     ),
//     *     @OA\Response(
//     *         response=422,
//     *         description="Ошибка валидации"
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера"
//     *     )
//     * )
//     */
//    public function protocolsIndex(Request $request): JsonResponse
//    {
//        try {
//            $validated = $request->validate([
//                's_text' => 'nullable|string|max:255',
//                's_status' => 'nullable|string|max:50',
//                's_type' => 'nullable|integer|exists:protocol_doc_types,id',
//                'date_from' => 'nullable|date_format:d.m.Y',
//                'date_till' => 'nullable|date_format:d.m.Y',
//                'created_user' => 'nullable|integer|exists:users,id',
//                'per_page' => 'nullable|integer|min:1|max:100'
//            ]);
//
//            $s_text = $validated['s_text'] ?? null;
//            $s_status = $validated['s_status'] ?? null;
//            $s_type = $validated['s_type'] ?? null;
//            $date_from = $validated['date_from'] ?? null;
//            $date_till = $validated['date_till'] ?? null;
//            $created_user = $validated['created_user'] ?? null;
//            $per_page = $validated['per_page'] ?? 25;
//
//            $search = ProtocolDocument::whereNotIn('status', [1]);
//
//            if (trim($s_text ?? '')) {
//                $search->where(function ($query) use ($s_text) {
//                    $query->where('doc_name', 'LIKE', '%' . $s_text . '%')
//                        ->orWhere('doc_number', 'LIKE', '%' . $s_text . '%');
//                });
//            }
//
//            if ($s_type) {
//                $search->where('doc_type_id', $s_type);
//            }
//
//            if ($s_status) {
//                $search->where('status', $s_status);
//            }
//
//            if ($created_user) {
//                $workUserIds = $this->getWorkUserIds($created_user);
//                $search->whereIn('created_user', $workUserIds);
//            }
//
//            if ($date_from && $date_till) {
//                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
//                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
//                $search->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
//            } elseif ($date_from) {
//                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
//                $search->where('doc_date', '>=', $tmp_date_from);
//            } elseif ($date_till) {
//                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
//                $search->where('doc_date', '<=', $tmp_date_till);
//            }
//
//            $models = $search->where('status', '!=', 6)
//                ->with(['docType'])
//                ->orderBy('id', 'DESC')
//                ->paginate($per_page);
//
//            $protocolTypes = ProtocolDocType::where('status', 1)->get();
//
//            $statusList = StatusList::where('type', 'prt_status')
//                ->whereNotIn('code', [1, 5, 6, 7])
//                ->orderBy('order_number')
//                ->get();
//
//            return response()->json([
//                'success' => true,
//                'data' => $models,
//                'meta' => [
//                    'protocol_types' => $protocolTypes,
//                    'status_list' => $statusList
//                ]
//            ], 200);
//
//        } catch (ModelNotFoundException $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Document not found'
//            ], 404);
//
//        } catch (Exception $e) {
//            Log::error('Error retrieving protocols', [
//                'error' => $e->getMessage(),
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error retrieving protocols',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
//
//    /**
//     * @OA\Delete(
//     *     path="/api/v1/doc-controller/work-documents",
//     *     summary="Удалить рабочий документ",
//     *     description="Полностью удаляет рабочий документ и все связанные данные",
//     *     tags={"DocContoller(Documents)"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\RequestBody(
//     *         required=true,
//     *         @OA\JsonContent(
//     *             required={"doc_id"},
//     *             @OA\Property(property="doc_id", type="integer", example=15)
//     *         )
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Документ успешно удалён"
//     *     ),
//     *     @OA\Response(
//     *         response=404,
//     *         description="Документ не найден"
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Ошибка сервера"
//     *     )
//     * )
//     */
//    public function delWorkDoc(Request $request): JsonResponse
//    {
//        $docId = $request->input('doc_id');
//
//        try {
//            DB::beginTransaction();
//
//            $document = Documents::findOrFail($docId);
//
//            DocumentJournals::where('doc_id', $docId)->delete();
//            DocumentExecutionSteps::where('j_id', $docId)->delete();
//            DocumentReplyComments::where('j_id', $docId)->delete();
//            DocumentResolutions::where('j_id', $docId)->delete();
//            DocumentFiles::where('doc_id', $docId)->delete();
//
//            // Комментарии (doc_type = 1)
//            $comments = DocumentComment::where('doc_id', $docId)
//                ->where('doc_type', 1)
//                ->get();
//
//            foreach ($comments as $comment) {
//                DocumentCommentUser::where('comment_id', $comment->id)->delete();
//            }
//
//            DocumentComment::where('doc_id', $docId)->delete();
//            DocumentCommentUser::where('doc_id', $docId)->delete();
//
//            $document->delete();
//
//            DB::commit();
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Work document deleted successfully'
//            ], 200);
//
//        } catch (ModelNotFoundException $e) {
//
//            DB::rollBack();
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Document not found'
//            ], 404);
//
//        } catch (\Exception $e) {
//
//            DB::rollBack();
//
//            Log::error('Error deleting work document', [
//                'doc_id' => $docId,
//                'user_id' => Auth::id(),
//                'error' => $e->getMessage()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error deleting document'
//            ], 500);
//        }
//    }
//
//    /**
//     * @OA\Delete(
//     *     path="/api/v1/doc-controller/protocols",
//     *     summary="Удалить протокол",
//     *     description="Полностью удаляет протокол и все связанные данные",
//     *     tags={"DocContoller(Protocols)"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\RequestBody(
//     *         required=true,
//     *         @OA\JsonContent(
//     *             required={"doc_id"},
//     *             @OA\Property(property="doc_id", type="integer", example=8)
//     *         )
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Протокол успешно удалён"
//     *     ),
//     *     @OA\Response(
//     *         response=404,
//     *         description="Протокол не найден"
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Ошибка сервера"
//     *     )
//     * )
//     */
//    public function delProtocol(Request $request): JsonResponse
//    {
//        $docId = $request->input('doc_id');
//
//        try {
//            DB::beginTransaction();
//
//            $protocol = ProtocolDocument::findOrFail($docId);
//
//            ProtocolDocMember::where('doc_id', $docId)->delete();
//            ProtocolDocFile::where('doc_id', $docId)->delete();
//            ProtocolDocText::where('doc_id', $docId)->delete();
//            ProtocolDocReplyComment::where('doc_id', $docId)->delete();
//
//            // Комментарии (doc_type = 2)
//            $comments = DocumentComment::where('doc_id', $docId)
//                ->where('doc_type', 2)
//                ->get();
//
//            foreach ($comments as $comment) {
//                DocumentCommentUser::where('comment_id', $comment->id)->delete();
//            }
//
//            DocumentComment::where('doc_id', $docId)
//                ->where('doc_type', 2)
//                ->delete();
//
//            $protocol->delete();
//
//            DB::commit();
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Protocol deleted successfully'
//            ], 200);
//
//        } catch (ModelNotFoundException $e) {
//
//            DB::rollBack();
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Protocol not found'
//            ], 404);
//
//        } catch (\Exception $e) {
//
//            DB::rollBack();
//
//            Log::error('Error deleting protocol', [
//                'doc_id' => $docId,
//                'user_id' => Auth::id(),
//                'error' => $e->getMessage()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error deleting protocol'
//            ], 500);
//        }
//    }
//}
