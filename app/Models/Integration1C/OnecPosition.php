<?php

namespace App\Models\Integration1C;

class OnecPosition extends BaseOnecModel
{
    protected $table = 'onec_positions';
    protected $fillable = ['onec_id', 'name', 'sync_status', 'payload', 'org_unit_id'];
}
