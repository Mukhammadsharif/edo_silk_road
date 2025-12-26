<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Journal\DepartmentJournals;
use App\Services\LanguageService;
use Illuminate\Http\Request;

class DepartmentJournalsController extends Controller
{
    //
    public function __construct(protected LanguageService $languageService){}

    public function index(Request $request)
    {
        //
        $name = $request->input('name');

        $search = DepartmentJournals::where('status', 1);

        if (trim(''.$name.'')) {
            $search->where('name_uz', 'LIKE', '%'.$name.'%')
                ->orWhere('name_ru', 'LIKE', '%'.$name.'%')
                ->orWhere('name_en', 'LIKE', '%'.$name.'%')
                ->orWhere('name_kr', 'LIKE', '%'.$name.'%');
        }

        $departments = Department::where('status', 1)->get();

        $models = $search->orderBy('id')->paginate(25);

        $models->appends([
            'name' => $name
        ]);

        return view('office.department-journals.index', compact('models', 'name', 'departments'));
    }

    public function create()
    {
        //
        $languages = $this->languageService->getList();

        $departments = Department::where('status', 1)->get();

        return view('office.department-journals.create', compact( 'languages', 'departments'));
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
            ->with('success',__('message.doc_created_success_message'));
    }
    public function edit($id)
    {
        //
        $model = DepartmentJournals::findOrFail($id);

        $languages = $this->languageService->getList();

        $departments = Department::where('status', 1)->get();

        return view('office.department-journals.edit',compact('model', 'languages', 'departments'));
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
            ->with('success',__('message.doc_updated_success_message'));
    }
}
