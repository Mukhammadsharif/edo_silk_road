<?php

namespace App\Models\Storage;

use App\Models\Admin\OrganizationUnits;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgStorageFolder extends Model
{
    use SoftDeletes;

    protected $table = 'org_storage_folders';

    protected $fillable = [
        'org_unit_id',
        'parent_id',
        'name',
        'path',
        'created_by',
    ];

    protected $casts = [
        'org_unit_id' => 'integer',
        'parent_id'   => 'integer',
        'created_by'  => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrgStorageFile::class, 'folder_id')->orderBy('name');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Все ID папок-потомков (рекурсивно), включая саму $this->id.
     * Использует материализованный path — один SQL-запрос вместо обхода дерева.
     */
    public function descendantIds(): array
    {
        $self = [$this->id];

        $prefix = rtrim($this->path, '/') . '/' . $this->name;
        // Все потомки имеют path, начинающийся на $prefix
        $descendants = self::query()
            ->where('org_unit_id', $this->org_unit_id)
            ->where('path', 'LIKE', $prefix . '%')
            ->pluck('id')
            ->toArray();

        return array_unique(array_merge($self, $descendants));
    }
}
