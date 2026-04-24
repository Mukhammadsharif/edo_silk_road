<?php

namespace App\Models\Document;

use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use App\Models\ECP\SignatureDocuments;
use App\Models\Journal\ConfidentialTypes;
use App\Models\Journal\JournalAttachedOrgs;
use App\Models\Journal\JournalTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;


class DocumentJournals extends Model
{

    protected $fillable = [
        'doc_id',
        'j_type',
        'j_number',
        'j_date',
        'dep_j_id',
        'org_unit_id',
        'confid_type',
        'created_dep',
        'created_user',
        'status'
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(JournalTypes::class, 'j_type');
    }
    public function doc(): BelongsTo
    {
        return $this->belongsTo(Documents::class, 'doc_id');
    }
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'created_user');
    }
    public function docStatus(): BelongsTo
    {
        return $this->belongsTo(ConfidentialTypes::class, 'confid_type');
    }
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(JournalAttachedOrgs::class, 'j_id', 'id');
    }
    public function statusWork(): BelongsTo
    {
        return $this->belongsTo(StatusList::class, 'status', 'code')->where('type', 'work_document');
    }
    public function step(): HasOne
    {
        $workIds = UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();

        return $this->hasOne(DocumentExecutionSteps::class, 'j_id', 'id')->whereIn('to_user', $workIds);
    }

    public function execStep(): HasOne
    {
        $workIds = UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();

        return $this->hasOne(DocumentExecutionSteps::class, 'j_id', 'id')
            ->whereIn('to_user', $workIds)
            ->whereHas('role', function ($query) { $query->where('doc_code', 'EXEC');});
    }
    public function executionSteps(): BelongsTo
    {
        return $this->belongsTo(DocumentExecutionSteps::class,  'id', 'j_id');
    }
    public function docOrg(): HasOne
    {
        return $this->hasOne(JournalAttachedOrgs::class, 'j_id', 'id');
    }
    public function statusOut(): BelongsTo
    {
        return $this->belongsTo(StatusList::class, 'status', 'code')->where('type', 'out_document');
    }
    public function dep(): HasOne
    {
        return $this->hasOne(DocToDeps::class, 'j_id', 'id');
    }
    public function workEcpInfo($doc_id, $id)
    {
        return SignatureDocuments::where('doc_id', $doc_id)->where('user_id', $id)->where('doc_type', '<', 12)->first();
    }

    public function directorResolution(): BelongsTo
    {
        return $this->belongsTo(DocumentResolutions::class,  'id', 'j_id')->orderBy('id', 'DESC');
    }

    public function files()
    {
        return $this->hasMany(DocumentFiles::class, 'j_id', 'id');
    }
}
