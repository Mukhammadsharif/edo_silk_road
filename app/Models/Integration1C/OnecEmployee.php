<?php

namespace App\Models\Integration1C;

class OnecEmployee extends BaseOnecModel
{
    protected $table = 'onec_employees';
    protected $fillable = [
        'onec_id',
        'person_id',
        'company_id',
        'employment_date',
        'employment_type',
        'stafflist_position_id',
        'division_id',
        'position_id',
        'transfer_date',
        'wage_rate_value',
        'wage_rate_presentation',
        'work_schedule_id',
        'dismissed',
        'employee_number',
        'sync_status',
        'payload',
        'org_unit_id'
    ];
    protected $casts = [
        'payload'         => 'array',
        'dismissed'       => 'boolean',
        'employment_date' => 'date',
        'transfer_date'   => 'date',
    ];
}
