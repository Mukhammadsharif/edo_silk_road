<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Admin\HR\HrEmpExperience;
use App\Models\Admin\HR\HrEmpOrder;
use App\Models\Admin\UserAuthHistory;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfileApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     summary="Получить профиль текущего пользователя",
     *     description="Возвращает полную информацию о профиле аутентифицированного пользователя",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="active_roles", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="hr_emp_orders", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="work_histories", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="auth_histories", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="user_experience", type="array", @OA\Items(type="object")),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     nullable=true,
     *                     example="http://localhost:8082/profile_img/img_1.png",
     *                     description="URL фотографии профиля пользователя"
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
     *         response=404,
     *         description="Пользователь не найден"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function profile(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $user = User::with(['userPersonal', 'currentWork', 'organization', 'currentSubscription'])->findOrFail($userId);

            $activeRoles = $this->authActiveUserRoleNames();

            $hrEmpOrders = HrEmpOrder::where('user_id', $userId)
                ->where('isActive', 'A')
                ->orderBy('from_date')
                ->get();

            $workHistories = UserWorkHistory::where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->get();

            $authHistories = UserAuthHistory::where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->limit(100)
                ->get();

            $userExp = HrEmpExperience::where('user_id', $userId)
                ->where('isActive', 'A')
                ->orderBy('from_date', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => $user,
                    'active_roles' => $activeRoles,
                    'hr_emp_orders' => $hrEmpOrders,
                    'work_histories' => $workHistories,
                    'auth_histories' => $authHistories,
                    'user_experience' => $userExp,
                    'image' => $user->image()??null,
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error retrieving profile', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving profile'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile/personal",
     *     summary="Обновить персональную информацию",
     *     description="Обновляет персональные данные пользователя (ФИО)",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"l_name", "f_name"},
     *             @OA\Property(property="l_name", type="string", maxLength=100, example="Иванов"),
     *             @OA\Property(property="f_name", type="string", maxLength=100, example="Иван"),
     *             @OA\Property(property="m_name", type="string", maxLength=100, example="Иванович")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Информация успешно обновлена",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User info updated successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
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
    public function personalUpdate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'l_name' => 'required|max:100',
                'f_name' => 'required|max:100',
                'm_name' => 'nullable|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $model = UserPersonalInfo::where('user_id', Auth::id())->firstOrFail();

            $model->update([
                'l_name' => $request->l_name,
                'f_name' => $request->f_name,
                'm_name' => $request->m_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User info updated successfully'
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error updating personal info', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating personal info'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile/password",
     *     summary="Обновить пароль",
     *     description="Изменяет пароль текущего пользователя",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password", "password_confirmation"},
     *             @OA\Property(property="password", type="string", format="password", minLength=6, example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", minLength=6, example="newpassword123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Пароль успешно обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
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
    public function passwordUpdate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail(Auth::id());

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error updating password', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating password'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/image",
     *     summary="Загрузить фото профиля",
     *     description="Загружает или обновляет фотографию профиля пользователя",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"profile_img"},
     *                 @OA\Property(
     *                     property="profile_img",
     *                     type="string",
     *                     format="binary",
     *                     description="Изображение профиля (jpeg, png, jpg, gif, svg, максимум 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Изображение успешно загружено",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile image updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="image_name", type="string", example="img_123.png")
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
    public function updateImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $imgName = 'img_' . $userId . '.png';

            $request->profile_img->move(public_path('profile_img/'), $imgName);

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'data' => [
                    'image_name' => $imgName
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error updating profile image', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating profile image'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/profile/image",
     *     summary="Удалить фото профиля",
     *     description="Удаляет фотографию профиля текущего пользователя",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Изображение успешно удалено",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile image deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Изображение не найдено"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function deleteProfileImage(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $imageName = 'img_' . $userId . '.png';
            $imagePath = public_path('profile_img/' . $imageName);

            if (File::exists($imagePath)) {
                File::delete($imagePath);

                return response()->json([
                    'success' => true,
                    'message' => 'Profile image deleted successfully'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Profile image not found'
            ], 404);

        } catch (\Throwable $e) {
            Log::error('Error deleting profile image', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting profile image'
            ], 500);
        }
    }
}
