<?php

namespace App\Http\Controllers;

use App\Models\Admin\StatusList;
use App\Models\Admin\UserWorkHistory;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocText;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HrPorotocolDocTypes\ProtocolDocTypeUsers;
use App\Models\HrPorotocolDocTypes\ProtocolDocTypes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\New_;

class ProtocolController extends Controller
{
    public function index(Request $request)
    {
        return view('hr.protocol.hr-protocol-docs');
    }

    public function create()
    {
        $protocol_type = ProtocolDocTypes::all();

//        $users = ProtocolDocTypeUsers::with('personal')->get();

        $users = UserWorkHistory::where('status', 1)->get();

        return view('hr.protocol.create',
            compact('protocol_type', 'users')
        );
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'doc_name' => 'required',
            'doc_number' => 'required',
            'doc_date' => 'required'
        ]);

        $dep = UserWorkHistory::where('user_id', Auth::id())->first();
        $protocol_doc = new ProtocolDocument();
        $protocol_doc->doc_name = $request->doc_name;
        $protocol_doc->doc_number = $request->doc_number;
        $protocol_doc->doc_type_id = $request->doc_type;
        $protocol_doc->status = 1;
        $protocol_doc->doc_hash = $this->uniqueHash(8);;
        $protocol_doc->doc_date = $request->doc_date;
        $protocol_doc->created_dep = $dep->dep_id;
        $protocol_doc->created_user = Auth::id();
        $protocol_doc->save();

        $protocol_text = new ProtocolDocText();
        $protocol_text->doc_id = $protocol_doc->id;
        $protocol_text->doc_text = $request->doc_text;
        $protocol_text->save();


        if ($request->hasFile('doc_files')) {
            $docFiles = $request->file('doc_files');
            foreach ($docFiles as $file) {
                $today = Carbon::today();
                $year = $today->year;
                $month = $today->month;
                $day = $today->day;
                $path = 'doc/' . $year . '/' . $month . '/' . $day . '/';

                $protocol_files = new ProtocolDocFile();
                $protocol_files->doc_id = $protocol_doc->id;
                $protocol_files->file_name = $file->getClientOriginalName();;
                $protocol_files->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
                $protocol_files->file_path = $path;
                $protocol_files->file_ext = $file->getClientOriginalExtension();
                $protocol_files->file_size = $file->getSize();
                Storage::disk('ftp_13')->put($path . $protocol_files->file_hash, file_get_contents($file->getRealPath()));
                $protocol_files->save();
            }
        }


        if ($request->users && is_array($request->users)) {
            $members = UserWorkHistory::whereIn('user_id', $request->users)->get();
            $docs = ProtocolDocTypeUsers::whereIn('user_id', $request->users)->get();
            if ($members) {
                foreach ($members as $member) {
                    foreach ($docs as $doc) {
                        $protocol_members = new ProtocolDocMember();
                        $protocol_members->doc_id = $protocol_doc->id;
                        $protocol_members->user_id = $member->user_id;
                        $protocol_members->role_id = 1;
                        $protocol_members->order_number = $doc->order_number ?? 1;
                        $protocol_members->status = 1;
                        $protocol_members->save();
                    }
                }
            }
        }

        $log_type = $protocol_doc->docType->title ?? 'Protocol Hr Emp order';
        $doc_name = $protocol_doc->doc_name ?? $protocol_doc->id;
        $this->createLog($protocol_doc->id, 1, $log_type, $doc_name);

        return redirect()->route('protocol.index')
            ->with('success', 'Hr Protocol created successfully.');
    }
}
