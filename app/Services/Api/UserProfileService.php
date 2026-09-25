<?php

namespace App\Services\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\HR\HrEmpExperience;
use App\Models\Admin\HR\HrEmpOrder;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Карточка сотрудника: просмотр, аватар, пароль, личные данные.
 *
 * Логика вынесена из TelegramProfileController без изменений — мобильное
 * приложение и мини-апп работают с профилем одинаково.
 */
class UserProfileService
{

    public function show(Request $request): JsonResponse
    {

        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'User not found'], 404);

        $user->loadMissing(['userPersonal', 'currentWork.department', 'currentWork.position']);
        $personal = $user->userPersonal;
        $work     = $user->currentWork;
        $organization = $user->organization;

        // Роли (с локализованным именем если есть)
        $roleIds  = UserRole::where('model_id', $user->id)->pluck('role_id')->toArray();
        $roleRows = \App\Models\Admin\Role::whereIn('id', $roleIds)->get(['id', 'name']);
        $roleNames = $roleRows->pluck('name')->toArray();

        // Аватар
        $avatarPath = public_path('profile_img/img_' . $user->id . '.png');
        $avatarUrl  = file_exists($avatarPath)
            ? url('/profile_img/img_' . $user->id . '.png') . '?v=' . filemtime($avatarPath)
            : null;

        $fullName  = $personal?->fullName();

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

        // HR Orders (xodim buyruqlari)
        $hrEmpOrders = HrEmpOrder::where('user_id', $user->id)
            ->where('isActive', 'A')
            ->orderBy('from_date')
            ->get()
            ->map(function ($o) {
                return [
                    'id'        => $o->id,
                    'ord_num'   => $o->ord_num,
                    'ord_name'  => $o->ord_name,
                    'ord_code'  => $o->ord_code,
                    'from_date' => $o->from_date ? Carbon::parse($o->from_date)->format('d.m.Y') : null,
                    'to_date'   => $o->to_date   ? Carbon::parse($o->to_date)->format('d.m.Y')   : null,
                ];
            })->values();

        // Work experience (ish tajribasi)
        $userExp = HrEmpExperience::where('user_id', $user->id)
            ->where('isActive', 'A')
            ->orderBy('from_date', 'ASC')
            ->get()
            ->map(function ($e) {
                return [
                    'id'        => $e->id,
                    'exp_name'  => $e->exp_name,    // ish joyi
                    'post_name' => $e->post_name,   // lavozimi
                    'exp_descr' => $e->exp_descr,   // lavozim tavsifi
                    'is_org'    => $e->is_org,      // 'A' = shtat, иначе нет
                    'from_date' => $e->from_date ? Carbon::parse($e->from_date)->format('d.m.Y') : null,
                    'to_date'   => $e->to_date   ? Carbon::parse($e->to_date)->format('d.m.Y')   : null,
                ];
            })->values();

        // Work histories (для нескольких организаций)
        $workHistories = UserWorkHistory::where('user_id', $user->id)
            ->where('status', 1)
            ->with(['department', 'position'])
            ->orderByDesc('id')
            ->get();

        // Resolve organizations in a single query
        $orgIds = $workHistories->pluck('org_id')->filter()->unique()->values()->toArray();
        $orgs = $orgIds
            ? \App\Models\Admin\OrganizationUnits::whereIn('id', $orgIds)->get()->keyBy('id')
            : collect();

        $workHistories = $workHistories->map(function ($w) use ($orgs) {
            $org = $orgs->get($w->org_id);
            return [
                'id'       => $w->id,
                'org_name' => $org?->name_uz,
                'dep_name' => $w->department?->name_uz,
                'pos_name' => $w->position?->name_uz,
            ];
        })->values();

        return response()->json([
            'profile' => [
                'id'           => $user->id,
                'username'     => $user->username,
                'email'        => $user->email,
                'full_name'    => $fullName,
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
                'department'    => $department,
                'position'      => $position,
                'roles'         => $roleNames,
                'hr_orders'     => $hrEmpOrders,
                'experiences'   => $userExp,
                'work_histories'=> $workHistories,
            ],
        ]);
    }

    /**
     * POST /api/telegram/profile/avatar  (multipart: avatar)
     */

    public function updateAvatar(Request $request): JsonResponse
    {

        $request->validate([
            'avatar' => 'required|file|image|max:5120', // 5 MB
        ]);

        $user = Auth::user();
        $file = $request->file('avatar');

        $dir = public_path('profile_img');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $target = $dir . '/img_' . $user->id . '.png';
        try {
            // Saving as .png (то же что в web flow)
            $file->move($dir, 'img_' . $user->id . '.png');
        } catch (\Throwable $e) {
            Log::error('TG avatar upload failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'upload_failed', 'message' => 'Yuklash xatosi'], 500);
        }

        return response()->json([
            'success'    => true,
            'avatar_url' => url('/profile_img/img_' . $user->id . '.png') . '?v=' . time(),
        ]);
    }

    /**
     * DELETE /api/telegram/profile/avatar
     */

    public function deleteAvatar(Request $request): JsonResponse
    {

        $user = Auth::user();
        $target = public_path('profile_img/img_' . $user->id . '.png');
        if (file_exists($target)) {
            @unlink($target);
        }
        return response()->json(['success' => true]);
    }

    /**
     * POST /api/telegram/profile/password
     */

    public function updatePassword(Request $request): JsonResponse
    {

        $request->validate([
            'current_password'      => 'required|string',
            'new_password'          => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'error'   => 'invalid_password',
                'message' => 'Joriy parol noto\'g\'ri',
            ], 422);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Parol o'zgartirildi",
        ]);
    }

    /**
     * POST /api/telegram/profile/info  — update F.I.SH.
     */

    public function updatePersonalInfo(Request $request): JsonResponse
    {

        $request->validate([
            'l_name' => 'required|string|max:100',
            'f_name' => 'required|string|max:100',
            'm_name' => 'nullable|string|max:100',
        ]);

        $model = UserPersonalInfo::where('user_id', Auth::id())->first();
        if (!$model) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $model->update([
            'l_name' => $request->input('l_name'),
            'f_name' => $request->input('f_name'),
            'm_name' => $request->input('m_name'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Ma'lumotlar yangilandi",
        ]);
    }
}
