<?php

namespace App\Models\Journal;

use App\Models\Document\DocumentOrganizations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class JournalAttachedOrgs extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'j_id',
        'doc_org_id'
    ];

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DocumentOrganizations::class, 'doc_org_id');
    }
}
