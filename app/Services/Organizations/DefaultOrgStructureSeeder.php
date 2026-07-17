<?php

namespace App\Services\Organizations;

use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\UserPosition;
use Illuminate\Support\Facades\File;

/**
 * Наполняет новую организацию дефолтными подразделениями и должностями
 * из seed-файлов, с корректными переводами на всех 4 языковых формах
 * (name_uz, name_kr, name_ru, name_en).
 *
 * Раньше при регистрации через ЭЦП создавалось лишь одно подразделение,
 * а должность бралась у Демо-организации (post_id = 1), из-за чего у новой
 * организации не было собственных должностей с правильными переводами.
 */
class DefaultOrgStructureSeeder
{
    private string $departmentsFile;
    private string $positionsFile;

    public function __construct()
    {
        $this->departmentsFile = base_path('database/seeders/data/departments.json');
        $this->positionsFile = base_path('database/seeders/data/user_position.json');
    }

    /**
     * @return array{department: ?Department, position: ?UserPosition}
     *         Верхнее подразделение (Головной офис) и верхняя должность
     *         (Ген. директор) — для назначения владельцу организации.
     */
    public function seed(OrganizationUnits $organization): array
    {
        $depIdMap = [];
        $topDepartment = $this->seedDepartments($organization, $depIdMap);
        $topPosition = $this->seedPositions($organization, $depIdMap);

        return [
            'department' => $topDepartment,
            'position'   => $topPosition,
        ];
    }

    /**
     * Создаёт все подразделения из seed-файла, сохраняя иерархию
     * (parent_id перемапливается с id из JSON на реальные id новых записей).
     *
     * @param  array<int,int>  $depIdMap  json_id => new_db_id (заполняется по ссылке)
     */
    private function seedDepartments(OrganizationUnits $organization, array &$depIdMap): ?Department
    {
        if (!File::exists($this->departmentsFile)) {
            return null;
        }

        $items = json_decode(File::get($this->departmentsFile), true)['departments'] ?? [];
        $orgCode = $organization->code;

        $topDepartment = null;

        // Проход 1 — создаём узлы (parent_id проставим во 2-м проходе).
        foreach ($items as $item) {
            $dep = Department::create([
                'code'         => ($item['code'] ?? 'DEP') . '_' . $orgCode,
                'org_unit_id'  => $organization->id,
                'name_uz'      => $item['name_uz'] ?? '',
                'name_kr'      => $item['name_kr'] ?? '',
                'name_ru'      => $item['name_ru'] ?? '',
                'name_en'      => $item['name_en'] ?? '',
                'parent_id'    => 0,
                'order_number' => $item['order_number'] ?? 0,
                'level_number' => $item['level_number'] ?? 0,
                'status'       => $item['status'] ?? 1,
            ]);

            if (isset($item['id'])) {
                $depIdMap[(int) $item['id']] = $dep->id;
            }

            if ((int) ($item['parent_id'] ?? 0) === 0 && $topDepartment === null) {
                $topDepartment = $dep;
            }
        }

        // Проход 2 — восстанавливаем иерархию по карте id.
        foreach ($items as $item) {
            $jsonId = (int) ($item['id'] ?? 0);
            $parentJsonId = (int) ($item['parent_id'] ?? 0);

            if ($parentJsonId && isset($depIdMap[$jsonId], $depIdMap[$parentJsonId])) {
                Department::where('id', $depIdMap[$jsonId])
                    ->update(['parent_id' => $depIdMap[$parentJsonId]]);
            }
        }

        return $topDepartment;
    }

    /**
     * Создаёт все должности из seed-файла для организации.
     * dep_ids перемапливаются с id подразделений из JSON на реальные id.
     *
     * @param  array<int,int>  $depIdMap  json_id => new_db_id
     */
    private function seedPositions(OrganizationUnits $organization, array $depIdMap): ?UserPosition
    {
        if (!File::exists($this->positionsFile)) {
            return null;
        }

        $items = json_decode(File::get($this->positionsFile), true)['user_positions'] ?? [];

        $topPosition = null;

        foreach ($items as $item) {
            $mappedDeps = [];
            foreach ($this->decodeDepIds($item['dep_ids'] ?? null) as $depId) {
                if (isset($depIdMap[(int) $depId])) {
                    $mappedDeps[] = (string) $depIdMap[(int) $depId];
                }
            }

            $position = UserPosition::create([
                'org_id'       => $organization->id,
                'name_uz'      => $item['name_uz'] ?? '',
                'name_kr'      => $item['name_kr'] ?? '',
                'name_ru'      => $item['name_ru'] ?? '',
                'name_en'      => $item['name_en'] ?? '',
                'order_number' => $item['order_number'] ?? 1,
                'dep_ids'      => json_encode($mappedDeps),
                'status'       => $item['status'] ?? 1,
            ]);

            if ($topPosition === null) {
                $topPosition = $position;
            }
        }

        return $topPosition;
    }

    /**
     * dep_ids в seed-файле хранятся строкой вида "[\"3\",\"4\"]"
     * (или уже массивом) — приводим к простому массиву.
     */
    private function decodeDepIds($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
