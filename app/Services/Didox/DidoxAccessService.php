<?php

namespace App\Services\Didox;

use App\Models\Admin\MenuList;
use App\Models\Admin\Role;
use App\Models\Admin\RoleMenu;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Доступ к модулю Didox через роли (меню в сайдбаре — статические, показываются
 * по правам). Без отдельной таблицы и без миграций.
 *
 * Две роли-флага:
 *   - «Didox» — ответственные сотрудники: видят всю категорию «Внешний ЭДО»
 *     (Входящие / Исходящие / Согласование). Выдаётся при включении Didox в
 *     подписке организации.
 *   - «Didox_Approver» — согласующие: видят категорию только с пунктом
 *     «Согласование». Выдаётся при отправке документа на внутреннее согласование.
 *
 * При выдаче/снятии роли сбрасывается кэш меню, чтобы раздел появился сразу.
 */
class DidoxAccessService
{
    /** Полная роль Didox (ответственные). Лениво создаётся, без меню в БД. */
    public function didoxRole(): Role
    {
        $role = Role::findOrCreate(config('didox.role_name', 'Didox'), 'web');

        // Старый динамический подход (меню EXT_DOC/DIDOX в menu_lists) заменён
        // статическим блоком сайдбара — чистим, чтобы не дублировалось.
        $this->cleanupLegacyDynamicMenus();

        return $role;
    }

    /** Роль согласующего Didox (только «Согласование»). */
    public function approverRole(): Role
    {
        return Role::findOrCreate(config('didox.approver_role_name', 'Didox_Approver'), 'web');
    }

    /**
     * Удаляет старые динамические меню Didox (EXT_DOC / DIDOX) из role_menus и
     * menu_lists — теперь раздел рисуется статически в сайдбаре по правам.
     */
    private function cleanupLegacyDynamicMenus(): void
    {
        $menuIds = MenuList::whereIn('code', ['EXT_DOC', 'DIDOX'])->pluck('id')->all();
        if (! $menuIds) {
            return;
        }
        RoleMenu::whereIn('menu_id', $menuIds)->delete();
        MenuList::whereIn('id', $menuIds)->delete();
    }

    /**
     * Выдать роль согласующего Didox списку пользователей, если её ещё нет.
     *
     * @param array<int|string> $userIds
     */
    public function grantToUsers(array $userIds): void
    {
        $userIds = array_values(array_filter(array_unique(array_map('intval', $userIds))));
        if (! $userIds) {
            return;
        }

        $role = $this->approverRole();

        foreach ($userIds as $uid) {
            $user = User::find($uid);
            if (! $user || $user->hasRole($role)) {
                continue;
            }
            $user->assignRole($role);
            Cache::forget("user_{$uid}_menus");
        }
    }

    /**
     * Активные user_id сотрудников организации (по трудовой истории).
     *
     * @return int[]
     */
    private function orgActiveUserIds(int $orgUnitId): array
    {
        return \App\Models\Admin\UserWorkHistory::where('org_id', $orgUnitId)
            ->where('status', 1)
            ->whereHas('user', function ($q) {
                $q->where('status', 1);
            })
            ->pluck('user_id')->map(fn ($v) => (int) $v)->unique()->values()->all();
    }

    /**
     * user_id сотрудников организации, у которых сейчас есть роль Didox
     * (ответственные) — для предвыбора в мультиселекте.
     *
     * @return int[]
     */
    public function responsibleUserIds(int $orgUnitId): array
    {
        $role = Role::where('name', config('didox.role_name', 'Didox'))->first();
        if (! $role) {
            return [];
        }

        // Активные согласующие получают роль Didox по задаче согласования —
        // их не показываем как «ответственных» в мультиселекте подписки.
        $activeApprovers = \App\Models\Didox\DidoxDocumentApprover::where('status', \App\Models\Didox\DidoxDocumentApprover::PENDING)
            ->whereHas('document', function ($q) use ($orgUnitId) {
                $q->where('org_unit_id', $orgUnitId);
            })
            ->pluck('user_id')->map(fn ($v) => (int) $v)->unique()->all();

        $out = [];
        foreach ($this->orgActiveUserIds($orgUnitId) as $uid) {
            if (in_array($uid, $activeApprovers, true)) {
                continue;
            }
            $user = User::find($uid);
            if ($user && $user->hasRole($role)) {
                $out[] = $uid;
            }
        }
        return $out;
    }

    /**
     * Включён ли Didox для организации — есть ли хотя бы один ответственный.
     */
    public function isEnabledForOrg(int $orgUnitId): bool
    {
        return count($this->responsibleUserIds($orgUnitId)) > 0;
    }

    /**
     * Синхронизация доступа к Didox для организации (тумблер в подписке).
     * Ответственные выбираются как КОНКРЕТНЫЕ сотрудники организации (2-3),
     * а не роль. Роль Didox выдаётся выбранным и снимается у остальных
     * сотрудников организации.
     *
     * @param array<int|string> $userIds выбранные ответственные сотрудники
     */
    public function syncForOrgUsers(int $orgUnitId, bool $hasAccess, array $userIds): void
    {
        $role = $this->didoxRole();

        $selected = $hasAccess
            ? array_values(array_filter(array_unique(array_map('intval', $userIds))))
            : [];

        // Активные согласующие Didox — у них доступ по задаче согласования,
        // роль не снимаем при пересохранении подписки.
        $activeApprovers = \App\Models\Didox\DidoxDocumentApprover::where('status', \App\Models\Didox\DidoxDocumentApprover::PENDING)
            ->whereHas('document', function ($q) use ($orgUnitId) {
                $q->where('org_unit_id', $orgUnitId);
            })
            ->pluck('user_id')->map(fn ($v) => (int) $v)->unique()->all();

        foreach ($this->orgActiveUserIds($orgUnitId) as $uid) {
            $user = User::find($uid);
            if (! $user) {
                continue;
            }

            $should = in_array($uid, $selected, true);
            $has    = $user->hasRole($role);

            if ($should && ! $has) {
                $user->assignRole($role);
                Cache::forget("user_{$uid}_menus");
            } elseif (! $should && $has && ! in_array($uid, $activeApprovers, true)) {
                $user->removeRole($role);
                Cache::forget("user_{$uid}_menus");
            }
        }
    }
}
