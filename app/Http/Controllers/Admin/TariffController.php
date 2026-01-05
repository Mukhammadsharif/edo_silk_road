<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Tariffs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TariffController extends Controller
{
    //
    public function index()
    {
        $models = Tariffs::orderBy('id', 'DESC')->paginate(20);

        return view('admin.tariffs.index', compact('models'));
    }

    public function create()
    {
        return view('admin.tariffs.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'            => 'required|string|max:100|unique:tariffs,code',
            'name'            => 'required|string|max:255',
            'max_users'       => 'required|integer|min:1',
            'max_documents'   => 'required|integer|min:1',
            'price_monthly'   => 'required|integer|min:0',
            'price_yearly'     => 'required|integer|min:0',
            'status'          => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Tariffs::create($request->all());

        return redirect()->route('tariffs.index')
            ->with('success', 'Tarif muvaffaqiyatli qo\'shildi.');
    }

    public function edit($id)
    {
        $model = Tariffs::findOrFail($id);

        return view('admin.tariffs.edit', compact('model'));
    }

    public function update(Request $request, $id)
    {
        $tariff = Tariffs::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code'            => 'required|string|max:100|unique:tariffs,code,' . $id,
            'name'            => 'required|string|max:255',
            'max_users'       => 'required|integer|min:1',
            'max_documents'   => 'required|integer|min:1',
            'price_monthly'   => 'required|integer|min:0',
            'price_yearly'     => 'required|integer|min:0',
            'status'          => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $tariff->update($request->all());

        return redirect()->route('tariffs.index')
            ->with('success', 'Tarif muvaffaqiyatli yangilandi.');
    }

    public function destroy($id)
    {
        $tariff = Tariffs::findOrFail($id);
        $tariff->delete();

        return redirect()->route('tariffs.index')
            ->with('success', 'Tarif muvaffaqiyatli o\'chirildi.');
    }

    public function show($id)
    {
        return redirect()->route('tariffs.edit', $id);
    }
}
