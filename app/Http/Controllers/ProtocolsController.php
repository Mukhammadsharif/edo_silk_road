<?php

namespace App\Http\Controllers;

use App\Models\Admin\StatusList;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserWorkHistory;
use App\Models\Comment\DocumentComment;
use App\Models\Comment\DocumentCommentUser;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocComments;
use App\Models\Protocol\ProtocolDocConfigs;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocTypeUser;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use App\Models\User;
use App\Services\OnlyOfficeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProtocolsController extends Controller
{
    protected OnlyOfficeService $onlyOfficeService;

    public function __construct(OnlyOfficeService $onlyOffice)
    {
        $this->onlyOfficeService = $onlyOffice;
    }

    public function index(Request $request, $doc_type)
    {
        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();
        $doc_code = $protocolType->doc_code;

        if (!Auth::user()->hasAnyPermission(['secretary_access_' . $doc_code, 'member_access_' . $doc_code])) {
            abort(403, 'Unauthorized');
        }

        $s_text = $request->input('s_text');
        $s_status = $request->input('s_status');
        $s_type = $request->input('s_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $created_user = $request->input('created_user');

        $cr_users = User::where('status', 1)->get();
        $doc_type_id = $protocolType->id;

        $status_public_doc = $protocolType->doc_code == 'PUB_DOCS';

        $isSecretary = Auth::user()->hasPermissionTo('secretary_access_' . $doc_code);

        if (!$s_status && !$isSecretary) {
            $s_status = 2;
        }

        if ($isSecretary) {
            $search = $status_public_doc
                ? ProtocolDocument::where('visibility', 2)
                : ProtocolDocument::whereHas('docType', function ($query) use ($doc_type) {
                    $query->where('doc_hash', $doc_type);
                });
        } else {
            $search = ProtocolDocMember::whereIn('user_id', Auth::user()->userWorkIDs())
                ->whereHas('document', function ($query) use ($doc_type_id) {
                    $query->where('doc_type_id', $doc_type_id);
                });
        }

        if (trim($s_text)) {
            $search->whereHas('document', function ($query) use ($s_text) {
                $query->where('doc_name', 'LIKE', '%' . $s_text . '%')
                    ->orWhere('doc_number', 'LIKE', '%' . $s_text . '%');
            });
        }

        if ($s_status) {
            if ($s_status != 'all') {
                $search->where('status', $s_status);
            } elseif ($isSecretary) {
                //
            } else {
                $search->where('status', '!=', 1);
            }
        } else {
            if ($isSecretary) {
                $search->whereIn('status', [1, 2, 3, 4, 5]);
            } else {
                $search->where('status', 2);
            }
        }

        if ($created_user) {
            $search->whereHas('document', function ($query) use ($created_user) {
                $query->whereIn('created_user', $this->getWorkUserIds($created_user));
            });
        }

        if ($date_from && $date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('document', function ($query) use ($tmp_date_from, $tmp_date_till) {
                $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
            });
        } elseif ($date_from && !$date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $search->whereHas('document', function ($query) use ($tmp_date_from) {
                $query->where('doc_date', '>=', $tmp_date_from);
            });
        } elseif (!$date_from && $date_till) {
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('document', function ($query) use ($tmp_date_till) {
                $query->where('doc_date', '<=', $tmp_date_till);
            });
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

        $statusList = StatusList::where('type', 'prt_status')->whereNotIn('code', [1, 6, 7])->get();

        $page = $isSecretary ? 'protocol.secretary.index' : 'protocol.members.index';

        return view($page, compact('models', 's_text', 's_status',
            's_type', 'statusList', 'protocolType', 'date_from', 'date_till', 'cr_users', 'created_user'));
    }

    public function view($id, $doc_hash)
    {
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();
        $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->firstOrFail();

        $doc_code = $protocolType->doc_code;

        if (!Auth::user()->hasAnyPermission(['secretary_access_' . $doc_code, 'member_access_' . $doc_code])) {
            abort(403, 'Unauthorized');
        }

        $secPermission = Auth::user()->hasPermissionTo('secretary_access_' . $doc_code);
        $memPermission = Auth::user()->hasPermissionTo('member_access_' . $doc_code);

        $isSecretary = ($secPermission && $memPermission && in_array($document->status, [1,4,5])) || ($secPermission && !$memPermission);

        $documentMember = ProtocolDocMember::where('doc_id', $document->id)->whereIn('user_id', $this->authActiveUserIds())->first();

        $documentSecretaryWithMember = ProtocolDocMember::where('doc_id', $document->id)
            ->where('role_id', '!=', 5)
            ->whereIn('user_id', $this->authActiveUserIds())->first();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $documentMembers = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', '!=', 1)->orderBy('order_number')->get();

        $present_roles = ProtocolDocMember::where('doc_id', $document->id)->pluck('role_id')->unique()->toArray();

        $documentMemberRoles = StatusList::where('type', 'prt_sign_type')
            ->where('code', '!=', 1)
            ->whereIn('code', $present_roles)
            ->orderBy('code')
            ->get();

        $documentReplyComments = ProtocolDocReplyComment::where('doc_id', $document->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $this->authActiveUser()->user_id)->first();

        $userComments = $isSecretary ? ProtocolDocComments::where('doc_id', $document->id)->get() : null;

        $documentComments = DocumentComment::where('doc_type', 2)->where('doc_id', $document->id)->get();

        $comment_inputs = ['doc_id' => $document->id, 'doc_hash' => $doc_hash, 'doc_type' => 2];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 2);
        })->where('document_comment_users.doc_id', $document->id)
            ->whereIn('document_comment_users.user_id', $this->authActiveUserIds())
            ->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $html_doc = null;
        $model = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $document->id)->first();
        if ($model) {
            $path = $model->doc_path . $model->doc_key . '.html';
            if (Storage::disk('public')->exists($path)) {
                $html_doc = Storage::disk('public')->get($path);
            }
        }

        $authrole = $isSecretary ? ['secretary'] : ['member'];

        $page = $isSecretary
            ? ($protocolType->parent && $protocolType->parent->doc_code == 'PRT_DOCS' ? 'protocol.prt-docs.view' : 'protocol.secretary.view')
            : 'protocol.members.view';

        return view($page, compact('document', 'documentMember', 'html_doc', 'documentFiles', 'documentMembers',
            'documentReplyComments', 'documentMemberRoles', 'qrKey', 'signType', 'userComments',
            'documentComments', 'comment_inputs', 'authrole', 'documentSecretaryWithMember'));
    }

    public function createOnly($doc_type)
    {
        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $service = $this->onlyOfficeService->openWordEditor();

        $document_params = $service['document_params'];

        $token = $service['token'];

        $onlyOfficeJsUrl = $document_params['only_office_js_url'];

        return view('protocol.secretary.create-only', compact('document_params', 'token', 'doc_type', 'onlyOfficeJsUrl'));
    }

    public function create($doc_type, $document_key)
    {
        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $protocolConfigRoleIds = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->pluck('role_id')->toArray();

        $user_roles = StatusList::where('type', 'prt_sign_type')
            ->whereIn('code', $protocolConfigRoleIds)
            ->whereNotIn('code', [1, 5])
            ->orderBy('code')
            ->get();

        $this->onlyOfficeService->createWordToHtmlConverted($document_key);

        $isFamiliarExists = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->where('role_id', 5)->exists();

        return view('protocol.secretary.create', compact('protocolType', 'user_roles', 'document_key', 'isFamiliarExists'));
    }

    public function store(Request $request, $doc_type)
    {
        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $documentKey = $request->input('document_key');
        if (!$documentKey) {
            Log::error('Document Key Not Provided');
            return redirect()->back()->withErrors(['document_key' => 'Document Key Not Provided']);
        }

        $this->validate($request, [
            'doc_name' => 'required|max:512',
            'doc_date' => 'date|required',
            'user_id' => 'required',
        ], [
            'doc_name.required' => __('validate.jdoc_name_mandatory'),
            'doc_date.required' => __('validate.doc_date_mandatory'),
            'user_id.required' => __('validate.user_id_document'),
        ]);

        $document_create = new ProtocolDocument();
        $document_create->doc_name = $request->doc_name;
        $document_create->org_id = $this->organizationId();
        $document_create->doc_number = $request->doc_number;
        $document_create->doc_date = $request->doc_date;
        $document_create->doc_term = $request->doc_term;
        $document_create->doc_type_id = $protocolType->id;
        $document_create->doc_hash = $this->uniqueId();
        $document_create->doc_code = $this->uniqueDocCode();
        $document_create->status = 1;
        $document_create->created_dep = $this->authActiveUser()->dep_id ?? 0;
        $document_create->created_user = $this->authActiveUser()->id ?? 0;
        $document_create->visibility = $request->doc_visibility ?? 1;
        $document_create->doc_lang = $request->doc_lang;
        $document_create->save();

        $today = Carbon::today();
        $year = $today->year;
        $month = $today->month;
        $day = $today->day;
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
                $document_members->role_id = $request->input('user_role_id')[$key] ?? 3;
                $document_members->order_number = $key + 1;
                $document_members->status = 1;
                $document_members->save();
            }
        }

        if ($request->hr_user_id && is_array($request->hr_user_id)) {
            foreach ($request->hr_user_id as $key1 => $item1) {
                $document_hr_emp = new ProtocolDocMember();
                $document_hr_emp->doc_id = $document_create->id;
                $document_hr_emp->user_id = $item1;
                $document_hr_emp->role_id = 5;
                $document_hr_emp->order_number = $key1+1;
                $document_hr_emp->status = 1;
                $document_hr_emp->save();
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
                $protocol_files->file_name = $file->getClientOriginalName();
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
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();
        $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $protocolConfigRoleIds = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->pluck('role_id')->toArray();

        $user_roles = StatusList::where('type', 'prt_sign_type')
            ->whereIn('code', $protocolConfigRoleIds)
            ->whereNotIn('code', [1, 5])
            ->orderBy('code')
            ->get();

        $selectedUsersToArray = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', '!=', 1)->pluck('user_id')->toArray();

        $selectedUserRoles = ProtocolDocMember::select('user_id', 'role_id')->where('doc_id', $document->id)->where('role_id', '!=', 1)->get();

        $this->onlyOfficeService->editWordToHtmlConverted($document_key, $document->id, 'PRT');

        $selectedHrEmp = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 5)->get();

        $isFamiliarExists = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->where('role_id', 5)->exists();

        return view('protocol.secretary.edit', compact('document', 'documentFiles', 'protocolType', 'user_roles', 'selectedUsersToArray', 'selectedUserRoles', 'document_key',
            'selectedHrEmp', 'isFamiliarExists'));
    }

    public function editOnly($id, $doc_hash)
    {
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();

        $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $service = $this->onlyOfficeService->editWordEditor($id, 'PRT');

        $document_params = $service['document_params'];

        $token = $service['token'];

        $onlyOfficeJsUrl = $document_params['only_office_js_url'];

        return view('protocol.secretary.edit-only', compact('document_params', 'token', 'id', 'doc_hash', 'onlyOfficeJsUrl'));
    }

    public function update(Request $request, $id, $doc_hash)
    {
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();
        $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $this->validate($request, [
            'doc_name' => 'required|max:512',
            'doc_date' => 'date|required',
            'user_id' => 'required',
        ], [
            'doc_name.required' => __('validate.doc_name_mandatory'),
            'doc_date.required' => __('validate.doc_date_mandatory'),
            'user_id.required' => __('validate.user_id_mandatory'),
        ]);

        $document->update([
            'doc_name' => $request->doc_name,
            'doc_number' => $request->doc_number,
            'doc_date' => $request->doc_date,
            'doc_term' => $request->doc_term,
            'visibility' => $request->doc_visibility ?? 1,
            'status' => 1,
            'doc_lang' => $request->doc_lang
        ]);

        $today = Carbon::today();
        $year = $today->year;
        $month = $today->month;
        $day = $today->day;
        $path = 'ood/' . $year . '/' . $month . '/' . $day . '/';
        $onlyOfficeDoc = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $document->id)->first();
        if (!$onlyOfficeDoc) {
            $onlyOfficeDoc = new OnlyOfficeDoc();
            $onlyOfficeDoc->doc_type = "PRT";
            $onlyOfficeDoc->doc_id = $document->id;
        }
        $onlyOfficeDoc->doc_key = $request->input('document_key');
        $onlyOfficeDoc->doc_path = $path;
        $onlyOfficeDoc->save();

        ProtocolDocMember::where('doc_id', $id)->delete();

        if ($request->user_id && is_array($request->user_id)) {
            foreach ($request->user_id as $key => $item) {
                $document_members = new ProtocolDocMember();
                $document_members->doc_id = $document->id;
                $document_members->user_id = $item;
                $document_members->role_id = $request->input('user_role_id')[$key] ?? 3;
                $document_members->order_number = $key + 1;
                $document_members->status = 1;
                $document_members->save();
            }
        }

        if ($request->hr_user_id && is_array($request->hr_user_id)) {
            foreach ($request->hr_user_id as $key1 => $item1) {
                $document_hr_emp = new ProtocolDocMember();
                $document_hr_emp->doc_id = $id;
                $document_hr_emp->user_id = $item1;
                $document_hr_emp->role_id = 5;
                $document_hr_emp->order_number = $key1+1;
                $document_hr_emp->status = 1;
                $document_hr_emp->save();
            }
        }

        if ($request->hasFile('doc_files')) {
            $docFiles = $request->file('doc_files');
            foreach ($docFiles as $file) {
                $path = 'doc/' . $year . '/' . $month . '/' . $day . '/';
                $protocol_files = new ProtocolDocFile();
                $protocol_files->doc_id = $id;
                $protocol_files->file_name = $file->getClientOriginalName();
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
        $document = ProtocolDocument::where('id', $id)->whereIn('status', [1, 4, 5])->first();
        if (!$document) {
            return response()->json([
                'success' => 201,
                'title' => __('message.error_message'),
                'message' => __('message.document_deleted_system')
            ]);
        }

        $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $documentType = $protocolType;

        ProtocolDocMember::where('doc_id', $id)->delete();

        $files = ProtocolDocFile::where('doc_id', $id)->get();
        if ($files) {
            foreach ($files as $file) {
                $path = '/' . $file->file_path . $file->file_hash;
                if (Storage::disk('ftp_13')->exists($path)) {
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
            'doc_type' => $documentType->doc_hash ?? '-',
            'message' => __('message.task_completed_successfully')
        ]);
    }

    public function sendToMembers($id, $doc_hash): RedirectResponse
    {
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->firstOrFail();
        $protocolType = ProtocolDocType::where('id', $document->doc_type_id)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $document->update([
            'status' => 2,
        ]);

        $configs = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->orderBy('order')->get();
        $signer_roles = $configs->pluck('role_id')->toArray();

        $url = 'protocol/' . $protocolType->doc_code . '/' . $id . '/' . $doc_hash;

        if ($protocolType->sign_type == 1) {
            // Parallel signing
            ProtocolDocMember::where('doc_id', $document->id)->whereIn('role_id', $signer_roles)->update(['status' => 2]);

            $members = ProtocolDocMember::where('doc_id', $document->id)->whereIn('role_id', $signer_roles)->get();

            foreach ($members as $member) {
                $this->createMailCron(
                    $member->user_id,
                    $document->docType->title,
                    $document->created_user ? UserPersonalInfo::where('user_id', $document->created_user)->first()->fullName() ?? '-' : '-',
                    $url,
                    $document->doc_name
                );
            }

            // Check if there are no pending members (e.g., no signers at all)
            $pending = ProtocolDocMember::where('doc_id', $document->id)->whereIn('role_id', $signer_roles)->whereNotIn('status', [3,5,7])->count();
            if ($pending == 0) {
                $document->update(['status' => 3]);
            }
        } else {
            // Sequential signing
            $this->activateNextRole($document, $protocolType, 0);
        }

        $log_type = $document->docType->title ?? 'Protocol Hr Emp order';
        $doc_name = $document->doc_name ?? $document->id;
        $this->createLog($document->id, 5, $log_type, $doc_name);

        return redirect()->route('prt-secretary-view', [$id, $doc_hash])
            ->with('success', __('message.task_completed_successfully'));
    }

    private function activateNextRole($document, $protocolType, $current_order)
    {
        $configs = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->orderBy('order')->get();
        $present_roles = ProtocolDocMember::where('doc_id', $document->id)->pluck('role_id')->unique()->toArray();

        $next_config = $configs->filter(function ($c) use ($current_order) {
            return $c->order > $current_order;
        })->sortBy('order')->first();

        while ($next_config) {
            if (in_array($next_config->role_id, $present_roles)) {
                ProtocolDocMember::where('doc_id', $document->id)
                    ->where('role_id', $next_config->role_id)
                    ->update(['status' => 2]);

                $next_members = ProtocolDocMember::where('doc_id', $document->id)
                    ->where('role_id', $next_config->role_id)
                    ->get();

                $url = 'protocol/' . $protocolType->doc_code . '/' . $document->id . '/' . $document->doc_hash;

                foreach ($next_members as $member) {
                    $this->createMailCron(
                        $member->user_id,
                        $document->docType->title,
                        $document->created_user ? UserPersonalInfo::where('user_id', $document->created_user)->first()->fullName() ?? '-' : '-',
                        $url,
                        $document->doc_name
                    );
                }

                return true;
            }

            // Move to the next config
            $current_order = $next_config->order;
            $next_config = $configs->filter(function ($c) use ($current_order) {
                return $c->order > $current_order;
            })->sortBy('order')->first();
        }

        // No more roles with members, complete the document
        $document->update(['status' => 3]);

        return false;
    }

    public function getDocTypeUsers($id)
    {
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

        $user_roles = StatusList::where('type', 'prt_sign_type')->orderBy('code')->get();

        return response()->json([
            'users' => $users,
            'user_roles' => $user_roles,
        ]);
    }

    /**
     * Search HR employees for a given protocol type, excluding those already in ProtocolDocMembers.
     *
     * @param Request $request
     * @param string $doc_hash
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchHrEmp(Request $request, $doc_hash)
    {
        $protocolType = ProtocolDocType::where('doc_hash', $doc_hash)->where('status', 1)->firstOrFail();

        if (!Auth::user()->hasPermissionTo('secretary_access_' . $protocolType->doc_code)) {
            abort(403, 'Unauthorized');
        }

        $query = trim($request->input('query', ''));
        $docId = $request->input('doc_id', 0);

        $employees = UserWorkHistory::where('user_work_histories.status', 1)
            ->join('user_personal_infos as up', 'user_work_histories.user_id', '=', 'up.user_id')
            ->leftJoin('departments as d', 'user_work_histories.dep_id', '=', 'd.id')
            ->leftJoin('user_positions as p', 'user_work_histories.post_id', '=', 'p.id')
            ->when($query, function ($q) use ($query) {
                $searchTerm = '%' . $query . '%';
                return $q->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('up.l_name', 'ILIKE', $searchTerm)
                        ->orWhere('up.f_name', 'ILIKE', $searchTerm)
                        ->orWhere('up.m_name', 'ILIKE', $searchTerm);
                });
            })
            ->whereNotIn('user_work_histories.user_id', function ($subQuery) use ($docId) {
                $subQuery->select('w.user_id')
                    ->from('protocol_doc_members as m')
                    ->join('user_work_histories as w', 'm.user_id', '=', 'w.id')
                    ->where('m.doc_id', $docId)
                    ->where('m.status', '!=', 7);
            })
            ->whereNotIn('user_work_histories.user_id', function ($subQuery) use ($protocolType) {
                $subQuery->select('user_id')
                    ->from('protocol_doc_type_users')
                    ->where('doc_type_id', $protocolType->id)
                    ->where('status', '!=', 7);
            })
            ->select(
                DB::raw('DISTINCT ON (up.user_id) user_work_histories.id'),
                'up.user_id', // Добавил для надежности
                'up.l_name',
                'up.f_name',
                'up.m_name',
                DB::raw("COALESCE(d.name_uz, '') as department_name"),
                DB::raw("COALESCE(p.name_uz, '') as position_name")
            )
            ->orderBy('up.user_id') // DISTINCT ON требует это поле первым
            ->orderBy('user_work_histories.id', 'desc')
            ->take(10)
            ->get();

        DB::enableQueryLog();
        Log::info('SearchHrEmp Query: ', [DB::getQueryLog()]);

        return response()->json($employees);
    }
}
