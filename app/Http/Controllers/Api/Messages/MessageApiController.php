<?php

namespace App\Http\Controllers\Api\Messages;

use App\Http\Controllers\Controller;
use App\Models\Messages\Messages;
use App\Models\Messages\MessageUsers;
use App\Models\Messages\MessageForwards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class MessageApiController extends Controller
{
//    /**
//     * @OA\Get(
//     *     path="/api/v1/messages/{type}",
//     *     summary="Получить список сообщений",
//     *     description="Возвращает список сообщений (входящие, исходящие, архив, удаленные)",
//     *     tags={"Messages"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="type",
//     *         in="path",
//     *         description="Тип сообщений (inbox, outbox, archive, deleted)",
//     *         required=true,
//     *         @OA\Schema(
//     *             type="string",
//     *             enum={"inbox", "outbox", "archive", "deleted"}
//     *         )
//     *     ),
//     *     @OA\Parameter(
//     *         name="s_text",
//     *         in="query",
//     *         description="Поиск по теме сообщения",
//     *         required=false,
//     *         @OA\Schema(type="string")
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_from",
//     *         in="query",
//     *         description="Дата от (формат: Y-m-d)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date")
//     *     ),
//     *     @OA\Parameter(
//     *         name="date_till",
//     *         in="query",
//     *         description="Дата до (формат: Y-m-d)",
//     *         required=false,
//     *         @OA\Schema(type="string", format="date")
//     *     ),
//     *     @OA\Parameter(
//     *         name="page",
//     *         in="query",
//     *         description="Номер страницы",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=1)
//     *     ),
//     *     @OA\Parameter(
//     *         name="per_page",
//     *         in="query",
//     *         description="Количество записей на странице",
//     *         required=false,
//     *         @OA\Schema(type="integer", default=25)
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Успешный ответ",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string"),
//     *             @OA\Property(
//     *                 property="data",
//     *                 type="object",
//     *                 @OA\Property(property="current_page", type="integer"),
//     *                 @OA\Property(property="total", type="integer"),
//     *                 @OA\Property(
//     *                     property="data",
//     *                     type="array",
//     *                     @OA\Items(
//     *                         @OA\Property(property="id", type="integer"),
//     *                         @OA\Property(property="subject", type="string"),
//     *                         @OA\Property(property="text", type="string"),
//     *                         @OA\Property(property="created_at", type="string"),
//     *                         @OA\Property(property="read_date", type="string", nullable=true)
//     *                     )
//     *                 )
//     *             )
//     *         )
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=400,
//     *         description="Неверный тип сообщений"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=401,
//     *         description="Неавторизован"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера"
//     *     )
//     * )
//     */
//    public function list(Request $request, $type)
//    {
//        try {
//            $validTypes = ['inbox', 'outbox', 'archive', 'deleted'];
//            if (!in_array($type, $validTypes)) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Invalid message type. Must be one of: inbox, outbox, archive, deleted'
//                ], 400);
//            }
//
//            $user = Auth::user();
//            $userId = $user->userWorkIDs()[0];
//
//            $s_text = $request->input('s_text');
//            $date_from = $request->input('date_from');
//            $date_till = $request->input('date_till');
//            $per_page = $request->input('per_page', 25);
//
//            $query = DB::table('messages')
//                ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
//                ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
//                ->leftJoin('user_work_histories as sender', function ($join) {
//                    $join->on('sender.id', '=', DB::raw('COALESCE(message_forwards.from_w_user_id, messages.from_w_user_id)'));
//                })
//                ->select(
//                    'messages.id',
//                    'messages.subject',
//                    'messages.text',
//                    'sender.id as sender_id',
//                    'messages.created_at',
//                    'messages.hash_code',
//                    DB::raw('COALESCE(message_users.read_date, message_forwards.read_date) as read_date')
//                );
//
//            switch ($type) {
//                case 'inbox':
//                    $query->where(function ($q) use ($userId) {
//                        $q->where(function ($sub) use ($userId) {
//                            $sub->where('message_users.to_w_user_id', $userId)
//                                ->whereNotIn('message_users.status', [2, 3]);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('message_forwards.to_w_user_id', $userId)
//                                ->whereNotIn('message_forwards.status', [2, 3]);
//                        });
//                    });
//                    break;
//
//                case 'outbox':
//                    $query->where(function ($q) use ($userId) {
//                        $q->where(function ($sub) use ($userId) {
//                            $sub->where('messages.from_w_user_id', $userId)
//                                ->whereNotIn('message_users.status', [3]);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('message_forwards.from_w_user_id', $userId)
//                                ->whereNotIn('message_forwards.status', [3]);
//                        });
//                    });
//                    break;
//
//                case 'archive':
//                    $query->where(function ($q) use ($userId) {
//                        $q->where(function ($sub) use ($userId) {
//                            $sub->where('message_users.to_w_user_id', $userId)
//                                ->where('message_users.status', 2);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('messages.from_w_user_id', $userId)
//                                ->where('message_users.status', 2);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('message_forwards.to_w_user_id', $userId)
//                                ->where('message_forwards.status', 2);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('message_forwards.from_w_user_id', $userId)
//                                ->where('message_forwards.status', 2);
//                        });
//                    });
//                    break;
//
//                case 'deleted':
//                    $query->where(function ($q) use ($userId) {
//                        $q->where(function ($sub) use ($userId) {
//                            $sub->where('message_users.to_w_user_id', $userId)
//                                ->where('message_users.status', 3);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('messages.from_w_user_id', $userId)
//                                ->where('message_users.status', 3);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('message_forwards.to_w_user_id', $userId)
//                                ->where('message_forwards.status', 3);
//                        })->orWhere(function ($sub) use ($userId) {
//                            $sub->where('message_forwards.from_w_user_id', $userId)
//                                ->where('message_forwards.status', 3);
//                        });
//                    });
//                    break;
//            }
//
//            if ($s_text) {
//                $query->where('messages.subject', 'ilike', '%' . $s_text . '%');
//            }
//
//            if ($date_from) {
//                $query->whereDate('messages.created_at', '>=', $date_from);
//            }
//
//            if ($date_till) {
//                $query->whereDate('messages.created_at', '<=', $date_till);
//            }
//
//            $models = $query->distinct('messages.id')
//                ->orderBy('messages.id', 'desc')
//                ->orderBy('messages.created_at', 'desc')
//                ->paginate($per_page);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Messages retrieved successfully',
//                'data' => $models
//            ], 200);
//
//        } catch (Exception $e) {
//            Log::error('Error retrieving messages', [
//                'error' => $e->getMessage(),
//                'type' => $type,
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error retrieving messages',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
//
//    /**
//     * @OA\Get(
//     *     path="/api/v1/messages/view/{id}",
//     *     summary="Просмотр сообщения",
//     *     description="Возвращает детальную информацию о сообщении",
//     *     tags={"Messages"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="id",
//     *         in="path",
//     *         description="ID сообщения",
//     *         required=true,
//     *         @OA\Schema(type="integer")
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Успешный ответ",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string"),
//     *             @OA\Property(
//     *                 property="data",
//     *                 type="object",
//     *                 @OA\Property(property="message", type="object"),
//     *                 @OA\Property(property="files", type="array", @OA\Items(type="object")),
//     *                 @OA\Property(property="recipients", type="array", @OA\Items(type="object"))
//     *             )
//     *         )
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=403,
//     *         description="Доступ запрещен"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=404,
//     *         description="Сообщение не найдено"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=401,
//     *         description="Неавторизован"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера"
//     *     )
//     * )
//     */
//    public function view($id)
//    {
//        try {
//            $user = Auth::user();
//            $userId = $user->userWorkIDs()[0];
//
//            $message = Messages::with(['files', 'sender.personal'])
//                ->findOrFail($id);
//
//            // Check if user has access to this message
//            $hasAccess = MessageUsers::where('message_id', $id)
//                    ->where('to_w_user_id', $userId)
//                    ->exists() ||
//                MessageForwards::where('message_id', $id)
//                    ->where('to_w_user_id', $userId)
//                    ->exists() ||
//                $message->from_w_user_id == $userId;
//
//            if (!$hasAccess) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Access denied to this message'
//                ], 403);
//            }
//
//            // Update read status if it's an inbox message
//            $messageUser = MessageUsers::where('message_id', $id)
//                ->where('to_w_user_id', $userId)
//                ->where('status', 1)
//                ->first();
//
//            if ($messageUser) {
//                $messageUser->update([
//                    'status' => 2,
//                    'read_date' => now()
//                ]);
//            }
//
//            $messageForward = MessageForwards::where('message_id', $id)
//                ->where('to_w_user_id', $userId)
//                ->where('status', 1)
//                ->first();
//
//            if ($messageForward) {
//                $messageForward->update([
//                    'status' => 2,
//                    'read_date' => now()
//                ]);
//            }
//
//            // Get recipients
//            $recipients = collect();
//
//            $messageUsers = MessageUsers::where('message_id', $id)
//                ->with(['user.personal', 'user.userPost'])
//                ->get();
//
//            $messageForwards = MessageForwards::where('message_id', $id)
//                ->with(['user.personal', 'user.userPost'])
//                ->get();
//
//            $recipients = $recipients->merge($messageUsers->map(function ($item) {
//                return [
//                    'user' => $item->user,
//                    'status' => $item->status,
//                    'read_date' => $item->read_date,
//                ];
//            }));
//
//            $recipients = $recipients->merge($messageForwards->map(function ($item) {
//                return [
//                    'user' => $item->user,
//                    'status' => $item->status,
//                    'read_date' => $item->read_date,
//                ];
//            }));
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Message retrieved successfully',
//                'data' => [
//                    'message' => $message,
//                    'recipients' => $recipients
//                ]
//            ], 200);
//
//        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Message not found'
//            ], 404);
//
//        } catch (Exception $e) {
//            Log::error('Error viewing message', [
//                'error' => $e->getMessage(),
//                'id' => $id,
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error retrieving message',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
//
//    /**
//     * @OA\Delete(
//     *     path="/api/v1/messages/{id}",
//     *     summary="Удалить сообщение",
//     *     description="Удаляет (помечает как удаленное) сообщение для текущего пользователя",
//     *     tags={"Messages"},
//     *     security={{"sanctum":{}}},
//     *
//     *     @OA\Parameter(
//     *         name="id",
//     *         in="path",
//     *         description="ID сообщения",
//     *         required=true,
//     *         @OA\Schema(type="integer")
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=200,
//     *         description="Сообщение успешно удалено",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="success", type="boolean", example=true),
//     *             @OA\Property(property="message", type="string", example="Message deleted successfully")
//     *         )
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=404,
//     *         description="Сообщение не найдено"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=401,
//     *         description="Неавторизован"
//     *     ),
//     *
//     *     @OA\Response(
//     *         response=500,
//     *         description="Внутренняя ошибка сервера"
//     *     )
//     * )
//     */
//    public function delete($id)
//    {
//        try {
//            $user = Auth::user();
//            $userId = $user->userWorkIDs()[0];
//            $authIds = $user->userWorkIDs();
//
//            $messageUser = MessageUsers::where('message_id', $id)
//                ->where('to_w_user_id', $userId)
//                ->first();
//
//            $messageForward = MessageForwards::where('message_id', $id)
//                ->where(function ($q) use ($userId) {
//                    $q->where('to_w_user_id', $userId)
//                        ->orWhere('from_w_user_id', $userId);
//                })
//                ->first();
//
//            $message = Messages::whereIn('from_w_user_id', $authIds)
//                ->where('id', $id)
//                ->first();
//
//            if ($messageUser) {
//                $messageUser->update(['status' => 3]);
//            } elseif ($messageForward) {
//                $messageForward->update(['status' => 3]);
//            } elseif ($message) {
//                MessageUsers::where('message_id', $id)->update(['status' => 3]);
//                MessageForwards::where('message_id', $id)->update(['status' => 3]);
//            } else {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Message not found'
//                ], 404);
//            }
//
//            Log::info('Message deleted successfully', [
//                'message_id' => $id,
//                'user_id' => Auth::id()
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Message deleted successfully'
//            ], 200);
//
//        } catch (Exception $e) {
//            Log::error('Error deleting message', [
//                'error' => $e->getMessage(),
//                'id' => $id,
//                'user_id' => Auth::id(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Error deleting message',
//                'error' => $e->getMessage()
//            ], 500);
//        }
//    }
}
