<?php

namespace App\Models\Storage;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgStorageFolderPermission extends Model
{
    protected $table = 'org_storage_folder_permissions';

    protected $fillable = [
        'folder_id',
        'user_id',
        'org_unit_id',
        'mode',          // 'allow' | 'deny'
        'granted_by',
    ];

    protected $casts = [
        'folder_id'   => 'integer',
        'user_id'     => 'integer',
        'org_unit_id' => 'integer',
        'granted_by'  => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(OrgStorageFolder::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
