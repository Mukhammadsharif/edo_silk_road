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
use Illuminate\Support\Facades\DB;

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
            $users = User::inOrganization($this->organizationId());
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
        $pendingSubscription = Auth::user()->organization?->pendingSubscription;

        // ── Дашборд руководителя: только для роли Director (или владельца организации) ──
        $isDirector = in_array('Director', $authRoleName) || $isOwner;
        $directorStats = null;
        if ($isDirector) {
            $directorStats = $this->buildDirectorStats($this->organizationId());
        }

        return view('home', compact('authRoleName', 'docs_count', 'employees', 'app_temps', 'isOwner', 'pendingSubscription', 'isDirector', 'directorStats'));
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

    /**
     * Сводная аналитика по документообороту организации для дашборда руководителя.
     * Все счётчики считаются по всей организации (org_unit_id), а не по одному пользователю,
     * чтобы Ген. директор видел полную картину, а не только свои документы.
     */
    private function buildDirectorStats($orgId)
    {
        $today = Carbon::now()->format('Y-m-d');

        // ── 1. Документы по стадиям процесса (в разрезе роли исполнения) ──
        // На исполнении (EXEC)
        $stageExec = DocumentJournals::where('org_unit_id', $orgId)
            ->whereHas('executionSteps', function ($q) {
                $q->whereIn('status', [2, 4])->whereHas('role', function ($r) {
                    $r->where('doc_code', 'EXEC');
                });
            })->where('status', '>', 1)->count();

        // На согласовании (AGREE)
        $stageAgree = DocumentJournals::where('org_unit_id', $orgId)
            ->whereHas('executionSteps', function ($q) {
                $q->where('status', 2)->whereHas('role', function ($r) {
                    $r->where('doc_code', 'AGREE');
                });
            })->where('status', '>', 1)->count();

        // На подписи (SIGN)
        $stageSign = DocumentJournals::where('org_unit_id', $orgId)
            ->whereHas('executionSteps', function ($q) {
                $q->where('status', 2)->whereHas('role', function ($r) {
                    $r->where('doc_code', 'SIGN');
                });
            })->where('status', '>', 1)->count();

        // На резолюции (RESOL)
        $stageResol = DocumentJournals::where('org_unit_id', $orgId)
            ->whereHas('executionSteps', function ($q) {
                $q->whereHas('role', function ($r) {
                    $r->where('doc_code', 'RESOL');
                });
            })->whereIn('status', [8, 11])->count();

        // ── 2. Документы по статусам (жизненный цикл) ──
        $statusDraft   = DocumentJournals::where('org_unit_id', $orgId)->where('status', 1)->count();
        $statusProcess = DocumentJournals::where('org_unit_id', $orgId)->where('status', 9)->count();
        $statusDone    = DocumentJournals::where('org_unit_id', $orgId)->where('status', 10)->count();
        $statusEnsured = DocumentJournals::where('org_unit_id', $orgId)->where('status', 11)->count();

        $statusExpired = DocumentJournals::where('org_unit_id', $orgId)
            ->whereHas('doc', function ($q) use ($today) {
                $q->where('doc_term', '<', $today)->whereNotIn('status', [2, 9, 10, 11]);
            })->whereNotIn('status', [1, 4, 13])->count();

        $totalDocs = DocumentJournals::where('org_unit_id', $orgId)->whereNotIn('status', [1, 4])->count();

        // ── 3. Разбивка по типам документов (топ-8) ──
        $typeRows = DocumentJournals::where('org_unit_id', $orgId)
            ->whereNotIn('status', [1, 4])
            ->select('j_type', DB::raw('count(*) as cnt'))
            ->groupBy('j_type')
            ->orderByDesc('cnt')
            ->limit(8)
            ->get();

        $types = [];
        foreach ($typeRows as $row) {
            $jt = JournalTypes::find($row->j_type);
            $name = null;
            if ($jt) {
                $langName = $jt->lang($jt->lang_id);
                $name = ($langName && $langName !== 'Lang') ? $langName : ($jt->title ?: ('#' . $row->j_type));
            }
            $types[] = [
                'name'  => $name ?: ('#' . $row->j_type),
                'count' => (int) $row->cnt,
            ];
        }

        // ── 4. Динамика регистрации документов за последние 6 месяцев ──
        $monthsUz = ['', 'Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyn', 'Iyl', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];
        $monthsRu = ['', 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
        $monthsEn = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $locale = app()->getLocale();
        $mMap = $locale === 'ru' ? $monthsRu : ($locale === 'en' ? $monthsEn : $monthsUz);

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $cnt = DocumentJournals::where('org_unit_id', $orgId)
                ->whereNotIn('status', [1, 4])
                ->whereYear('j_date', $m->year)
                ->whereMonth('j_date', $m->month)
                ->count();
            $monthly[] = [
                'label' => $mMap[(int) $m->format('n')] . ' ' . $m->format('y'),
                'count' => $cnt,
            ];
        }

        return [
            'stages' => [
                'exec'  => $stageExec,
                'agree' => $stageAgree,
                'sign'  => $stageSign,
                'resol' => $stageResol,
            ],
            'statuses' => [
                'draft'   => $statusDraft,
                'process' => $statusProcess,
                'done'    => $statusDone,
                'ensured' => $statusEnsured,
                'expired' => $statusExpired,
            ],
            'types'   => $types,
            'monthly' => $monthly,
            'totals'  => [
                'documents' => $totalDocs,
                'expired'   => $statusExpired,
                'active'    => $statusProcess + $stageExec,
            ],
        ];
    }

    public function getMenuCount(Request $request)
    {
        $id = $request->id;

        $menu = RoleMenu::find($id);
        $count = 0;

        if ($menu) {
            if ($menu->child && $menu->child->count() > 0) {
                foreach ($menu->child as $child) {
                    $count += $child->menuCount($child->id);
                }
            } else {
                // Лист-меню (без дочерних) — считаем его собственный счётчик,
                // напр. DIDOX. Иначе JS обнуляет бейдж после загрузки.
                $count = $menu->menuCount($menu->id);
            }
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
