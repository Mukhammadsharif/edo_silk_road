<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserWorkHistory;
use App\Models\Storage\OrgStorageFile;
use App\Models\Storage\OrgStorageFolder;
use App\Models\Storage\OrgStorageFolderPermission;
use App\Models\User;
use App\Services\OrgStoragePermissionService;
use App\Services\OrgStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class OrgStorageController extends Controller
{
    use OrgStorageAccessTrait;

    public function __construct(
        private OrgStorageService $service,
        private OrgStoragePermissionService $permissionService
    ) {
    }

    // ============================================================
    //  ПРОСМОТР
    // ============================================================

    public function index(Request $request)
    {
        $orgUnitId = $this->resolveOrgUnitId(
            $request->query('org') !== null ? (int) $request->query('org') : null
        );

        if (!$this->canReadOrgStorage($orgUnitId)) {
            return $this->denyStorage();
        }

        // У корня нет ACL — обычные сотрудники видят только видимые папки и НЕ видят
        // файлы в корне (привилегированные видят всё).
        return $this->renderFolderView($orgUnitId, null);
    }

    public function showFolder(Request $request, int $id)
    {
        $folder = OrgStorageFolder::findOrFail($id);

        if (!$this->canReadOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        // Проверка ACL: видит ли пользователь эту папку?
        $roles = $this->authActiveUserRoleNames();
        $user  = Auth::user();
        if (!$this->permissionService->canReadFolderContents($user, $folder, $roles)) {
            return $this->denyStorage();
        }

        return $this->renderFolderView($folder->org_unit_id, $folder);
    }

    private function renderFolderView(int $orgUnitId, ?OrgStorageFolder $folder)
    {
        $activeFolderId = $folder?->id;
        $user  = Auth::user();
        $roles = $this->authActiveUserRoleNames();

        // Все папки org одним запросом
        $allFolders = OrgStorageFolder::where('org_unit_id', $orgUnitId)
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'path']);

        // ACL: какие папки видны юзеру?
        $visibility = $this->permissionService->resolveVisibility(
            $user, $orgUnitId, $roles, $allFolders
        );
        $visibleMap     = $visibility['visible'];      // [id => true]
        $directMap      = $visibility['direct'];       // [id => true]
        $passthroughMap = $visibility['passthrough'];  // [id => true]
        $isPrivileged   = $this->permissionService->isPrivileged($user, $orgUnitId, $roles);

        // Фильтруем дерево
        $visibleFolders = $allFolders->filter(function ($f) use ($visibleMap) {
            return isset($visibleMap[$f->id]);
        })->values();

        // Контент текущей папки.
        // ВАЖНО: если эта папка — passthrough (юзер не имеет прямого доступа,
        // но видит её ради потомков), то файлы не показываем,
        // подпапки — только видимые
        $isPassthrough = $activeFolderId && isset($passthroughMap[$activeFolderId]);

        // Файлы в текущей папке
        if ($isPrivileged) {
            $files = OrgStorageFile::where('org_unit_id', $orgUnitId)
                ->where('folder_id', $activeFolderId)
                ->orderBy('name')
                ->get();
        } elseif ($folder && !$isPassthrough && isset($visibleMap[$folder->id])) {
            // Юзер имеет реальный доступ к содержимому этой папки
            $files = OrgStorageFile::where('org_unit_id', $orgUnitId)
                ->where('folder_id', $activeFolderId)
                ->orderBy('name')
                ->get();
        } else {
            // Корень для обычного юзера, либо passthrough — файлы не показываем
            $files = collect();
        }

        // Подпапки текущей папки (только видимые)
        $childFolders = $visibleFolders->where('parent_id', $activeFolderId)->values();

        $breadcrumb = $this->buildBreadcrumb($folder);

        $availableOrgs = $this->canChooseOrg()
            ? OrganizationUnits::where('status', 1)->orderBy('name_uz')->get()
            : collect();

        $organization = OrganizationUnits::find($orgUnitId);
        $canWrite     = $this->canWriteOrgStorage($orgUnitId);

        return view('storage.index', compact(
            'folder', 'visibleFolders', 'childFolders', 'files', 'breadcrumb',
            'availableOrgs', 'organization', 'orgUnitId', 'canWrite',
            'activeFolderId', 'directMap', 'passthroughMap', 'isPrivileged', 'isPassthrough'
        ));
    }

    private function buildBreadcrumb(?OrgStorageFolder $folder): array
    {
        if (!$folder) return [];

        $crumbs = [];
        $current = $folder;
        while ($current) {
            array_unshift($crumbs, [
                'id'   => $current->id,
                'name' => $current->name,
            ]);
            $current = $current->parent;
        }
        return $crumbs;
    }

    // ============================================================
    //  CRUD ПАПОК
    // ============================================================

    public function createFolder(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:org_storage_folders,id',
            'org'       => 'nullable|integer',
        ]);

        $orgUnitId = $this->resolveOrgUnitId($request->input('org') ? (int) $request->input('org') : null);

        if (!$this->canWriteOrgStorage($orgUnitId)) {
            return $this->denyStorage();
        }

        $parentId = $request->input('parent_id') ? (int) $request->input('parent_id') : null;
        if ($parentId !== null) {
            $parent = OrgStorageFolder::find($parentId);
            if (!$parent || (int) $parent->org_unit_id !== $orgUnitId) {
                return back()->with('error', 'Родительская папка не найдена');
            }
        }

        try {
            $folder = $this->service->createFolder(
                $orgUnitId, $parentId, $request->input('name'), Auth::id()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('createFolder failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Ошибка создания папки');
        }

        $redirect = $parentId
            ? route('storage.folder.show', $parentId)
            : route('storage.index');
        return redirect($redirect)->with('success', 'Папка создана');
    }

    public function renameFolder(Request $request, int $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $folder = OrgStorageFolder::findOrFail($id);
        if (!$this->canWriteOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        try {
            $this->service->renameFolder($folder, $request->input('name'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Папка переименована');
    }

    public function deleteFolder(Request $request, int $id)
    {
        $folder = OrgStorageFolder::findOrFail($id);
        if (!$this->canWriteOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        $parentId = $folder->parent_id;
        $this->service->softDeleteFolder($folder);

        $redirect = $parentId
            ? route('storage.folder.show', $parentId)
            : route('storage.index');
        return redirect($redirect)->with('success', 'Папка перемещена в корзину');
    }

    // ============================================================
    //  CRUD ФАЙЛОВ
    // ============================================================

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file'      => 'required|file',
            'folder_id' => 'nullable|integer',
            'org'       => 'nullable|integer',
        ]);

        $orgUnitId = $this->resolveOrgUnitId($request->input('org') ? (int) $request->input('org') : null);

        if (!$this->canWriteOrgStorage($orgUnitId)) {
            return $this->denyStorage();
        }

        $folderId = $request->input('folder_id') ? (int) $request->input('folder_id') : null;

        try {
            $this->service->uploadFile($request->file('file'), $orgUnitId, $folderId, Auth::id());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('uploadFile failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Ошибка загрузки файла');
        }

        $redirect = $folderId
            ? route('storage.folder.show', $folderId)
            : route('storage.index');
        return redirect($redirect)->with('success', 'Файл загружен');
    }

    public function renameFile(Request $request, int $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $file = OrgStorageFile::findOrFail($id);
        if (!$this->canWriteOrgStorage($file->org_unit_id)) {
            return $this->denyStorage();
        }

        try {
            $this->service->renameFile($file, $request->input('name'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Файл переименован');
    }

    public function deleteFile(Request $request, int $id)
    {
        $file = OrgStorageFile::findOrFail($id);
        if (!$this->canWriteOrgStorage($file->org_unit_id)) {
            return $this->denyStorage();
        }

        $folderId = $file->folder_id;
        $this->service->softDeleteFile($file);

        $redirect = $folderId
            ? route('storage.folder.show', $folderId)
            : route('storage.index');
        return redirect($redirect)->with('success', 'Файл перемещён в корзину');
    }

    public function downloadFile(int $id)
    {
        $file = OrgStorageFile::findOrFail($id);

        // Базовая проверка: юзер вообще из этой org / Super_Admin
        if (!$this->canReadOrgStorage($file->org_unit_id)) {
            return $this->denyStorage();
        }

        // ACL: если не привилегированный — проверяем доступ к папке файла
        $user  = Auth::user();
        $roles = $this->authActiveUserRoleNames();
        if (!$this->permissionService->canReadFile($user, $file->org_unit_id, $file->folder_id, $roles)) {
            return $this->denyStorage();
        }

        $response = $this->service->downloadResponse($file);
        if ($response === null) {
            return back()->with('error', 'Файл не найден на FTP');
        }
        return $response;
    }

    public function previewFile(int $id)
    {
        $file = OrgStorageFile::findOrFail($id);
        if (!$this->canReadOrgStorage($file->org_unit_id)) {
            return $this->denyStorage();
        }

        $user  = Auth::user();
        $roles = $this->authActiveUserRoleNames();
        if (!$this->permissionService->canReadFile($user, $file->org_unit_id, $file->folder_id, $roles)) {
            return $this->denyStorage();
        }

        if (!$file->isPreviewable()) {
            $response = $this->service->downloadResponse($file);
            return $response ?: back()->with('error', 'Файл не найден');
        }

        $content = $this->service->readFile($file);
        if ($content === null) {
            return back()->with('error', 'Файл не найден на FTP');
        }

        $contentType = match (strtolower($file->extension)) {
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return Response::make($content, 200, [
            'Content-Type'        => $contentType,
            'Content-Disposition' => 'inline; filename="' . addslashes($file->name) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ============================================================
    //  ПРАВА ДОСТУПА К ПАПКЕ
    // ============================================================

    /**
     * GET /storage/folder/{id}/permissions
     * Страница управления правами для папки.
     */
    public function showPermissions(int $id)
    {
        $folder = OrgStorageFolder::findOrFail($id);

        // Право управлять правами = write для этой org
        if (!$this->canWriteOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        // Текущие записи прав
        $permissions = OrgStorageFolderPermission::where('folder_id', $folder->id)
            ->with('user.userPersonal')
            ->get();

        // Все сотрудники этой org для select (можно искать через AJAX, см. searchOrgUsers)
        $orgUserIds = UserWorkHistory::where('org_id', $folder->org_unit_id)
            ->where('status', 1)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->toArray();

        $orgUsers = User::whereIn('id', $orgUserIds)
            ->where('status', 1)
            ->with('userPersonal')
            ->orderBy('username')
            ->get();

        $organization = OrganizationUnits::find($folder->org_unit_id);

        return view('storage.permissions', compact(
            'folder', 'permissions', 'orgUsers', 'organization'
        ));
    }

    /**
     * POST /storage/folder/{id}/permissions
     * Назначить право пользователю.
     */
    public function grantPermission(Request $request, int $id)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'mode'    => 'required|in:allow,deny',
        ]);

        $folder = OrgStorageFolder::findOrFail($id);
        if (!$this->canWriteOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        $userId = (int) $request->input('user_id');
        $mode   = $request->input('mode');

        // КРИТИЧНО: пользователь должен принадлежать к этой org.
        // Даже Super_Admin не может дать сотруднику orgA право на папку orgB.
        if (!$this->permissionService->userBelongsToOrg($userId, $folder->org_unit_id)) {
            return back()->with(
                'error',
                'Этот пользователь не является сотрудником данной организации'
            );
        }

        // Запрещаем назначать привилегированных юзеров — у них и так есть доступ
        $targetUser = User::find($userId);
        if (!$targetUser) {
            return back()->with('error', 'Пользователь не найден');
        }

        try {
            OrgStorageFolderPermission::updateOrCreate(
                [
                    'folder_id' => $folder->id,
                    'user_id'   => $userId,
                ],
                [
                    'org_unit_id' => $folder->org_unit_id,
                    'mode'        => $mode,
                    'granted_by'  => Auth::id(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('grantPermission failed', [
                'folder_id' => $folder->id,
                'user_id'   => $userId,
                'error'     => $e->getMessage(),
            ]);
            return back()->with('error', 'Не удалось сохранить право');
        }

        $msg = $mode === 'allow' ? 'Доступ предоставлен' : 'Доступ запрещён';
        return back()->with('success', $msg);
    }

    /**
     * DELETE /storage/folder/{id}/permissions/{permission_id}
     * Отозвать ранее назначенное право.
     */
    public function revokePermission(int $id, int $permissionId)
    {
        $folder = OrgStorageFolder::findOrFail($id);
        if (!$this->canWriteOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        $perm = OrgStorageFolderPermission::where('id', $permissionId)
            ->where('folder_id', $folder->id)
            ->first();

        if (!$perm) {
            return back()->with('error', 'Запись не найдена');
        }

        $perm->delete();
        return back()->with('success', 'Право отозвано');
    }

    // ============================================================
    //  КОРЗИНА
    // ============================================================

    public function trash(Request $request)
    {
        $orgUnitId = $this->resolveOrgUnitId(
            $request->query('org') !== null ? (int) $request->query('org') : null
        );

        if (!$this->canWriteOrgStorage($orgUnitId)) {
            return $this->denyStorage();
        }

        $folders = OrgStorageFolder::onlyTrashed()
            ->where('org_unit_id', $orgUnitId)
            ->orderByDesc('deleted_at')
            ->get();

        $files = OrgStorageFile::onlyTrashed()
            ->where('org_unit_id', $orgUnitId)
            ->orderByDesc('deleted_at')
            ->get();

        $organization = OrganizationUnits::find($orgUnitId);
        $availableOrgs = $this->canChooseOrg()
            ? OrganizationUnits::where('status', 1)->orderBy('name_uz')->get()
            : collect();

        return view('storage.trash', compact(
            'folders', 'files', 'organization', 'orgUnitId', 'availableOrgs'
        ));
    }

    public function restoreFolder(int $id)
    {
        $folder = OrgStorageFolder::onlyTrashed()->findOrFail($id);
        if (!$this->canWriteOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        if ($folder->parent_id) {
            $parent = OrgStorageFolder::find($folder->parent_id);
            if (!$parent || $parent->trashed()) {
                return back()->with('error', 'Сначала восстановите родительскую папку');
            }
        }

        $this->service->restoreFolder($folder);
        return back()->with('success', 'Папка восстановлена');
    }

    public function restoreFile(int $id)
    {
        $file = OrgStorageFile::onlyTrashed()->findOrFail($id);
        if (!$this->canWriteOrgStorage($file->org_unit_id)) {
            return $this->denyStorage();
        }

        if ($file->folder_id) {
            $folder = OrgStorageFolder::find($file->folder_id);
            if (!$folder || $folder->trashed()) {
                return back()->with('error', 'Сначала восстановите содержащую папку');
            }
        }

        $this->service->restoreFile($file);
        return back()->with('success', 'Файл восстановлен');
    }

    public function purgeFolder(int $id)
    {
        $folder = OrgStorageFolder::onlyTrashed()->findOrFail($id);
        if (!$this->canWriteOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        // При окончательном удалении папки чистим её ACL-записи
        OrgStorageFolderPermission::where('folder_id', $folder->id)->delete();

        $this->service->purgeFolder($folder);
        return back()->with('success', 'Папка удалена окончательно');
    }

    public function purgeFile(int $id)
    {
        $file = OrgStorageFile::onlyTrashed()->findOrFail($id);
        if (!$this->canWriteOrgStorage($file->org_unit_id)) {
            return $this->denyStorage();
        }

        $this->service->purgeFile($file);
        return back()->with('success', 'Файл удалён окончательно');
    }
}
