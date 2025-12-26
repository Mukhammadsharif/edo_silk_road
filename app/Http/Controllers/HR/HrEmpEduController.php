<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin\HR\HrEmpEducation;
use App\Models\User;
use Illuminate\Http\Request;

class HrEmpEduController extends Controller
{
    //
    public function store(Request $request)
    {
        //
        $request->validate([
            'user_id' => 'required',
            'edu_name' => 'required',
            'fac_name' => 'required',
            'spec_name' => 'required',
            'dip_name' => 'required',
            'from_date' => 'required'
        ]);

        $user = User::find($request->user_id);

        if (($request->from_date > $request->to_date) && $request->to_date) {

            return back()->withErrors(['msg' => 'Ta`lim davr oralig`i to`g`ri emas!']);

        }

        $from_date = $request->from_date.'-01-01';

        if ($request->to_date) {
            $to_date = $request->to_date.'-12-31';
        } else {
            $to_date = null;
        }

        $create = new HrEmpEducation();
        $create->edu_code = $request->user_id;
        $create->edu_name = $request->edu_name;
        $create->fac_name = $request->fac_name;
        $create->spec_name = $request->spec_name;
        $create->dip_name = $request->dip_name;
        $create->from_date = $from_date;
        $create->to_date = $to_date;
        $create->user_id = $request->user_id;
        $create->created_id = $this->authActiveUser()->id;
        $create->save();

        return redirect()->route('hr.emp.edu.view', [$user->id, $user->user_hash])
            ->with('success','Data created successfully.');
    }

    public function show($id)
    {
        //
        $user = User::findOrFail($id);

        return view('hr.admin.emp-education.create', compact('user', 'id'));
    }

    public function edit($id)
    {
        //
        $model = HrEmpEducation::findOrFail($id);
        $user = User::findOrFail($model->user_id);

        return view('hr.admin.emp-education.edit',compact('model', 'user'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'edu_name' => 'required',
            'fac_name' => 'required',
            'spec_name' => 'required',
            'dip_name' => 'required',
            'from_date' => 'required'
        ]);

        if (($request->from_date > $request->to_date) && $request->to_date) {

            return back()->withErrors(['msg' => 'Ta`lim davr oralig`i to`g`ri emas!']);

        }

        $from_date = $request->from_date.'-01-01';

        if ($request->to_date) {
            $to_date = $request->to_date.'-12-31';
        } else {
            $to_date = null;
        }

        $isActive = $request->isActive??'P';

        $model = HrEmpEducation::findOrFail($id);

        $user = User::find($model->user_id);

        $model->update([
            'edu_name' => $request->edu_name,
            'fac_name' => $request->fac_name,
            'spec_name' => $request->spec_name,
            'dip_name' => $request->dip_name,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'created_id' => $this->authActiveUser()->id,
            'isActive' => $isActive
        ]);

        return redirect()->route('hr.emp.edu.view', [$user->id, $user->user_hash])
            ->with('success','Data updated successfully.');
    }
}
