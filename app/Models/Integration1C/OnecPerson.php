<?php

namespace App\Models\Integration1C;

class OnecPerson extends BaseOnecModel
{
    protected $table = 'onec_persons';
    protected $fillable = [
        'onec_id', 'last_name', 'first_name', 'patronymic', 'gender', 'birth_date',
        'tax_id', 'insurance_number', 'identity_document', 'birth_place',
        'mobile_phone_number', 'email', 'registration_address', 'residential_address',
        'picture', 'citizenship', 'salary_card_numbers', 'sync_status', 'sync_attempts', 'promoted_at', 'sync_error', 'payload', 'org_unit_id'
    ];
    protected $casts = [
        'payload'             => 'array',
        'identity_document'   => 'array',
        'salary_card_numbers' => 'array',
        'birth_date'          => 'date',
    ];
}
