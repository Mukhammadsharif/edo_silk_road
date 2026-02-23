<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\IconClassList;
use App\Models\Admin\Role;
use App\Models\Admin\StatusList;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusListController extends Controller
{
    //
    public function __construct(protected LanguageService $languageService)
    {
        /*$this->middleware('permission:role-list', ['only' => ['index']]);
        $this->middleware('permission:role-create', ['only' => ['create','store','addPermissionToRole','givePermissionToRole']]);
        $this->middleware('permission:role-edit', ['only' => ['update','edit']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);*/
    }

    public function index(Request $request)
    {
        //
        $s_text = $request->input('s_text');

        $s_type = $request->input('s_type');

        $search = StatusList::orderBy('order_number');

        $types = StatusList::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        if (trim(''.$s_text.'')) {
            $search->where('title', 'ILIKE', "%{$s_text}%");
        }

        if ($s_type){
            $search->where('type', $s_type);
        }

        $models = $search->paginate(25);

        $models->appends([
            's_text' => $s_text,
            's_type' => $s_type,
        ]);

        return view('admin.status-lists.index', compact('models', 's_text', 's_type', 'types'));
    }

    public function create()
    {
        //
        $languages = $this->languageService->getList();

        $iconClassList = IconClassList::where('status', 1)->get();

        return view('admin.status-lists.create', compact('languages', 'iconClassList'));
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            'title' => 'required|max:50',
            'type' => 'required|max:20',
            'code' => 'required',
        ]);

        StatusList::create($request->all());

        return redirect()->route('status-lists.index')
            ->with('success','StatusName created successfully.');
    }

    public function edit($id)
    {
        //
        $model = StatusList::findOrFail($id);

        $languages = $this->languageService->getList();

        $iconClassList = IconClassList::where('status', 1)->get();

        return view('admin.status-lists.edit',compact('model', 'languages', 'iconClassList'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'title' => 'required|max:50',
            'type' => 'max:20',
        ]);

        $model = StatusList::findOrFail($id);

        $model->update([
            'title' => $request->title,
            'lang_id' => $request->lang_id,
            'type' => $request->type,
            'code' => $request->code,
            'icon_id' => $request->icon_id,
            'class_id' => $request->class_id,
            'order_number' => $request->order_number
        ]);

        return redirect()->route('status-lists.index')
            ->with('success','StatusName updated successfully');
    }

    public function destroy($id)
    {
        //
    }

    public function fetchStatusList()
    {
        $models = StatusList::orderBy('type')->paginate(25);

        return response()->json([
            'models'=>$models,
        ]);

    }

    public function search($query)
    {
        $models = StatusList::where('title', 'ILIKE', '%'.$query.'%')
            ->orWhere('type', 'ILIKE', '%'.$query.'%')
            ->orderBy('type')
            ->paginate(25);

        return response()->json([
            'status'=>200,
            'models'=> $models,
        ]);
    }
}
