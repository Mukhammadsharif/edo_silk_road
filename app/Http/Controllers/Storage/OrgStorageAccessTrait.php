<?php

namespace App\Http\Controllers\Storage;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Support\Facades\Auth;

/**
 * Доступ к хранилищу организации:
 *
 *  READ (просмотр + скачивание):
 *      - Любой активный сотрудник этой организации (UserWorkHistory.status=1)
 *      - Super_Admin (везде)
 *
 *  WRITE (создание/изменение/удаление):
 *      - Super_Admin (везде)
 *      - Director / IT_Admin (только своя org)
 *
 *  Доступ к корзине = WRITE.
 */
trait OrgStorageAccessTrait
{
    /**
     * Право на просмотр / скачивание файлов хранилища этой org.
     */
    protected function canReadOrgStorage(int $orgUnitId): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $roles = $this->authActiveUserRoleNames();

        // Super_Admin — всегда
        if (in_array('Super_Admin', $roles, true)) {
            return true;
        }

        // Активный сотрудник этой организации
        return UserWorkHistory::where('user_id', $user->id)
            ->where('org_id', $orgUnitId)
            ->where('status', 1)
            ->exists();
    }

    /**
     * Право на создание / изменение / удаление + корзина.
     */
    protected function canWriteOrgStorage(int $orgUnitId): bool
    {
        $roles = $this->authActiveUserRoleNames();

        if (in_array('Super_Admin', $roles, true)) {
            return true;
        }

        if (in_array('Director', $roles, true) || in_array('IT_Admin', $roles, true)) {
            return (int) Auth::user()->org_unit_id === $orgUnitId;
        }

        return false;
    }

    /**
     * Может ли пользователь видеть селектор организаций (Super_Admin).
     */
    protected function canChooseOrg(): bool
    {
        return in_array('Super_Admin', $this->authActiveUserRoleNames(), true);
    }

    /**
     * Резолвит org_unit_id для текущего запроса.
     * Super_Admin может явно задать ?org=N, остальные — всегда своя org.
     */
    protected function resolveOrgUnitId(?int $requestedOrgId): int
    {
        if ($this->canChooseOrg() && $requestedOrgId !== null) {
            return $requestedOrgId;
        }
        return (int) Auth::user()->org_unit_id;
    }

    /**
     * Сокращение: 403 redirect назад с сообщением.
     */
    protected function denyStorage()
    {
        return back()->with(
            'error',
            __('field.access_denied') ?? 'Нет доступа к хранилищу организации'
        );
    }
}
