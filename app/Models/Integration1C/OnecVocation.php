<?php

namespace App\Models\Integration1C;

class OnecVocation extends BaseOnecModel
{
    protected $table = 'onec_vocations';
    protected $fillable = ['onec_id', 'person_id', 'components', 'sync_status', 'payload', 'org_unit_id'];
    protected $casts = [
        'payload'    => 'array',
        'components' => 'array',
    ];
}
