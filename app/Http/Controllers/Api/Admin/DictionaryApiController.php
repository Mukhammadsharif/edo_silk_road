<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Role;
use Spatie\TranslationLoader\LanguageLine;

class DictionaryApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/roles",
     *     summary="Получить список ролей",
     *     description="Возвращает активные роли, отсортированные по статусу и названию",
     *     tags={"Dictionaries"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(ref="#/components/schemas/RoleListResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function roleList(): JsonResponse
    {
        try {
            $roles = Role::where('status', 1)
                ->orderBy('status')
                ->orderBy('name')
                ->paginate(100);

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $roles
            ]);

        } catch (\Throwable $e) {

            Log::error('Error retrieving roles', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving roles'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/languages",
     *     summary="Получить языковые строки",
     *     description="Возвращает список языковых ключей и переводов",
     *     tags={"Dictionaries"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(ref="#/components/schemas/LanguageListResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function languageList(): JsonResponse
    {
        try {
            $languages = LanguageLine::orderByDesc('id')
                ->paginate(100);

            return response()->json([
                'success' => true,
                'message' => 'Languages retrieved successfully',
                'data' => $languages
            ]);

        } catch (\Throwable $e) {

            Log::error('Error retrieving languages', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving languages'
            ], 500);
        }
    }
}
