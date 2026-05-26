<?php

namespace App\Services;

use App\Models\Admin\UserWorkHistory;
use App\Models\Storage\OrgStorageFolder;
use App\Models\Storage\OrgStorageFolderPermission;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Логика прав доступа к папкам:
 *
 *  Привилегированные роли (минуют ACL):
 *      - Super_Admin                     — везде
 *      - Director / IT_Admin             — своя org
 *
 *  Обычные сотрудники:
 *      - Доступ к папке = идём вверх по дереву предков, ищем первую папку
 *        с явным разрешением для этого юзера. Если по дороге встретился
 *        explicit 'deny' — доступ запрещён.
 *      - Промежуточные папки (без прав, но содержащие папки-с-правами в поддереве)
 *        видны как "проходные" — нужно чтобы юзер мог открыть глубоко-вложенную папку
 *        через breadcrumb / прямую ссылку.
 *
 *  Сервис вычисляет права для ВСЕХ папок организации одним запросом,
 *  чтобы не делать N+1 при рендеринге дерева.
 */
class OrgStoragePermissionService
{
    /**
     * Привилегирован ли user в этой org (видит всё без ACL).
     * Принимает массив имён ролей.
     */
    public function isPrivileged(User $user, int $orgUnitId, array $roleNames): bool
    {
        if (in_array('Super_Admin', $roleNames, true)) {
            return true;
        }
        if (in_array('Director', $roleNames, true) || in_array('IT_Admin', $roleNames, true)) {
            return (int) $user->org_unit_id === $orgUnitId;
        }
        return false;
    }

    /**
     * Может ли пользователь быть назначен в качестве цели права доступа на папку.
     * Защита: нельзя дать сотруднику orgA право на папку orgB — даже Super_Admin.
     */
    public function userBelongsToOrg(int $userId, int $orgUnitId): bool
    {
        return UserWorkHistory::where('user_id', $userId)
            ->where('org_id', $orgUnitId)
            ->where('status', 1)
            ->exists();
    }

    /**
     * Вычислить набор папок видимых юзеру в данной организации.
     *
     * Возвращает структуру:
     *   [
     *     'visible' => [folder_id => true, ...],  // эти папки юзер видит в дереве
     *     'direct'  => [folder_id => true, ...],  // папки с прямым доступом (для UI-индикатора)
     *     'passthrough' => [folder_id => true, ...], // "проходные" (виден сам узел, но не содержимое)
     *   ]
     *
     * Для привилегированного юзера все папки идут в 'direct'.
     */
    public function resolveVisibility(
        User $user,
        int $orgUnitId,
        array $roleNames,
        Collection $allFolders   // плоский список папок org с полями id, parent_id
    ): array {
        // Привилегированные — видят всё
        if ($this->isPrivileged($user, $orgUnitId, $roleNames)) {
            $direct = [];
            foreach ($allFolders as $f) {
                $direct[$f->id] = true;
            }
            return [
                'visible'     => $direct,
                'direct'      => $direct,
                'passthrough' => [],
            ];
        }

        // Подгружаем все ACL-записи юзера в этой org
        $permissions = OrgStorageFolderPermission::where('user_id', $user->id)
            ->where('org_unit_id', $orgUnitId)
            ->get(['folder_id', 'mode']);

        if ($permissions->isEmpty()) {
            return [
                'visible'     => [],
                'direct'      => [],
                'passthrough' => [],
            ];
        }

        // Карта folder_id → mode ('allow' | 'deny')
        $permMap = [];
        foreach ($permissions as $p) {
            $permMap[$p->folder_id] = $p->mode;
        }

        // Индекс parent_id для обхода вверх
        $byId = [];
        foreach ($allFolders as $f) {
            $byId[$f->id] = $f;
        }

        // Для каждой папки определяем эффективный доступ.
        // Идём вверх по предкам. Если встретили 'deny' раньше 'allow' — нет доступа.
        // Если встретили 'allow' — есть доступ.
        // Если ничего не нашли — нет доступа.
        $directlyAccessible = []; // [folder_id => true]

        foreach ($allFolders as $f) {
            $current = $f;
            $verdict = null; // null | 'allow' | 'deny'

            // Защита от циклов (теоретически не должны быть, но мало ли)
            $visited = [];
            while ($current && !isset($visited[$current->id])) {
                $visited[$current->id] = true;
                if (isset($permMap[$current->id])) {
                    $verdict = $permMap[$current->id];
                    break;
                }
                $current = $current->parent_id ? ($byId[$current->parent_id] ?? null) : null;
            }

            if ($verdict === 'allow') {
                $directlyAccessible[$f->id] = true;
            }
        }

        // Теперь проходные узлы: предки папок из $directlyAccessible нужно показать
        // чтобы юзер мог дойти через дерево/breadcrumb.
        $passthrough = [];
        foreach (array_keys($directlyAccessible) as $accessibleId) {
            $current = $byId[$accessibleId] ?? null;
            while ($current && $current->parent_id) {
                $parentId = $current->parent_id;
                if (!isset($directlyAccessible[$parentId])) {
                    $passthrough[$parentId] = true;
                }
                $current = $byId[$parentId] ?? null;
            }
        }

        // Direct ACL-папки (для UI-индикатора)
        $direct = [];
        foreach ($permMap as $fid => $mode) {
            if ($mode === 'allow' && isset($byId[$fid])) {
                $direct[$fid] = true;
            }
        }

        $visible = $directlyAccessible + $passthrough;

        return [
            'visible'     => $visible,
            'direct'      => $direct,
            'passthrough' => $passthrough,
        ];
    }

    /**
     * Может ли пользователь читать конкретную папку (по её id).
     * Используется для авторизации в контроллере при доступе через прямой URL.
     *
     * "Читать" = видеть содержимое (файлы и подпапки). Промежуточные passthrough
     * папки сами видны в дереве, но их прямое содержимое тоже разрешено к показу —
     * содержимое = только разрешённые потомки.
     */
    public function canReadFolderContents(
        User $user,
        OrgStorageFolder $folder,
        array $roleNames
    ): bool {
        if ($this->isPrivileged($user, $folder->org_unit_id, $roleNames)) {
            return true;
        }

        // Идём вверх по предкам и ищем allow без перекрытия deny.
        $current = $folder;
        $visited = [];
        while ($current && !isset($visited[$current->id])) {
            $visited[$current->id] = true;
            $perm = OrgStorageFolderPermission::where('user_id', $user->id)
                ->where('folder_id', $current->id)
                ->first();
            if ($perm) {
                return $perm->mode === 'allow';
            }
            $current = $current->parent;
        }

        // Возможно это passthrough-узел — у юзера нет права на эту папку,
        // но есть на потомка. В таком случае позволим открыть папку
        // (содержимое будет отфильтровано по visible-маске).
        return $this->folderHasAccessibleDescendant($user, $folder);
    }

    /**
     * Может ли пользователь читать конкретный файл?
     * Файл наследует права содержащей папки.
     * Файлы в корне (folder_id = null) видны только привилегированным.
     */
    public function canReadFile(
        User $user,
        int $orgUnitId,
        ?int $folderId,
        array $roleNames
    ): bool {
        if ($this->isPrivileged($user, $orgUnitId, $roleNames)) {
            return true;
        }

        // Файл в корне — обычным юзерам нет
        if ($folderId === null) {
            return false;
        }

        $folder = OrgStorageFolder::find($folderId);
        if (!$folder) return false;

        return $this->canReadFolderContents($user, $folder, $roleNames);
    }

    /**
     * Имеет ли юзер хотя бы одного потомка с прямым allow.
     * Используется чтобы решить — показывать ли passthrough-узел при прямом URL.
     */
    private function folderHasAccessibleDescendant(User $user, OrgStorageFolder $folder): bool
    {
        $prefix = rtrim($folder->path, '/') . '/' . $folder->name;
        $descendantIds = OrgStorageFolder::where('org_unit_id', $folder->org_unit_id)
            ->where('path', 'LIKE', $prefix . '%')
            ->pluck('id')
            ->toArray();

        if (empty($descendantIds)) return false;

        return OrgStorageFolderPermission::where('user_id', $user->id)
            ->whereIn('folder_id', $descendantIds)
            ->where('mode', 'allow')
            ->exists();
    }
}
