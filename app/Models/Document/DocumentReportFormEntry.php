<?php

namespace App\Models\Document;

use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Model;

class DocumentReportFormEntry extends Model
{
    //
    protected $fillable = ['document_report_form_id', 'values', 'filled_by'];

    protected $casts = [
        'values' => 'array',
    ];

    public function documentReportForm(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentReportForm::class, 'document_report_form_id');
    }

    public function filledBy($id)
    {
        $workUser = UserWorkHistory::find($id);
        $shortName = '';
        if ($workUser) {
            $shortName = $workUser->personal->shortName()??'';
        }

        return $shortName;
    }
}
