<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin\HR\HrEmpExperience;
use App\Models\User;
use Illuminate\Http\Request;

class HrEmpExpController extends Controller
{
    //
    public function store(Request $request)
    {
        //
        $request->validate([
            'user_id' => 'required',
            'exp_name' => 'required',
            'post_name' => 'required',
            'from_date' => 'required'
        ]);

        $user = User::findOrFail($request->user_id);

        if (strtotime($request->from_date) > strtotime($request->to_date??now())) {
            return back()->withErrors(['msg' => 'Ish davr oralig`i to`g`ri emas']);
        }

        $isOrg = 'N';
        if ($request->is_org) {
            $isOrg = 'Y';
        }

        $create = new HrEmpExperience();
        $create->exp_name = $request->exp_name;
        $create->post_name = $request->post_name;
        $create->is_org = $isOrg;
        $create->from_date = date('Y-m-d', strtotime($request->from_date));
        if ($request->to_date){
            $create->to_date = date('Y-m-d', strtotime($request->to_date));
        }
        $create->exp_descr = $request->exp_descr;
        $create->user_id = $request->user_id;
        $create->created_id = $this->authActiveUser()->id;
        $create->save();

        return redirect()->route('hr.emp.exp.view', [$user->id, $user->user_hash])
            ->with('success', __('message.task_completed_successfully'));
    }

    public function show($id)
    {
        //
        $user = User::findOrFail($id);

        return view('hr.admin.emp-exp.create', compact('user', 'id'));
    }

    public function edit($id)
    {
        //
        $model = HrEmpExperience::findOrFail($id);

        $user = User::findOrFail($model->user_id);

        return view('hr.admin.emp-exp.edit',compact('model', 'user'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'exp_name' => 'required',
            'from_date' => 'required'
        ]);

        if (strtotime($request->from_date) > strtotime($request->to_date??now())) {
            return back()->withErrors(['msg' => 'Ish davr oralig`i to`g`ri emas']);
        }

        $isOrg = 'N';
        if ($request->is_org) {
            $isOrg = 'Y';
        }

        $model = HrEmpExperience::findOrFail($id);

        $user = User::findOrFail($model->user_id);

        $to_date = null;
        if ($request->to_date) {
            $to_date = date('Y-m-d', strtotime($request->to_date));
        }

        $model->update([
            'exp_name' => $request->exp_name,
            'post_name' => $request->post_name,
            'is_org' => $isOrg,
            'exp_descr' => $request->exp_descr,
            'from_date' => date('Y-m-d', strtotime($request->from_date)),
            'to_date' => $to_date,
            'created_id' => $this->authActiveUser()->id,
            'isActive' => $request->isActive
        ]);

        return redirect()->route('hr.emp.exp.view', [$user->id, $user->user_hash])
            ->with('success', __('message.doc_updated_success_message'));
    }
}
