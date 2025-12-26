<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentOrganizations;
use App\Models\Journal\JournalTypes;
use App\Services\LanguageService;
use Illuminate\Http\Request;

class DocOrganizationsController extends Controller
{

    public function __construct(protected LanguageService $languageService){}

    public function index(Request $request)
    {
        //
        $org_name = $request->input('org_name');

        $search = DocumentOrganizations::where('status', 1);

        if (trim(''.$org_name.'')) {
            $search->where('name_uz', 'LIKE', '%'.$org_name.'%')->orWhere('name_ru', 'LIKE', '%'.$org_name.'%')->orWhere('name_en', 'LIKE', '%'.$org_name.'%');
        }

        $journals = JournalTypes::where('status', 1)->get();

        $models = $search->orderBy('id')->paginate(25);

        $models->appends([
            'org_name' => $org_name
        ]);

        return view('office.doc-org.index', compact('models', 'org_name', 'journals'));
    }

    public function create()
    {
        //
        $model = DocumentOrganizations::orderBy('id', 'DESC')->first();
        $order = 1;
        if ($model) {
            $order = $model->org_order+1;
        }

        $languages = $this->languageService->getList();

        $journals = JournalTypes::where('status', 1)->get();

        return view('office.doc-org.create', compact('order', 'languages', 'journals'));
    }

    public function store(Request $request)
    {
        //
        $request->validate([
            'name_uz' => 'required|max:255',
            'name_ru' => 'required|max:255',
            'name_en' => 'required|max:255',
            'for_doc_type' => 'required',
        ]);

        $doc_organization = new DocumentOrganizations();
        $doc_organization->name_uz = $request->name_uz;
        $doc_organization->name_ru = $request->name_ru;
        $doc_organization->name_en = $request->name_en;
        $doc_organization->for_doc_type = $request->for_doc_type;
        $doc_organization->status = 1;
        $doc_organization->save();


        return redirect()->route('doc-org.index')
            ->with('success',__('message.doc_created_success_message'));
    }
    public function edit($id)
    {
        //
        $model = DocumentOrganizations::findOrFail($id);

        $languages = $this->languageService->getList();

        $journals = JournalTypes::where('status', 1)->get();

        return view('office.doc-org.edit',compact('model', 'languages', 'journals'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'name_uz' => 'required|max:255',
            'name_ru' => 'required|max:255',
            'name_en' => 'required|max:255',
            'for_doc_type' => 'required',
        ]);

        $doc_organization = DocumentOrganizations::findOrFail($id);

        $doc_organization->update([
            'name_uz' => $request->name_uz,
            'name_ru' => $request->name_ru,
            'name_en' => $request->name_en,
            'for_doc_type' => $request->for_doc_type,
            'status' => $request->status
        ]);

        return redirect()->route('doc-org.index')
            ->with('success',__('message.doc_updated_success_message'));
    }
}
