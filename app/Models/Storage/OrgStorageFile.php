<?php

namespace App\Models\Storage;

use App\Models\Admin\OrganizationUnits;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgStorageFile extends Model
{
    use SoftDeletes;

    protected $table = 'org_storage_files';

    protected $fillable = [
        'org_unit_id',
        'folder_id',
        'name',
        'original_name',
        'extension',
        'mime_type',
        'size_bytes',
        'ftp_path',
        'ftp_hash',
        'created_by',
    ];

    protected $casts = [
        'org_unit_id' => 'integer',
        'folder_id'   => 'integer',
        'size_bytes'  => 'integer',
        'created_by'  => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(OrgStorageFolder::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Можно ли превью этого типа файла прямо в браузере.
     */
    public function isPreviewable(): bool
    {
        return in_array(strtolower($this->extension), ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'], true);
    }

    /**
     * Иконка-имя для UI.
     */
    public function iconName(): string
    {
        $ext = strtolower($this->extension);
        return match ($ext) {
            'doc', 'docx' => 'file-text',
            'xls', 'xlsx' => 'file-spreadsheet',
            'pdf'         => 'file',
            'png', 'jpg', 'jpeg', 'gif', 'webp' => 'file-image',
            default       => 'file',
        };
    }

    /**
     * Размер в человекочитаемом виде.
     */
    public function humanSize(): string
    {
        $bytes = $this->size_bytes ?? 0;
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}
