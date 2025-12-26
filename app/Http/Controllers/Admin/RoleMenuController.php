<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\MenuList;
use App\Models\Admin\Role;
use App\Models\Admin\RoleMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleMenuController extends Controller
{
    //
    public function index()
    {
        //
        $models = RoleMenu::orderBy('role_id')->orderBy('menu_type', 'DESC')->orderBy('order_number')->paginate(25);

        $roles = Role::where('status', 1)->get();

        $menuLists = MenuList::where('status', 1)->orderBy('title')->get();

        $role_id = 0;

        return view('admin.menus.role-menu', compact('models','menuLists', 'roles', 'role_id'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function search(Request $request)
    {
        //
        $text = $request->input('text');

        $role_id = $request->input('user_role')??0;

        $menuLists = MenuList::orderBy('title')->get();

        $roles = Role::where('status', 1)->get();

        $search = RoleMenu::orderBy('parent_id')->orderBy('order_number');

        if ($text) {
            $search->whereHas('menuList', function($query) use($text){
                $query->where('title','like', '%'.$text.'%')->orWhere('url_path','like', '%'.$text.'%');
            });
        }

        if ($role_id) {
            $search->where(function ($query) use ($role_id){
                $query->where('role_id', $role_id);
            });
        }

        $models = $search->paginate(25)->appends ( array ('text' => $text) );

        return view('admin.menus.role-menu',
            compact('models','menuLists','roles','text', 'role_id'))
            ->with('i', (request()->input('page', 1) - 1) * 25);

    }

    public function getParent($id)
    {
        //
        $model = DB::table('role_menus as a')
            ->join('roles as r', 'a.role_id', 'r.id')
            ->join('menu_lists as ml', 'a.menu_id', 'ml.id')
            ->select('a.id', 'ml.title')
            ->where('a.role_id', $id)->where('a.menu_type', 2)
            ->get();

        $message = 'Success';

        return response()->json(['message' => $message, 'model' => $model], 200);

    }

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'role_id'      => 'required',
            'menu_id'      => 'required',
            //'menu_type'    => 'required',
            //'parent_id'    => 'required',
            'order_number' => 'required',
            //'status'       => 'required'
        ]);

        RoleMenu::create($request->all());

        return response()->json([
            'status' => 200,
            'message' => 'Ok'
        ]);

    }

    public function edit($id)
    {
        //
        $model = RoleMenu::findOrFail($id);

        $parent = DB::table('role_menus as a')
            ->join('menu_lists as ml', 'a.menu_id', 'ml.id')
            ->select('a.id', 'ml.title')
            ->where('a.role_id', $model->role_id)->where('a.menu_type', 2)
            ->get();

        return response()->json(['model' => $model, 'parent' => $parent], 200);

    }

    public function update(Request $request, $id)
    {
        //
        $this->validate($request, [
            'role_id'   => 'required',
            'menu_id'   => 'required',
            'menu_type' => 'required',
            'order_number'      => 'required',
        ]);

        $model = RoleMenu::find($id);

        if ($model) {
            $model->update([
                'role_id' => $request->role_id,
                'menu_id' => $request->menu_id,
                'menu_type' => $request->menu_type,
                'order_number' => $request->order_number,
                'parent_id' => $request->parent_id,
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

        $model = RoleMenu::whereIn('id',$ids)->get();

        if($model->count() > 0){

            foreach ($model as $item) {
                $child = RoleMenu::where('parent_id', $item->id)->get();
                if ($child->count() > 0) {
                    foreach ($child as $i) {
                        $i->delete();
                    }
                }
            }

            RoleMenu::destroy(collect($ids));

        }

        return response()->json([
            'status' => 200,
            'message' => 'Ok'
        ]);
    }
}
