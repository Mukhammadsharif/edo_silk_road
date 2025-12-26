<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Permission::truncate();

        // Path to your JSON file
        $jsonFile = base_path('database/seeders/data/permissions.json');

        // Read and decode the JSON file
        $jsonData = json_decode(File::get($jsonFile), true);

        foreach ($jsonData['rows'] as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'guard_name' => $permission['guard_name'],
                'lang_id' => $permission['lang_id'],
                'parent_id' => $permission['parent_id']
            ]);
        }
    }
}
