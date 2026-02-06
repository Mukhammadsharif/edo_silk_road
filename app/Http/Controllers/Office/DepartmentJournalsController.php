<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Journal\DepartmentJournals;
use App\Services\LanguageService;
use Illuminate\Http\Request;

class DepartmentJournalsController extends Controller
{
    //
    public function __construct(protected LanguageService $languageService)
    {
    }

    public function index(Request $request)
    {
        //
        $name = $request->input('name');

        $org_id = $request->input('org_id');

        $isSuperUser = $this->isSuperUser();

        $organizations = OrganizationUnits::where('status', 1)->get();

        if ($isSuperUser) {
            $departments = Department::where('status', 1)->get();
        } else {
            $departments = Department::where('org_unit_id', $this->organizationId())->where('status', 1)->get();
        }

        if ($isSuperUser) {
            $search = DepartmentJournals::where('status', 1);
        } else {
            $search = DepartmentJournals::whereHas('department', function ($query) {
                $query->where('org_unit_id', $this->organizationId());
            })->where('status', 1);
        }

        if ($org_id) {
            $search->whereHas('department', function ($query) use ($org_id) {
                $query->where('org_unit_id', $org_id);
            });
        }

        if (trim('' . $name . '')) {
            $search->where('name_uz', 'LIKE', '%' . $name . '%')
                ->orWhere('name_ru', 'LIKE', '%' . $name . '%')
                ->orWhere('name_en', 'LIKE', '%' . $name . '%')
                ->orWhere('name_kr', 'LIKE', '%' . $name . '%');
        }


        $models = $search->orderBy('id')->paginate(25);

        $models->appends([
            'name' => $name,
            'org_id' => $org_id,
        ]);

        return view('office.department-journals.index', compact('models', 'name', 'departments', 'organizations',
            'org_id', 'isSuperUser'));
    }

    public function create()
    {
        //
        $languages = $this->languageService->getList();

        if ($this->isSuperUser()) {
            $departments = collect();
        } else {
            $departments = Department::where('org_unit_id', $this->organizationId())->where('status', 1)->orderBy('order_number')->get();
        }

        $isSuperUser = $this->isSuperUser();

        $organizations = OrganizationUnits::where('status', 1)->get();

        return view('office.department-journals.create', compact('languages', 'departments', 'organizations', 'isSuperUser'));
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            'name_uz' => 'required|max:255',
            'department' => 'required',
        ]);

        $doc_organization = new DepartmentJournals();
        $doc_organization->dep_id = $request->department;
        $doc_organization->type = $request->type;
        $doc_organization->name_uz = $request->name_uz;
        $doc_organization->name_ru = $request->name_ru;
        $doc_organization->name_en = $request->name_en;
        $doc_organization->name_kr = $request->name_kr;
        $doc_organization->status = 1;
        $doc_organization->save();


        return redirect()->route('department-journals.index')
            ->with('success', __('message.doc_created_success_message'));
    }

    public function edit($id)
    {
        //
        $model = DepartmentJournals::findOrFail($id);

        $org_id = $model->department->org_unit_id;

        $languages = $this->languageService->getList();

        $departments = Department::where('org_unit_id', $org_id)->where('status', 1)->get();

        return view('office.department-journals.edit', compact('model', 'languages', 'departments'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'name_uz' => 'required|max:255',
            'department' => 'required',
        ]);

        $department_journal = DepartmentJournals::findOrFail($id);

        $department_journal->update([
            'name_uz' => $request->name_uz,
            'name_ru' => $request->name_ru,
            'name_en' => $request->name_en,
            'name_kr' => $request->name_kr,
            'dep_id' => $request->department,
            'type' => $request->type,
            'status' => $request->status
        ]);

        return redirect()->route('department-journals.index')
            ->with('success', __('message.doc_updated_success_message'));
    }
}
