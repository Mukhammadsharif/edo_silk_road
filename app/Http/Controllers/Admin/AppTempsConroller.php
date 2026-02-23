<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AppTemps;
use Illuminate\Http\Request;

class AppTempsConroller extends Controller
{
    public function index()
    {
        //
        $models = AppTemps::where('isActive', 'A')->latest()->get();

        return view('admin.app-temps.index', compact('models'));

    }

    public function create()
    {
        //
        return view('admin.app-temps.create');
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            //'t_code' => 'required|unique:app_temps|max:20',
            't_name' => 'required|max:100',
            't_text' => 'required',
        ]);

        $model = AppTemps::latest()->first();
        $code = 1;
        if ($model) {
            $code = $model->count() + 1;
        }

        $tCode = str_pad($code, 4, '0', STR_PAD_LEFT);

        $create = new AppTemps();
        $create->t_code = $tCode;
        $create->t_name = $request->t_name;
        $create->t_text = $request->t_text;
        $create->created_id = $this->authActiveUser()->id;
        $create->save();

        return redirect()->route('app-temps.index')
            ->with('success', __('message.task_completed_successfully'));
    }

    public function show($id)
    {
        //
        $model = AppTemps::findOrFail($id);

        return view('admin.app-temps.show',compact('model'));
    }

    public function edit($id)
    {
        //
        $model = AppTemps::findOrFail($id);

        return view('admin.app-temps.edit',compact('model'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            't_name' => 'required|max:100',
            't_text' => 'required',
        ]);
        $isActive = $request->isActive??'P';

        $model = AppTemps::findOrFail($id);

        $model->update([
            //'post_code' => $request->post_code,
            't_name' => $request->t_name,
            't_text' => $request->t_text,
            'isActive' => $isActive
        ]);

        return redirect()->route('app-temps.index')
            ->with('success', __('message.task_completed_successfully'));
    }

    public function destroy($id)
    {
        //
    }
}
