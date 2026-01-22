<?php

namespace Database\Seeders;

use App\Models\Admin\IconClassList;
use App\Models\Admin\StatusList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class IconListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        IconClassList::truncate();
        $jsonFile = base_path('database/seeders/data/icon_list.json');
        $jsonData = json_decode(File::get($jsonFile), true);
        foreach ($jsonData['icon_class_lists'] as $role) {
            DB::table('icon_class_lists')->insert($role); // id ni saqlaydi
        }

        StatusList::truncate();
        $jsonFile1 = base_path('database/seeders/data/status_list.json');
        $jsonData1 = json_decode(File::get($jsonFile1), true);
        foreach ($jsonData1['status_lists'] as $role1) {
            StatusList::create([
                'title' => $role1['title'],
                'lang_id' => $role1['lang_id'],
                'type' => $role1['type'],
                'code' => $role1['code'],
                'icon_id' => $role1['icon_id'],
                'class_id' => $role1['class_id'],
                'order_number' => $role1['order_number'],
            ]);
        }
    }
}
