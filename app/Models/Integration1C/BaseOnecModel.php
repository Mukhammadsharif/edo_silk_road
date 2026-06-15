<?php

namespace App\Models\Integration1C;

use Illuminate\Database\Eloquent\Model;

abstract class BaseOnecModel extends Model
{
    protected $casts = [
        'payload' => 'array',
    ];

    /** Поля, по которым 1С присылает upsert. UUID-ключ всегда onec_id. */
    public static function upsertKey(): string
    {
        return 'onec_id';
    }
}
