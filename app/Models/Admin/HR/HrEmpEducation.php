<?php

namespace App\Models\Admin\HR;

use Illuminate\Database\Eloquent\Model;

class HrEmpEducation extends Model
{
    protected $fillable = [
        'user_id',
        'edu_code',
        'edu_name',
        'fac_name',
        'spec_name',
        'dip_name',
        'course',
        'from_date',
        'to_date',
        'created_id',
        'isActive'
    ];
}
