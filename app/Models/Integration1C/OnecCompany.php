<?php

namespace App\Models\Integration1C;

class OnecCompany extends BaseOnecModel
{
    protected $table = 'onec_companies';
    protected $fillable = [
        'onec_id',
        'name',
        'tax_id',
        'is_branch',
        'parent_organization_id',
        'sync_status',
        'payload',
        'org_unit_id'
    ];
    protected $casts = [
        'payload'   => 'array',
        'is_branch' => 'boolean',
    ];
}
