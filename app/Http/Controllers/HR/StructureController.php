<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\UserWorkHistory;

class StructureController extends Controller
{
    //
    public function structure()
    {
        //
        $departments = Department::where('parent_id', 0)->where('isActive', 'A')->orderBy('dep_order')->get();

        return view('hr.structure', compact('departments'));
    }

    public function getUsers($dep_id)
    {
        $users = UserWorkHistory::with('personal', 'userPost')
            ->where('isActive', 'A')->where('dep_id', $dep_id)->get();

        return response()->json($users);
    }

}
