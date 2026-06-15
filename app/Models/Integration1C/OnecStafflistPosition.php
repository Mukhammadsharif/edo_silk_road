<?php

namespace App\Models\Integration1C;

class OnecStafflistPosition extends BaseOnecModel
{
    protected $table = 'onec_stafflist_positions';
    protected $fillable = [
        'onec_id', 'name', 'company_id', 'division_id', 'position_id',
        'sync_status', 'payload',
    ];
}
