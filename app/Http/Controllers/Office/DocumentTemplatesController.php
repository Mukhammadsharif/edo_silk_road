<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
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

        $search = DocumentTypeTemps::query();

        if (trim(''.$doc_name.'')) {
            $search->whereHas('document', function ($query) use ($doc_name) {
                $query->where('title', 'like', '%'.$doc_name.'%');
            });
        }

        $models = $search->orderBy('id')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name
        ]);

        return view('document-settings.document-templates.index', compact('models', 'doc_name'));
    }

    public function create()
    {
        //
        $mainTypes =  JournalTypes::whereHas('childs')->pluck('id')->toArray();

        $documentTypes = JournalTypes::whereNotIn('id', $mainTypes)->where('status', 1)->orderBy('order_number')->get();

        return view('document-settings.document-templates.create', compact('documentTypes'));
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
