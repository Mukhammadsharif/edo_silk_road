<?php

namespace App\Models\Document;

use App\Models\Admin\Department;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentReplyComments extends Model
{

    protected $fillable = [
        'doc_exec_step_id',
        'j_id',
        'from_dep',
        'from_user',
        'reply_comment_type',
        'res_text',
        'status'
    ];

    public function statusReply(): BelongsTo
    {
        return $this->belongsTo(StatusList::class, 'status', 'code')->where('type', 'doc_signer');
    }
    public function fromDep(): BelongsTo
    {
        return $this->belongsTo(Department::class,'from_dep');
    }
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class,'from_user');
    }
    public function replyFiles()
    {
        return $this->hasMany(ReplyDocumentFiles::class,'step_id');
    }
}
