<?php

namespace App\Http\Controllers\Api\Protocol;

use App\Http\Controllers\Controller;;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocComments;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Protocol Secretary",
 *     description="API для работы секретаря с протоколами"
 * )
 */
class ProtocolApiSecretaryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/protocol/secretary/{doc_type}",
     *     summary="Получить список протоколов",
     *     tags={"Protocol Secretary"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doc_type",
     *         in="path",
     *         required=true,
     *         description="Хэш типа документа",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="s_text",
     *         in="query",
     *         description="Поиск по названию или номеру документа",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="s_status",
     *         in="query",
     *         description="Фильтр по статусу",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Дата от (формат: d.m.Y)",
     *         @OA\Schema(type="string", format="date", example="01.12.2024")
     *     ),
     *     @OA\Parameter(
     *         name="date_till",
     *         in="query",
     *         description="Дата до (формат: d.m.Y)",
     *         @OA\Schema(type="string", format="date", example="31.12.2025")
     *     ),
     *     @OA\Parameter(
     *         name="created_user",
     *         in="query",
     *         description="ID пользователя создавшего документ",
     *         @OA\Schema(type="integer")
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
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             ),
     *             @OA\Property(property="filters", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=404, description="Тип документа не найден"),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function index(Request $request, $doc_type): JsonResponse
    {
        try {
            // Валидация входных данных
            $validator = Validator::make($request->all(), [
                's_text' => 'nullable|string|max:255',
                's_status' => 'nullable|string',
                'date_from' => 'nullable|date_format:d.m.Y',
                'date_till' => 'nullable|date_format:d.m.Y',
                'created_user' => 'nullable|integer|exists:users,id',
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

            // Проверка существования типа документа
            $protocolType = ProtocolDocType::where('doc_hash', $doc_type)
                ->where('status', 1)
                ->first();

            if (!$protocolType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Тип документа не найден'
                ], 404);
            }

            $s_text = $request->input('s_text');
            $s_status = $request->input('s_status');
            $date_from = $request->input('date_from');
            $date_till = $request->input('date_till');
            $created_user = $request->input('created_user');
            $per_page = $request->input('per_page', 25);

            $status_public_doc = ($protocolType->doc_code == 'PUB_DOCS');

            if ($status_public_doc) {
                $search = ProtocolDocument::where('visibility', 2);
            } else {
                $search = ProtocolDocument::whereHas('docType', function ($query) use ($doc_type) {
                    $query->where('doc_hash', $doc_type);
                });
            }

            // Поиск по тексту
            if (trim($s_text)) {
                $search->where(function ($query) use ($s_text) {
                    $query->where('doc_name', 'LIKE', '%' . $s_text . '%')
                        ->orWhere('doc_number', 'LIKE', '%' . $s_text . '%');
                });
            }

            // Фильтр по статусу
            if ($s_status && $s_status != 'all') {
                $search->where('status', $s_status);
            } else {
                if ($status_public_doc) {
                    $search->whereIn('status', [2, 3, 4, 5]);
                } else {
                    $search->whereIn('status', [1, 2, 4, 5]);
                }
            }

            // Фильтр по создателю
            if ($created_user) {
                $search->where('created_user', $created_user);
            }

            // Фильтр по датам
            if ($date_from && $date_till) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y-m-d');
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y-m-d');
                $search->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
            } elseif ($date_from) {
                $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y-m-d');
                $search->where('doc_date', '>=', $tmp_date_from);
            } elseif ($date_till) {
                $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y-m-d');
                $search->where('doc_date', '<=', $tmp_date_till);
            }

            $models = $search->with([
                'docType:id,title,doc_code,doc_hash',
                'secretary.personal:user_id,l_name,f_name,m_name'
            ])
                ->orderBy('id', 'DESC')
                ->paginate($per_page);

            return response()->json([
                'success' => true,
                'message' => 'Данные успешно получены',
                'data' => $models,
                'filters' => [
                    's_text' => $s_text,
                    's_status' => $s_status,
                    'date_from' => $date_from,
                    'date_till' => $date_till,
                    'created_user' => $created_user
                ],
                'protocol_type' => [
                    'id' => $protocolType->id,
                    'title' => $protocolType->title,
                    'doc_code' => $protocolType->doc_code,
                    'doc_hash' => $protocolType->doc_hash
                ]
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
     *     path="/api/v1/protocol/secretary/{id}/{doc_hash}",
     *     summary="Просмотр протокола",
     *     tags={"Protocol Secretary"},
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
     *                 @OA\Property(property="members", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="html_content", type="string"),
     *                 @OA\Property(property="qr_key", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function view($id, $doc_hash): JsonResponse
    {
        try {
            // Проверка существования документа
            $document = ProtocolDocument::where('id', $id)
                ->where('doc_hash', $doc_hash)
                ->with([
                    'docType:id,title,doc_code,doc_hash,parent_id',
                    'secretary.personal:user_id,l_name,f_name,m_name'
                ])
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ не найден'
                ], 404);
            }

            // Получение файлов
            $documentFiles = ProtocolDocFile::where('doc_id', $document->id)
                ->orderBy('file_ext', 'DESC')
                ->get();

            // Получение участников (кроме HR сотрудников)
            $documentMembers = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', '!=', 4)
                ->with([
                    'userWork.personal:user_id,l_name,f_name,m_name',
                    'userWork.department:id,name_uz,name_ru',
                    'userWork.position:id,name_uz,name_ru',
                ])
                ->orderBy('order_number')
                ->get();

            // Получение HR сотрудников
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
                if (Storage::disk('public')->exists($path)) {
                    $html_doc = Storage::disk('public')->get($path);
                }
            }

            // Получение QR кода
            $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')
                ->where('doc_id', $document->id)
                ->first();

            // Получение комментариев
            $userComments = ProtocolDocComments::where('doc_id', $document->id)
                ->with([
                    'creator.personal:user_id,l_name,f_name,m_name'
                ])
                ->orderBy('created_date', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Документ успешно получен',
                'data' => [
                    'document' => $document,
                    'members' => $documentMembers,
                    'hr_employees' => $documentHrEmp,
                    'files' => $documentFiles,
                    'html_content' => $html_doc,
                    'qr_key' => $qrKey,
                    'comments' => $userComments
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
     *     path="/api/v1/protocol/secretary/{id}/{doc_hash}/send",
     *     summary="Отправить протокол участникам",
     *     tags={"Protocol Secretary"},
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
     *         description="Документ успешно отправлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Документ успешно отправлен участникам")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Не авторизован"),
     *     @OA\Response(response=404, description="Документ не найден")
     * )
     */
    public function sendToMembers($id, $doc_hash): JsonResponse
    {
        try {
            $document = ProtocolDocument::where('id', $id)
                ->where('doc_hash', $doc_hash)
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ не найден'
                ], 404);
            }

            // Проверка статуса документа
            if ($document->status != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Документ уже был отправлен или имеет некорректный статус'
                ], 422);
            }

            // Обновление статуса документа
            $document->update(['status' => 2]);

            // Обновление статуса участников
            ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', '!=', 4)
                ->update(['status' => 2]);

            $members = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', '!=', 4)
                ->with('userWork.personal')
                ->get();

            // Здесь можно добавить отправку уведомлений
            // Например через очереди или события

            return response()->json([
                'success' => true,
                'message' => 'Документ успешно отправлен участникам',
                'data' => [
                    'document_id' => $document->id,
                    'members_count' => $members->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отправке документа',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
