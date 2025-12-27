<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class OrganizationUnits extends Model
{
    //
    protected $fillable = [
        'code',
        'name_uz',
        'name_kr',
        'name_ru',
        'name_en',
        'tax_number',
        'owner_user_id',
        'parent_id',
        'level',
        'status',
        'meta_data'
    ];
}
