<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramMiniAppToken;
use App\Models\Admin\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramProfileController extends Controller
{
    /**
     * Резолв юзера из Bearer токена.
     * Идентично паттерну в TelegramDocumentController.
     */
    private function resolveUser(Request $request): ?TelegramMiniAppToken
    {
        $bearer = $request->bearerToken();
        if (!$bearer) return null;
        $record = TelegramMiniAppToken::findValidAuth($bearer);
        if (!$record) return null;
        Auth::setUser($record->user);
        return $record;
    }

    /**
     * GET /api/telegram/profile
     *
     * Возвращает данные сотрудника для карточки в Mini App:
     *  - ФИО, email, username, телефон
     *  - Аватар (если есть)
     *  - Организация (по текущей выбранной org_unit_id)
     *  - Отдел и должность (из current work_history в текущей организации)
     *  - Список ролей
     */
    public function show(Request $request): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Подгружаем связи лениво — некоторые могут быть null
        $user->loadMissing(['userPersonal', 'currentWork.department', 'currentWork.position']);

        $personal = $user->userPersonal;
        $work     = $user->currentWork;

        // Организация по текущей выбранной
        $organization = $user->organization;

        // Роли
        $roleIds  = UserRole::where('model_id', $user->id)->pluck('role_id')->toArray();
        $roleNames = \App\Models\Admin\Role::whereIn('id', $roleIds)
            ->pluck('name')
            ->toArray();

        // Аватар — есть ли файл по convention /profile_img/img_{id}.png
        $avatarPath = public_path('profile_img/img_' . $user->id . '.png');
        $avatarUrl  = file_exists($avatarPath)
            ? url('/profile_img/img_' . $user->id . '.png')
            : null;

        // ФИО для уровней языка — берём из UserPersonalInfo если есть
        $fullName  = $personal?->fullName();
        $shortName = $personal && method_exists($personal, 'shortName') ? $personal->shortName() : null;

        // Департамент/должность — мультиязычные методы lang()
        $department = null;
        if ($work && $work->department) {
            $department = method_exists($work->department, 'lang')
                ? $work->department->lang($work->department->id)
                : ($work->department->name_uz ?? null);
        }

        $position = null;
        if ($work && $work->position) {
            $position = method_exists($work->position, 'lang')
                ? $work->position->lang($work->position->id)
                : ($work->position->name_uz ?? null);
        }

        return response()->json([
            'profile' => [
                'id'           => $user->id,
                'username'     => $user->username,
                'email'        => $user->email,
                'full_name'    => $fullName,
                'short_name'   => $shortName,
                'phone'        => $personal?->phone,
                'birthday'     => $personal?->birthday,
                'avatar_url'   => $avatarUrl,
                'organization' => $organization ? [
                    'id'         => $organization->id,
                    'name_uz'    => $organization->name_uz,
                    'name_ru'    => $organization->name_ru ?? null,
                    'name_en'    => $organization->name_en ?? null,
                    'tax_number' => $organization->tax_number ?? null,
                ] : null,
                'department'   => $department,
                'position'     => $position,
                'roles'        => $roleNames,
            ],
        ]);
    }
}
