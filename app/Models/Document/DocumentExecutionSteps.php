<?php

namespace App\Models\Document;

use App\Models\Admin\Department;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use App\Models\Resolution\ResolutionExecutionRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentExecutionSteps extends Model
{

    protected $fillable = [
        'j_id',
        'from_dep',
        'from_user',
        'to_dep',
        'to_user',
        'exec_role',
        'res_exec_role',
        'status',
        'queue_number',
    ];

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class,'to_user');
    }
    public function journal(): BelongsTo
    {
        return $this->belongsTo(DocumentJournals::class, 'j_id');
    }
    public function toSign(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class,'to_user');
    }
    public function statusSign(): BelongsTo
    {
        return $this->belongsTo(StatusList::class,  'status', 'code')->where('type', 'doc_signer');
    }
    public function statusExec(): BelongsTo
    {
        return $this->belongsTo(StatusList::class,  'status', 'code')->where('type', 'doc_exec');
    }
    public function role(): HasMany
    {
        return $this->hasMany(DocumentExecutionRoles::class,  'id', 'exec_role');
    }
    public function replyComment(): HasOne
    {
        return $this->hasOne(DocumentReplyComments::class,  'doc_exec_step_id', 'id');
    }
    public function replyComments(): HasMany
    {
        return $this->hasMany(DocumentReplyComments::class, 'doc_exec_step_id', 'id');
    }
    public function replyDocs($j_id, $user_id)
    {
        return DocumentReplyComments::where('j_id', $j_id)->where('from_user', $user_id)->get();
    }
    public function toDep(): BelongsTo
    {
        return $this->belongsTo(Department::class,'to_dep');
    }
    public function resExecRole(): BelongsTo
    {
        return $this->belongsTo(ResolutionExecutionRoles::class,'res_exec_role');
    }

    public function signQrCode($doc_id, $user_id, $step_id)
    {
        $edoDocument = Documents::find($doc_id);
        if ($edoDocument) {
            $user = UserWorkHistory::find($user_id);
            $edoSignDoc = DocumentExecutionSteps::where('j_id', $edoDocument->journal->id)
                ->where('to_user', $user_id)->whereIn('status', [5, 6])
                ->where('id', $step_id)
                ->first();
            if ($user && $edoSignDoc) {
                $domainName = env('APP_URL', 'http://edoline.uz');
                return QrCode::size(60)
                    ->generate($domainName.'/d/'.$edoDocument->doc_code);
            }
        }

        return $this->qrImage();
    }

    public function printSignQrCode($doc_id, $user_id, $step_id)
    {
        $edoDocument = Documents::find($doc_id);
        if ($edoDocument) {
            $user = UserWorkHistory::find($user_id);
            $edoSignDoc = DocumentExecutionSteps::where('j_id', $edoDocument->journal->id)
                ->where('to_user', $user_id)->whereIn('status', [5, 6])
                ->where('id', $step_id)
                ->first();
            if ($user && $edoSignDoc) {
                $domainName = env('APP_URL', 'http://edoline.uz');
                $qr_code = base64_encode(QrCode::format('svg')->size(60)->generate($domainName.'/d/'.$edoDocument->doc_code));
                return '<img src="data:image/png;base64, '.$qr_code.'">';
            }
        }

        return $this->qrImage();
    }
    public function qrImage()
    {
        return '<img src="'.asset('/images/signature.png').'" style="width: 48px">';
    }

    public function scopeResolution($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('doc_code', 'RESOL')->where('status', 1);
        });
    }
}
