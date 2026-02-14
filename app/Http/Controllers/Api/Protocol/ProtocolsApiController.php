<?php

namespace App\Http\Controllers\Api\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Admin\StatusList;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocType;
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
 *     name="Protocols API",
 *     description="API для работы с протоколами (для членов и участников)"
 * )
 */
class ProtocolsApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/protocol/protocols",
     *     summary="Получить список всех протоколов (независимо от типа, для авторизованного пользователя)",
     *     tags={"Protocols API"},
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
     *         description="Фильтр по статусу (по умолчанию 2; 'all' для всех кроме черновиков)",
     *         @OA\Schema(type="string", default="2")
     *     ),
     *     @OA\Parameter(
     *         name="doc_type",
     *         in="query",
     *         description="Фильтр по хэшу типа документа (опционально)",
     *         @OA\Schema(type="string")
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
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Валидация входных данных
            $validator = Validator::make($request->all(), [
                's_text' => 'nullable|string|max:255',
                's_status' => 'nullable|string',
                'doc_type' => 'nullable|string|exists:protocol_doc_types,doc_hash',
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
            $doc_type = $request->input('doc_type');
            $per_page = $request->input('per_page', 25);

            // Получение ID рабочих записей пользователя
            $userWorkIds = $user->userWorkIDs();

            // Построение запроса для членов (не секретарей)
            $search = ProtocolDocMember::whereIn('user_id', $userWorkIds);

            // Фильтр по типу документа, если указан
            if ($doc_type) {
                $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->first();
                if (!$protocolType) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Тип документа не найден'
                    ], 404);
                }
                if (!$user->hasPermissionTo('member_access_' . $protocolType->doc_code)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Доступ запрещен для этого типа документа'
                    ], 403);
                }
                $search->whereHas('document', function ($query) use ($protocolType) {
                    $query->where('doc_type_id', $protocolType->id);
                });
            }

            // Поиск по тексту
            if (trim($s_text)) {
                $search->whereHas('document', function ($query) use ($s_text) {
                    $query->where('doc_name', 'ILIKE', '%' . $s_text . '%')
                        ->orWhere('doc_number', 'ILIKE', '%' . $s_text . '%');
                });
            }

            // Фильтр по статусу
            if ($s_status == 'all') {
                $search->where('status', '!=', 1);
            } elseif ($s_status) {
                $search->where('status', $s_status);
            } else {
                $search->where('status', 2);
            }

            // Пагинация с отношениями
            $models = $search->with([
                'document.docType:id,title,doc_code,doc_hash',
                'document.secretary.personal:user_id,l_name,f_name,m_name',
                'userWork.personal:user_id,l_name,f_name,m_name'
            ])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            // Получение списка статусов
            $statusList = StatusList::where('type', 'prt_status')
                ->whereNotIn('code', [1, 6, 7])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Данные успешно получены',
                'data' => $models,
                'filters' => [
                    's_text' => $s_text,
                    's_status' => $s_status,
                    'doc_type' => $doc_type
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
     *     path="/api/v1/protocol/protocols/{id}/{doc_hash}",
     *     summary="Просмотр протокола по ID и хэшу",
     *     tags={"Protocols API"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID документа",
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
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="html_content", type="string"),
     *                 @OA\Property(property="qr_key", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
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
            $document = ProtocolDocument::where('id', $id)
                ->where('doc_hash', $doc_hash)
                ->with(['docType:id,title,doc_code,doc_hash'])
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ не найден'
                ], 404);
            }

            $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->first();
            if (!$protocolType || !$user->hasPermissionTo('member_access_' . $protocolType->doc_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен для этого документа'
                ], 403);
            }

            // Получение записи члена
            $documentMember = ProtocolDocMember::where('doc_id', $document->id)
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
                    'message' => 'Доступ запрещен. Вы не участник этого документа'
                ], 403);
            }

            // Получение файлов
            $documentFiles = ProtocolDocFile::where('doc_id', $document->id)
                ->orderBy('file_ext', 'DESC')
                ->get();

            // Получение всех участников
            $documentMembers = ProtocolDocMember::where('doc_id', $document->id)
                ->with([
                    'userWork.personal:user_id,l_name,f_name,m_name',
                    'userWork.department:id,name_uz,name_ru',
                    'userWork.position:id,name_uz,name_ru',
                    'role:id,code,name_uz,name_ru'
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
     *     path="/api/v1/protocol/protocols/{id}/{doc_hash}/sign",
     *     summary="Подписать/ознакомиться с протоколом",
     *     tags={"Protocols API"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID документа",
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
     *         description="Успешно подписан/ознакомлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Вы успешно ознакомились с документом")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=403, description="Доступ запрещен"),
     *     @OA\Response(response=404, description="Документ не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
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
            $document = ProtocolDocument::where('id', $id)
                ->where('doc_hash', $doc_hash)
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ не найден'
                ], 404);
            }

            $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->first();
            if (!$protocolType || !$user->hasPermissionTo('member_access_' . $protocolType->doc_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен для этого документа'
                ], 403);
            }

            // Получение записи члена
            $documentMember = ProtocolDocMember::where('doc_id', $document->id)
                ->whereIn('user_id', $user->userWorkIDs())
                ->first();

            if (!$documentMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещен. Вы не участник этого документа'
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

            // Проверка завершения (если все члены подписали, обновить статус документа)
            $pending = ProtocolDocMember::where('doc_id', $document->id)
                ->whereNotIn('status', [3, 5, 7])
                ->count();

            if ($pending == 0) {
                $document->update(['status' => 3]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Вы успешно ознакомились с документом',
                'data' => [
                    'member' => $documentMember,
                    'document_status' => $document->status
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
