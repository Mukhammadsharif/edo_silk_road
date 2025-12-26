<?php

namespace App\Http\Controllers\Api\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Protocol\ProtocolDocument;
use App\Models\Protocol\ProtocolDocType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
class ProtocolApiMembersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/protocol/member-protocols",
     *     summary="Список протоколов",
     *     description="Возвращает список протоколов с фильтрами",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="doc_type_hash", in="query", required=false, @OA\Schema(type="string"), description="Хэш типа документа"),
     *     @OA\Parameter(name="s_text", in="query", required=false, @OA\Schema(type="string"), description="Поиск по названию/номеру"),
     *     @OA\Parameter(name="s_status", in="query", required=false, @OA\Schema(type="integer"), description="Статус (по умолчанию 2)"),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date", example="01.01.2025")),
     *     @OA\Parameter(name="date_till", in="query", required=false, @OA\Schema(type="string", format="date", example="31.12.2025")),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Успешный ответ",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Данные успешно получены"),
     *              @OA\Property(property="data", type="object")
     *          )
     *     ),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'doc_type_hash' => 'nullable|string',
                's_text'        => 'nullable|string',
                's_status'      => 'nullable|integer',
                'date_from'     => 'nullable|date_format:d.m.Y',
                'date_till'     => 'nullable|date_format:d.m.Y',
            ]);

            $query = ProtocolDocument::query();

            if ($docTypeHash = $request->input('doc_type_hash')) {
                $docType = ProtocolDocType::where('doc_hash', $docTypeHash)->firstOrFail();
                $query->where('doc_type_id', $docType->id);
            }

            if ($sText = $request->input('s_text')) {
                $query->where(function ($q) use ($sText) {
                    $q->where('doc_name', 'LIKE', "%{$sText}%")
                        ->orWhere('doc_number', 'LIKE', "%{$sText}%");
                });
            }

            $sStatus = $request->input('s_status', 2);
            if ($sStatus !== 'all') {
                $query->where('status', $sStatus);
            }

            // Фильтр по датам
            if ($request->filled('date_from')) {
                $from = Carbon::createFromFormat('d.m.Y', $request->date_from)->format('Y-m-d');
                $query->whereDate('doc_date', '>=', $from);
            }
            if ($request->filled('date_till')) {
                $till = Carbon::createFromFormat('d.m.Y', $request->date_till)->format('Y-m-d');
                $query->whereDate('doc_date', '<=', $till);
            }

            $documents = $query->orderByDesc('id')->paginate(25);

            return response()->json($documents, 200);

        } catch (Exception $e) {
            Log::error('Error fetching protocol list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error fetching protocols',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/protocol/member-protocol/{doc_hash}",
     *     summary="Просмотр протокола",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="doc_hash", in="path", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *           response=200,
     *           description="Успешный ответ",
     *           @OA\JsonContent(
     *               @OA\Property(property="success", type="boolean", example=true),
     *               @OA\Property(property="message", type="string", example="Данные успешно получены"),
     *               @OA\Property(property="data", type="object")
     *           )
     *      ),
     *     @OA\Response(response=404, description="Протокол не найден"),
     *     @OA\Response(response=500, description="Внутренняя ошибка")
     * )
     */
    public function show($doc_hash): \Illuminate\Http\JsonResponse
    {
        try {
            $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();

            return response()->json($document, 200);

        } catch (Exception $e) {
            Log::error('Error viewing protocol', [
                'doc_hash' => $doc_hash,
                'error'    => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Protocol not found or error occurred',
                'error'   => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/protocol/member-protocol/{doc_hash}/sign",
     *     summary="Подпись протокола",
     *     description="Подписывает протокол текущим авторизованным пользователем",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="doc_hash", in="path", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Протокол успешно подписан"),
     *     @OA\Response(response=400, description="Нельзя подписать"),
     *     @OA\Response(response=404, description="Протокол не найден"),
     *     @OA\Response(response=500, description="Ошибка сервера")
     * )
     */
    public function sign(Request $request, $doc_hash): \Illuminate\Http\JsonResponse
    {
        try {
            $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();

            // Здесь логика подписи — пример: обновляем статус участника
            // Предположим, что есть связь через ProtocolDocMember
            $member = $document->members()->where('user_id', auth('sanctum')->id())->first();

            if (!$member) {
                return response()->json(['message' => 'You are not a member of this protocol'], 400);
            }

            if ($member->status == 3) { // предположим, 3 = уже подписан
                return response()->json(['message' => 'Already signed'], 400);
            }

            $member->status = 3; // статус "подписан"
            $member->signed_at = now();
            $member->save();

            return response()->json([
                'message' => 'Protocol signed successfully',
                'document' => $document->load('members')
            ], 200);

        } catch (Exception $e) {
            Log::error('Error signing protocol', [
                'doc_hash' => $doc_hash,
                'user_id'  => auth('sanctum')->id() ?? 'unknown',
                'error'    => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error signing protocol',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
