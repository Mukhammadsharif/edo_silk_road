<?php

namespace App\Http\Controllers\Api\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Admin\StatusList;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Protocols",
 *     description="API для работы HR сотрудников с приказами"
 * )
 */
class ProtocolApiEmpHrOrdersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/protocol/hr-orders",
     *     summary="Получить список HR приказов(API для работы HR сотрудников с приказами)",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="s_text",
     *         in="query",
     *         description="Поиск по названию или номеру документа",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="s_status",
     *         in="query",
     *         description="Фильтр по статусу (по умолчанию 2)",
     *         @OA\Schema(type="string", default="2")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество элементов на странице",
     *         @OA\Schema(type="integer", default=25)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Данные успешно получены"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Валидация
            $validator = Validator::make($request->all(), [
                's_text' => 'nullable|string|max:255',
                's_status' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }

            $s_text = $request->input('s_text');
            $s_status = $request->input('s_status', 2);
            $per_page = $request->input('per_page', 25);

            // Получение ID рабочих записей пользователя
            $userWorkIds = $user->userWorkIDs();

            // Построение запроса (только HR сотрудники - role_id = 4)
            $search = ProtocolDocMember::where('role_id', 4)
                ->whereIn('user_id', $userWorkIds);

            // Поиск по тексту
            if (trim($s_text)) {
                $search->whereHas('document', function ($query) use ($s_text) {
                    $query->where('doc_name', 'LIKE', '%' . $s_text . '%')
                        ->orWhere('doc_number', 'LIKE', '%' . $s_text . '%');
                });
            }

            // Фильтр по статусу
            if ($s_status == 'all') {
                $search->whereNotIn('status', [1, 6]);
            } elseif ($s_status) {
                $search->where('status', $s_status);
            } else {
                $search->where('status', 2);
            }

            $models = $search->with([
                'document.docType:id,title,doc_code,doc_hash',
                'document.secretary.personal:user_id,l_name,f_name,m_name',
                'userWork.personal:user_id,l_name,f_name,m_name'
            ])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            // Получение статусов
            $statusList = StatusList::where('type', 'prt_status')
                ->whereNotIn('code', [1, 6])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Данные успешно получены',
                'data' => $models,
                'filters' => [
                    's_text' => $s_text,
                    's_status' => $s_status
                ],
                'status_list' => $statusList
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при получении данных',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/protocol/hr-orders/{id}/{doc_hash}",
     *     summary="Просмотр HR приказа(API для работы HR сотрудников с приказами)",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID записи HR сотрудника",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="doc_hash",
     *         in="path",
     *         required=true,
     *         description="Хэш документа",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Документ успешно получен"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="document", type="object"),
     *                 @OA\Property(property="member", type="object"),
     *                 @OA\Property(property="members", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="hr_employees", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="html_content", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function view($id, $doc_hash): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }

            // Получение документа
            $document = ProtocolDocument::where('doc_hash', $doc_hash)
                ->with(['docType:id,title,doc_code,doc_hash'])
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ не найден'
                ], 404);
            }

            // Получение записи HR сотрудника
            $documentMember = ProtocolDocMember::where('id', $id)
                ->where('doc_id', $document->id)
                ->where('role_id', 4)
                ->whereIn('user_id', $user->userWorkIDs())
                ->with([
                    'userWork.personal:user_id,l_name,f_name,m_name',
                    'userWork.department:id,name_uz,name_ru',
                    'userWork.position:id,name_uz,name_ru'
                ])
                ->first();

            if (!$documentMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Вы не являетесь HR сотрудником этого документа'
                ], 403);
            }

            // Получение файлов
            $documentFiles = ProtocolDocFile::where('doc_id', $document->id)
                ->orderBy('file_ext', 'DESC')
                ->get();

            // Получение участников (не HR)
            $documentMembers = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', '!=', 4)
                ->with([
                    'userWork.personal:user_id,l_name,f_name,m_name',
                    'userWork.department:id,name_uz,name_ru',
                    'userWork.position:id,name_uz,name_ru',
                    'role:id,code,name_uz,name_ru'
                ])
                ->orderBy('order_number')
                ->get();

            // Получение всех HR сотрудников
            $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', 4)
                ->with([
                    'userWork.personal:user_id,l_name,f_name,m_name',
                    'userWork.department:id,name_uz,name_ru',
                    'userWork.position:id,name_uz,name_ru'
                ])
                ->orderBy('order_number')
                ->get();

            // Получение HTML контента
            $html_doc = null;
            $model = OnlyOfficeDoc::where('doc_type', 'PRT')
                ->where('doc_id', $document->id)
                ->first();

            if ($model) {
                $path = $model->doc_path . $model->doc_key . '.html';
                if (Storage::disk('ftp')->exists($path)) {
                    $html_doc = Storage::disk('ftp')->get($path);
                }
            }

            // Получение QR кода
            $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')
                ->where('doc_id', $document->id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Документ успешно получен',
                'data' => [
                    'document' => $document,
                    'member' => $documentMember,
                    'members' => $documentMembers,
                    'hr_employees' => $documentHrEmp,
                    'files' => $documentFiles,
                    'html_content' => $html_doc,
                    'qr_key' => $qrKey
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при получении документа',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/protocol/hr-orders/{id}/{doc_hash}/sign",
     *     summary="Ознакомиться с HR приказом(API для работы HR сотрудников с приказами)",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID записи HR сотрудника",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="doc_hash",
     *         in="path",
     *         required=true,
     *         description="Хэш документа",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="comment", type="string", description="Комментарий", example="Ознакомлен")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно ознакомлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Вы успешно ознакомились с документом")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function sign(Request $request, $id, $doc_hash): JsonResponse
    {
        try {
            // Валидация
            $validator = Validator::make($request->all(), [
                'comment' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован'
                ], 401);
            }

            // Получение документа
            $document = ProtocolDocument::where('doc_hash', $doc_hash)->first();
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ не найден'
                ], 404);
            }

            // Получение записи HR сотрудника
            $documentMember = ProtocolDocMember::where('id', $id)
                ->where('doc_id', $document->id)
                ->where('role_id', 4)
                ->whereIn('user_id', $user->userWorkIDs())
                ->first();

            if (!$documentMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Вы не являетесь HR сотрудником этого документа'
                ], 403);
            }

            // Проверка статуса
            if ($documentMember->status == 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Вы уже ознакомились с этим документом'
                ], 422);
            }

            if ($documentMember->status != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ недоступен для ознакомления'
                ], 422);
            }

            // Обновление статуса
            $documentMember->update([
                'status' => 3,
                'signed_date' => Carbon::now(),
                'comment' => $request->input('comment')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Вы успешно ознакомились с документом',
                'data' => [
                    'member' => $documentMember
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при ознакомлении с документом',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
