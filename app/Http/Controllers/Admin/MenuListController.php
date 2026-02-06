<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\IconClassList;
use App\Models\Admin\MenuList;
use App\Models\Admin\RoleMenu;
use App\Services\LanguageService;
use Illuminate\Http\Request;

class MenuListController extends Controller
{
    //
    public function __construct(protected LanguageService $languageService)
    {
        /*$this->middleware('permission:role-list', ['only' => ['index']]);
        $this->middleware('permission:role-create', ['only' => ['create','store','addPermissionToRole','givePermissionToRole']]);
        $this->middleware('permission:role-edit', ['only' => ['update','edit']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);*/
    }

    public function index()
    {
        //
        $models = MenuList::orderBy('created_at', 'DESC')->paginate(25);

        $parentMenu = MenuList::where('parent_id', 0)->orderBy('created_at', 'DESC')->get();

        $languages = $this->languageService->getList();

        // Type 1-Icon, 2-Badge
        $icons = IconClassList::where('type', 2)->where('status', 1)->get();

        return view('admin.menus.menu-list', compact('models', 'parentMenu', 'languages', 'icons'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function search(Request $request)
    {
        //
        $input = $request->input('input');

        $models = MenuList::where('title', 'like', '%'.$input.'%')
            ->orWhere('url_path', 'like', '%'.$input.'%')
            ->orWhere('code', 'like', '%'.$input.'%')
            /*->orWhereHas('coreMenu', function($query) use($input){
                $query->where('title','like', '%'.$input.'%');
            })*/
            ->orderBy('created_at', 'DESC')
            ->paginate(25);

        $parentMenu = MenuList::where('parent_id', 0)->orderBy('created_at', 'DESC')->get();

        $models->appends( array ('input' => $input) );

        $languages = $this->languageService->getList();

        $icons = IconClassList::where('type', 2)->where('status', 1)->get();;

        return view('admin.menus.menu-list',
            compact('models','input', 'parentMenu', 'languages', 'icons'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'title'     => 'required',
            'url_path'      => 'required',
        ]);

        $store = new MenuList();
        $store->title = $request->title;
        $store->url_path = $request->url_path;
        $store->code = $request->code;
        $store->parent_id = $request->parent_id??0;
        $store->lang_id = $request->lang_id;
        $store->icon_id = $request->icon_id;
        $store->count = $request->count;
        $store->save();

        return response()->json([
            'status' => 200,
            'message' => 'Ok'
        ]);

    }

    public function edit($id)
    {
        //
        $model = MenuList::findOrFail($id);

        $languages = $this->languageService->getList();

        $iconClassList = IconClassList::where('type', 2)->where('status', 1)->get();

        return response()->json([
            'model' => $model,
            'languages' => $languages,
            'iconClassList' => $iconClassList
        ]);
    }

    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'title'     => 'required',
            'url_path'      => 'required'
        ]);

        $model = MenuList::find($id);

        if ($model) {
            $model->update([
                'title' => $request->title,
                'url_path' => $request->url_path,
                'code' => $request->code,
                'parent_id' => $request->parent_id??0,
                'lang_id' => $request->lang_id,
                'icon_id' => $request->icon_id,
                'count' => $request->count,
                'status' => $request->status
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Ok'
        ]);
    }

    public function destroy($id)
    {
        //
        $ids = explode(",",$id);

        $model = MenuList::whereIn('id',$ids)->get();

        $role_menus = RoleMenu::whereIn('menu_id', $ids)->get();

        if($role_menus->count() > 0){
            RoleMenu::whereIn('menu_id', $ids)->delete();
        }

        if($model->count() > 0){

            MenuList::destroy(collect($ids));

        }

        return response()->json([
            'status' => 200,
            'message' => 'Ok'
        ]);
    }
}
