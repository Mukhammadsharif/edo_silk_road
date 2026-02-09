<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Admin\StatusList;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Comment\DocumentComment;
use App\Models\Comment\DocumentCommentUser;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentOrganizations;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\Document\Documents;
use App\Models\Document\ReplyDocumentFiles;
use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use App\Models\DocumentTypeTemps\DocumentTypeTemps;
use App\Models\Journal\JournalTypes;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\QrCodeKeys;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class JournalController extends Controller
{
    //
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }
    public function workDocs(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_type = $request->input('doc_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status');

        $search = DocumentJournals::whereHas('journal', function ($query) {
            $query->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING']);
        })->whereIn('created_user', $this->authActiveUserIds());

        $today = Carbon::now()->format('Y-m-d');

        if ($status == '12') {
            $search->whereHas('doc', function ($query) use ($today) {
                $query->where(function ($docQuery) use ($today) {
                    $docQuery->where('doc_term', '<', $today)
                        ->whereNotIn('status', [2, 9, 10, 11]);
                });
            });
        } elseif ($status == '14') {
            $search->whereNotIn('status', [2, 10, 11])
                ->whereHas('directorResolution', function ($directorResolutionQuery) use ($today) {
                    $directorResolutionQuery->where('res_term', '<', $today);
                });
        } elseif ($status) {
            $search->where('status', $status);
        } else {
            $search->where('status', '!=', 2);
        }

        if ($doc_type && $doc_type != 'all') {
            $search->where('j_type', $doc_type);
        }

        if (trim($doc_name)) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%')
                    ->orWhere('doc_text', 'ILIKE', '%'.$doc_name.'%');
            });
        }

        if ($date_from && $date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
                $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
            });
        } elseif ($date_from && !$date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_from) {
                $query->where('doc_date', '>=', $tmp_date_from);
            });
        } elseif (!$date_from && $date_till) {
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_till) {
                $query->where('doc_date', '<=', $tmp_date_till);
            });
        }

        $models = $search->where('status', '!=', 13)->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_type' => $doc_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
        ]);
        $models->withQueryString();

        $journal_types = JournalTypes::whereDoesntHave('childs')
            ->where('status', 1)
            ->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING'])
            ->orderBy('order_number')->get();

        $statusNames = StatusList::where('type', 'work_document')
            ->whereNotIn('code', [4,13])
            ->orderBy('order_number')->get();

        $default_status = StatusList::where('type', 'work_document')->orderBy('code')->first();

        return view('office.journal.work.index',
            compact('models', 'doc_name', 'journal_types', 'doc_type', 'status', 'date_from', 'date_till','statusNames', 'default_status'));
    }

    public function inboxDocs(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_org = $request->input('doc_org');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status');


        $search = DocumentJournals::whereHas('journal', function ($query) {
            $query->where('doc_code', 'BASE_INCOMING');
        });

        if ($status) {
            $search->where('status', $status);
        }

        if ($doc_org && $doc_org != 'all') {
            $search->whereHas('docOrg', function ($query) use ($doc_org) {
                $query->where('doc_org_id', $doc_org);
            });
        }

        if (trim($doc_name)) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%')
                    ->orWhere('doc_number', 'ILIKE', '%'.$doc_name.'%');;
            });
        }

        if ($date_from && $date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
                $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
            });
        } elseif ($date_from && !$date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_from) {
                $query->where('doc_date', '>=', $tmp_date_from);
            });
        } elseif (!$date_from && $date_till) {
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_till) {
                $query->where('doc_date', '<=', $tmp_date_till);
            });
        }

        $models = $search->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_org' => $doc_org,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
        ]);
        $models->withQueryString();

        $statusNames = StatusList::where('type', 'work_document')
            ->whereNotIn('code', [4,13])
            ->orderBy('order_number')->get();

        $default_status = StatusList::where('type', 'work_document')->orderBy('code')->first();

        $journal = JournalTypes::where('doc_code', 'BASE_INCOMING')->first();

        $doc_organizations = DocumentOrganizations::where('for_doc_type', $journal->id)->where('status', 1)->get();

        return view('office.journal.inbox.index',
            compact('models', 'doc_name', 'doc_org', 'status', 'date_from', 'date_till','statusNames', 'default_status',
                'doc_organizations'));
    }

    public function outgoingDocs(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_org = $request->input('doc_org');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $doc_number = $request->input('doc_number');
        $status = $request->input('status');

        $search = DocumentJournals::whereHas('journal', function ($query) {
            $query->where('doc_code', 'BASE_OUTGOING');
        })->whereIn('created_user', $this->authActiveUserIds());

        if ($status) {
            $search->where('status', $status);
        }

        if ($doc_org && $doc_org != 'all') {
            $search->whereHas('docOrg', function ($query) use ($doc_org) {
                $query->where('doc_org_id', $doc_org);
            });
        }

        if (trim($doc_name)) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%')
                    ->orWhere('doc_text', 'ILIKE', '%'.$doc_name.'%');
            });
        }

        if ($date_from && $date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_from, $tmp_date_till) {
                $query->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
            });
        } elseif ($date_from && !$date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_from) {
                $query->where('doc_date', '>=', $tmp_date_from);
            });
        } elseif (!$date_from && $date_till) {
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y/m/d');
            $search->whereHas('doc', function ($query) use ($tmp_date_till) {
                $query->where('doc_date', '<=', $tmp_date_till);
            });
        }

        if ($doc_number) {
            $search->whereHas('doc', function ($query) use ($doc_number) {
                $query->where('doc_number', $doc_number);
            });;
        }

        $models = $search->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_org' => $doc_org,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
        ]);
        $models->withQueryString();

        $statusNames = StatusList::where('type', 'out_document')
            ->whereNotIn('code', [4,13])
            ->orderBy('order_number')->get();

        $default_status = StatusList::where('type', 'out_document')->orderBy('code')->first();

        $journal = JournalTypes::where('doc_code', 'BASE_OUTGOING')->first();

        $doc_organizations = DocumentOrganizations::where('for_doc_type', $journal->id)->where('status', 1)->get();

        return view('office.journal.sent.index',
            compact('models', 'doc_name', 'doc_org', 'status', 'date_from', 'date_till','statusNames', 'default_status',
                'doc_organizations'));
    }

    public function viewWork($id, $hash): Factory|View|Application
    {
        //
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->firstOrFail();

        $documentFiles= DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user??0)->first();

        $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', $directorDocument->to_user??0)->get();
        $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', '!=', $directorDocument->to_user??0)->get();

        $isAddResolution = [];

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();


        $agUserCount = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->whereIn('status', [-6,-5,0])->count();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $edoCancelMessages = [];

        $view = 'view';

        if (str_contains($journal->journal->doc_code, 'APP')) {
            $view = 'view-app';
        } elseif ($journal->journal->doc_code == 'COMMAND') {
            $view = 'view-order';
        } elseif ($journal->journal->doc_code == 'HR_ORD_MT') {
            $view = 'view-hr-order';
        } elseif ($journal->journal->doc_code == 'REPORT_FORM') {
            $view = 'view-report-form';
        }

        $qrKey = QrCodeKeys::where('doc_type', 'work_doc')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

        $execution_step_ids = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->pluck('id')->toArray();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $id)->get();

        $comment_inputs = ['doc_id' => $id, 'doc_hash' => $hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
            'document_comment_users.is_read' => 2,
            'document_comment_users.is_read_date' => Carbon::now()
        ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        $type = 'internal';

        return view('office.journal.work.'.$view, compact('authUser','journal', 'document','edoCancelMessages', 'documentFiles',
            'directorDocument','directorResolution','selectedPerformers', 'isAddResolution','selectedAddPerformers','html_doc',
            'agreement_users', 'replyDocuments','agUserCount', 'qrKey', 'signer', 'signType', 'execution_step_ids', 'documentComments', 'comment_inputs',
            'model', 'type'));

    }

    public function viewInbox($id, $hash): Factory|View|Application
    {
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->firstOrFail();

        $documentFiles= DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user??0)->first();

        $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', $directorDocument->to_user)->get();

        $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', '!=', $directorDocument->to_user)->get();

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'work_doc')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

        $execution_step_ids = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->pluck('id')->toArray();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $id)->get();

        $comment_inputs = ['doc_id' => $id, 'doc_hash' => $hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $type = 'incoming';

        return view('office.journal.inbox.view', compact('authUser','journal', 'document', 'documentFiles',
            'directorDocument','directorResolution','selectedPerformers', 'selectedAddPerformers',
            'agreement_users', 'replyDocuments', 'qrKey', 'signer', 'signType', 'execution_step_ids', 'documentComments', 'comment_inputs',
             'type'));
    }

    public function viewSent($id, $hash): Factory|View|Application
    {
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->firstOrFail();

        $documentFiles= DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user??0)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'out_doc')->where('doc_id', $document->id)->first();

        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $id)->get();

        $comment_inputs = ['doc_id' => $id, 'doc_hash' => $hash, 'doc_type' => 1];

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        $type = 'outgoing';

        return view('office.journal.sent.view', compact('authUser','journal', 'document', 'documentFiles',
            'directorDocument','directorResolution', 'agreement_users',  'qrKey', 'signType', 'documentComments', 'comment_inputs',
            'replyDocuments', 'html_doc', 'type', 'model'));
    }

    public function deleteDocument($id): \Illuminate\Http\JsonResponse
    {
        //

        $journal = DocumentJournals::findOrFail($id);

        $url = \route('home');

        if ($journal) {
            if ($journal->journal->doc_code === 'BASE_INCOMING') {
                $url = \route('office.inbox-docs');
            } else if ($journal->journal->doc_code === 'BASE_OUTGOING') {
                $url = \route('office.outgoing-docs');
            } else {
                $url = \route('office.work-docs');
            }

            $document = Documents::where('id', $journal->doc_id)->firstOrFail();

            if ($document) $document->delete();

            $documentFiles= DocumentFiles::where('j_id', $journal->id)->get();

            if ($documentFiles) {
                foreach ($documentFiles as $file) $file->delete();
            }

            $executionSteps = DocumentExecutionSteps::where('j_id', $id)->get();

            if ($executionSteps) {
                foreach ($executionSteps as $step) $step->delete();
            }

            $comments = DocumentReplyComments::where('j_id', $id)->get();

            if ($comments) {
                foreach ($comments as $comment) $comment->delete();
            }

            $resolutions = DocumentResolutions::where('j_id', $id)->get();

            if ($resolutions) {
                foreach ($resolutions as $resolution) $resolution->delete();
            }

            $replyDocumentFiles = ReplyDocumentFiles::where('j_id', $id)->get();

            if ($replyDocumentFiles) {
                foreach ($replyDocumentFiles as $replyDocumentFile) $replyDocumentFile->delete();
            }

            $journal->delete();

            $journalType = $journal->journal->title??'';
            $docName = $journal->doc->doc_name??'';
            $this->createLog($journal->doc_id, 3, $journalType, $docName);

            return response()->json([
                'success' => 200,
                'title' => __('message.success_message'),
                'message' => __('message.doc_deleted_success_message'),
                'url' => $url,
            ]);

        } else {
            return response()->json([
                'success' => 201,
                'title' => __('message.error_message'),
                'message' => __('message.document_deleted_system'),
                'url' => $url,
            ]);
        }

    }
}
