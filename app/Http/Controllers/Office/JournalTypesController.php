<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Admin\AppTemps;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Journal\JournalExecutionStepRoles;
use App\Models\Journal\JournalExecutionSteps;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\TranslationLoader\LanguageLine;

class JournalTypesController extends Controller
{

    public function __construct(protected LanguageService $languageService){}

    public function index(Request $request)
    {
        //
        if($this->isSuperUser()) {
            return $this->superUserIndex($request);
        } else {
            return $this->adminIndex($request);
        }
    }

    public function superUserIndex(Request $request)
    {
        $s_text = $request->input('s_text');

        $search = OrganizationUnits::where('status', 1);

        if (trim(''.$s_text.'')) {
            $search->where(
                DB::raw("(name_uz || ' ' || name_kr || ' ' || name_ru || ' ' || name_en)"),
                'ILIKE',
                "%{$s_text}%"
            );
        }

        $organizations = $search->paginate(25);

        $organizations->appends([
            's_text' => $s_text
        ]);

        return view('document-settings.document.super-user-index', compact('organizations', 's_text'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function superUserOrgDocs(Request $request, $orgId)
    {
        $doc_name = $request->input('doc_name');

        $search = JournalTypes::where('org_id', $orgId)->where('status', 1);

        if (trim(''.$doc_name.'')) {
            $search->where('title', 'LIKE', '%'.$doc_name.'%')->orWhere('doc_code', 'LIKE', '%'.$doc_name.'%');
        }

        $models = $search->orderBy('parent_id')->orderBy('order_number')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name
        ]);

        $parentDocumentTypes = JournalTypes::where('org_id', $orgId)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        $isSuperUser = $this->isSuperUser();

        return view('document-settings.document.index', compact('models', 'doc_name', 'parentDocumentTypes', 'isSuperUser'));
    }

    public function adminIndex(Request $request)
    {
        $doc_name = $request->input('doc_name');

        $search = JournalTypes::where('org_id', $this->organizationId())->where('status', 1);

        if (trim(''.$doc_name.'')) {
            $search->where('title', 'LIKE', '%'.$doc_name.'%')->orWhere('doc_code', 'LIKE', '%'.$doc_name.'%');
        }

        $models = $search->orderBy('parent_id')->orderBy('order_number')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name
        ]);

        $parentDocumentTypes = JournalTypes::where('org_id', $this->organizationId())
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        $isSuperUser = $this->isSuperUser();

        return view('document-settings.document.index', compact('models', 'doc_name', 'parentDocumentTypes', 'isSuperUser'));
    }

    public function create()
    {
        //
        $model = JournalTypes::orderBy('id', 'DESC')->first();

        $order = 1;

        if ($model) {
            $order = $model->doc_order+1;
        }

        $docCode = "APP";

        $isSuperUser = $this->isSuperUser();

        if ($isSuperUser){
            $journalTypes = null;
        } else {
            $journalTypes = JournalTypes::where('org_id', $this->organizationId())->where('status', 1)->get();
        }

        $organizations = OrganizationUnits::where('status', 1)->get();

        return view('document-settings.document.create', compact('docCode', 'order', 'journalTypes',
            'isSuperUser', 'organizations'));
    }

    public function getOrganizationJournals($orgId)
    {
        return response()->json([
           'data' => JournalTypes::where('org_id', $orgId)->where('status', 1)->get(),
           'status' => 200,
           'message' => 'success'
        ]);
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            'name_uz' => 'required|max:255'
        ]);

        $parent_id = empty($request->parent_id) ? 0 : $request->parent_id;

        $level = 1;

        $parent_department = JournalTypes::find($parent_id);
        if ($parent_department) {
            $level = $parent_department->level_number + 1;
        }

        $ord_number = JournalTypes::where('parent_id', $request->parent_id)->orderBy('id', 'desc')->first();

        if ($ord_number) {
            $order = $ord_number->order_number + 1;
        }

        $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 4));

        if($request->parent_id){
            $code = JournalTypes::where('parent_id', $request->parent_id)->where('doc_code', 'LIKE', '%'.'APP'.'%')->first();
        }

        $last_number = JournalTypes::where('parent_id', 0)->orderBy('id', 'desc')->first();

        $new_ord = $last_number->order_number +1;

        $lang_id = null;

        $lang_key = $this->generateUniqueLangSlug(LanguageLine::class, $request->name_uz);

        if (trim($request->name_uz)) {

           $lang =  LanguageLine::create([
                'group' => 'document',
                'key' => $lang_key,
                'text' => [
                    'uz' => $request->name_uz,
                    'ru' => $request->name_ru??$request->name_uz.' RU',
                    'en' => $request->name_en??$request->name_uz.' EN',
                ],
            ]);

           $lang_id = $lang->id;
        }

        $journal_type = new JournalTypes();
        $journal_type->org_id = $request->org_id ?: $this->organizationId();
        $journal_type->parent_id = $parent_id;
        $journal_type->title = $request->name_uz;
        $journal_type->lang_id = $lang_id;
        $journal_type->doc_code = isset($code->doc_code) ? $code->doc_code .'_' .$random : $random;
        $journal_type->level_number = $level;
        $journal_type->order_number = $order??$new_ord;
        $journal_type->status = $request->status == 0;
        $journal_type->save();

        $app = JournalTypes::where('parent_id', $parent_id)->where('doc_code', 'LIKE', '%'.'APP'.'%')->first();

        if ($app){
            $app_temp = new AppTemps();
            $app_temp->journal_id = $journal_type->id;
            $app_temp->t_name = $request->name_uz;
            $app_temp->t_code = $journal_type->doc_code;
            $app_temp->t_text = '';
            $app_temp->status = $request->status == 0;
            $app_temp->created_id = 1;
            $app_temp->save();
        }

        return redirect()->route('document-type.edit', $journal_type->id)
            ->with('success', __('message.doc_created_success_message'));
    }

    public function edit($id)
    {
        //
        $model = JournalTypes::findOrFail($id);

        $journalTypes = JournalTypes::where('status', 1)->get();

        $languages = $this->languageService->getList();

        $execution_roles = DocumentExecutionRoles::where('status', 1)->orderBy('id')->get();

        $unused_role_names = ['Director', 'Deputy_Director', 'Dep_Head', 'Dep_Emp'];

        $roles = Role::where('status', 1)->whereIn('name', $unused_role_names)->orderBy('name')->get();

        $journal_exec_steps = JournalExecutionSteps::where('journal_type_id', $id)->get();

        $unselected_exec_steps = DocumentExecutionRoles::whereNotIn('id', $journal_exec_steps->pluck('exec_role_id')->toArray())->get();

        $listGroupItemColors = ['list-group-item-primary', 'list-group-item-warning', 'list-group-item-success', 'list-group-item-info'];

        $listGroupDescriptions = ["Bo'lim boshlig'i yoki blok rahbari tanlanadi",
            "Bo'lim xodimi, bo'lim boshlig'i yoki boshqa departament direktorlari tanlanadi",
            "Rahbar yoki rahbar o'rinbosarlaridan biri tanlanadi",
            "Hujjat ijrochilari: xodim, bo'lim boshligi, departament direktori tanlanadi",
        ];

        return view('document-settings.document.edit',compact('model', 'journalTypes', 'languages', 'execution_roles', 'roles',
            'journal_exec_steps', 'unselected_exec_steps', 'listGroupItemColors', 'listGroupDescriptions'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'doc_code' => 'required|max:25',
            'title' => 'required|max:255',
            'lang_id' => 'required',
            'order_number' => 'required',
        ]);

        $journalType  = JournalTypes::findOrFail($id);

        if ($journalType) {
            $journalType->update([
                'title' => $request->title,
                'parent_id' => $request->parent_id??0,
                'lang_id' => $request->lang_id,
                'doc_code' => $request->doc_code,
                'order_number' => $request->order_number,
                'status' => $request->status == 'active' ? 1 : 0
            ]);
        }

        $journal = AppTemps::where('journal_id', $journalType->id)->first();

        if ($journal){
            $journal->update([
                'status' => $request->status == 'active' ? 1 : 0
            ]);
        }


        return redirect()->route('document-type.index')
            ->with('success', __('message.doc_updated_success_message'));
    }

    public function stepStore(Request $request)
    {
        $parameters = $request->request->all();

        $doc_execution_steps = JournalExecutionSteps::where('journal_type_id', $request->journal_type_id)->get();

        if ($doc_execution_steps->count()) {
            foreach ($doc_execution_steps as $doc_execution_step) {

                $doc_execution_step_roles = JournalExecutionStepRoles::where('j_exec_step_id', $doc_execution_step->id)->get();

                if ($doc_execution_step_roles->count()) {
                    foreach ($doc_execution_step_roles as $doc_execution_step_role) $doc_execution_step_role->delete();
                }

                $doc_execution_step->delete();
            }
        }

        foreach ($parameters as $key => $parameter){
            if (!in_array($key, ['_token', 'journal_type_id', 'control'])){
                $doc_exec_role = DocumentExecutionRoles::where('doc_code', trim($key))->where('status', 1)->first();
                $doc_exec_step = new JournalExecutionSteps();
                $doc_exec_step->journal_type_id = $request->journal_type_id;
                $doc_exec_step->exec_role_id = $doc_exec_role->id;
                $doc_exec_step->exec_status = 0;
                $doc_exec_step->save();
            }
        }

        $journal_type_id = $request->journal_type_id;

        $control = $request->control;

        $status = $control ? 1 : 0;

        $journal_control = JournalTypeControls::where('journal_type_id', $journal_type_id)->first();

        if ($journal_control) {
            $journal_control->update(['status' => $status]);
        } else {
            $new_journal_control = new JournalTypeControls();
            $new_journal_control->journal_type_id = $journal_type_id;
            $new_journal_control->status = $status;
            $new_journal_control->save();
        }

        return redirect()->route('document-type.edit', $request->journal_type_id)
            ->with('success', __('message.doc_created_success_message'));
    }

    public function stepRoles(Request $request, $id, $j_type_id)
    {
        //
        $request->validate([
            'role_ids' => 'required',
        ]);

        $role_ids = $request->role_ids;
        $status = $request->status;

        $journal_exec_step = JournalExecutionSteps::find($id);

        $journal_exec_step->update(['exec_status' => $status]);

        $old_roles = JournalExecutionStepRoles::where('j_exec_step_id', $id)->get();
        if ($old_roles->count()){
            foreach ($old_roles as $old_role) $old_role->delete();
        }

        if (count($role_ids)){
            foreach ($role_ids as $role_id) {
                $journal_exec_step_role = new JournalExecutionStepRoles();
                $journal_exec_step_role->j_exec_step_id = $id;
                $journal_exec_step_role->role_id = $role_id;
                $journal_exec_step_role->save();
            }
        }

        return redirect()->route('document-type.edit', $j_type_id)
            ->with('success', __('message.doc_created_success_message'));
    }

    public function control(Request $request)
    {
        $journal_type_id = $request->journal_type_id;

        $control = $request->control;

        $status = $control ? 1 : 0;

        $journal_control = JournalTypeControls::where('journal_type_id', $journal_type_id)->first();

        if ($journal_control) {
            $journal_control->update(['status' => $status]);
        } else {
            $new_journal_control = new JournalTypeControls();
            $new_journal_control->journal_type_id = $journal_type_id;
            $new_journal_control->status = $status;
            $new_journal_control->save();
        }

        return redirect()->route('document-type.edit', $journal_type_id)
            ->with('success', __('message.doc_created_success_message'));
    }

    public function showapps(Request $request)
    {
        $doc_name = $request->input('doc_name');

        $search = AppTemps::where('status', 1);

        if (trim(''.$doc_name.'')) {
            $search->where('t_name', 'LIKE', '%'.$doc_name.'%');
        }

        $models = $search->orderBy('id')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name
        ]);

        return view('document-settings.document.view-app', compact('models', 'doc_name'));
    }

    public function editapps($id)
    {
        //
        $model = AppTemps::findOrFail($id);

        return view('document-settings.document.edit-app',compact('model'));
    }

    public function updateapp(Request $request, $id)
    {
        //
        $request->validate([
            't_name' => 'required|max:100',
//            't_text' => 'required',
            't_code' => 'required',
        ]);


        $model = AppTemps::findOrFail($id);

        $journal = JournalTypes::where('id', $model->journal_id);

        $model->update([
            't_name' => $request->t_name,
            't_text' => $request->t_text??'',
            't_code' => $request->t_code,
            'status' => $request->status == 'active' ? 1 : 0
        ]);

        $journal->update([
            'status' => $request->status == 'active' ? 1 : 0
        ]);

        return redirect()->route('app-document.edit', ['id' => $model->id])
            ->with('success', __('message.task_completed_successfully'));
    }
}
