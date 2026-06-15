<?php

namespace App\Models\Integration1C;

class OnecDivision extends BaseOnecModel
{
    protected $table = 'onec_divisions';
    protected $fillable = [
        'onec_id',
        'code',
        'name',
        'parent_division_id',
        'company_id',
        'sync_status',
        'payload',
        'org_unit_id'
    ];
}
