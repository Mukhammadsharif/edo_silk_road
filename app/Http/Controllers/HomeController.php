<?php

namespace App\Http\Controllers;

use App\Models\Admin\RoleMenu;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Document\DocumentJournals;
use App\Models\Journal\JournalTypes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        //
        $authRoleName = $this->authActiveUserRoleNames();

        $doc_exec_menu_id = 0;
        $doc_sign_menu_id = 0;
        $doc_approve_menu_id = 0;
        $my_work_docs_menu_id = 0;
        $my_in_docs_menu_id = 0;
        $my_out_docs_menu_id = 0;
        $doc_conf_menu_id = 0;

        $sidebarMenus = Auth::user()->sidebarMenus();
        if ($sidebarMenus) {
            foreach ($sidebarMenus as $menu) {
                if ($menu->child) {
                    foreach ($menu->child as $child) {
                        if (isset($child->menuList->code)) {
                            if ($child->menuList->code == 'DOC_EXEC') {
                                $doc_exec_menu_id = $child->menuCount($child->id);
                            } elseif ($child->menuList->code == 'DOC_SIGN') {
                                $doc_sign_menu_id = $child->menuCount($child->id);
                            } elseif ($child->menuList->code == 'DOC_APPROVE') {
                                $doc_approve_menu_id = $child->menuCount($child->id);
                            } elseif ($child->menuList->code == 'OF_W_DOC') {
                                $my_work_docs_menu_id = $child->menuCount($child->id);
                            } elseif ($child->menuList->code == 'OF_OUT_DOC') {
                                $my_out_docs_menu_id = $child->menuCount($child->id);
                            } elseif ($child->menuList->code == 'OF_IN_DOC') {
                                $my_in_docs_menu_id = $child->menuCount($child->id);
                            } elseif ($child->menuList->code == 'DOC_CONF') {
                                $doc_conf_menu_id = $child->menuCount($child->id);
                            }
                        }
                    }
                }
            }
        }

        $my_docs_menu_id = $my_work_docs_menu_id + $my_out_docs_menu_id + $my_in_docs_menu_id;

        $docs_count = [
            'doc_exec_menu_id' => $doc_exec_menu_id,
            'doc_sign_menu_id' => $doc_sign_menu_id,
            'doc_approve_menu_id' => $doc_approve_menu_id,
            'doc_conf_menu_id' => $doc_conf_menu_id,
            'my_docs_menu_id' => $my_docs_menu_id,
        ];

        if ($this->isSuperUser()) {
            $users = User::all();
            $totalEmpCount = $users->count();
            $activeEmpCount = $users->where('status', 1)->count();
            $deletedEmpCount = $users->where('status', 3)->count();
        } else {
            $users = User::where('org_unit_id', $this->organizationId());
            $totalEmpCount = $users->count();
            $activeEmpCount = $users->where('status', 1)->count();
            $deletedEmpCount = $users->where('status', 3)->count();
        }

        $employees = [
            'totalEmpCount' => $totalEmpCount,
            'activeEmpCount' => $activeEmpCount,
            'deletedEmpCount' => $deletedEmpCount
        ];

        $isOwner = $this->isOwner();

        $app_temps = JournalTypes::where('doc_code', 'ILIKE', 'APP_%')->where('parent_id', '<>', 0)->where('level_number', 2)->where('status', 1)->get();
        $pendingSubscription = Auth::user()->organization->pendingSubscription ?? null;

        return view('home', compact('authRoleName', 'docs_count', 'employees', 'app_temps', 'isOwner', 'pendingSubscription'));
    }

    public function lang($locale)
    {
        App::setLocale($locale);

        session()->put('locale', $locale);

        $updateUserLocale = UserPersonalInfo::where('user_id', Auth::user()->id)->first();

        if ($updateUserLocale) {
            $updateUserLocale->update(['lang_code' => $locale]);
        }

        return redirect()->back();
    }

    public function chartDocuments()
    {
        $process = DocumentJournals::where('status', 9)->count();

        $now = Carbon::now()->format('Y-m-d');

        $ensured = DocumentJournals::where('status', 11)->count();

        $process_end = DocumentJournals::where('status', 10)->count();

        $expired = DocumentJournals::whereHas('doc', function ($query) use ($now) {
            $query->where('doc_term', '<', $now)->whereNotIn('status', [2, 9, 10, 11]);
        })->whereNotIn('status', [1, 4, 13])->count();

        $all = DocumentJournals::whereNotIn('status', [1, 4])->count();

        $all_documents = $all;

        return response()->json([
            'process' => $process,
            'ensured' => $ensured,
            'process_end' => $process_end,
            'expired' => $expired,
            'all_documents' => $all_documents
        ]);
    }

    public function getMenuCount(Request $request)
    {
        $id = $request->id;

        $menu = RoleMenu::find($id);
        $count = 0;

        if ($menu) {
            if ($menu->child) {
                foreach ($menu->child as $child) {
                    $count += $child->menuCount($child->id);
                }
            }
            /*if (in_array($menu->menuList->code, ['KOL_ORG', 'KOL_ORG_S'])) {
                $element = $menu->getKolOrg($menu->menu_id);

                if ($element && $element->child) {
                    $method = ($menu->menuList->code === 'KOL_ORG') ? 'menuCountMem' : 'menuCount';
                    $count = $this->recursiveMenuCount($element, $method);
                }
            }

            else if ($menu->child) {
                foreach ($menu->child as $child) {
                    $count += $child->menuCount($child->id);
                }
            }*/
        }

        return response()->json(['count' => $count, 'success' => $count ? 200 : 201]);
    }

    private function recursiveMenuCount($element, $method)
    {
        $count = 0;

        foreach ($element->child as $child) {
            $count += $child->$method($child->id);

            if ($child->child) {
                $count += $this->recursiveMenuCount($child, $method);
            }
        }

        return $count;
    }

}
