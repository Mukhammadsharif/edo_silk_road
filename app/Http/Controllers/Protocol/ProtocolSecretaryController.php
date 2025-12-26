<?php

namespace App\Http\Controllers\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserWorkHistory;
use App\Models\Comment\DocumentComment;
use App\Models\Comment\DocumentCommentUser;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocComments;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocText;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocTypeUser;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use App\Models\User;
use App\Services\OnlyOfficeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProtocolSecretaryController extends Controller
{
    //

    protected OnlyOfficeService $onlyOfficeService;

    public function __construct(OnlyOfficeService $onlyOffice)
    {
        $this->onlyOfficeService = $onlyOffice;
    }
    public function index(Request $request, $doc_type)
    {
        //
        $s_text = $request->input('s_text');
        $s_status = $request->input('s_status');
        $s_type = $request->input('s_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $created_user = $request->input('created_user');

        $cr_users = User::where('status', 1)->get();
        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();

        $status_public_doc = false;
        if ($protocolType->doc_code == 'PUB_DOCS') {
            $status_public_doc = true;
            $search = ProtocolDocument::where('visibility', 2);
        } else {
            $search = ProtocolDocument::whereHas('docType', function ($query) use ($doc_type){
                $query->where('doc_hash', $doc_type);
            });
        }

        if (trim(''.$s_text.'')) {
            $search->where('doc_name', 'LIKE', '%'.$s_text.'%')->orWhere('doc_number', 'LIKE', '%'.$s_text.'%');
        }

        if ($s_status) {
            if ($s_status != 'all') {
                $search->where('status', $s_status);
            }
        } else {
            if ($status_public_doc) {
                $search->whereIn('status', [2,3,4,5]);
            } else {
                $search->whereIn('status', [1,2,4,5]);
            }
        }

        if ($created_user) {
            $search->whereIn('created_user', $this->getWorkUserIds($created_user));
        }

        if ($date_from && $date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
        } elseif ($date_from && !$date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $search->where('doc_date', '>=', $tmp_date_from);
        } elseif (!$date_from && $date_till) {
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->where('doc_date', '<=', $tmp_date_till);
        }

        $models = $search->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            's_text' => $s_text,
            's_status' => $s_status,
            's_type' => $s_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'created_user' => $created_user
        ]);

        $statusList = StatusList::where('type', 'prt_status')->whereNotIn('code', [6, 7])->get();

        $page = 'protocol.secretary.index';
        $prt_docs = $protocolType->parent->doc_code??0;
        if ($prt_docs == 'PRT_DOCS') {
            $page = 'protocol.prt-docs.index';
        }

        return view($page, compact('models', 's_text', 's_status',
            's_type', 'statusList', 'protocolType', 'date_from', 'date_till', 'cr_users', 'created_user'));
    }

    public function view($id, $doc_hash)
    {
        //
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();

//        $documentText = ProtocolDocText::where('doc_id', $document->id)->first();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $documentSecretaryWithMember = ProtocolDocMember::where('doc_id', $document->id)
            ->where('role_id', '!=', 4)
            ->whereIn('user_id', $this->authActiveUserIds())->first();

        $documentMembers = ProtocolDocMember::where('doc_id', $document->id)
            ->where('role_id', '!=', 4)
            ->orderBy('order_number')->get();

        $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->orderBy('order_number')->get();

        $documentMemberRoles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        $documentReplyComments = ProtocolDocReplyComment::where('doc_id', $document->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $this->authActiveUser()->user_id)->first();

        $userComments = ProtocolDocComments::where('doc_id', $document->id)->get();

        $authrole = $this->authActiveUserRoleNames();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 2)->where('doc_id', $document->id)->get();

        $comment_inputs = ['doc_id' => $document->id, 'doc_hash' => $doc_hash, 'doc_type' => 2];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 2);
        })->where('document_comment_users.doc_id', $document->id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $page = 'protocol.secretary.view';
        $prt_docs = $document->docType->parent->doc_code??0;
        if ($prt_docs == 'PRT_DOCS') {
            $page = 'protocol.prt-docs.view';
        }

        $html_doc = null;
        $model = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $document->id)->first();
        if ($model) {
            $path = $model->doc_path.$model->doc_key.'.html';
            if (Storage::disk('public')->exists($path)){
                $html_doc = Storage::disk('public')->get($path);
            }
        }

        return view($page, compact('document', 'documentSecretaryWithMember', 'documentFiles', 'documentMembers', 'documentHrEmp', 'documentReplyComments', 'documentMemberRoles',
            'qrKey', 'signType','userComments', 'authrole', 'documentComments', 'comment_inputs', 'html_doc'));
    }

    public function createOnly($doc_type)
    {
        //
        $service = $this->onlyOfficeService->openWordEditor();

        $document_params = $service['document_params'];

        $token = $service['token'];

        $onlyOfficeJsUrl = $document_params['only_office_js_url'];

        return view('protocol.secretary.create-only', compact( 'document_params', 'token', 'doc_type', 'onlyOfficeJsUrl'));
    }

    public function editOnly($id, $doc_hash)
    {
        //
        $service = $this->onlyOfficeService->editWordEditor($id, 'PRT');

        $document_params = $service['document_params'];

        $token = $service['token'];

        $onlyOfficeJsUrl = $document_params['only_office_js_url'];

        return view('protocol.secretary.edit-only', compact( 'document_params', 'token', 'id', 'doc_hash', 'onlyOfficeJsUrl'));

    }

    public function create($doc_type, $document_key)
    {
        //
        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();

        $hrOrderEmp = [];

        if ($protocolType->doc_code == 'PRT_HR') {
            $hrOrderEmp = UserWorkHistory::where('status', 1)
                ->whereHas('personal', function ($query) {
                    $query->where('f_name', '!=', 'SuperAdmin');
                })
                ->get();
        }

        $user_roles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        $page = 'protocol.secretary.create';
        $prt_docs = $protocolType->parent->doc_code??0;
        if ($prt_docs == 'PRT_DOCS') {
            $page = 'protocol.prt-docs.create';
        }

        $this->onlyOfficeService->createWordToHtmlConverted($document_key);

        return view($page, compact('protocolType', 'hrOrderEmp', 'user_roles', 'document_key'));
    }

    public function store(Request $request, $doc_type)
    {
        $documentKey = $request->input('document_key');

        if (!$documentKey) {
            Log::error('Document Key Not Provided');
            return redirect()->back()->withErrors(['document_key' => 'Document Key Not Provided']);
        }

        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();

        $prt_docs = $protocolType->parent->doc_code??0;
        if ($prt_docs != 'PRT_DOCS') {
            $this->validate($request, [
                //'doc_type' => 'required',
                'doc_name' => 'required|max:512',
                //'doc_number' => 'required|max:50',
                'doc_date' => 'date|required',
                'user_id' => 'required',
            ], [
                //'doc_type.required' =>  __('validate.doc_type_mandatory'),
                'doc_name.required' => __('validate.jdoc_name_mandatory'),
                'doc_date.required' => __('validate.doc_date_mandatory'),
                'user_id.required' => __('validate.user_id_document'),
            ]);
        }

        $document_create = new ProtocolDocument();
        $document_create->doc_name = $request->doc_name;
        $document_create->doc_number = $request->doc_number;
        $document_create->doc_date = $request->doc_date;
        $document_create->doc_term = $request->doc_term;
        $document_create->doc_type_id = $protocolType->id;
        $document_create->doc_hash = $this->uniqueId();
        $document_create->doc_code = $this->uniqueDocCode();
        $document_create->status = 1;
        $document_create->created_dep = $this->authActiveUser()->dep_id??0;
        $document_create->created_user = $this->authActiveUser()->id??0;
        $document_create->visibility = $request->doc_visibility??1;
        $document_create->doc_lang = $request->doc_lang;
        $document_create->save();

//        $document_text = new ProtocolDocText();
//        $document_text->doc_id = $document_create->id;
//        $document_text->doc_text = $request->doc_text;
//        $document_text->save();

        $today = Carbon::today(); $year = $today->year; $month = $today->month; $day = $today->day;
        $path = 'ood/' . $year . '/' . $month . '/' . $day . '/';
        $onlyOfficeDoc = new OnlyOfficeDoc();
        $onlyOfficeDoc->doc_type = "PRT";
        $onlyOfficeDoc->doc_id = $document_create->id;
        $onlyOfficeDoc->doc_key = $documentKey;
        $onlyOfficeDoc->doc_path = $path;
        $onlyOfficeDoc->save();

        if ($request->user_id && is_array($request->user_id)) {
            foreach ($request->user_id as $key => $item) {
                $document_members = new ProtocolDocMember();
                $document_members->doc_id = $document_create->id;
                $document_members->user_id = $item;
                $document_members->role_id = $request->input('user_role_id')[$key];
                $document_members->order_number = $key+1;
                $document_members->status = 1;
                $document_members->save();
            }
        }

        // PRT_HR emp
        if ($protocolType->doc_code == 'PRT_HR') {
            if ($request->hr_user_id && is_array($request->hr_user_id)) {
                foreach ($request->hr_user_id as $key1 => $item1) {
                    $document_hr_emp = new ProtocolDocMember();
                    $document_hr_emp->doc_id = $document_create->id;
                    $document_hr_emp->user_id = $item1;
                    $document_hr_emp->role_id = 4;
                    $document_hr_emp->order_number = $key1+1;
                    $document_hr_emp->status = 1;
                    $document_hr_emp->save();
                }
            }
        }

        if ($request->hasFile('doc_files')) {
            $docFiles = $request->file('doc_files');
            foreach ($docFiles as $file) {
                $today = Carbon::today();
                $year = $today->year;
                $month = $today->month;
                $day = $today->day;
                $path = 'doc/' . $year . '/' . $month . '/' . $day . '/';

                $protocol_files = new ProtocolDocFile();
                $protocol_files->doc_id = $document_create->id;
                $protocol_files->file_name = $file->getClientOriginalName();;
                $protocol_files->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
                $protocol_files->file_path = $path;
                $protocol_files->file_ext = $file->getClientOriginalExtension();
                $protocol_files->file_size = $file->getSize();
                Storage::disk('ftp_13')->put($path . $protocol_files->file_hash, file_get_contents($file->getRealPath()));
                $protocol_files->save();
            }
        }

        $log_type = $document_create->docType->title ?? 'Protocol Hr Emp order';
        $doc_name = $document_create->doc_name ?? $document_create->id;
        $this->createLog($document_create->id, 1, $log_type, $doc_name);

        return redirect()->route('prt-secretary-view', [$document_create->id, $document_create->doc_hash])
            ->with('success', __('message.task_completed_successfully'));
    }

    public function edit($id, $doc_hash, $document_key)
    {
        //
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();

//        $documentText = ProtocolDocText::where('doc_id', $document->id)->first();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $selectedUsers = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', '!=', 4)->orderBy('order_number')->get();

        $selectedUsersToArray = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', '!=', 4)->pluck('user_id')->toArray();

        $users = ProtocolDocTypeUser::where('doc_type_id', $document->doc_type_id)->whereNotIN('user_id', $selectedUsersToArray)->get();

        $user_roles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        $selectedUserRoles = ProtocolDocMember::select('user_id', 'role_id')->where('doc_id', $document->id)->get();

        $this->onlyOfficeService->editWordToHtmlConverted($document_key, $document->id, 'PRT');

        $selectedHrEmpToArray = [];
        $hrOrderEmp = [];
        $hr_emp_order = $document->docType->doc_code??0;

        if ($hr_emp_order == 'PRT_HR') {

            $selectedHrEmpToArray = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->pluck('user_id')->toArray();

            $hrOrderEmp = UserWorkHistory::where('status', 1)->whereHas('personal', function ($query) {
                $query->where('f_name', '!=', 'Admin');
            })->get();

        }

        $prt_docs = $document->docType->parent->doc_code??0;
        $page = 'protocol.secretary.edit';
        if ($prt_docs == 'PRT_DOCS') {
            $page = 'protocol.prt-docs.edit';
        }

        return view($page, compact('document',
            'documentFiles', 'users', 'selectedUsers', 'user_roles', 'selectedUserRoles', 'selectedHrEmpToArray', 'hrOrderEmp'));
    }

    public function update(Request $request, $id, $doc_hash)
    {
        //
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();
        $prt_docs = $document->docType->parent->doc_code??0;
        if ($prt_docs != 'PRT_DOCS') {
            $this->validate($request, [
                'doc_name' => 'required|max:512',
                'doc_date' => 'date|required',
                'user_id' => 'required',
            ], [
                'doc_name.required' =>  __('validate.doc_name_mandatory'),
                'doc_date.required' => __('validate.doc_date_mandatory'),
                'user_id.required' => __('validate.user_id_mandatory'),
            ]);
        }

        $document->update([
            'doc_name' => $request->doc_name,
            'doc_number' => $request->doc_number,
            'doc_date' => $request->doc_date,
            'doc_term' => $request->doc_term,
            'visibility' => $request->doc_visibility??1,
            'status' => 1,
            'doc_lang' => $request->doc_lang
        ]);

        ProtocolDocMember::where('doc_id', $id)->delete();

        if ($request->user_id && is_array($request->user_id)) {

            foreach ($request->user_id as $key => $item) {
                $document_members = new ProtocolDocMember();
                $document_members->doc_id = $id;
                $document_members->user_id = $item;
                $document_members->role_id = $request->input('user_role_id')[$key];
                $document_members->order_number = $key+1;
                $document_members->status = 1;
                $document_members->save();
            }
        }

        if ($request->hr_user_id && is_array($request->hr_user_id)) {

            foreach ($request->hr_user_id as $key1 => $item1) {
                $document_members = new ProtocolDocMember();
                $document_members->doc_id = $id;
                $document_members->user_id = $item1;
                $document_members->role_id = 4;
                $document_members->order_number = $key1+1;
                $document_members->status = 1;
                $document_members->save();
            }
        }

        if ($request->hasFile('doc_files')) {
            $docFiles = $request->file('doc_files');
            foreach ($docFiles as $file) {
                $today = Carbon::today();
                $year = $today->year;
                $month = $today->month;
                $day = $today->day;
                $path = 'doc/' . $year . '/' . $month . '/' . $day . '/';

                $protocol_files = new ProtocolDocFile();
                $protocol_files->doc_id = $id;
                $protocol_files->file_name = $file->getClientOriginalName();;
                $protocol_files->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
                $protocol_files->file_path = $path;
                $protocol_files->file_ext = $file->getClientOriginalExtension();
                $protocol_files->file_size = $file->getSize();
                Storage::disk('ftp_13')->put($path . $protocol_files->file_hash, file_get_contents($file->getRealPath()));
                $protocol_files->save();
            }
        }

        $log_type = $document->docType->title ?? 'Protocol Hr Emp order';
        $doc_name = $document->doc_name ?? $document->id;
        $this->createLog($document->id, 2, $log_type, $doc_name);

        return redirect()->route('prt-secretary-view', [$id, $doc_hash])
            ->with('success', __('message.task_completed_successfully'));
    }

    public function delete($id)
    {
        $document = ProtocolDocument::where('id', $id)->whereIn('status', [1,4,5])->first();

        if ($document) {
            $documentType = ProtocolDocType::where('id', $document->doc_type_id)->first();

            ProtocolDocText::where('doc_id', $id)->delete();

            ProtocolDocMember::where('doc_id', $id)->delete();

            $files = ProtocolDocFile::where('doc_id', $id)->get();

            if ($files) {
                foreach ($files as $file) {
                    $path = '/'.$file->file_path.$file->file_hash;

                    if (Storage::disk('ftp_13')->exists($path)){

                        Storage::disk('ftp_13')->delete($path);
                    }
                    $file->delete();
                }
            }

            $document->delete();

            $log_type = $document->docType->title ?? 'Protocol Hr Emp order';
            $doc_name = $document->doc_name ?? $document->id;
            $this->createLog($document->id, 3, $log_type, $doc_name);

            return response()->json([
                'success' => 200,
                'title' => __('message.success_message'),
                'doc_type' => $documentType->doc_hash??'-',
                'message' => __('message.task_completed_successfully')
            ]);
        }

        return response()->json([
            'success' => 201,
            'title' => __('message.error_message'),
            'message' => __('message.document_deleted_system')
        ]);
    }

    public function sendToMembers($id, $doc_hash): RedirectResponse
    {
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();

        $document->update([
            'status' => 2,
        ]);

        ProtocolDocMember::where('doc_id', $document->id)->where('role_id', '!=', 4)->update(['status' => 2]);

        $members = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', '!=', 4)->get();

        $url = 'protocol/members/view/'.$id.'/'.$doc_hash;

        foreach ($members as $member) {
            $this->createMailCron($member->user_id, $document->docType->title, $document->secretary->personal->fullName()??'-', $url, $document->doc_name);
        }

        $log_type = $document->docType->title ?? 'Protocol Hr Emp order';
        $doc_name = $document->doc_name ?? $document->id;
        $this->createLog($document->id, 5, $log_type, $doc_name);

        return redirect()->route('prt-secretary-view', [$id, $doc_hash])
            ->with('success', __('message.task_completed_successfully'));
    }

    public function getDocTypeUsers($id)
    {
        //
        $get_local = session()->get('locale');
        $local_name = 'name_uz as name';
        if ($get_local == 'ru') {
            $local_name = 'name_ru as name';
        } elseif ($get_local == 'en') {
            $local_name = 'name_en as name';
        }

        $users = ProtocolDocTypeUser::query()
            ->with([
                'userWork.personal' => function ($query) {
                    $query->select('user_id', 'l_name', 'f_name');
                },
                'userWork.department' => function ($query) use ($local_name) {
                    $query->select('id', $local_name);
                },
                'userWork.position' => function ($query) use ($local_name) {
                    $query->select('id', $local_name);
                }
            ])
            ->where('doc_type_id', $id)->where('status', 1)
            ->orderBy('order_number')
            ->get();

        $user_roles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        return response()->json([
            'users' => $users,
            'user_roles' => $user_roles,
        ]);

    }
}
