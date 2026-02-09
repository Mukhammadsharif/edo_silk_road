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
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProtocolEmpHrOrdersController extends Controller
{
    //
    public function index(Request $request)
    {
        //
        $s_text = $request->input('s_text');

        $s_status = $request->input('s_status')??2;

        $s_type = $request->input('s_type');

        $search = ProtocolDocMember::where('role_id', 4)->whereIN('user_id', Auth::user()->userWorkIDs());

        if (trim(''.$s_text.'')) {
            $search->whereHas('document', function ($query) use ($s_text) {
                $query->where('doc_name', 'ILIKE', '%'.$s_text.'%')->orWhere('doc_number', 'ILIKE', '%'.$s_text.'%');
            });
        }

        if ($s_status == 'all') {
            $search->whereNotIn('status', [1,6]);
        } elseif ($s_status) {
            $search->where('status', $s_status);
        } else {
            $search->where('status', 2);
        }

        $models = $search->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            's_text' => $s_text,
            's_status' => $s_status,
            's_type' => $s_type
        ]);

        $statusList = StatusList::where('type', 'prt_status')->whereNotIn('code', [1,6])->get();

        return view('protocol.emp-hr-orders.index', compact('models', 's_text', 's_status',
            's_type', 'statusList'));
    }

    public function view($id, $doc_hash)
    {
        //
        $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();

        $documentMember = ProtocolDocMember::where('id', $id)->where('doc_id', $document->id)->firstOrFail();

//        $documentText = ProtocolDocText::where('doc_id', $document->id)->first();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $documentMembers = ProtocolDocMember::where('doc_id', $document->id)->get();

        $documentMemberRoles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->orderBy('order_number')->get();

        $documentReplyComments = ProtocolDocReplyComment::where('doc_id', $document->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $this->authActiveUser()->user_id)->first();

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

        $html_doc = null;
        $model = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $document->id)->first();
        if ($model) {
            $path = $model->doc_path.$model->doc_key.'.html';
            if (Storage::disk('ftp')->exists($path)){
                $html_doc = Storage::disk('ftp')->get($path);
            }
        }

        return view('protocol.emp-hr-orders.view', compact('document', 'documentMember',
             'documentFiles', 'documentMembers', 'documentReplyComments', 'documentMemberRoles',
            'documentHrEmp', 'qrKey', 'signType', 'authrole', 'documentComments', 'comment_inputs', 'html_doc'));
    }
}
