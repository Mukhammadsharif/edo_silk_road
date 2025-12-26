<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocLog extends Model
{
    protected $fillable = [
        'user_id',
        'doc_id',
        'act_id',
        'act_date',
        'log_type',
        'doc_name',
        'computer_id'
    ];

    public function userWorkHistory(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class,'user_id');
    }

    public function actType(): BelongsTo
    {
        return $this->belongsTo(StatusList::class, 'act_id', 'code')
            ->where('type', 'document_log');
    }
}
