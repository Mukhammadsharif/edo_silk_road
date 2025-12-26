<?php

namespace App\Models\ECP;

use App\Models\Admin\UserWorkHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SignatureDocuments extends Model
{
    use HasFactory;

    protected $fillable =[
        'doc_id',
        'doc_type',
        'user_id',
        'key_id',
        'signing_time'
    ];

    public function docInfo()
    {
        return $this->hasOne(UserSignatureKey::class, 'id','key_id');
    }

    public function sign_user()
    {
        return $this->hasOne(UserWorkHistory::class, 'id', 'user_id');
    }

    public function type(): HasOne
    {
        return $this->hasOne(SignatureDocumentType::class, 'id', 'doc_type');
    }
}
