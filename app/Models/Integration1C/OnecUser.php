<?php

namespace App\Models\Integration1C;

class OnecUser extends BaseOnecModel
{
    protected $table = 'onec_users';
    protected $fillable = ['person_id', 'username', 'sync_status', 'payload', 'org_unit_id'];

    public static function upsertKey(): string
    {
        return 'person_id';
    }
}
