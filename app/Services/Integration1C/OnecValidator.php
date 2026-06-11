<?php

namespace App\Services\Integration1C;

use Illuminate\Support\Facades\Validator;

/**
 * Валидация входящих данных 1С с понятными сообщениями.
 * Для каждой сущности — свой набор правил create_update.
 * Возвращает массив ошибок (поле => сообщение) или пустой массив если ОК.
 */
class OnecValidator
{
    /** Правила для одиночной записи (create_update) по сущности */
    private const RULES = [
        'companies' => [
            'ID'                   => 'required|uuid',
            'name'                 => 'required|string|max:255',
            'taxID'                => 'nullable|string|max:20',
            'isBranch'             => 'nullable|boolean',
            'parentOrganizationID' => 'nullable|uuid',
        ],
        'divisions' => [
            'ID'               => 'required|uuid',
            'name'             => 'required|string|max:255',
            'code'             => 'nullable|string|max:50',
            'parentDivisionID' => 'nullable|uuid',
            'companyID'        => 'required|uuid',
        ],
        'positions' => [
            'ID'   => 'required|uuid',
            'name' => 'required|string|max:255',
        ],
        'stafflist_positions' => [
            'ID'         => 'required|uuid',
            'name'       => 'required|string|max:255',
            'companyID'  => 'required|uuid',
            'divisionID' => 'required|uuid',
            'positionID' => 'required|uuid',
        ],
        'persons' => [
            'ID'                => 'required|uuid',
            'lastName'          => 'required|string|max:255',
            'firstName'         => 'required|string|max:255',
            'patronymic'        => 'nullable|string|max:255',
            'gender'            => 'nullable|in:male,female',
            'birthDate'         => 'nullable|string',
            'taxID'             => 'nullable|string|max:20',
            'insuranceNumber'   => 'nullable|string|max:30',
            'identityDocument'  => 'nullable|array',
            'mobilePhoneNumber' => 'nullable|string|max:30',
            'eMail'             => 'nullable|email',
            'citizenship'       => 'nullable|string|max:5',
            'salaryCardNumbers' => 'nullable|array',
        ],
        'employees' => [
            'ID'                  => 'required|uuid',
            'personID'            => 'required|uuid',
            'companyID'           => 'required|uuid',
            'employmentDate'      => 'nullable|string',
            'employmentType'      => 'nullable|string',
            'staffListPositionID' => 'nullable|uuid',
            'divisionID'          => 'nullable|uuid',
            'positionID'          => 'nullable|uuid',
            'transferDate'        => 'nullable|string',
            'wageRateValue'       => 'nullable|numeric',
            'dismissed'           => 'nullable|boolean',
            'employeeNumber'      => 'nullable|string|max:50',
        ],
        'vocations' => [
            'ID'         => 'required|uuid',
            'personID'   => 'required|uuid',
            'components' => 'nullable|array',
        ],
        'users' => [
            'personID' => 'required|uuid',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
        ],
    ];

    /** Человекочитаемые имена сущностей для сообщений */
    private const ENTITY_NAMES = [
        'companies'           => 'Компания',
        'divisions'           => 'Подразделение',
        'positions'           => 'Должность',
        'stafflist_positions' => 'Штатная позиция',
        'persons'             => 'Физлицо',
        'employees'           => 'Сотрудник',
        'vocations'           => 'Отпуск',
        'users'               => 'Пользователь',
    ];

    public function hasEntity(string $entity): bool
    {
        return isset(self::RULES[$entity]);
    }

    public function entityName(string $entity): string
    {
        return self::ENTITY_NAMES[$entity] ?? $entity;
    }

    /**
     * Валидировать одну запись.
     * @return array список ошибок [{field, message}] — пустой если ОК
     */
    public function validate(string $entity, array $data): array
    {
        $rules = self::RULES[$entity] ?? null;
        if (!$rules) {
            return [['field' => '_entity', 'message' => "Неизвестная сущность: {$entity}"]];
        }

        // Пустые строки в ссылочных полях (parent*ID и т.п.) трактуем как отсутствие значения,
        // чтобы 'nullable|uuid' не падал на "" (1С присылает "" вместо null)
        foreach ($data as $k => $v) {
            if ($v === '' && (str_ends_with($k, 'ID') || str_contains($k, 'parent'))) {
                $data[$k] = null;
            }
        }

        $messages = $this->messages($entity, $rules);
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $msgs) {
                $errors[] = ['field' => $field, 'message' => $msgs[0]];
            }
            return $errors;
        }
        return [];
    }

    /** Понятные сообщения: какое поле и что не так */
    private function messages(string $entity, array $rules): array
    {
        $msgs = [];
        foreach ($rules as $field => $rule) {
            $msgs["{$field}.required"] = "Не передано обязательное поле '{$field}'";
            $msgs["{$field}.string"]   = "Поле '{$field}' должно быть строкой";
            $msgs["{$field}.numeric"]  = "Поле '{$field}' должно быть числом";
            $msgs["{$field}.boolean"]  = "Поле '{$field}' должно быть true/false";
            $msgs["{$field}.array"]    = "Поле '{$field}' должно быть массивом";
            $msgs["{$field}.email"]    = "Поле '{$field}' должно быть корректным email";
            $msgs["{$field}.in"]       = "Поле '{$field}' имеет недопустимое значение";
            $msgs["{$field}.max"]      = "Поле '{$field}' слишком длинное";
            $msgs["{$field}.uuid"]     = "Поле '{$field}' должно быть корректным UUID";
        }
        return $msgs;
    }
}
