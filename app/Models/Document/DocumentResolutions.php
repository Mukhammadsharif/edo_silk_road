<?php

namespace App\Models\Document;

use App\Models\Admin\UserWorkHistory;
use App\Models\Resolution\ResolutionTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentResolutions extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_exec_step_id',
        'j_id',
        'from_dep',
        'from_user',
        'res_type',
        'res_text',
        'res_term',
        'status'
    ];

    public function resType(): BelongsTo
    {
        return $this->belongsTo(ResolutionTypes::class, 'res_type');
    }
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class,'from_user');
    }
}
