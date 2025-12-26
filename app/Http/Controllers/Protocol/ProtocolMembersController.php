<?php

namespace App\Http\Controllers\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Comment\DocumentComment;
use App\Models\Comment\DocumentCommentUser;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocText;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProtocolMembersController extends Controller
{
    //
    public function index(Request $request, $doc_type)
    {
        //
        $s_text = $request->input('s_text');
        $s_status = $request->input('s_status')??2;
        $s_type = $request->input('s_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $created_user = $request->input('created_user');

        $cr_users = User::where('status', 1)->get();
        $protocolType = ProtocolDocType::where('doc_hash', $doc_type)->where('status', 1)->firstOrFail();

        $doc_type_id = $protocolType->id;

        $search = ProtocolDocMember::where('role_id', '!=', 4)->whereIN('user_id', Auth::user()->userWorkIDs())
            ->whereHas('document', function ($query) use ($doc_type_id) {
                $query->where('doc_type_id', $doc_type_id);
            });

        if (trim(''.$s_text.'')) {
            $search->whereHas('document', function ($query) use ($s_text) {
                $query->where('doc_name', 'LIKE', '%'.$s_text.'%')->orWhere('doc_number', 'LIKE', '%'.$s_text.'%');
            });
        }

        if ($s_status == 'all') {
            $search->where('status', '!=', 1);
        } elseif ($s_status) {
            $search->where('status', $s_status);
        } else {
            $search->where('status', 2);
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

        $statusList = StatusList::where('type', 'prt_status')->whereNotIn('code', [1,6,7])->get();

        return view('protocol.members.index', compact('models', 's_text', 's_status',
            's_type', 'statusList', 'protocolType', 'date_from', 'date_till', 'cr_users', 'created_user'));
    }

    public function view($id, $doc_hash)
    {
        //
        $authrole = $this->authActiveUserRoleNames();

        $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();

        $documentMember = ProtocolDocMember::where('id', $id)->where('doc_id', $document->id)->firstOrFail();

//        $documentText = ProtocolDocText::where('doc_id', $document->id)->first();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $documentMembers = ProtocolDocMember::where('doc_id', $document->id)->get();

        $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->orderBy('order_number')->get();

        $documentMemberRoles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        $documentReplyComments = ProtocolDocReplyComment::where('doc_id', $document->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $this->authActiveUser()->user_id)->first();

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

        $html_doc = null;
        $model = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $document->id)->first();
        if ($model) {
            $path = $model->doc_path.$model->doc_key.'.html';
            if (Storage::disk('public')->exists($path)){
                $html_doc = Storage::disk('public')->get($path);
            }
        }

        return view('protocol.members.view', compact('document', 'documentMember',
            'html_doc', 'documentFiles', 'documentMembers', 'documentReplyComments', 'documentMemberRoles', 'documentHrEmp', 'qrKey',
            'signType', 'documentComments', 'comment_inputs', 'authrole'));
    }
}
