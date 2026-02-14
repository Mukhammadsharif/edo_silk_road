<?php

namespace App\Http\Controllers\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Admin\Role;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocComments;
use App\Models\Protocol\ProtocolDocCommentUsers;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocText;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProtocolCommentController extends Controller
{
    //
    public function getCounts(): JsonResponse
    {
        //
        $auth_role = 'user';

        $comments = ProtocolDocCommentUsers::with(['comment.userWork.personal', 'comment.document', 'userWork.personal'])
            ->whereIn('user_id', $this->authActiveUserIds())->where('is_read', 1)->orderBy('id', 'DESC')->get();

        if (in_array($this->chairmanUser, $this->authActiveUserRoleNames())) {
            $auth_role = 'chairman';
        }

        return response()->json([
            'success' => 200,
            'auth_role' => $auth_role,
            'comments' => $comments,
        ]);

    }

    public function getComments($id): JsonResponse
    {
        $comments = ProtocolDocCommentUsers::where('doc_com_id', $id)->get();

        $commentsArray = [];

        foreach ($comments as $comment) {
            $editedComment = [
                'status' => $comment->is_read,
                'time' => $comment->is_read_date,
                'user' => $comment->userWork->personal->shortName(),
            ];

            $commentsArray[] = $editedComment;
        }

        return response()->json([
            'success' => 200,
            'comments' => $commentsArray,
        ]);

    }

    public function viewComment($id, $doc_hash)
    {
        //
        $view_page = 'protocol.secretary.view';
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->first();

        $documentMember = ProtocolDocMember::where('doc_id', $id)->whereIn('user_id', $this->authActiveUserIds())->first();

        if ($documentMember) {
            $view_page = 'protocol.members.view';
        }

        if (in_array($this->chairmanUser, $this->authActiveUserRoleNames())) {
            $view_page = 'doc-controller.protocols.view';
        }

//        $documentText = ProtocolDocText::where('doc_id', $document->id)->first();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $documentMembers = ProtocolDocMember::where('doc_id', $document->id)->get();

        $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->orderBy('order_number')->get();

        $documentMemberRoles = StatusList::where('type', 'prt_role_type')->orderBy('code')->get();

        $documentReplyComments = ProtocolDocReplyComment::where('doc_id', $document->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $this->authActiveUser()->user_id)->first();

        $userComments = ProtocolDocComments::where('doc_id', $document->id)->get();

        $userCommentsIds = $userComments->count() ?  $userComments->pluck('id')->toArray() : [];

        $userDocComments = ProtocolDocCommentUsers::whereIn('doc_com_id', $userCommentsIds)
            ->whereIn('user_id', $this->authActiveUserIds())->get();

        if ($userDocComments->count()) {
            foreach ($userDocComments as $userDocComment) {
                $userDocComment->update([
                    'is_read' => 2,
                    'is_read_date' => Carbon::now()
                ]);
            }
        }

        $authrole = $this->authActiveUserRoleNames();

        $html_doc = null;
        $model = OnlyOfficeDoc::where('doc_type', 'PRT')->where('doc_id', $document->id)->first();
        if ($model) {
            $path = $model->doc_path.$model->doc_key.'.html';
            if (Storage::disk('ftp')->exists($path)){
                $html_doc = Storage::disk('ftp')->get($path);
            }
        }

        return view($view_page, compact('document', 'documentMember',
            'documentFiles', 'documentMembers', 'documentReplyComments', 'documentMemberRoles', 'documentHrEmp', 'qrKey',
            'signType', 'userComments', 'userDocComments', 'authrole', 'html_doc'));
    }

    public function sendComment(Request $request, $id, $doc_hash): JsonResponse
    {
        //
        $code = 201;
        $document = ProtocolDocument::where('id', $id)->where('doc_hash', $doc_hash)->first();
        if ($document) {
            $code = 200;

            $protocolDocComment = new ProtocolDocComments();
            $protocolDocComment->doc_id = $document->id;
            $protocolDocComment->doc_text = $request->r_text;
            $protocolDocComment->created_id = $this->authActiveUser()->id;
            $protocolDocComment->status = 1;
            $protocolDocComment->created_date = Carbon::now();
            $protocolDocComment->save();

            // Members
            $members = ProtocolDocMember::where('doc_id', $id)->get();
            if ($members->count()) {
                foreach ($members as $member) {
                    $protocolDocCommentUser = new ProtocolDocCommentUsers();
                    $protocolDocCommentUser->doc_com_id = $protocolDocComment->id;
                    $protocolDocCommentUser->user_id = $member->user_id;
                    $protocolDocCommentUser->is_read_date = Carbon::now();
                    $protocolDocCommentUser->save();
                }
            }

            // Chairman
            $roleChairman = Role::where('name', 'Kuzatuv Kengashi Raisi')->where('status', 1)->first();
            if ($roleChairman) {

                $userRoles = UserRole::where('role_id', $roleChairman->id)->get();
                if (count($userRoles)) {
                    foreach ($userRoles as $userRole) {
                        $userWork = UserWorkHistory::where('user_id', $userRole->model_id)->where('status', 1)->first();
                        if ($userWork) {
                            $commentSecretary = new ProtocolDocCommentUsers();
                            $commentSecretary->doc_com_id = $protocolDocComment->id;
                            $commentSecretary->user_id = $userWork->id;
                            $commentSecretary->is_read_date = Carbon::now();
                            $commentSecretary->save();
                        }
                    }
                }

            }

            $checkSecretary = ProtocolDocCommentUsers::where('user_id', $document->created_user)->first();
            if (!$checkSecretary) {
                // Secretary
                $commentSecretary = new ProtocolDocCommentUsers();
                $commentSecretary->doc_com_id = $protocolDocComment->id;
                $commentSecretary->user_id = $document->created_user;
                $commentSecretary->is_read_date = Carbon::now();
                $commentSecretary->save();
            }


        }

        return response()->json([
            'success' => $code,
            'title' => __('message.success_message'),
            'message' => __('message.your_comment_has_been_sent_successfully')
        ]);
    }
}
