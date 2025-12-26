<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin\HR\HrEmpFamily;
use App\Models\Admin\StatusList;
use App\Models\User;
use Illuminate\Http\Request;

class HrEmpFamController extends Controller
{
    //
    public function store(Request $request)
    {
        //
        $request->validate([
            'user_id' => 'required',
            'fam_code' => 'required',
            'fam_name' => 'required',
            'br_address' => 'required',
            'live_address' => 'required',
            'post_address' => 'required',
            'br_date' => 'required',
        ]);

        $user = User::findOrFail($request->user_id);

        $create = new HrEmpFamily();
        $create->user_id = $request->user_id;
        $create->fam_code = $request->fam_code;
        $create->fam_name = $request->fam_name;
        $create->br_address = $request->br_address;
        $create->live_address = $request->live_address;
        $create->post_address = $request->post_address;
        $create->post_name = $request->post_name;
        $create->br_date = date('Y-m-d', strtotime($request->br_date));
        $create->created_id = $this->authActiveUser()->id;
        $create->save();

        return redirect()->route('hr.emp.fam.view', [$user->id, $user->user_hash])
            ->with('success','Data created successfully.');
    }

    public function show($id)
    {
        //
        $user = User::findOrFail($id);

        $familyList = StatusList::where('type', 'hr_emp_fam_name')->orderBY('code')->get();

        return view('hr.admin.emp-family.create', compact('user', 'id', 'familyList'));
    }

    public function edit($id)
    {
        //
        $model = HrEmpFamily::findOrFail($id);

        $user = User::findOrFail($model->user_id);

        $familyList = StatusList::where('type', 'hr_emp_fam_name')->orderBY('code')->get();

        return view('hr.admin.emp-family.edit',compact('model', 'user', 'familyList'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'fam_code' => 'required',
            'fam_name' => 'required',
            'br_address' => 'required',
            'live_address' => 'required',
            'post_address' => 'required',
            'br_date' => 'required',
        ]);

        $isActive = $request->isActive??'P';

        $model = HrEmpFamily::findOrFail($id);

        $user = User::findOrFail($model->user_id);

        $model->update([
            'fam_code' => $request->fam_code,
            'fam_name' => $request->fam_name,
            'br_address' => $request->br_address,
            'live_address' => $request->live_address,
            'post_address' => $request->post_address,
            'post_name' => $request->post_name,
            'br_date' => date('Y-m-d', strtotime($request->br_date)),
            'created_id' => $this->authActiveUser()->id,
            'isActive' => $isActive
        ]);

        return redirect()->route('hr.emp.fam.view', [$user->id, $user->user_hash])
            ->with('success','Data updated successfully.');
    }
}
