<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Admin\OrganizationUnits;
use App\Models\DocumentTypeTemps\DocumentTypeTemps;
use App\Models\Journal\JournalTypes;
use Illuminate\Http\Request;

class DocumentTemplatesController extends Controller
{
    //
    public function index(Request $request)
    {
        //
        $doc_name = $request->input('doc_name');
        $org_id = $request->input('org_id');

        $search = DocumentTypeTemps::query();

        if (trim(''.$doc_name.'')) {
            $search->whereHas('document', function ($query) use ($doc_name) {
                $query->where('title', 'ILIKE', '%'.$doc_name.'%');
            });
        }


        $organizations = OrganizationUnits::where('status', 1)->get();

        $isSuperUser = $this->isSuperUser();

        if ($isSuperUser && $org_id) {
            $search->whereHas('document', function ($query) use ($org_id) {
                $query->where('org_id', $org_id);
            });
        } elseif ($isSuperUser && !$org_id) {
            $search->whereHas('document', function ($query) {});
        } else {
            $search->whereHas('document', function ($query) {
                $query->where('org_id', $this->organizationId());
            });
        }

        $models = $search->orderBy('id')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'org_id' => $org_id,
        ]);

        return view('document-settings.document-templates.index', compact('models', 'doc_name', 'organizations',
            'org_id', 'isSuperUser'));
    }

    public function create()
    {
        //
        $mainTypes =  JournalTypes::whereHas('childs')->pluck('id')->toArray();

        $isSuperUser = $this->isSuperUser();

        if ($isSuperUser){
            $documentTypes = null;
        } else {
            $documentTypes = JournalTypes::where('org_id', $this->organizationId())->whereNotIn('id', $mainTypes)->where('status', 1)->orderBy('order_number')->get();
        }

        $organizations = OrganizationUnits::where('status', 1)->get();

        return view('document-settings.document-templates.create', compact('documentTypes', 'isSuperUser', 'organizations'));
    }

    public function getOrganizationJournals($orgId)
    {
        $mainTypes =  JournalTypes::where('org_id', $orgId)->whereHas('childs')->pluck('id')->toArray();

        return response()->json([
            'data' => JournalTypes::where('org_id', $orgId)->whereNotIn('id', $mainTypes)->where('status', 1)->get(),
            'status' => 200,
            'message' => 'success'
        ]);
    }

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'doc_id' => 'required',
            't_text' => 'required',
        ]);

        $template = new DocumentTypeTemps();
        $template->doc_type_id = $request->input('doc_id');
        $template->t_text = $request->input('t_text');
        $template->status = 1;
        $template->save();

        return redirect()->route('document.templates.edit', ['id' => $template->id]);
    }

    public function edit($id)
    {
        //
        $template = DocumentTypeTemps::findOrFail($id);

        $mainTypes =  JournalTypes::whereHas('childs')->pluck('id')->toArray();

        $documentTypes = JournalTypes::whereNotIn('id', $mainTypes)->where('status', 1)->orderBy('order_number')->get();

        return view('document-settings.document-templates.edit', compact('template', 'documentTypes'));
    }

    public function update(Request $request, $id) {

        $this->validate($request, [
            'doc_id' => 'required',
            't_text' => 'required',
        ]);

        $template = DocumentTypeTemps::find($id);

        $template->update([
           'doc_type_id' => $request->input('doc_id'),
           't_text' => $request->input('t_text'),
           'status' => $request->input('status') == 'active' ? 1 : 0,
        ]);

        return redirect()->route('document.templates.edit', ['id' => $template->id]);
    }
}
