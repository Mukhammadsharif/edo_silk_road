<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use Illuminate\Http\Request;
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

    /**
     * @OA\Get(
     *     path="/api/v1/organization-units",
     *     summary="Справочник организаций",
     *     description="Возвращает организации со всеми полями. Поддерживает фильтры по статусу, родителю, уровню и поиск по названию, коду или ИНН.",
     *     tags={"Dictionaries"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Фильтр по статусу (1 — активные). По умолчанию отдаются все.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         required=false,
     *         description="Только дочерние указанной организации. 0 — корневые.",
     *         @OA\Schema(type="integer", example=0)
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         required=false,
     *         description="Уровень вложенности",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Поиск по названию (uz/kr/ru/en), коду или ИНН",
     *         @OA\Schema(type="string", example="vazirlik")
     *     ),
     *     @OA\Parameter(
     *         name="with_parent",
     *         in="query",
     *         required=false,
     *         description="Добавить объект родительской организации",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Записей на странице (1–500, по умолчанию 100)",
     *         @OA\Schema(type="integer", example=100)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(ref="#/components/schemas/OrganizationUnitListResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Неавторизован"),
     *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
     * )
     */
    public function organizationUnitList(Request $request): JsonResponse
    {
        try {
            $query = OrganizationUnits::query();

            // Статус не фильтруем по умолчанию: справочник должен показывать
            // и отключённые организации, иначе по ним нельзя разобрать
            // исторические документы
            if ($request->filled('status')) {
                $query->where('status', (int) $request->input('status'));
            }

            // parent_id = 0 означает корневую организацию, поэтому filled() тут
            // не годится: он считает "0" пустым значением
            if ($request->has('parent_id') && $request->input('parent_id') !== '') {
                $query->where('parent_id', (int) $request->input('parent_id'));
            }

            if ($request->filled('level')) {
                $query->where('level', (int) $request->input('level'));
            }

            if ($search = trim((string) $request->input('search'))) {
                $query->where(function ($q) use ($search) {
                    foreach (['name_uz', 'name_kr', 'name_ru', 'name_en', 'code', 'tax_number'] as $field) {
                        $q->orWhere($field, 'ilike', '%' . $search . '%');
                    }
                });
            }

            if ($request->boolean('with_parent')) {
                $query->with('parent');
            }

            $perPage = min(max((int) $request->input('per_page', 100), 1), 500);

            $units = $query->orderBy('level')
                ->orderBy('parent_id')
                ->orderBy('name_uz')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Organization units retrieved successfully',
                'data' => $units
            ]);

        } catch (\Throwable $e) {

            Log::error('Error retrieving organization units', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving organization units'
            ], 500);
        }
    }
}
