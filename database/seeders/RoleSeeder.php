<?php

namespace Database\Seeders;

use App\Models\Admin\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('menu_lists')->truncate();
        DB::table('role_menus')->truncate();

        $jsonFile = base_path('database/seeders/data/roles.json');
        $jsonData = json_decode(File::get($jsonFile), true);
        foreach ($jsonData['roles'] as $role) {
            Role::create([
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
                'lang_id' => $role['lang_id'],
                'status' => $role['status'],
            ]);
        }

        // menu_lists ni id lar bilan insert qilamiz
        $menus = json_decode(file_get_contents(database_path('seeders/data/menu_list.json')), true)['menu_lists'];
        foreach ($menus as $menu) {
            DB::table('menu_lists')->insert($menu); // id ni saqlaydi
        }

        $roleMenuIdMap = [];
        $roleMenus = json_decode(file_get_contents(database_path('seeders/data/role_menu.json')), true)['role_menus'];
        foreach ($roleMenus as $menu) {
            if ($menu['parent_id'] === 0) {
                $newId = DB::table('role_menus')->insertGetId($menu);
                $roleMenuIdMap[$menu['id']] = $newId;
                //print_r($roleMenuIdMap); die;
            }
        }

        // Endi parent_id != 0 bo'lganlarni joylaymiz
        foreach ($roleMenus as $menu) {
            if ($menu['parent_id'] !== 0) {
                $oldParentId = $menu['parent_id'];
                $menu['parent_id'] = $roleMenuIdMap[$oldParentId] ?? 0;
                $newId = DB::table('role_menus')->insertGetId($menu);
                $roleMenuIdMap[$menu['id']] = $newId;
            }
        }

        // Sequence ni MAX(id) ga moslab o'rnatish
        //DB::statement("SELECT setval('role_menus_id_seq', (SELECT MAX(id) FROM role_menus))");
    }
}
