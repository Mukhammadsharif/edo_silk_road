<?php

namespace App\Services\Integration1C;

use Carbon\Carbon;

/**
 * Маппинг полей между форматом 1С (camelCase, даты DD-MM-YYYY)
 * и колонками БД EDOLINE (snake_case, даты Y-m-d).
 *
 * toLocal()  — входящие из 1С  → колонки БД
 * toOnec()   — наши данные      → формат 1С (для исходящих)
 */
class OnecMapper
{
    /** Карта сущность => [onecField => localColumn] */
    private const MAP = [
        'companies' => [
            'ID'                   => 'onec_id',
            'name'                 => 'name',
            'taxID'                => 'tax_id',
            'isBranch'             => 'is_branch',
            'parentOrganizationID' => 'parent_organization_id',
        ],
        'divisions' => [
            'ID'               => 'onec_id',
            'code'             => 'code',
            'name'             => 'name',
            'parentDivisionID' => 'parent_division_id',
            'companyID'        => 'company_id',
        ],
        'positions' => [
            'ID'   => 'onec_id',
            'name' => 'name',
        ],
        'stafflist_positions' => [
            'ID'         => 'onec_id',
            'name'       => 'name',
            'companyID'  => 'company_id',
            'divisionID' => 'division_id',
            'positionID' => 'position_id',
        ],
        'persons' => [
            'ID'                  => 'onec_id',
            'lastName'            => 'last_name',
            'firstName'           => 'first_name',
            'patronymic'          => 'patronymic',
            'gender'              => 'gender',
            'birthDate'           => 'birth_date',
            'taxID'               => 'tax_id',
            'insuranceNumber'     => 'insurance_number',
            'identityDocument'    => 'identity_document',
            'birthPlace'          => 'birth_place',
            'mobilePhoneNumber'   => 'mobile_phone_number',
            'eMail'               => 'email',
            'registrationAddress' => 'registration_address',
            'residentialAddress'  => 'residential_address',
            'picture'             => 'picture',
            'citizenship'         => 'citizenship',
            'salaryCardNumbers'   => 'salary_card_numbers',
        ],
        'employees' => [
            'ID'                   => 'onec_id',
            'personID'             => 'person_id',
            'companyID'            => 'company_id',
            'employmentDate'       => 'employment_date',
            'employmentType'       => 'employment_type',
            'staffListPositionID'  => 'stafflist_position_id',
            'divisionID'           => 'division_id',
            'positionID'           => 'position_id',
            'transferDate'         => 'transfer_date',
            'wageRateValue'        => 'wage_rate_value',
            'wageRatePresentation' => 'wage_rate_presentation',
            'workScheduleID'       => 'work_schedule_id',
            'dismissed'            => 'dismissed',
            'employeeNumber'       => 'employee_number',
        ],
        'vocations' => [
            'ID'         => 'onec_id',
            'personID'   => 'person_id',
            'components' => 'components',
        ],
        'users' => [
            'personID' => 'person_id',
            'username' => 'username',
        ],
    ];

    /** Поля-даты по сущности (формат DD-MM-YYYY в 1С) */
    private const DATE_FIELDS = [
        'persons'   => ['birthDate'],
        'employees' => ['employmentDate', 'transferDate'],
    ];

    /** Входящие из 1С → колонки БД */
    public function toLocal(string $entity, array $onec): array
    {
        $map = self::MAP[$entity] ?? [];
        $dateFields = self::DATE_FIELDS[$entity] ?? [];
        $local = [];

        foreach ($map as $onecKey => $col) {
            if (!array_key_exists($onecKey, $onec)) continue;
            $val = $onec[$onecKey];

            // Пустую строку для ссылочных/UUID-полей трактуем как null
            if ($val === '' && (str_ends_with($col, '_id') || str_contains($col, 'parent'))) {
                $val = null;
            }

            if (in_array($onecKey, $dateFields, true) && $val) {
                $val = $this->parseDate($val);
            }
            $local[$col] = $val;
        }

        // Полный исходный JSON для аудита
        $local['payload'] = $onec;
        $local['sync_status'] = 'staged';

        return $local;
    }

    /** Наши данные (модель) → формат 1С (для исходящих) */
    public function toOnec(string $entity, array $local): array
    {
        $map = array_flip(self::MAP[$entity] ?? []);
        $dateFields = self::DATE_FIELDS[$entity] ?? [];
        $onec = [];

        foreach ($map as $col => $onecKey) {
            if (!array_key_exists($col, $local)) continue;
            $val = $local[$col];

            // даты обратно в DD-MM-YYYY
            if ($val && in_array($onecKey, $dateFields, true)) {
                try { $val = Carbon::parse($val)->format(config('onec.date_format', 'd-m-Y')); }
                catch (\Throwable $e) {}
            }
            $onec[$onecKey] = $val;
        }

        return $onec;
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;
        // 1С присылает DD-MM-YYYY
        foreach (['d-m-Y', 'Y-m-d', 'd.m.Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value)->format('Y-m-d');
            } catch (\Throwable $e) {}
        }
        try { return Carbon::parse($value)->format('Y-m-d'); }
        catch (\Throwable $e) { return null; }
    }
}
