<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Admin\Role;
use App\Models\Admin\UserRole;
use App\Models\Comment\DocumentComment;
use App\Models\Comment\DocumentCommentUser;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\Documents;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocument;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class DocumentCommentController extends Controller
{
    //
    public function getCounts(): JsonResponse
    {
        //
        $comments = DocumentCommentUser::with(['comment.createdUser.personal', 'userWork.personal'])
            ->whereIn('user_id', $this->authActiveUserIds())->where('is_read', 1)
            ->orderBy('id', 'DESC')->get();

        return response()->json([
            'success' => 200,
            'comments' => $comments,
        ]);

    }

    public function getComments($id): JsonResponse
    {
        $comments = DocumentCommentUser::where('comment_id', $id)->orderBy('id', 'DESC')->get();

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

    public function searchComments(Request $request)
    {
        $keyword = $request->keyword;

        $comments = DocumentCommentUser::with(['comment.createdUser.personal', 'userWork.personal'])
            ->whereIn('user_id', $this->authActiveUserIds())->where('is_read', 1)
            ->orderBy('id', 'DESC')->get();

        if($keyword != ''){
            $comments = DocumentCommentUser::with(['comment.createdUser.personal', 'userWork.personal'])
                ->whereHas('comment.createdUser.personal', function ($query) use($keyword) {
                    $query->where(DB::raw("CONCAT_WS(' ', l_name, f_name, m_name)"), 'ILIKE', '%'.$keyword.'%');
                })->whereHas('comment', function ($query1) {
                    $query1->where('created_date', '>=', Carbon::now()->subDays(30)->toDateTimeString());
                })->whereIn('user_id', $this->authActiveUserIds())
                ->orderBy('document_comment_users.id', 'DESC')->get();
        }

        return response()->json([
            'comments' => $comments
        ]);
    }

    public function viewComment($doc_id, $doc_type)
    {
        //
        $authActiveUser = $this->authActiveUser()->id;
        $view_page = 'protocol.secretary.view';
        if ($doc_type == 1) {
            $journal = DocumentJournals::find($doc_id);
            if ($journal) {
                $document = Documents::find($journal->doc_id);
                if ($document) {
                    if ($journal->created_user == $authActiveUser) {
                        if ($journal->journal->doc_code === 'BASE_INCOMING'){
                            return Redirect::route('office.view-inbox-doc', [$doc_id, $document->doc_hash]);
                        } else if ($journal->journal->doc_code === 'BASE_OUTGOING') {
                            return Redirect::route('office.view-sent-doc', [$doc_id, $document->doc_hash]);
                        } else {
                            return Redirect::route('office.view-work-doc', [$doc_id, $document->doc_hash]);
                        }
                    }

                    $sign_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                        $query->where('doc_code', 'SIGN')->where('status', 1);
                    })->whereIn('to_user', $this->authActiveUserIds())->first();

                    if ($sign_step) {
                        return Redirect::route('document.sign.view', [$doc_id, $document->doc_hash]);
                    }

                    $agreement_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                        $query->where('doc_code', 'AGREE')->where('status', 1);
                    })->whereIn('to_user', $this->authActiveUserIds())->first();

                    if ($agreement_step) {
                        return Redirect::route('document.approval.view', [$doc_id, $document->doc_hash]);
                    }

                    $resolution_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                        $query->where('doc_code', 'RESOL')->where('status', 1);
                    })->whereIn('to_user', $this->authActiveUserIds())->first();

                    if ($resolution_step) {
                        return Redirect::route('document.confirm.view', [$doc_id, $document->doc_hash]);
                    }

                    $execution_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                        $query->where('doc_code', 'EXEC')->where('status', 1);
                    })->whereIn('to_user', $this->authActiveUserIds())->first();

                    if ($execution_step) {
                        return Redirect::route('document.exec.view', [$doc_id, $document->doc_hash]);
                    }

                    if (in_array($this->chairmanUser, $this->authActiveUserRoleNames())) {
                        return Redirect::route('doc-controller.action-route', [$doc_id, $document->doc_hash]);
                    }
                }
            }
        } elseif ($doc_type == 2) {
            $protocol = ProtocolDocument::find($doc_id);
            if ($protocol) {
                if ($protocol->created_user == $authActiveUser) {
                    return Redirect::route('doc-controller.protocol-view', [$doc_id, $protocol->doc_hash]);
                }

                $documentMember = ProtocolDocMember::where('doc_id', $doc_id)->whereIn('user_id', $this->authActiveUserIds())->first();
                if ($documentMember) {
                    if ($documentMember->role_id == 4) {
                        return Redirect::route('prt-emp-hr-order-view', [$documentMember->id, $protocol->doc_hash]);
                    }
                    return Redirect::route('protocol-members-view', [$documentMember->id, $protocol->doc_hash]);
                }

                if (in_array($this->chairmanUser, $this->authActiveUserRoleNames())) {
                    return Redirect::route('doc-controller.protocol-view', [$doc_id, $protocol->doc_hash]);
                }

            }
        }

        return Redirect::back();

    }

    public function sendComment(Request $request, $id, $doc_hash): JsonResponse
    {
        //
        $code = 201;
        $doc_id = $request->com_doc_id;
        $doc_hash = $request->com_doc_hash;
        $doc_type = $request->com_doc_type;
        $doc_text = $request->r_text;

        $commentCreate = new DocumentComment();
        $commentCreate->doc_type = $doc_type;
        $commentCreate->doc_id = $doc_id;
        $commentCreate->doc_text = $doc_text;
        $commentCreate->created_id = $this->authActiveUser()->id;
        $commentCreate->status = 1;
        $commentCreate->created_date = Carbon::now();
        $commentCreate->save();

        if ($doc_type == 1) {
            $code = 200;

            $journal = DocumentJournals::find($doc_id);
            if ($journal) {
                $arrayCreatedUser = array($journal->created_user);

                $arrayExecutors = DocumentExecutionSteps::where('j_id', $doc_id)->pluck('to_user')->toArray();

                $arrayChairman = [];
                $roleChairman = Role::where('name', 'Kuzatuv Kengashi Raisi')->where('status', 1)->first();
                if ($roleChairman) {
                    $arrayChairman = UserRole::leftJoin('user_work_histories', function ($join) {
                        $join->on('model_has_roles.model_id', '=', 'user_work_histories.user_id')->where('user_work_histories.status', 1);
                    })->where('model_has_roles.role_id', $roleChairman->id)
                        ->where('user_work_histories.id', '!=', null)
                        ->pluck('user_work_histories.id')->toArray();
                }

                $arrayMerge = array_merge($arrayCreatedUser, $arrayExecutors, $arrayChairman);

                if (count($arrayMerge)) {
                    foreach (array_unique($arrayMerge) as $value) {
                        $commentUserCreate = new DocumentCommentUser();
                        $commentUserCreate->comment_id = $commentCreate->id;
                        $commentUserCreate->doc_id = $doc_id;
                        $commentUserCreate->user_id = $value;
                        $commentUserCreate->is_read_date = Carbon::now();
                        $commentUserCreate->save();
                    }
                }

                $journalType = $journal->journal->title??'';
                $docName = $journal->doc->doc_name??'';
                $this->createLog($journal->doc_id, 27, $journalType, $docName);
            }
        } elseif($doc_type == 2) {
            $code = 200;
            $protocol = ProtocolDocument::find($doc_id);
            if ($protocol) {
                $arrayCreatedUser = array($protocol->created_user);

                $arrayExecutors = ProtocolDocMember::where('doc_id', $doc_id)->pluck('user_id')->toArray();

                $arrayChairman = [];
                $roleChairman = Role::where('name', 'Kuzatuv Kengashi Raisi')->where('status', 1)->first();
                if ($roleChairman) {
                    $arrayChairman = UserRole::leftJoin('user_work_histories', function ($join) {
                        $join->on('model_has_roles.model_id', '=', 'user_work_histories.user_id')->where('user_work_histories.status', 1);
                    })->where('model_has_roles.role_id', $roleChairman->id)
                        ->where('user_work_histories.id', '!=', null)
                        ->pluck('user_work_histories.id')->toArray();
                }

                $arrayMerge = array_merge($arrayCreatedUser, $arrayExecutors, $arrayChairman);

                if (count($arrayMerge)) {
                    foreach (array_unique($arrayMerge) as $value) {
                        $commentUserCreate = new DocumentCommentUser();
                        $commentUserCreate->comment_id = $commentCreate->id;
                        $commentUserCreate->doc_id = $doc_id;
                        $commentUserCreate->user_id = $value;
                        $commentUserCreate->is_read_date = Carbon::now();
                        $commentUserCreate->save();
                    }
                }

                $log_type = $protocol->docType->title ?? 'Protocol Hr Emp order';
                $doc_name = $protocol->doc_name ?? $protocol->id;
                $this->createLog($protocol->id, 27, $log_type, $doc_name);
            }

        }

        return response()->json([
            'success' => $code,
            'title' => __('message.success_message'),
            'message' => __('message.your_comment_has_been_sent_successfully')
        ]);
    }
}
