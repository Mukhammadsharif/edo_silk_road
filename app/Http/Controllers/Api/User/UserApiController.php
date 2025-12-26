<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Получить список пользователей",
     *     description="Возвращает список пользователей с персональными данными, текущим местом работы и ролями",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество записей на странице",
     *         required=false,
     *         @OA\Schema(type="integer", example=25)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(ref="#/components/schemas/UserListResponse")
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
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 25);

            $users = User::with([
                'userPersonal',
                'currentWork',
                'userRoles'
            ])
                ->orderByDesc('id')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Error retrieving users', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving users'
            ], 500);
        }
    }
}
