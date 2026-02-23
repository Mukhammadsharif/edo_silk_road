<?php

namespace App\Http\Controllers\Api\Office;

use App\Http\Controllers\Controller;
use App\Models\Admin\StatusList;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\Document\Documents;
use App\Models\Document\ReplyDocumentFiles;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;

class ExecutionApiController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/for-execution",
     *     summary="Получить список документов на исполнение",
     *     description="Возвращает список документов, назначенных текущему пользователю для исполнения",
     *     tags={"Executions"},
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
     *         name="doc_type",
     *         in="query",
     *         description="ID типа документа",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     *         description="Количество записей на странице",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="doc_name", type="string"),
     *                         @OA\Property(property="doc_number", type="string"),
     *                         @OA\Property(property="doc_date", type="string"),
     *                         @OA\Property(property="status", type="integer"),
     *                         @OA\Property(property="journal_type", type="string")
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
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function forExecutionIndex(Request $request)
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $doc_name = $request->input('doc_name');
            $doc_type = $request->input('doc_type');
            $status = $request->input('status');
            $date_from = $request->input('date_from');
            $date_till = $request->input('date_till');
            $per_page = $request->input('per_page', 25);

            $statusArray = $status ? [$status] : [2, 4];

            $search = DocumentJournals::whereHas('executionSteps', function ($query) use ($workIds, $statusArray) {
                $query->whereIn('to_user', $workIds)
                    ->whereIn('status', $statusArray)
                    ->whereHas('role', function ($q) {
                        $q->where('doc_code', 'EXEC');
                    });
            })->where('status', '>', 1);

            if ($doc_type && $doc_type != 'all') {
                $search->where('j_type', $doc_type);
            }

            if (trim($doc_name)) {
                $search->whereHas('doc', function ($query) use ($doc_name) {
                    $query->where('doc_name', 'ILIKE', '%' . $doc_name . '%')
                        ->orWhere('doc_number', 'ILIKE', '%' . $doc_name . '%');
                });
            }

            if ($date_from && $date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
                $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
                    $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
                });
            }

            $models = $search->where('status', '!=', 13)
                ->with(['doc', 'journal'])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            return response()->json([
                'success' => true,
                'message' => 'Documents retrieved successfully',
                'data' => $models
            ], 200);

        } catch (Exception $e) {
            Log::error('Error retrieving execution documents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/for-sign",
     *     summary="Получить список документов на подпись",
     *     description="Возвращает список документов для подписания текущим пользователем",
     *     tags={"Executions"},
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
     *         name="status",
     *         in="query",
     *         description="Статус документа (по умолчанию 2)",
     *         required=false,
     *         @OA\Schema(type="string", default="2")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
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
    public function forSignIndex(Request $request)
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $doc_name = $request->input('doc_name');
            $status = $request->input('status', 2);
            $per_page = $request->input('per_page', 25);

            $search = DocumentJournals::whereHas('executionSteps', function ($query) use ($status, $workIds) {
                $query->whereIn('to_user', $workIds)
                    ->whereHas('role', function ($q) {
                        $q->where('doc_code', 'SIGN');
                    });
            })->where('status', '>', 1);

            if ($status != 'all') {
                $search->whereHas('executionSteps', function ($query) use ($status, $workIds) {
                    $query->whereIn('to_user', $workIds)
                        ->where('status', $status)
                        ->whereHas('role', function ($q) {
                            $q->where('doc_code', 'SIGN');
                        });
                });
            }

            if (trim($doc_name)) {
                $search->whereHas('doc', function ($query) use ($doc_name) {
                    $query->where('doc_name', 'ILIKE', '%' . $doc_name . '%')
                        ->orWhere('doc_number', 'ILIKE', '%' . $doc_name . '%');
                });
            }

            $models = $search->with(['doc', 'journal'])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            return response()->json([
                'success' => true,
                'message' => 'Sign documents retrieved successfully',
                'data' => $models
            ], 200);

        } catch (Exception $e) {
            Log::error('Error retrieving sign documents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/for-approval",
     *     summary="Получить документы на согласование",
     *     description="Возвращает список документов, назначенных текущему пользователю для согласования",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="doc_name",
     *         in="query",
     *         description="Поиск по названию или номеру документа",
     *         required=false,
     *         @OA\Schema(type="string", example="Приказ")
     *     ),
     *     @OA\Parameter(
     *         name="doc_type",
     *         in="query",
     *         description="ID типа документа",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Статус документа (или 'all')",
     *         required=false,
     *         @OA\Schema(type="string", example="2")
     *     ),
     *     @OA\Parameter(
     *         name="created_user",
     *         in="query",
     *         description="ID автора документа",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Дата от (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", example="01.01.2025")
     *     ),
     *     @OA\Parameter(
     *         name="date_till",
     *         in="query",
     *         description="Дата до (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", example="31.01.2025")
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
     *         description="Количество записей на странице",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25),
     *                 @OA\Property(property="total", type="integer", example=134),
     *                 @OA\Property(property="last_page", type="integer", example=6),
     *
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="j_type", type="integer", example=3),
     *                         @OA\Property(property="status", type="integer", example=2),
     *                         @OA\Property(property="created_user", type="integer", example=15),
     *
     *                         @OA\Property(
     *                             property="doc",
     *                             type="object",
     *                             @OA\Property(property="doc_name", type="string", example="Приказ о назначении"),
     *                             @OA\Property(property="doc_number", type="string", example="PR-45/01"),
     *                             @OA\Property(property="doc_date", type="string", example="2025-01-15")
     *                         ),
     *
     *                         @OA\Property(
     *                             property="execution_steps",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=555),
     *                                 @OA\Property(property="to_user", type="integer", example=7),
     *                                 @OA\Property(property="status", type="integer", example=2),
     *                                 @OA\Property(
     *                                     property="role",
     *                                     type="object",
     *                                     @OA\Property(property="doc_code", type="string", example="AGREE")
     *                                 )
     *                             )
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
     *     @OA\Response(
     *         response=403,
     *         description="Нет доступа"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */

    public function forApprovalIndex(Request $request): JsonResponse
    {
        try {
            $doc_name = $request->input('doc_name');
            $doc_type = $request->input('doc_type');
            $date_from = $request->input('date_from');
            $date_till = $request->input('date_till');
            $status = $request->input('status', 2);
            $created_user = $request->input('created_user');
            $perPage = $request->input('per_page', 25);

            $workIds = $this->authActiveUserIds();

            $search = DocumentJournals::whereHas('executionSteps', function ($q) use ($workIds, $status) {
                $q->whereIn('to_user', $workIds)
                    ->whereHas('role', fn ($r) => $r->where('doc_code', 'AGREE'));

                if ($status !== 'all') {
                    $q->where('status', $status);
                }
            })->where('status', '>', 1);

            if ($created_user) {
                $search->whereIn('created_user', $this->getWorkUserIds($created_user));
            }

            if ($doc_type && $doc_type !== 'all') {
                $search->where('j_type', $doc_type);
            }

            if ($doc_name) {
                $search->whereHas('doc', function ($q) use ($doc_name) {
                    $q->where('doc_name', 'ILIKE', "%$doc_name%")
                        ->orWhere('doc_number', 'ILIKE', "%$doc_name%");
                });
            }

            if ($date_from || $date_till) {
                $search->whereHas('doc', function ($q) use ($date_from, $date_till) {
                    if ($date_from) {
                        $q->where('doc_date', '>=', Carbon::createFromFormat('d.m.Y', $date_from)->format('Y-m-d'));
                    }
                    if ($date_till) {
                        $q->where('doc_date', '<=', Carbon::createFromFormat('d.m.Y', $date_till)->format('Y-m-d'));
                    }
                });
            }

            $models = $search
                ->where('status', '!=', 13)
                ->orderByDesc('id')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Documents for approval retrieved',
                'data' => $models
            ]);

        } catch (\Exception $e) {
            Log::error('forApprovalIndexApi error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/v1/executions/for-confirm",
     *     summary="Документы на подтверждение",
     *     description="Возвращает документы, ожидающие подтверждения (резолюции)",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="doc_name",
     *         in="query",
     *         description="Поиск по названию или номеру документа",
     *         required=false,
     *         @OA\Schema(type="string", example="Приказ")
     *     ),
     *     @OA\Parameter(
     *         name="doc_type",
     *         in="query",
     *         description="ID типа документа",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Статус документа (или 'all')",
     *         required=false,
     *         @OA\Schema(type="string", example="2")
     *     ),
     *     @OA\Parameter(
     *         name="created_user",
     *         in="query",
     *         description="ID автора документа",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Дата от (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", example="01.01.2025")
     *     ),
     *     @OA\Parameter(
     *         name="date_till",
     *         in="query",
     *         description="Дата до (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", example="31.01.2025")
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
     *         description="Количество записей на странице",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25),
     *                 @OA\Property(property="total", type="integer", example=134),
     *                 @OA\Property(property="last_page", type="integer", example=6),
     *
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="j_type", type="integer", example=3),
     *                         @OA\Property(property="status", type="integer", example=2),
     *                         @OA\Property(property="created_user", type="integer", example=15),
     *
     *                         @OA\Property(
     *                             property="doc",
     *                             type="object",
     *                             @OA\Property(property="doc_name", type="string", example="Приказ о назначении"),
     *                             @OA\Property(property="doc_number", type="string", example="PR-45/01"),
     *                             @OA\Property(property="doc_date", type="string", example="2025-01-15")
     *                         ),
     *
     *                         @OA\Property(
     *                             property="execution_steps",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=555),
     *                                 @OA\Property(property="to_user", type="integer", example=7),
     *                                 @OA\Property(property="status", type="integer", example=2),
     *                                 @OA\Property(
     *                                     property="role",
     *                                     type="object",
     *                                     @OA\Property(property="doc_code", type="string", example="AGREE")
     *                                 )
     *                             )
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
     *     @OA\Response(
     *         response=403,
     *         description="Нет доступа"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */

    public function forConfirmIndex(Request $request): JsonResponse
    {
        try {
            $doc_name = $request->input('doc_name');
            $doc_type = $request->input('doc_type');
            $date_from = $request->input('date_from');
            $date_till = $request->input('date_till');
            $status = $request->input('status');
            $created_user = $request->input('created_user');
            $perPage = $request->input('per_page', 25);

            $workIds = $this->authActiveUserIds();
            $statusArray = $status ? [$status] : [8, 11];
            $today = Carbon::now()->format('Y-m-d');

            $search = DocumentJournals::whereHas('executionSteps', function ($q) use ($workIds) {
                $q->whereIn('to_user', $workIds)
                    ->whereHas('role', fn ($r) => $r->where('doc_code', 'RESOL'));
            })->whereNotIn('status', [1, 4, 5, 6, 7, 13]);

            if ($created_user) {
                $search->whereIn('created_user', $this->getWorkUserIds($created_user));
            }

            if ($status === '12') {
                $search->whereHas('doc', fn ($q) =>
                $q->where('doc_term', '<', $today)->whereNotIn('status', [2, 9, 10, 11])
                );
            } elseif ($status === '14') {
                $search->whereHas('directorResolution', fn ($q) => $q->where('res_term', '<', $today))
                    ->whereNotIn('status', [2, 10, 11]);
            } elseif ($status !== 'all') {
                $search->whereIn('status', $statusArray);
            }

            if ($doc_type && $doc_type !== 'all') {
                $search->where('j_type', $doc_type);
            }

            if ($doc_name) {
                $search->whereHas('doc', function ($q) use ($doc_name) {
                    $q->where('doc_name', 'ILIKE', "%$doc_name%")
                        ->orWhere('doc_number', 'ILIKE', "%$doc_name%");
                });
            }

            if ($date_from || $date_till) {
                $search->whereHas('doc', function ($q) use ($date_from, $date_till) {
                    if ($date_from) {
                        $q->where('doc_date', '>=', Carbon::createFromFormat('d.m.Y', $date_from)->format('Y-m-d'));
                    }
                    if ($date_till) {
                        $q->where('doc_date', '<=', Carbon::createFromFormat('d.m.Y', $date_till)->format('Y-m-d'));
                    }
                });
            }

            $models = $search->orderByDesc('id')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Documents for confirmation retrieved',
                'data' => $models
            ]);

        } catch (\Exception $e) {
            Log::error('forConfirmIndexApi error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/for-control",
     *     summary="Документы на контроле",
     *     description="Возвращает документы, находящиеся на контроле",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="doc_name",
     *         in="query",
     *         description="Поиск по названию или номеру документа",
     *         required=false,
     *         @OA\Schema(type="string", example="Приказ")
     *     ),
     *     @OA\Parameter(
     *         name="doc_type",
     *         in="query",
     *         description="ID типа документа",
     *         required=false,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Статус документа (или 'all')",
     *         required=false,
     *         @OA\Schema(type="string", example="2")
     *     ),
     *     @OA\Parameter(
     *         name="created_user",
     *         in="query",
     *         description="ID автора документа",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Дата от (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", example="01.01.2025")
     *     ),
     *     @OA\Parameter(
     *         name="date_till",
     *         in="query",
     *         description="Дата до (формат: d.m.Y)",
     *         required=false,
     *         @OA\Schema(type="string", example="31.01.2025")
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
     *         description="Количество записей на странице",
     *         required=false,
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Documents retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25),
     *                 @OA\Property(property="total", type="integer", example=134),
     *                 @OA\Property(property="last_page", type="integer", example=6),
     *
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="j_type", type="integer", example=3),
     *                         @OA\Property(property="status", type="integer", example=2),
     *                         @OA\Property(property="created_user", type="integer", example=15),
     *
     *                         @OA\Property(
     *                             property="doc",
     *                             type="object",
     *                             @OA\Property(property="doc_name", type="string", example="Приказ о назначении"),
     *                             @OA\Property(property="doc_number", type="string", example="PR-45/01"),
     *                             @OA\Property(property="doc_date", type="string", example="2025-01-15")
     *                         ),
     *
     *                         @OA\Property(
     *                             property="execution_steps",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=555),
     *                                 @OA\Property(property="to_user", type="integer", example=7),
     *                                 @OA\Property(property="status", type="integer", example=2),
     *                                 @OA\Property(
     *                                     property="role",
     *                                     type="object",
     *                                     @OA\Property(property="doc_code", type="string", example="AGREE")
     *                                 )
     *                             )
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
     *     @OA\Response(
     *         response=403,
     *         description="Нет доступа"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */

    public function forControlIndex(Request $request): JsonResponse
    {
        try {
            $doc_name = $request->input('doc_name');
            $doc_type = $request->input('doc_type');
            $date_from = $request->input('date_from');
            $date_till = $request->input('date_till');
            $status = $request->input('status');
            $perPage = $request->input('per_page', 25);

            $controls = JournalTypeControls::where('status', 1)->pluck('journal_type_id');
            $today = Carbon::now()->format('Y-m-d');

            $search = DocumentJournals::whereIn('j_type', $controls)
                ->where('status', '!=', 1);

            if ($status === '12') {
                $search->whereHas('doc', fn ($q) =>
                $q->where('doc_term', '<', $today)->whereNotIn('status', [2, 9, 10, 11])
                );
            } elseif ($status === '14') {
                $search->whereHas('directorResolution', fn ($q) => $q->where('res_term', '<', $today))
                    ->whereNotIn('status', [2, 10, 11]);
            } elseif ($status) {
                $search->where('status', $status);
            }

            if ($doc_type && $doc_type !== 'all') {
                $search->where('j_type', $doc_type);
            }

            if ($doc_name) {
                $search->whereHas('doc', function ($q) use ($doc_name) {
                    $q->where('doc_name', 'ILIKE', "%$doc_name%")
                        ->orWhere('doc_number', 'ILIKE', "%$doc_name%");
                });
            }

            if ($date_from || $date_till) {
                $search->whereHas('doc', function ($q) use ($date_from, $date_till) {
                    if ($date_from) {
                        $q->where('doc_date', '>=', Carbon::createFromFormat('d.m.Y', $date_from)->format('Y-m-d'));
                    }
                    if ($date_till) {
                        $q->where('doc_date', '<=', Carbon::createFromFormat('d.m.Y', $date_till)->format('Y-m-d'));
                    }
                });
            }

            $models = $search
                ->where('status', '!=', 13)
                ->orderByDesc('id')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Documents for control retrieved',
                'data' => $models
            ]);

        } catch (\Exception $e) {
            Log::error('forControlIndexApi error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/document/{j_id}/execution",
     *     summary="Просмотр документа для исполнения",
     *     description="Возвращает детальную информацию о документе на исполнении",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="journal", type="object"),
     *                 @OA\Property(property="document", type="object"),
     *                 @OA\Property(property="html_doc", type="string"),
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="execution_steps", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="current_execution", type="object"),
     *                 @OA\Property(property="director_resolution", type="object"),
     *                 @OA\Property(property="reply_documents", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=403, description="Нет доступа"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function viewExecDocument($j_id): JsonResponse
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);
            $document = Documents::where('id', $journal->doc_id)->firstOrFail();

            // Check if user has access to this document
            $hasAccess = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = $journal->files;

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'RESOL');
                })
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal', 'toUser.department'])
                ->get();

            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal', 'toUser.department'])
                ->get();

            $current_execution = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->whereIn('to_user', $workIds)
                ->first();

            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'SIGN');
                })
                ->with(['toUser.personal'])
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'AGREE');
                })
                ->with(['toUser.personal'])
                ->get();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)
                ->with(['fromUserWork.personal', 'files'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            return response()->json([
                'success' => true,
                'message' => 'Execution document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'selected_performers' => $selectedPerformers,
                    'selected_add_performers' => $selectedAddPerformers,
                    'current_execution' => $current_execution,
                    'signer' => $signer,
                    'agreement_users' => $agreement_users,
                    'reply_documents' => $replyDocuments
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing execution document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/document/{j_id}/sign",
     *     summary="Просмотр документа для подписания",
     *     description="Возвращает детальную информацию о документе для подписания",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=403, description="Нет доступа"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function viewSignDocument($j_id): JsonResponse
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);
            $document = Documents::where('id', $journal->doc_id)->firstOrFail();

            $hasAccess = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'SIGN');
                })
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = $journal->files;

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'RESOL');
                })
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'SIGN');
                })
                ->whereIn('to_user', $workIds)
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'AGREE');
                })
                ->with(['toUser.personal'])
                ->get();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)
                ->with(['fromUserWork.personal', 'files'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            return response()->json([
                'success' => true,
                'message' => 'Sign document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'selected_performers' => $selectedPerformers,
                    'selected_add_performers' => $selectedAddPerformers,
                    'signer' => $signer,
                    'agreement_users' => $agreement_users,
                    'reply_documents' => $replyDocuments
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing sign document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/document/{j_id}/approval",
     *     summary="Просмотр документа для согласования",
     *     description="Возвращает детальную информацию о документе для согласования",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Успешный ответ"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=403, description="Нет доступа"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function viewApprovalDocument($j_id): JsonResponse
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);
            $document = Documents::where('id', $journal->doc_id)->firstOrFail();

            $hasAccess = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'AGREE');
                })
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = $journal->files;

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'RESOL');
                })
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'SIGN');
                })
                ->with(['toUser.personal'])
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'AGREE');
                })
                ->with(['toUser.personal'])
                ->get();

            $current_agreement = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'AGREE');
                })
                ->whereIn('to_user', $workIds)
                ->first();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)
                ->with(['fromUserWork.personal', 'files'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            return response()->json([
                'success' => true,
                'message' => 'Approval document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'selected_performers' => $selectedPerformers,
                    'selected_add_performers' => $selectedAddPerformers,
                    'signer' => $signer,
                    'agreement_users' => $agreement_users,
                    'current_agreement' => $current_agreement,
                    'reply_documents' => $replyDocuments
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing approval document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/document/{j_id}/confirm",
     *     summary="Просмотр документа для подтверждения",
     *     description="Возвращает детальную информацию о документе для подтверждения резолюции",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Успешный ответ"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=403, description="Нет доступа"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function viewConfirmDocument($j_id): JsonResponse
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);
            $document = Documents::where('id', $journal->doc_id)->firstOrFail();

            $hasAccess = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'RESOL');
                })
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = $journal->files;

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'RESOL');
                })
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'SIGN');
                })
                ->with(['toUser.personal'])
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'AGREE');
                })
                ->with(['toUser.personal'])
                ->get();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)
                ->with(['fromUserWork.personal', 'files'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            return response()->json([
                'success' => true,
                'message' => 'Confirm document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'selected_performers' => $selectedPerformers,
                    'selected_add_performers' => $selectedAddPerformers,
                    'signer' => $signer,
                    'agreement_users' => $agreement_users,
                    'reply_documents' => $replyDocuments
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing confirm document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/document/{j_id}/control",
     *     summary="Просмотр документа на контроле",
     *     description="Возвращает детальную информацию о документе на контроле",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Успешный ответ"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function viewControlDocument($j_id): JsonResponse
    {
        try {
            $journal = DocumentJournals::findOrFail($j_id);
            $document = Documents::where('id', $journal->doc_id)->firstOrFail();

            $documentFiles = $journal->files;

            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'RESOL');
                })
                ->first();

            $directorResolution = DocumentResolutions::where('j_id', $journal->id)
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->first();

            $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'EXEC');
                })
                ->where('from_user', '!=', $directorDocument->to_user ?? 0)
                ->with(['toUser.personal'])
                ->get();

            $signer = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'SIGN');
                })
                ->with(['toUser.personal'])
                ->first();

            $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($q) {
                    $q->where('doc_code', 'AGREE');
                })
                ->with(['toUser.personal'])
                ->get();

            $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)
                ->with(['fromUserWork.personal', 'files'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            return response()->json([
                'success' => true,
                'message' => 'Control document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'director_document' => $directorDocument,
                    'director_resolution' => $directorResolution,
                    'selected_performers' => $selectedPerformers,
                    'selected_add_performers' => $selectedAddPerformers,
                    'signer' => $signer,
                    'agreement_users' => $agreement_users,
                    'reply_documents' => $replyDocuments
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing control document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/document/{j_id}",
     *     summary="Просмотр документа для исполнения",
     *     description="Возвращает детальную информацию о документе",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="journal", type="object"),
     *                 @OA\Property(property="document", type="object"),
     *                 @OA\Property(property="html_doc", type="sting"),
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="execution_steps", type="array", @OA\Items(type="object"))
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
    public function viewDocument($j_id)
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);
            $document = Documents::where('id', $journal->doc_id)->firstOrFail();

            // Check if user has access to this document
            $hasAccess = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this document'
                ], 403);
            }

            $documentFiles = $journal->files;
            $executionSteps = $journal->executionSteps()
                ->with(['role', 'fromUser.personal'])
                ->get();

            $html_doc = $this->documentService->getHtmlDoc($journal, $document);

            return response()->json([
                'success' => true,
                'message' => 'Document retrieved successfully',
                'data' => [
                    'journal' => $journal,
                    'document' => $document,
                    'html_doc' => $html_doc,
                    'files' => $documentFiles,
                    'execution_steps' => $executionSteps
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error viewing document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/executions/document/{j_id}/sign",
     *     summary="Подписать документ",
     *     description="Подписывает документ от имени текущего пользователя",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ecp_r_text"},
     *             @OA\Property(
     *                 property="ecp_r_text",
     *                 type="string",
     *                 description="Комментарий к подписи",
     *                 example="Документ рассмотрен и одобрен"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Документ успешно подписан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document signed successfully")
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
    public function signDocument(Request $request, $j_id)
    {
        try {
            $validated = $request->validate([
                'ecp_r_text' => 'required|string|max:1000'
            ]);

            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);

            // Find sign step for current user
            $signStep = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', function ($query) {
                    $query->where('doc_code', 'SIGN');
                })
                ->where('status', 2)
                ->firstOrFail();

            // Update sign step status
            $signStep->update([
                'status' => 5,
                'r_text' => $validated['ecp_r_text']
            ]);

            // Update journal status if needed
            $pendingSteps = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($query) {
                    $query->where('doc_code', 'SIGN');
                })
                ->where('status', '!=', 5)
                ->count();

            if ($pendingSteps == 0) {
                $journal->update(['status' => 7]);
            }

            Log::info('Document signed successfully', [
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'step_id' => $signStep->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document signed successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document or sign step not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error signing document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error signing document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/executions/document/{j_id}/approve",
     *     summary="Согласовать документ",
     *     description="Согласовывает документ от имени текущего пользователя",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ecp_r_text"},
     *             @OA\Property(
     *                 property="ecp_r_text",
     *                 type="string",
     *                 description="Комментарий к согласованию",
     *                 example="Документ рассмотрен и согласован"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Документ успешно согласован",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document approved successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function approveDocument(Request $request, $j_id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ecp_r_text' => 'required|string|max:1000'
            ]);

            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);

            $approvalStep = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', function ($query) {
                    $query->where('doc_code', 'AGREE');
                })
                ->where('status', 2)
                ->firstOrFail();

            $approvalStep->update([
                'status' => 5,
                'r_text' => $validated['ecp_r_text']
            ]);

            $pendingSteps = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereHas('role', function ($query) {
                    $query->where('doc_code', 'AGREE');
                })
                ->where('status', '!=', 5)
                ->count();

            if ($pendingSteps == 0) {
                $isInControls = JournalTypeControls::where('journal_type_id', $journal->j_type)
                    ->where('status', 1)
                    ->exists();

                if ($isInControls) {
                    $journal->update(['status' => 7]);
                } else {
                    $journal->update(['status' => 8]);

                    $resolutionStep = DocumentExecutionSteps::where('j_id', $journal->id)
                        ->whereHas('role', function ($query) {
                            $query->where('doc_code', 'RESOL');
                        })
                        ->first();

                    if ($resolutionStep) {
                        $resolutionStep->update(['status' => 2]);
                    }
                }
            }

            Log::info('Document approved successfully', [
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'step_id' => $approvalStep->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document approved successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document or approval step not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error approving document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error approving document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/executions/document/{j_id}/reject",
     *     summary="Отклонить или вернуть документ",
     *     description="Отклоняет или возвращает документ на доработку",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"confirm_id", "r_text"},
     *             @OA\Property(property="confirm_id", type="integer", description="Статус: 2 - отменить, 3 - вернуть", example=3),
     *             @OA\Property(property="r_text", type="string", description="Причина отклонения", example="Необходимо исправить данные")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Документ успешно отклонен/возвращен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document rejected successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function rejectDocument(Request $request, $j_id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'confirm_id' => 'required|integer|in:2,3',
                'r_text' => 'required|string|max:1000'
            ]);

            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);

            $step = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->where('status', '!=', 6)
                ->firstOrFail();

            $status = $validated['confirm_id'];
            $execStatus = $status == 2 ? 3 : 4;

            $step->update([
                'status' => $execStatus,
                'r_text' => $validated['r_text']
            ]);

            $journal->update(['status' => $status]);

            Log::info('Document rejected', [
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'status' => $status,
                'step_id' => $step->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document rejected successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document or execution step not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error rejecting document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/executions/document/{step_id}/reply",
     *     summary="Добавить ответ к документу",
     *     description="Добавляет комментарий-ответ к документу с возможностью прикрепления файлов",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="step_id",
     *         in="path",
     *         description="ID шага исполнения",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"r_text"},
     *                 @OA\Property(property="r_text", type="string", description="Текст ответа"),
     *                 @OA\Property(property="type", type="integer", description="Тип комментария"),
     *                 @OA\Property(
     *                     property="doc_files[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Прикрепленные файлы"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ответ успешно добавлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reply added successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Шаг не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function replyDocument(Request $request, $step_id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'r_text' => 'required|string',
                'type' => 'nullable|integer',
                'doc_files.*' => 'nullable|file|max:10240'
            ]);

            $step = DocumentExecutionSteps::findOrFail($step_id);

            $comment = new DocumentReplyComments();
            $comment->doc_exec_step_id = $step_id;
            $comment->j_id = $step->j_id;
            $comment->from_dep = Auth::user()->currentWork->dep_id ?? null;
            $comment->from_user = Auth::user()->currentWork->id ?? null;
            $comment->reply_comment_type = $validated['type'] ?? 0;
            $comment->res_text = $validated['r_text'];
            $comment->status = 1;
            $comment->save();

            if ($request->hasFile('doc_files')) {
                $docFiles = $request->file('doc_files');
                foreach ($docFiles as $file) {
                    $today = Carbon::today();
                    $path = 'doc/' . $today->year . '/' . $today->month . '/' . $today->day . '/';

                    $documentFile = new ReplyDocumentFiles();
                    $documentFile->step_id = $comment->id;
                    $documentFile->j_id = $step->j_id;
                    $documentFile->file_name = $file->getClientOriginalName();
                    $documentFile->file_hash = uniqid() . '.' . $file->getClientOriginalExtension();
                    $documentFile->file_path = $path;
                    $documentFile->file_ext = $file->getClientOriginalExtension();
                    $documentFile->file_size = $file->getSize();
                    Storage::disk('ftp_13')->put($path . $documentFile->file_hash, file_get_contents($file->getRealPath()));
                    $documentFile->save();
                }
            }

            $step->update(['status' => 4]);

            return response()->json([
                'success' => true,
                'message' => 'Reply added successfully',
                'data' => $comment
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execution step not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error adding reply', [
                'error' => $e->getMessage(),
                'step_id' => $step_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error adding reply',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/executions/document/{j_id}/execute",
     *     summary="Завершить исполнение документа",
     *     description="Отмечает документ как исполненный",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="j_id",
     *         in="path",
     *         description="ID журнала документа",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Документ успешно исполнен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document executed successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function executeDocument($j_id): JsonResponse
    {
        try {
            $user = Auth::user();
            $workIds = $user->userWorkIDs();

            $journal = DocumentJournals::findOrFail($j_id);

            $execDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', function ($query) {
                    $query->where('doc_code', 'EXEC');
                })
                ->firstOrFail();

            $execDocument->update(['status' => 6]);

            $journal->update(['status' => 11]);

            Log::info('Document executed', [
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'exec_id' => $execDocument->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document executed successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document or execution step not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error executing document', [
                'error' => $e->getMessage(),
                'j_id' => $j_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error executing document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/executions/status-list",
     *     summary="Получить справочники статусов документов",
     *     description="Возвращает наборы статусов для исполнения, согласования, подтверждения и контроля документов",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Status lists retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                    property="execution_statuses",
     *                    type="array",
     *                    description="Статусы исполнения документов",
     *                    @OA\Items(ref="#/components/schemas/StatusListItem")
     *                  ),
     *
     *                  @OA\Property(
     *                      property="sign_statuses",
     *                      type="array",
     *                      description="Статусы подписания документов",
     *                      @OA\Items(ref="#/components/schemas/StatusListItem")
     *                  ),
     *
     *                  @OA\Property(
     *                      property="approval_statuses",
     *                      type="array",
     *                      description="Статусы согласования документов",
     *                      @OA\Items(ref="#/components/schemas/StatusListItem")
     *                   ),
     *
     *                  @OA\Property(
     *                      property="confirm_statuses",
     *                      type="array",
     *                      description="Статусы подтверждения",
     *                      @OA\Items(ref="#/components/schemas/StatusListItem")
     *                  ),
     *
     *                  @OA\Property(
     *                      property="control_statuses",
     *                      type="array",
     *                      description="Статусы контроля",
     *                      @OA\Items(ref="#/components/schemas/StatusListItem")
     *                  )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */

    public function statusList(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Status lists retrieved successfully',
                'data' => [
                    'execution_statuses' => StatusList::where('type', 'doc_exec')
                        ->orderBy('order_number')
                        ->get(),

                    'sign_statuses' => StatusList::where('type', 'doc_signer')
                        ->orderBy('order_number')
                        ->get(),

                    'approval_statuses' => StatusList::where('type', 'doc_signer')
                        ->orderBy('order_number')
                        ->get(),

                    'confirm_statuses' => StatusList::where('type', 'work_document')
                        ->whereNotIn('code', [1, 4, 5, 6, 7, 13])
                        ->orderBy('order_number')
                        ->get(),

                    'control_statuses' => StatusList::where('type', 'work_document')
                        ->where('code', '!=', 1)
                        ->orderBy('order_number')
                        ->get(),
                ]
            ]);

        } catch (\Exception $e) {

            Log::error('Error retrieving status lists', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving status lists'
            ], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/api/v1/executions/journal-list",
     *     summary="Получить справочники типов журналов",
     *     description="Возвращает наборы типов журналов для исполнения, подписания, согласования, подтверждения и контроля",
     *     tags={"Executions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(ref="#/components/schemas/JournalListResponse")
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
    public function journalList(): JsonResponse
    {
        try {

            $execution_journal_types = JournalTypes::whereDoesntHave('childs')
                ->where('status', 1)
                ->whereNotIn('doc_code', ['BASE_OUTGOING'])
                ->orderBy('order_number')
                ->get();

            $sign_journal_types = JournalTypes::whereDoesntHave('childs')
                ->where('status', 1)
                ->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING'])
                ->orderBy('order_number')
                ->get();

            $approval_journal_types = JournalTypes::whereDoesntHave('childs')
                ->where('status', 1)
                ->whereNotIn('doc_code', ['BASE_INCOMING'])
                ->orderBy('order_number')
                ->get();

            $confirm_journal_types = JournalTypes::whereDoesntHave('childs')
                ->where('status', 1)
                ->orderBy('order_number')
                ->get();

            $controls = JournalTypeControls::where('status', 1)
                ->pluck('journal_type_id')
                ->toArray();

            $control_journal_types = JournalTypes::whereDoesntHave('childs')
                ->where('status', 1)
                ->whereIn('id', $controls)
                ->orderBy('order_number')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Journal types retrieved successfully',
                'data' => [
                    'execution_journal_types' => $execution_journal_types,
                    'sign_journal_types'      => $sign_journal_types,
                    'approval_journal_types'  => $approval_journal_types,
                    'confirm_journal_types'   => $confirm_journal_types,
                    'control_journal_types'   => $control_journal_types,
                ]
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Error retrieving journal types', [
                'error'   => $e->getMessage(),
                'user_id'=> auth()->id(),
                'trace'  => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving journal types'
            ], 500);
        }
    }


}
