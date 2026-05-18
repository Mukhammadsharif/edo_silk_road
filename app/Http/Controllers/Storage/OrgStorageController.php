<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Models\Admin\OrganizationUnits;
use App\Models\Storage\OrgStorageFile;
use App\Models\Storage\OrgStorageFolder;
use App\Services\OrgStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class OrgStorageController extends Controller
{
    use OrgStorageAccessTrait;

    public function __construct(private OrgStorageService $service)
    {
    }

    // ============================================================
    //  ПРОСМОТР
    // ============================================================

    /**
     * GET /storage          — корень текущей org
     * GET /storage?org=N    — корень другой org (только super_admin)
     */
    public function index(Request $request)
    {
        $orgUnitId = $this->resolveOrgUnitId(
            $request->query('org') !== null ? (int) $request->query('org') : null
        );

        if (!$this->canReadOrgStorage($orgUnitId)) {
            return $this->denyStorage();
        }

        return $this->renderFolderView($orgUnitId, null);
    }

    /**
     * GET /storage/folder/{id}
     */
    public function showFolder(Request $request, int $id)
    {
        $folder = OrgStorageFolder::findOrFail($id);

        if (!$this->canReadOrgStorage($folder->org_unit_id)) {
            return $this->denyStorage();
        }

        return $this->renderFolderView($folder->org_unit_id, $folder);
    }

    private function renderFolderView(int $orgUnitId, ?OrgStorageFolder $folder)
    {
        $activeFolderId = $folder?->id;

        // ВСЁ дерево папок организации одним запросом — для левой панели tree-view.
        // Для типичной корпоративной структуры (100-500 папок) это быстрее,
        // чем lazy-load по клику на каждый узел.
        $allFolders = OrgStorageFolder::where('org_unit_id', $orgUnitId)
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'path']);

        // Файлы в текущей папке (правая панель)
        $files = OrgStorageFile::where('org_unit_id', $orgUnitId)
            ->where('folder_id', $activeFolderId)
            ->orderBy('name')
            ->get();

        // Подпапки текущей папки (для отображения в правой панели как "плитки")
        $childFolders = $allFolders->where('parent_id', $activeFolderId)->values();

        $breadcrumb = $this->buildBreadcrumb($folder);

        // Список организаций для super_admin селектора
        $availableOrgs = $this->canChooseOrg()
            ? OrganizationUnits::where('status', 1)->orderBy('name_uz')->get()
            : collect();

        $organization = OrganizationUnits::find($orgUnitId);

        // Read-only сотрудники не должны видеть management-кнопки
        $canWrite = $this->canWriteOrgStorage($orgUnitId);

        return view('storage.index', compact(
            'folder', 'allFolders', 'childFolders', 'files', 'breadcrumb',
            'availableOrgs', 'organization', 'orgUnitId', 'canWrite', 'activeFolderId'
        ));
    }

    private function buildBreadcrumb(?OrgStorageFolder $folder): array
    {
        if (!$folder) return [];

        $crumbs = [];
        $current = $folder;
        // Идём вверх до корня
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

        // Если parent_id задан — он должен быть в той же org
        $parentId = $request->input('parent_id') ? (int) $request->input('parent_id') : null;
        if ($parentId !== null) {
            $parent = OrgStorageFolder::find($parentId);
            if (!$parent || (int) $parent->org_unit_id !== $orgUnitId) {
                return back()->with('error', 'Родительская папка не найдена');
            }
        }

        try {
            $folder = $this->service->createFolder(
                $orgUnitId,
                $parentId,
                $request->input('name'),
                Auth::id()
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
        if (!$this->canReadOrgStorage($file->org_unit_id)) {
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

        if (!$file->isPreviewable()) {
            // Не превьюится — отдаём download
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

        // Если родитель — soft-deleted, восстанавливать нельзя (некуда положить)
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

        // Аналогично: если папка-родитель в корзине — нельзя
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
