<?php

namespace Database\Seeders;

use App\Models\Admin\Department;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserPosition;
use App\Models\Admin\UserWorkHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        //
        Department::truncate();
        $jsonFile1 = base_path('database/seeders/data/departments.json');
        $jsonData1 = json_decode(File::get($jsonFile1), true);
        foreach ($jsonData1['departments'] as $item1) {
            Department::create([
                'code' => $item1['code'],
                'name_uz' => $item1['name_uz'],
                'name_kr' => $item1['name_kr'],
                'name_ru' => $item1['name_ru'],
                'name_en' => $item1['name_en'],
                'parent_id' => $item1['parent_id'],
                'order_number' => $item1['order_number'],
                'level_number' => $item1['level_number'],
                'status' => $item1['status'],
            ]);
        }

        UserPosition::truncate();
        $jsonFile2 = base_path('database/seeders/data/user_position.json');
        $jsonData2 = json_decode(File::get($jsonFile2), true);
        foreach ($jsonData2['user_positions'] as $item2) {
            UserPosition::create([
                'name_uz' => $item2['name_uz'],
                'name_kr' => $item2['name_kr'],
                'name_ru' => $item2['name_ru'],
                'name_en' => $item2['name_en'],
                'order_number' => $item2['order_number'],
                'dep_ids' => $item2['dep_ids'],
                'status' => $item2['status'],
            ]);
        }

        User::truncate();
        UserPersonalInfo::truncate();
        UserWorkHistory::truncate();

        // Path to your JSON file
        $jsonFile = base_path('database/seeders/data/users.json');

        // Read and decode the JSON file
        $jsonData = json_decode(File::get($jsonFile), true);

        foreach ($jsonData['rows'] as $role) {
            // Super Admin
            $user = User::create([
                'username' => $role['username'],
                'email' => $role['email'],
                'password' => Hash::make($role['password']),
                'user_hash' => $this->uniqueId(),
                'status' => $role['status']
            ]);

            if ($user) {
                $this->createPersonal($user->id, $role['f_name']);

                $this->createUserWork($user->id, $role['dep_id'], $role['post_id']);

                if ($role['username'] == 'super_admin') {
                    $superAdminRole = Role::where('name', 'Super_Admin')->first();
                    if ($superAdminRole) {
                        $superAdminPermissions = Permission::pluck('id','id')->all();
                        $superAdminRole->syncPermissions($superAdminPermissions);
                    }
                    $user->assignRole([$superAdminRole->id]);

                    $itAdminRole = Role::where('name', 'Dep_Emp')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'it_admin') {
                    $itAdminRole = Role::where('name', 'IT_Admin')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                    $itAdminRole = Role::where('name', 'Dep_Emp')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'hr_admin') {
                    $itAdminRole = Role::where('name', 'HR_Admin')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                    $itAdminRole = Role::where('name', 'Dep_Head')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'office_emp') {
                    $itAdminRole = Role::where('name', 'Office_Emp')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'control_emp') {
                    $itAdminRole = Role::where('name', 'Control_Emp')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                    $itAdminRole = Role::where('name', 'Dep_Emp')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'director') {
                    $itAdminRole = Role::where('name', 'Director')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'deputy_director') {
                    $itAdminRole = Role::where('name', 'Deputy_Director')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'dep_head') {
                    $itAdminRole = Role::where('name', 'Dep_Head')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                } elseif ($role['username'] == 'dep_emp') {
                    $itAdminRole = Role::where('name', 'Dep_Emp')->first();
                    if ($itAdminRole) {
                        $user->assignRole([$itAdminRole->id]);
                    }
                }
            }
        }
    }

    public function createPersonal($user_id, $user_name): true
    {
        //
        $officeEmpPersonal = new UserPersonalInfo();
        $officeEmpPersonal->user_id = $user_id;
        $officeEmpPersonal->is_emp = 2;
        $officeEmpPersonal->doc_pin = 12345678912345;
        $officeEmpPersonal->doc_number = 'AD1234567';
        $officeEmpPersonal->doc_type = 2;
        $officeEmpPersonal->birthday = '1991-01-01';
        $officeEmpPersonal->gender = 1;
        $officeEmpPersonal->phone = 971253456;
        $officeEmpPersonal->l_name = $user_name;
        $officeEmpPersonal->f_name = $user_name;
        $officeEmpPersonal->m_name = $user_name;
        $officeEmpPersonal->lang_code = 'uz';
        $officeEmpPersonal->doc_sign_type = 1;
        $officeEmpPersonal->sort = 1;
        $officeEmpPersonal->save();

        return true;
    }

    public function createUserWork($user_id, $dep_id, $post_id): true
    {
        //
        $nextYear = Carbon::now()->addYear()->format('Y-m-d');

        $officeEmpWork = new UserWorkHistory();
        $officeEmpWork->user_id = $user_id;
        $officeEmpWork->dep_id = $dep_id;
        $officeEmpWork->post_id = $post_id;
        $officeEmpWork->from_date = Carbon::now();
        $officeEmpWork->to_date = $nextYear;
        $officeEmpWork->status = 1;
        $officeEmpWork->save();

        return true;
    }

    public function uniqueId(): string
    {
        return (string) Uuid::uuid4();
    }
}
