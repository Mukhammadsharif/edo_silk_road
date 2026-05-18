<?php

namespace App\Services;

use App\Models\Storage\OrgStorageFile;
use App\Models\Storage\OrgStorageFolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Сервис для работы с файловым хранилищем организации (FTP + БД).
 *
 * FTP-layout: /storage/{org_unit_id}/{uuid}.{ext}
 *
 * Логическая иерархия папок хранится только в БД — на FTP лежит плоский
 * набор blob'ов с UUID-именами. Это исключает path-traversal атаки и
 * делает переименование/перемещение папок мгновенным (только БД).
 */
class OrgStorageService
{
    public const ALLOWED_EXTENSIONS = [
        'doc', 'docx',
        'xls', 'xlsx',
        'pdf',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
    ];

    public const MAX_FILE_SIZE_BYTES = 50 * 1024 * 1024; // 50 MB

    private const FTP_DISK = 'ftp';

    /**
     * Загрузить файл с UploadedFile на FTP и создать запись в БД.
     */
    public function uploadFile(
        UploadedFile $file,
        int $orgUnitId,
        ?int $folderId,
        ?int $userId
    ): OrgStorageFile {
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \InvalidArgumentException(
                'Тип файла .' . $extension . ' не разрешён. Допустимые типы: '
                . implode(', ', self::ALLOWED_EXTENSIONS)
            );
        }

        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            throw new \InvalidArgumentException(
                'Размер файла превышает '
                . round(self::MAX_FILE_SIZE_BYTES / 1024 / 1024) . ' MB'
            );
        }

        // Если folder_id указан — проверяем что он принадлежит этой org
        if ($folderId !== null) {
            $folder = OrgStorageFolder::where('id', $folderId)
                ->where('org_unit_id', $orgUnitId)
                ->whereNull('deleted_at')
                ->first();
            if (!$folder) {
                throw new \InvalidArgumentException('Папка не найдена');
            }
        }

        $uuid = (string) Str::uuid();
        $ftpFilename = $uuid . '.' . $extension;

        // Каталог /storage/{org_unit_id}/ — относительно ftp.root
        $ftpDir = 'storage/' . $orgUnitId;
        $ftpPath = $ftpDir . '/' . $ftpFilename;

        try {
            Storage::disk(self::FTP_DISK)->putFileAs($ftpDir, $file, $ftpFilename);
        } catch (\Throwable $e) {
            Log::error('OrgStorageService: FTP upload failed', [
                'org_unit_id' => $orgUnitId,
                'folder_id'   => $folderId,
                'error'       => $e->getMessage(),
            ]);
            throw new \RuntimeException('Не удалось загрузить файл на FTP');
        }

        return OrgStorageFile::create([
            'org_unit_id'   => $orgUnitId,
            'folder_id'     => $folderId,
            'name'          => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'extension'     => $extension,
            'mime_type'     => $file->getMimeType(),
            'size_bytes'    => $file->getSize(),
            'ftp_path'      => $ftpPath,
            'ftp_hash'      => $uuid,
            'created_by'    => $userId,
        ]);
    }

    /**
     * Получить содержимое файла с FTP.
     */
    public function readFile(OrgStorageFile $file): ?string
    {
        try {
            if (!Storage::disk(self::FTP_DISK)->exists($file->ftp_path)) {
                Log::warning('OrgStorageService: file missing on FTP', [
                    'id'       => $file->id,
                    'ftp_path' => $file->ftp_path,
                ]);
                return null;
            }
            return Storage::disk(self::FTP_DISK)->get($file->ftp_path);
        } catch (\Throwable $e) {
            Log::error('OrgStorageService: read failed', [
                'id'    => $file->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Поток для скачивания.
     */
    public function downloadResponse(OrgStorageFile $file)
    {
        if (!Storage::disk(self::FTP_DISK)->exists($file->ftp_path)) {
            return null;
        }
        return Storage::disk(self::FTP_DISK)->download($file->ftp_path, $file->name);
    }

    /**
     * Soft delete файла. На FTP оставляем blob для возможного восстановления.
     */
    public function softDeleteFile(OrgStorageFile $file): void
    {
        $file->delete();
    }

    /**
     * Восстановить из корзины.
     */
    public function restoreFile(OrgStorageFile $file): void
    {
        $file->restore();
    }

    /**
     * Окончательно удалить файл (из корзины) — и БД-запись, и blob с FTP.
     */
    public function purgeFile(OrgStorageFile $file): void
    {
        try {
            if (Storage::disk(self::FTP_DISK)->exists($file->ftp_path)) {
                Storage::disk(self::FTP_DISK)->delete($file->ftp_path);
            }
        } catch (\Throwable $e) {
            Log::warning('OrgStorageService: FTP delete failed', [
                'id'    => $file->id,
                'error' => $e->getMessage(),
            ]);
            // Не бросаем — пусть запись из БД всё равно удалится, чтобы не висел "призрак"
        }
        $file->forceDelete();
    }

    /**
     * Создать папку, посчитав path материализованно.
     */
    public function createFolder(
        int $orgUnitId,
        ?int $parentId,
        string $name,
        ?int $userId
    ): OrgStorageFolder {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 255) {
            throw new \InvalidArgumentException('Недопустимое имя папки');
        }

        // Имя не должно содержать слешей, перевода строк и т.п.
        if (preg_match('#[/\\\\]#u', $name) || str_contains($name, "\0")) {
            throw new \InvalidArgumentException('Имя папки содержит недопустимые символы');
        }

        $parentPath = '/';
        if ($parentId !== null) {
            $parent = OrgStorageFolder::where('id', $parentId)
                ->where('org_unit_id', $orgUnitId)
                ->whereNull('deleted_at')
                ->first();
            if (!$parent) {
                throw new \InvalidArgumentException('Родительская папка не найдена');
            }
            // path родителя + его имя = "родительский путь" для нашего path
            $parentPath = rtrim($parent->path, '/') . '/' . $parent->name;
            if ($parentPath === '/' . $parent->name) {
                $parentPath = '/' . $parent->name;
            }
        }

        // Уникальность имени в рамках одного parent_id
        $exists = OrgStorageFolder::where('org_unit_id', $orgUnitId)
            ->where('parent_id', $parentId)
            ->whereNull('deleted_at')
            ->where('name', $name)
            ->exists();
        if ($exists) {
            throw new \InvalidArgumentException('Папка с таким именем уже существует');
        }

        return OrgStorageFolder::create([
            'org_unit_id' => $orgUnitId,
            'parent_id'   => $parentId,
            'name'        => $name,
            'path'        => $parentPath,
            'created_by'  => $userId,
        ]);
    }

    /**
     * Soft delete папки и всех её потомков (включая файлы).
     */
    public function softDeleteFolder(OrgStorageFolder $folder): void
    {
        $descendantIds = $folder->descendantIds();

        // Файлы в этих папках soft-delete
        OrgStorageFile::whereIn('folder_id', $descendantIds)
            ->where('org_unit_id', $folder->org_unit_id)
            ->delete();

        // Сами папки
        OrgStorageFolder::whereIn('id', $descendantIds)
            ->where('org_unit_id', $folder->org_unit_id)
            ->delete();
    }

    /**
     * Восстановить папку (только саму, без потомков — потомки восстанавливаются индивидуально).
     */
    public function restoreFolder(OrgStorageFolder $folder): void
    {
        $folder->restore();
    }

    /**
     * Окончательно удалить папку и всё внутри — включая FTP blobs.
     */
    public function purgeFolder(OrgStorageFolder $folder): void
    {
        $descendantIds = $folder->descendantIds();

        // Все файлы во всех потомках — включая soft-deleted
        $files = OrgStorageFile::withTrashed()
            ->whereIn('folder_id', $descendantIds)
            ->where('org_unit_id', $folder->org_unit_id)
            ->get();

        foreach ($files as $f) {
            try {
                if (Storage::disk(self::FTP_DISK)->exists($f->ftp_path)) {
                    Storage::disk(self::FTP_DISK)->delete($f->ftp_path);
                }
            } catch (\Throwable $e) {
                Log::warning('OrgStorageService: FTP delete failed during purgeFolder', [
                    'file_id' => $f->id,
                    'error'   => $e->getMessage(),
                ]);
            }
            $f->forceDelete();
        }

        OrgStorageFolder::withTrashed()
            ->whereIn('id', $descendantIds)
            ->where('org_unit_id', $folder->org_unit_id)
            ->forceDelete();
    }

    /**
     * Переименовать папку. Обновляет path всех потомков.
     */
    public function renameFolder(OrgStorageFolder $folder, string $newName): void
    {
        $newName = trim($newName);
        if ($newName === '' || mb_strlen($newName) > 255) {
            throw new \InvalidArgumentException('Недопустимое имя папки');
        }
        if (preg_match('#[/\\\\]#u', $newName) || str_contains($newName, "\0")) {
            throw new \InvalidArgumentException('Имя папки содержит недопустимые символы');
        }
        if ($newName === $folder->name) return;

        // Проверка коллизии в parent
        $exists = OrgStorageFolder::where('org_unit_id', $folder->org_unit_id)
            ->where('parent_id', $folder->parent_id)
            ->whereNull('deleted_at')
            ->where('name', $newName)
            ->where('id', '!=', $folder->id)
            ->exists();
        if ($exists) {
            throw new \InvalidArgumentException('Папка с таким именем уже существует');
        }

        // Old prefix для path всех потомков
        $oldPrefix = rtrim($folder->path, '/') . '/' . $folder->name;
        $newPrefix = rtrim($folder->path, '/') . '/' . $newName;

        $folder->name = $newName;
        $folder->save();

        // Обновляем path у всех потомков (REPLACE по prefix через сырой UPDATE).
        // path всех потомков начинается с $oldPrefix — заменяем эту префиксную часть.
        \DB::statement(
            "UPDATE org_storage_folders
             SET path = CONCAT(?, SUBSTRING(path, ?))
             WHERE org_unit_id = ?
               AND path LIKE ?",
            [
                $newPrefix,
                strlen($oldPrefix) + 1,
                $folder->org_unit_id,
                $oldPrefix . '%',
            ]
        );
    }

    /**
     * Переименовать файл (только displayed name, ftp_path/hash не трогаем).
     */
    public function renameFile(OrgStorageFile $file, string $newName): void
    {
        $newName = trim($newName);
        if ($newName === '' || mb_strlen($newName) > 255) {
            throw new \InvalidArgumentException('Недопустимое имя файла');
        }
        if (preg_match('#[/\\\\]#u', $newName) || str_contains($newName, "\0")) {
            throw new \InvalidArgumentException('Имя файла содержит недопустимые символы');
        }

        // Если юзер указал имя без extension — добавим
        if (!str_contains($newName, '.')) {
            $newName .= '.' . $file->extension;
        }

        $file->name = $newName;
        $file->save();
    }
}
