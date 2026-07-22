<?php

namespace App\Http\Controllers;

use App\Models\Admin\StatusList;
use App\Services\FreeSign\FreeSignRouteService;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\Comment\DocumentComment;
use App\Models\Comment\DocumentCommentUser;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\Document\Documents;
use App\Models\Document\ReplyDocumentFiles;
use App\Models\Journal\JournalExecutionStepRoles;
use App\Models\Journal\JournalExecutionSteps;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\QrCodeKeys;
use App\Models\Resolution\ReplyCommentTypes;
use App\Models\Resolution\ResolutionExecutionRoles;
use App\Models\Resolution\ResolutionTypes;
use App\Models\User;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExecutionController extends Controller
{
    //
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function forExecutionIndex(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_type = $request->input('doc_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status');
        $created_user = $request->input('created_user');

        $workIds = $this->authActiveUserIds();

        $statusNames = StatusList::where('type', 'doc_exec')->orderBy('order_number')->get();

        $cr_users = User::where('status', 1)->where('org_unit_id', $this->organizationId())->get();

        $statusArray = $status ? [$status] : [2,4];

        $search = DocumentJournals::where('org_unit_id', $this->organizationId())
            ->whereHas('executionSteps', function ($query) use ($workIds) {
            $query->whereIn('to_user', $workIds)->where('status', '>', 1)->whereHas('role', function ($query) {
                $query->where('doc_code', 'EXEC');
            });
        })->where('status', '>', 7);

        if ($status != 'all') {
            $search = DocumentJournals::where('org_unit_id', $this->organizationId())
                ->whereHas('executionSteps', function ($query) use ($statusArray, $workIds) {
                $query->whereIn('to_user', $workIds)->whereIn('status', $statusArray)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'EXEC');
                });
            })->where('status', '>', 1);
        }

        if ($doc_type && $doc_type != 'all') {
            $search->where('j_type', $doc_type);
        }

        if ($created_user){
            $search->whereIn('created_user', $this->getWorkUserIds($created_user));
        }

        if (trim(''.$doc_name.'')) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%');
                $query->orWhere('doc_number', 'ILIKE', '%'.$doc_name.'%');
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

        $journal_types = JournalTypes::whereDoesntHave('childs')
            ->where('org_id', $this->organizationId())
            ->where('status', 1)
            ->whereNotIn('doc_code', ['BASE_OUTGOING'])
            ->orderBy('order_number')->get();


        $models = $search->where('status', '!=', 13)->orderBy('id', 'DESC')->paginate(25);


        $models->appends([
            'doc_name' => $doc_name,
            'doc_type' => $doc_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
            'created_user' => $created_user,
            'cr_users' => $cr_users
        ]);
        $models->withQueryString();

        return view('execution.executor.index', compact('models', 'journal_types', 'doc_name', 'doc_type', 'date_from',
            'date_till', 'status', 'statusNames', 'cr_users', 'created_user'));
    }

    public function forSignIndex(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_type = $request->input('doc_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status')??2;
        $created_user = $request->input('created_user');

        $statusNames = StatusList::where('type', 'doc_signer')->orderBy('order_number')->get();

        $cr_users = User::where('status', 1)->where('org_unit_id', $this->organizationId())->get();

        $workIds = $this->authActiveUserIds();

        $search = DocumentJournals::where('org_unit_id', $this->organizationId())
            ->whereHas('executionSteps', function ($query) use ($status, $workIds) {
            $query->whereIn('to_user', $workIds)->whereHas('role', function ($query) {
                $query->where('doc_code', 'SIGN');
            });
        })->where('status', '>', 1);

        if ($created_user){
            $search->whereIn('created_user', $this->getWorkUserIds($created_user));
        }

        if ($status != 'all') {
            $search = DocumentJournals::where('org_unit_id', $this->organizationId())
                ->whereHas('executionSteps', function ($query) use ($status, $workIds) {
                $query->whereIn('to_user', $workIds)->where('status', $status)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'SIGN');
                });
            })->where('status', '>', 1);
        }

        if ($doc_type && $doc_type != 'all') {
            $search->where('j_type', $doc_type);
        }

        if (trim(''.$doc_name.'')) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%');
                $query->orWhere('doc_number', 'ILIKE', '%'.$doc_name.'%');
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

        $journal_types = JournalTypes::whereDoesntHave('childs')
            ->where('org_id', $this->organizationId())
            ->where('status', 1)
            ->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING'])
            ->orderBy('order_number')->get();


        $models = $search->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_type' => $doc_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
            'created_user' => $created_user,
        ]);
        $models->withQueryString();

        return view('execution.sign.index', compact('models', 'journal_types', 'doc_name', 'doc_type', 'date_from',
            'date_till', 'status', 'statusNames', 'created_user', 'cr_users'));
    }

    public function forApprovalIndex(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_type = $request->input('doc_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status')??2;
        $created_user = $request->input('created_user');

        $workIds = $this->authActiveUserIds();

        $statusNames = StatusList::where('type', 'doc_signer')->orderBy('order_number')->get();

        $cr_users = User::where('status', 1)->where('org_unit_id', $this->organizationId())->get();

        $search = DocumentJournals::where('org_unit_id', $this->organizationId())
            ->whereHas('executionSteps', function ($query) use ($status, $workIds) {
            $query->whereIn('to_user', $workIds)->whereHas('role', function ($query) {
                $query->where('doc_code', 'AGREE');
            });
        })->where('status', '>', 1);

        if ($status != 'all') {
            $search = DocumentJournals::where('org_unit_id', $this->organizationId())
                ->whereHas('executionSteps', function ($query) use ($status, $workIds) {
                $query->whereIn('to_user', $workIds)->where('status', $status)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'AGREE');
                });
            })->where('status', '>', 1);
        }

        if ($created_user){
            $search->whereIn('created_user', $this->getWorkUserIds($created_user));
        }

        if ($doc_type && $doc_type != 'all') {
            $search->where('j_type', $doc_type);
        }

        if (trim(''.$doc_name.'')) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%');
                $query->orWhere('doc_number', 'ILIKE', '%'.$doc_name.'%');
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

        $journal_types = JournalTypes::whereDoesntHave('childs')
            ->where('org_id', $this->organizationId())
            ->where('status', 1)
            ->whereNotIn('doc_code', ['BASE_INCOMING'])
            ->orderBy('order_number')->get();

        $models = $search->where('status', '!=', 13)->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_type' => $doc_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
            'created_user' => $created_user,
            'cr_users' => $cr_users
        ]);
        $models->withQueryString();

        return view('execution.agreement.index', compact('models', 'journal_types', 'doc_name', 'doc_type', 'date_from',
            'date_till', 'status', 'statusNames', 'cr_users', 'created_user'));
    }

    public function forConfirmIndex(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_type = $request->input('doc_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status');
        $created_user = $request->input('created_user');

        $workIds = $this->authActiveUserIds();

        $statusArray = $status ? [$status] : [8,11];

        $statusNames = StatusList::where('type', 'work_document')->whereNotIn('code', [1, 4, 5, 6, 7, 13])->orderBy('order_number')->get();

        $cr_users = User::where('status', 1)->where('org_unit_id', $this->organizationId())->get();

        $search = DocumentJournals::where('org_unit_id', $this->organizationId())
            ->whereHas('executionSteps', function ($query) use ($status, $workIds) {
            $query->whereIn('to_user', $workIds)->whereHas('role', function ($query) {
                $query->where('doc_code', 'RESOL');
            });
        })->whereNotIn('status', [1, 4, 5, 6, 7, 13]);

        if ($created_user){
            $search->whereIn('created_user', $this->getWorkUserIds($created_user));
        }

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
        }

        if (!in_array($status, ['all', '12', '14'])) {
            $search = DocumentJournals::where('org_unit_id', $this->organizationId())
                ->whereHas('executionSteps', function ($query) use ($status, $workIds) {
                $query->whereIn('to_user', $workIds)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'RESOL');
                });
            })->whereIn('status', $statusArray);
        }

        if ($doc_type && $doc_type != 'all') {
            $search->where('j_type', $doc_type);
        }

        if (trim(''.$doc_name.'')) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%');
                $query->orWhere('doc_number', 'ILIKE', '%'.$doc_name.'%');
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

        $journal_types = JournalTypes::whereDoesntHave('childs')
            ->where('org_id', $this->organizationId())
            ->where('status', 1)
            ->orderBy('order_number')->get();

        $models = $search->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_type' => $doc_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
            'created_user' => $created_user,
        ]);
        $models->withQueryString();

        return view('execution.confirm.index', compact('models', 'journal_types', 'doc_name', 'doc_type', 'date_from',
            'date_till', 'status', 'statusNames', 'cr_users', 'created_user'));
    }

    public function forControlIndex(Request $request): Factory|View|Application
    {
        //
        $doc_name = $request->input('doc_name');
        $doc_type = $request->input('doc_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status');

        $controls = JournalTypeControls::where('status', 1)->pluck('journal_type_id')->toArray();

        $statusNames = StatusList::where('type', 'work_document')->where('code', '!=', 1)->orderBy('order_number')->get();

        /* ═══ v41: контроль стал ОПЦИЕЙ ДОКУМЕНТА (free-sign) ═══
         | Выбор хранится в ОТДЕЛЬНОЙ таблице free_sign_journal_controls
         | (document_journals не трогаем). Видимость в списке «Контроль»:
         |   запись enabled=true  -> показываем ВСЕГДА (даже если тип
         |          не в controls) — иначе статус 7 = невидим = зависание
         |   запись enabled=false -> НЕ показываем (явно отключён)
         |   записи нет           -> как раньше, по типу (штатные/старые)
         | Подзапросы (не pluck) — чтобы не грузить id в память.
         */
        $fsControlOn  = \App\Models\FreeSignJournalControl::query()->select('j_id')->where('enabled', true);
        $fsControlOff = \App\Models\FreeSignJournalControl::query()->select('j_id')->where('enabled', false);

        $search = DocumentJournals::where('org_unit_id', $this->organizationId())
            ->where('status', '!=', 1)
            ->where(function ($q) use ($controls, $fsControlOn, $fsControlOff) {
                $q->whereIn('id', $fsControlOn)
                  ->orWhere(function ($q2) use ($controls, $fsControlOff) {
                      $q2->whereIn('j_type', $controls)->whereNotIn('id', $fsControlOff);
                  });
            });

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
        }

        if ($doc_type && $doc_type != 'all') {
            $search->where('j_type', $doc_type);
        }

        if (trim(''.$doc_name.'')) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'ILIKE', '%'.$doc_name.'%');
                $query->orWhere('doc_number', 'ILIKE', '%'.$doc_name.'%');
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

        $journal_types = JournalTypes::whereDoesntHave('childs')
            ->where('org_id', $this->organizationId())
            ->where('status', 1)
            ->whereIn('id', $controls)
            ->orderBy('order_number')->get();

        $models = $search->where('status', '!=', 13)->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_type' => $doc_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
        ]);
        $models->withQueryString();

        return view('execution.doc-control.index', compact('models', 'journal_types', 'doc_name', 'doc_type', 'date_from',
            'date_till', 'status', 'statusNames'));
    }

    public function viewSignDocument($j_id, $doc_hash): Factory|View|Application|\Illuminate\Http\RedirectResponse
    {
        //
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($j_id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $doc_hash)->firstOrFail();
        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

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

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $edoCancelMessages = [];

        $qr_doc_type = $journal->journal->doc_code == 'BASE_OUTGOING' ? 'out_doc' : 'work_doc';

        $qrKey = QrCodeKeys::where('doc_type', $qr_doc_type)->where('doc_id', $document->id)->first();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $j_id)->get();

        $comment_inputs = ['doc_id' => $j_id, 'doc_hash' => $doc_hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->where('document_comment_users.doc_id', $j_id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        // Free-sign документ? Признак — наличие расставленных меток.
        // От этого зависит, какой ecp_doc_type отправит форма подписи.
        $isFreeSign = app(FreeSignRouteService::class)->isFreeSign($journal->doc_id);

        // Free-sign? → PDF-viewer вместо HTML (см. confirm.templates.free-sign-page)
        [$isFreeSignDoc, $documentFile, $pdfVersion, $fsGhosts] = $this->freeSignPdfData($journal);

        return view('execution.sign.view', compact('authUser','journal', 'document','edoCancelMessages', 'documentFiles',
            'directorDocument','directorResolution','selectedPerformers', 'isAddResolution','selectedAddPerformers',
            'agreement_users', 'replyDocuments', 'qrKey', 'signer', 'signType', 'documentComments', 'comment_inputs', 'html_doc', 'model', 'isFreeSign',
            'isFreeSignDoc', 'documentFile', 'pdfVersion', 'fsGhosts'));
    }

    public function viewApprovalDocument($j_id, $doc_hash): Factory|View|Application|\Illuminate\Http\RedirectResponse
    {
        $authUser = $this->authActiveUser();

        $authUserIds = $this->authActiveUserIds();

        $journal = DocumentJournals::findOrFail($j_id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $doc_hash)->firstOrFail();

        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

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

        $current_agreement = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->whereIn('to_user', $authUserIds)->first();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $edoCancelMessages = [];

        $qr_doc_type = $journal->journal->doc_code == 'BASE_OUTGOING' ? 'out_doc' : 'work_doc';

        $qrKey = QrCodeKeys::where('doc_type', $qr_doc_type)->where('doc_id', $document->id)->first();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $j_id)->get();

        $comment_inputs = ['doc_id' => $j_id, 'doc_hash' => $doc_hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->where('document_comment_users.doc_id', $j_id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        // Free-sign документ? Признак — наличие расставленных меток.
        $isFreeSign = app(FreeSignRouteService::class)->isFreeSign($journal->doc_id);

        // Free-sign? → PDF-viewer вместо HTML (см. confirm.templates.free-sign-page)
        [$isFreeSignDoc, $documentFile, $pdfVersion, $fsGhosts] = $this->freeSignPdfData($journal);

        return view('execution.agreement.view', compact('authUser','journal', 'document','edoCancelMessages', 'documentFiles',
            'directorDocument','directorResolution','selectedPerformers', 'isAddResolution','selectedAddPerformers',
            'agreement_users', 'replyDocuments', 'qrKey', 'signer', 'signType', 'current_agreement', 'documentComments', 'comment_inputs', 'html_doc', 'model', 'isFreeSign',
            'isFreeSignDoc', 'documentFile', 'pdfVersion', 'fsGhosts'));
    }

    public function viewControlDocument($j_id, $doc_hash): Factory|View|Application
    {
        //
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($j_id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $doc_hash)->firstOrFail();

        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

        $documentFiles= DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user??0)->first();

        $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', $directorDocument->to_user??0)->get();

        $isAddResolution = [];

        $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', '!=', $directorDocument->to_user??0)->get();

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $current_agreement = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->whereIn('to_user', $this->authActiveUserIds())->first();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $edoCancelMessages = [];

        $qr_doc_type = $journal->journal->doc_code == 'BASE_OUTGOING' ? 'out_doc' : 'work_doc';

        $qrKey = QrCodeKeys::where('doc_type', $qr_doc_type)->where('doc_id', $document->id)->first();

        $execution_step = JournalExecutionSteps::where('journal_type_id', $journal->j_type)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'EXEC');
            })->first();

        $execution_step_ids = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->pluck('id')->toArray();

        $execution_users = [];
        if ($execution_step) {
            $execution_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $execution_step->id)->pluck('role_id')->toArray();
            $user_roles = UserRole::whereIn('role_id', $execution_roles_ids)->pluck('model_id')->toArray();
            $execution_users = UserWorkHistory::where('org_id', $this->organizationId())
                ->whereIn('user_id', $user_roles)->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })->where('status', 1)->limit(5)->get();
        }

        $resolutionExecRoles = ResolutionExecutionRoles::where('status', 1)->orderBy('id')->get();

        $resolutionTypes = ResolutionTypes::where('status', 1)->get();

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        // Free-sign? → PDF-viewer вместо HTML (см. confirm.templates.free-sign-page)
        [$isFreeSignDoc, $documentFile, $pdfVersion, $fsGhosts] = $this->freeSignPdfData($journal);

        return view('execution.doc-control.view', compact('authUser','journal', 'document','edoCancelMessages', 'documentFiles',
            'directorDocument','directorResolution','selectedPerformers', 'isAddResolution','selectedAddPerformers',
            'agreement_users', 'replyDocuments', 'qrKey', 'signer', 'signType', 'current_agreement', 'execution_users',
            'resolutionExecRoles', 'resolutionTypes', 'execution_step_ids', 'html_doc', 'model',
            'isFreeSignDoc', 'documentFile', 'pdfVersion', 'fsGhosts'));
    }

    public function viewConfirmDocument($j_id, $doc_hash): Factory|View|Application
    {
        //
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($j_id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $doc_hash)->firstOrFail();

        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

        $documentFiles= DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user??0)->first();

        $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', $directorDocument->to_user??0)->get();

        $isAddResolution = [];

        $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', '!=', $directorDocument->to_user??0)->get();

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $current_agreement = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->whereIn('to_user', $this->authActiveUserIds())->first();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $edoCancelMessages = [];

        $qr_doc_type = $journal->journal->doc_code == 'BASE_OUTGOING' ? 'out_doc' : 'work_doc';

        $qrKey = QrCodeKeys::where('doc_type', $qr_doc_type)->where('doc_id', $document->id)->first();

        /*$execution_step = JournalExecutionSteps::where('journal_type_id', $journal->j_type)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'EXEC');
            })->first();*/

        $execution_step_ids = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->pluck('id')->toArray();

        //$execution_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $execution_step->id)->pluck('role_id')->toArray();

        $execution_users = [];

        $resolutionExecRoles = ResolutionExecutionRoles::where('status', 1)->orderBy('id')->get();

        $resolutionTypes = ResolutionTypes::where('status', 1)->get();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $j_id)->get();

        $comment_inputs = ['doc_id' => $j_id, 'doc_hash' => $doc_hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->where('document_comment_users.doc_id', $j_id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        // Free-sign? → PDF-viewer вместо HTML (см. confirm.templates.free-sign-page)
        [$isFreeSignDoc, $documentFile, $pdfVersion, $fsGhosts] = $this->freeSignPdfData($journal);

        return view('execution.confirm.view', compact('authUser','journal', 'document','edoCancelMessages', 'documentFiles',
            'directorDocument','directorResolution','selectedPerformers', 'isAddResolution','selectedAddPerformers',
            'agreement_users', 'replyDocuments', 'qrKey', 'signer', 'signType', 'current_agreement', 'execution_users',
            'resolutionExecRoles', 'resolutionTypes', 'execution_step_ids', 'documentComments', 'comment_inputs', 'html_doc', 'model',
            'isFreeSignDoc', 'documentFile', 'pdfVersion', 'fsGhosts'));
    }

    public function viewExecDocument($j_id, $doc_hash): Factory|View|Application
    {
        //
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($j_id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $doc_hash)->firstOrFail();

        $signType = UserPersonalInfo::where('user_id', $authUser->user_id)->first();

        $documentFiles= DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user??0)->first();

        $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', $directorDocument->to_user??0)->get();

        $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', '!=', $directorDocument->to_user??0)->get();

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $current_execution = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->whereIn('to_user', $this->authActiveUserIds())->first();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'work_doc')->where('doc_id', $document->id)->first();

        $execution_step = JournalExecutionSteps::where('journal_type_id', $journal->j_type)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'EXEC');
            })->first();

        $execution_step_ids = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->pluck('id')->toArray();

        $execution_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $execution_step->id)->pluck('role_id')->toArray();
        $user_roles = UserRole::whereIn('role_id', $execution_roles_ids)->pluck('model_id')->toArray();
        $execution_users = UserWorkHistory::where('org_id', $this->organizationId())
            ->whereIn('user_id', $user_roles)->whereHas('user', function ($query) {
                $query->where('status', 1);
            })->where('status', 1)
            ->whereNotIn('id', $selectedPerformers->pluck('to_user')->toArray())
            ->whereNotIn('id', $selectedAddPerformers->pluck('to_user')->toArray())
            ->get();

        $resolutionExecRoles = ResolutionExecutionRoles::where('status', 1)->orderBy('id')->get();

        $resolutionTypes = ResolutionTypes::where('status', 1)->get();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $j_id)->get();

        $comment_inputs = ['doc_id' => $j_id, 'doc_hash' => $doc_hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->where('document_comment_users.doc_id', $j_id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        // Free-sign? → PDF-viewer вместо HTML (см. confirm.templates.free-sign-page)
        [$isFreeSignDoc, $documentFile, $pdfVersion, $fsGhosts] = $this->freeSignPdfData($journal);

        return view('execution.executor.view', compact('authUser','journal', 'document', 'documentFiles',
            'directorDocument','directorResolution','selectedPerformers','selectedAddPerformers',
            'agreement_users', 'replyDocuments', 'qrKey', 'signer', 'signType', 'current_execution', 'execution_users',
            'resolutionExecRoles', 'resolutionTypes', 'execution_step_ids', 'documentComments', 'comment_inputs', 'html_doc', 'model',
            'isFreeSignDoc', 'documentFile', 'pdfVersion', 'fsGhosts'));
    }

    public function createResolution(Request $request, $j_id): RedirectResponse
    {
        //
        $request->validate([
            'user_id' => 'required',
            'user_role' => 'required',
            'dep_id' => 'required',
        ], [
            'user_id.required' =>  __('validate.user_id_mandatory'),
            'user_role.required' => __('validate.user_role_mandatory'),
            'dep_id.required' => __('validate.dep_id_mandatory')
        ]);

        $user_ids = $request->user_id;
        $user_roles = $request->user_role;
        $dep_ids = $request->dep_id;
        $resolution_type = $request->r_type;
        $perf_term = $request->perf_term;
        $ecp_r_text = $request->ecp_r_text;

        $executionRole = DocumentExecutionRoles::where('doc_code', 'EXEC')->first();
        $journal = DocumentJournals::find($j_id);
        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        if (count($user_ids)){
            foreach ($user_ids as $key => $user_id) {
                $execution_step = new DocumentExecutionSteps();
                $execution_step->j_id = $journal->id;
                $execution_step->from_dep = $this->userWork($directorDocument->to_user)->dep_id;
                $execution_step->from_user = $this->userWork($directorDocument->to_user)->id;
                $execution_step->to_dep = $dep_ids[$key];
                $execution_step->to_user = $user_id;
                $execution_step->exec_role = $executionRole->id;
                $execution_step->res_exec_role = $user_roles[$key];
                $execution_step->status = 1;
                $execution_step->save();
            }
        }

        $resolution = new DocumentResolutions();
        $resolution->doc_exec_step_id = $directorDocument->id;
        $resolution->j_id = $journal->id;
        $resolution->from_dep = $this->userWork($directorDocument->to_user)->dep_id;
        $resolution->from_user = $this->userWork($directorDocument->to_user)->id;
        $resolution->res_type = $resolution_type;
        $resolution->res_text = $ecp_r_text;
        $resolution->res_term = $perf_term;
        $resolution->status = 1;
        $resolution->save();

        $journal->update(['status' => 8]);

        $directorDocument->update(['status' => 2]);

        $journalType = $journal->journal->title??'';
        $docName = $journal->doc->doc_name??'';
        $act_id = $directorDocument->to_user === $this->authActiveUser()->id ? 9 : 24;
        $this->createLog($journal->doc_id, $act_id, $journalType, $docName);

        return back()->with('success', __('message.task_completed_successfully'));
    }

    public function changeResolution(Request $request, $j_id): RedirectResponse
    {
        //
        $request->validate([
            'user_id' => 'required',
            'user_role' => 'required',
            'dep_id' => 'required',
            [
                'user_id.required' => __('validate.add_user_mandatory'),
                'user_role.required' => __('validate.user_role_mandatory'),
                'dep_id.required' => __('validate.dep_id_mandatory'),
            ]
        ]);

        $user_ids = $request->user_id;
        $user_roles = $request->user_role;
        $dep_ids = $request->dep_id;
        $resolution_type = $request->r_type;
        $perf_term = $request->perf_term;
        $ecp_r_text = $request->ecp_r_text;

        $journal = DocumentJournals::find($j_id);
        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $oldExecutionSteps = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->get();

        if ($oldExecutionSteps && $oldExecutionSteps->count()){
            foreach ($oldExecutionSteps as $oldExecutionStep) $oldExecutionStep->delete();
        }

        if (count($user_ids)){
            foreach ($user_ids as $key => $user_id) {
                $execution_step = new DocumentExecutionSteps();
                $execution_step->j_id = $journal->id;
                $execution_step->from_dep = $this->userWork($directorDocument->to_user)->dep_id;
                $execution_step->from_user = $this->userWork($directorDocument->to_user)->id;
                $execution_step->to_dep = $dep_ids[$key];
                $execution_step->to_user = $user_id;
                $execution_step->exec_role = $this->getExecutiveRole()->id;
                $execution_step->res_exec_role = $user_roles[$key];
                $execution_step->status = 1;
                $execution_step->save();
            }
        }

        $resolution = DocumentResolutions::where('j_id', $journal->id)->first();
        $resolution->update([
            'res_type' => $resolution_type,
            'res_text' => $ecp_r_text,
            'res_term' => $perf_term
        ]);

        $journalType = $journal->journal->title??'';
        $docName = $journal->doc->doc_name??'';
        $this->createLog($journal->doc_id, 29, $journalType, $docName);

        return back()->with('success', __('message.task_completed_successfully'));
    }

    public function replyDocument(Request $request, $step_id): RedirectResponse
    {
        //
        $this->validate($request, [
            'r_text'     => 'required',
        ]);

        $text = $request->r_text;
        $type = $request->type;

        $step = DocumentExecutionSteps::find($step_id);

        $comment = new DocumentReplyComments();
        $comment->doc_exec_step_id = $step_id;
        $comment->j_id = $step->j_id;
        $comment->from_dep = $this->authActiveUser()->dep_id;
        $comment->from_user = $this->authActiveUser()->id;
        $comment->reply_comment_type = $type ?: 0;
        $comment->res_text = $text;
        $comment->status = 1;
        $comment->save();

        if ($request->hasFile('doc_files')) {
            $docFiles = $request->file('doc_files');
            foreach ($docFiles as $file) {
                $today = Carbon::today(); $year = $today->year; $month = $today->month; $day = $today->day;
                $path = 'doc/' . $year . '/' . $month . '/' . $day . '/';

                $documentFile = new ReplyDocumentFiles();
                $documentFile->step_id  = $comment->id;
                $documentFile->j_id = $step->j_id;
                $documentFile->file_name = $file->getClientOriginalName();;
                $documentFile->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
                $documentFile->file_path = $path;
                $documentFile->file_ext = $file->getClientOriginalExtension();
                $documentFile->file_size = $file->getSize();
                Storage::disk('ftp_13')->put($path . $documentFile->file_hash, file_get_contents($file->getRealPath()));
                $documentFile->save();
            }
        }

        $step->update(['status' => 4]);

        $journal = DocumentJournals::find($step->j_id);

        $journalType = $journal->journal->title??'';
        $docName = $journal->doc->doc_name??'';
        $this->createLog($journal->doc_id, 13, $journalType, $docName);

        return back()->with('success', __('message.task_completed_successfully'));
    }

    public function addPerformers(Request $request, $j_id): RedirectResponse
    {
        //
        $request->validate([
            'user_id' => 'required',
            'user_role' => 'required',
            'dep_id' => 'required',
        ]);

        $user_roles = $request->user_role;
        $user_ids = $request->user_id;
        $dep_ids = $request->dep_id;

        $executionRole = DocumentExecutionRoles::where('doc_code', 'EXEC')->first();
        $journal = DocumentJournals::find($j_id);
        $execDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)
            ->where('to_user', $this->authActiveUser()->id)
            ->first();

        if (count($user_ids)){
            foreach ($user_ids as $key => $user_id) {
                $execution_step = new DocumentExecutionSteps();
                $execution_step->j_id = $journal->id;
                $execution_step->from_dep = $this->userWork($execDocument->to_user)->dep_id;
                $execution_step->from_user = $this->userWork($execDocument->to_user)->id;
                $execution_step->to_dep = $dep_ids[$key];
                $execution_step->to_user = $user_id;
                $execution_step->exec_role = $executionRole->id;
                $execution_step->res_exec_role = $user_roles[$key];
                $execution_step->status = 2;
                $execution_step->save();
            }
        }

        $execDocument->update(['status'=> 4]);

        $journalType = $journal->journal->title??'';
        $docName = $journal->doc->doc_name??'';
        $this->createLog($journal->doc_id, 14, $journalType, $docName);

        return back()->with('success', __('message.task_completed_successfully'));
    }

    public function deleteReplyComment($id): JsonResponse
    {
        $comment = DocumentReplyComments::find($id);

        if ($comment) {

            $files = ReplyDocumentFiles::where('j_id', $comment->j_id)->where('step_id', $comment->id)->get();

            if (count($files)) {
                foreach ($files as $file) {
                    $path = '/'.$file->file_path.$file->file_hash;

                    if (Storage::disk('ftp_13')->exists($path)){

                        Storage::disk('ftp_13')->delete($path);
                    }
                    $file->delete();
                }
            }

            $comment->delete();
        }

        $checkComment = DocumentReplyComments::where('j_id', $comment->j_id)->where('from_user', $this->authActiveUser()->id)->count();
        if (!$checkComment) {
            //$step = DocumentExecutionSteps::find($comment->doc_exec_step_id);
            $stepStatusUpdate = DocumentExecutionSteps::where('j_id', $comment->j_id)
                ->where('to_user', $this->authActiveUser()->id)
                ->where('exec_role', 4)->first();
            if ($stepStatusUpdate) {
                $stepStatusUpdate->update(['status' => 2]);
            }
        }

        $journal = DocumentJournals::find($comment->j_id);

        $journalType = $journal->journal->title??'';
        $docName = $journal->doc->doc_name??'';
        $this->createLog($journal->doc_id, 26, $journalType, $docName);

        return response()->json([
            'success' => 200,
            'message' => __('message.doc_deleted_success_message')
        ]);
    }

    public function confirmReject(Request $request, $j_id): JsonResponse
    {
        //
        $status = $request->confirm_id;
        $r_text = $request->r_text;

        $ecp_r_text = $request->ecp_r_text;
        $document_id = $request->ecp_doc_id;
        $ecp_confirm_id = $request->ecp_confirm_id;
        $ecp_doc_type = $request->ecp_doc_type;

        $journal = DocumentJournals::find($j_id);
        if (in_array($status, [2, 3])) {

            $sign_step = DocumentExecutionSteps::where('j_id', $j_id)->whereHas('role', function ($query) {
                $query->where('doc_code', 'SIGN')->where('status', 1);
            })->where('to_user', $this->authActiveUser()->id)->first();

            $resolution_step = DocumentExecutionSteps::where('j_id', $j_id)->whereHas('role', function ($query) {
                $query->where('doc_code', 'RESOL')->where('status', 1);
            })->where('to_user', $this->authActiveUser()->id)->first();

            $agreement_step = DocumentExecutionSteps::where('j_id', $j_id)->whereHas('role', function ($query) {
                $query->where('doc_code', 'AGREE')->where('status', 1);
            })->where('to_user', $this->authActiveUser()->id)->first();

                $doc_code = $status == 2 ? 'DOC_CANCELLED' : 'DOC_RETURNED';

                $exec_status = $status == 2 ? 3 : 4;

                $replyCommentType = ReplyCommentTypes::where('doc_code', $doc_code)->first();

                $journalType = $journal->journal->title??'';
                $docName = $journal->doc->doc_name??'';

                if ($sign_step) {
                    $sign_step->update(['status' => $exec_status, 'r_text' => $r_text??'']);

                    $this->extracted($sign_step, $journal, $replyCommentType, $r_text, $exec_status);

                    $act_id = $exec_status == 3 ? 22 : 23;

                    $this->createLog($journal->doc_id, $act_id, $journalType, $docName);
                }

                if ($agreement_step) {
                    $agreement_step->update(['status' => $exec_status, 'r_text' => $r_text??'']);

                    $this->extracted($agreement_step, $journal, $replyCommentType, $r_text, $exec_status);

                    $act_id = $exec_status == 3 ? 7 : 8;

                    $this->createLog($journal->doc_id, $act_id, $journalType, $docName);
                }

                $journal->update(['status' => $status]);

                if ($resolution_step) {
                    $resolution_step->update(['status' => $exec_status, 'r_text' => $r_text??'']);

                    $this->extracted($resolution_step, $journal, $replyCommentType, $r_text, $exec_status);

                    $act_id = $exec_status == 3 ? 11 : 12;

                    $this->createLog($journal->doc_id, $act_id, $journalType, $docName);
                }

            return response()->json([
                    'success' => 200,
                    'title' => __('message.success_message'),
                    'message' => __('message.task_completed_successfully'),
                ]);
        } elseif($ecp_confirm_id == 5) {

            if ($ecp_doc_type === 'from_signer_to_ag') {
                $journal = DocumentJournals::where('doc_id', $document_id)->first();

                $isInJournalTypeControls = JournalTypeControls::where('journal_type_id', $journal->j_type)->where('status', 1)->first();

                $sign_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'SIGN')->where('status', 1);
                })->first();

                $agreement_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'AGREE')->where('status', 1);
                })->get();

                $resolution_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'RESOL')->where('status', 1);
                })->first();

                $signed_agreement_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'AGREE')->where('status', 1);
                })->where('status', 5)->get();

                $sign_step->update(['status' => 5]);

                $journal->update(['status' => 6]);

                foreach ($agreement_step as $agreement) $agreement->update(['status' => 2]);

                $replyCommentType = ReplyCommentTypes::where('doc_code', 'DOC_SIGNED')->first();

                $replyComment = new DocumentReplyComments();
                $replyComment->doc_exec_step_id = $sign_step->id;
                $replyComment->j_id = $journal->id;
                $replyComment->from_dep = $this->authActiveUser()->dep_id;
                $replyComment->from_user = $this->authActiveUser()->id;
                $replyComment->reply_comment_type = $replyCommentType->id;
                $replyComment->res_text = $ecp_r_text;
                $replyComment->status = 5;
                $replyComment->save();

                $journalType = $journal->journal->title??'';
                $docName = $journal->doc->doc_name??'';
                $this->createLog($journal->doc_id, 4, $journalType, $docName);
            }

            if ($ecp_doc_type === 'from_signer_to_c_res') {
                $sign_step->update(['status' => 5]);

                if ($isInJournalTypeControls) {
                    $journal->update(['status' => 7]);
                } else {
                    $journal->update(['status' => 8]);

                    $resolution_step->update(['status' => 2]);
                }

                $replyCommentType = ReplyCommentTypes::where('doc_code', 'DOC_SIGNED')->first();

                $replyComment = new DocumentReplyComments();
                $replyComment->doc_exec_step_id = $sign_step->id;
                $replyComment->j_id = $journal->id;
                $replyComment->from_dep = $this->authActiveUser()->dep_id;
                $replyComment->from_user = $this->authActiveUser()->id;
                $replyComment->reply_comment_type = $replyCommentType->id;
                $replyComment->res_text = $ecp_r_text;
                $replyComment->status = 5;
                $replyComment->save();

                $journalType = $journal->journal->title??'';
                $docName = $journal->doc->doc_name??'';
                $this->createLog($journal->doc_id, 28, $journalType, $docName);
            }

            if ($ecp_doc_type === 'from_ag_to_res') {
                $current_agreement = DocumentExecutionSteps::where('j_id', $journal->id)
                    ->where('exec_role', $this->getAgreementRole()->id)->whereIn('to_user', $this->authActiveUserIds())->first();

                if ($agreement_step->count() - $signed_agreement_step->count() == 1) {
                    if ($isInJournalTypeControls) {
                        $journal->update(['status' => 7]);
                    } else {
                        $journal->update(['status' => 8]);

                        $resolution_step->update(['status' => 2]);
                    }
                }

                $current_agreement->update(['status' => 5]);

                $replyCommentType = ReplyCommentTypes::where('doc_code', 'DOC_SIGNED')->first();

                $replyComment = new DocumentReplyComments();
                $replyComment->doc_exec_step_id = $sign_step->id;
                $replyComment->j_id = $journal->id;
                $replyComment->from_dep = $this->authActiveUser()->dep_id;
                $replyComment->from_user = $this->authActiveUser()->id;
                $replyComment->reply_comment_type = $replyCommentType->id;
                $replyComment->res_text = $ecp_r_text;
                $replyComment->status = 5;
                $replyComment->save();

                $journalType = $journal->journal->title??'';
                $docName = $journal->doc->doc_name??'';
                $this->createLog($journal->doc_id, 28, $journalType, $docName);
            }

            return response()->json([
                'success' => 200,
                'title' => __('message.success_message'),
                'message' => __('message.task_completed_successfully'),
            ]);
        } else {
            return response()->json([
                'success' => 201,
                'title' => __('message.error_message'),
                'message' => __('message.document_deleted_system')
            ]);
        }
    }

    public function controlSendToEdit(Request $request, $j_id): JsonResponse
    {
        $status = $request->confirm_id;

        $r_text = $request->r_text;

        if ($status == 4) {

            $journal = DocumentJournals::find($j_id);

            if ($journal) {
                $journal->update(['status' => 9]);

                $step = DocumentExecutionSteps::where('j_id', $j_id)->where('exec_role', 3)
                    ->where('to_user', $this->authActiveUser()->id)->first();

                if ($step) {
                    $step->update(['status' => 9]);

                    $comment_type = ReplyCommentTypes::where('doc_code', 'DOC_RETURNED')->first();

                    $this->extracted1($step, $journal, $comment_type, $r_text, 4);
                }

                DocumentExecutionSteps::where('j_id', $j_id)->where('exec_role', 4)->update(['status' => 4]);

                $journalType = $journal->journal->title??'';
                $docName = $journal->doc->doc_name??'';
                $this->createLog($journal->doc_id, 25, $journalType, $docName);

            }

        } else {

            $step = DocumentExecutionSteps::where('j_id', $j_id)->where('to_user', $this->authActiveUser()->id)->first();

            $journal = DocumentJournals::find($j_id);

            $journal->update(['status' => $status]);

            $reply_comment_type_doc_code = $status == 2 ? 'DOC_CANCELLED' : 'DOC_RETURNED';

            $replyStatus = $status == 2 ? 3 : 4;

            $comment_type = ReplyCommentTypes::where('doc_code', $reply_comment_type_doc_code)->first();

            if ($step) {
                $step->update(['status' => $status]);

                $this->extracted1($step, $journal, $comment_type, $r_text, $replyStatus);
            } else {
                $resolution_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                    $query->where('doc_code', 'RESOL')->where('status', 1);
                })->first();

                $this->extracted1($resolution_step, $journal, $comment_type, $r_text, $replyStatus);
            }

            $journalType = $journal->journal->title??'';
            $docName = $journal->doc->doc_name??'';
            $this->createLog($journal->doc_id, 25, $journalType, $docName);

        }

        return response()->json([
            'success' => 200,
            'title' => __('message.success_message'),
            'message' => __('message.task_completed_successfully'),
        ]);
    }

    public function executeWorkDoc($exec_id): RedirectResponse
    {
        //
        $execDocument = DocumentExecutionSteps::findOrFail($exec_id);

        $journal = DocumentJournals::find($execDocument->j_id);

        $resolutionStep = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
            $query->where('doc_code', 'RESOL')->where('status', 1);
        })->first();

        $parentStep =  DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('to_user', $execDocument->from_user)
            ->whereHas('role', function ($query) {
                $query->where('doc_code', 'RESOL')->where('status', 1);
            })->first();

        /*$selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)
            ->where('from_user', '!=', $resolutionStep->to_user)
            ->get();*/

        $execDocument->update(['status'=> 6]);

        if ($execDocument->from_user === $resolutionStep->to_user) {
            $parentStep->update(['status'=> 6]);

            $journal->update(['status' => 11]);

            /*if ($selectedAddPerformers && $selectedAddPerformers->count()) {
                foreach ($selectedAddPerformers as $selectedAddPerformer) $selectedAddPerformer->update(['status' => 6]);
            }*/
        }

        $comment_type = ReplyCommentTypes::where('doc_code', 'PERF')->first();

        $this->extracted1($resolutionStep, $journal, $comment_type, 'Hujjat ijrosini bajardi', 6);

        $journalType = $journal->journal->title??'';
        $docName = $journal->doc->doc_name??'';
        $this->createLog($journal->doc_id, 30, $journalType, $docName);

        return back()->with('success', __('message.task_completed_successfully'));
    }

    public function searchResPerformers(Request $request): JsonResponse
    {
        $user_fio = $request->search_key;

        if (trim(''.$user_fio.'')) {
            $users = UserWorkHistory::with('personal', 'userPost')
                ->where('org_id', $this->organizationId())
                ->whereHas('personal', function ($query) use ($user_fio) {
                    $query->where(DB::raw("CONCAT_WS(' ', l_name, f_name, m_name)"), 'ILIKE', '%'.$user_fio.'%');
                })
                ->where('status', 1)
//                ->where('id', '!=', $this->authActiveUser()->id)
                ->whereHas('user', function ($query1) {
                    $query1->where('status', 1);
                })->get();

            $resUserRoles = ResolutionExecutionRoles::where('status', 1)->orderBy('id')->get();

            return response()->json([
                'success' => 200,
                'users' => $users,
                'user_roles' => $resUserRoles,
            ]);
        }

        return response()->json([
            'success' => 201
        ]);

    }

    public function extracted($resolution_step, $journal, $replyCommentType, mixed $r_text, int $exec_status): void
    {
        if ($resolution_step) {
            $replyComment = new DocumentReplyComments();
            $replyComment->doc_exec_step_id = $resolution_step->id;
            $replyComment->j_id = $journal->id;
            $replyComment->from_dep = $this->authActiveUser()->dep_id;
            $replyComment->from_user = $this->authActiveUser()->id;
            $replyComment->reply_comment_type = $replyCommentType->id;
            $replyComment->res_text = $r_text??'';
            $replyComment->status = $exec_status;
            $replyComment->save();
        }
    }

    public function extracted1($step, $journal, $comment_type, mixed $r_text, mixed $status): void
    {
        $comment = new DocumentReplyComments();
        $comment->doc_exec_step_id = $step->id;
        $comment->j_id = $journal->id;
        $comment->from_dep = $this->authActiveUser()->dep_id;
        $comment->from_user = $this->authActiveUser()->id;
        $comment->reply_comment_type = $comment_type->id;
        $comment->res_text = $r_text ?? '';
        $comment->status = $status;
        $comment->save();
    }

    /**
     * Free-sign документ? → отдаём PDF-viewer вместо HTML-текста.
     *
     * Признак: у документа есть расставленные метки подписи.
     * $documentFile — ПОСЛЕДНЯЯ версия PDF (со штампами подписавших):
     * каждая подпись создаёт новую запись document_files.
     *
     * @return array{0: bool, 1: \App\Models\Document\DocumentFiles|null, 2: int|null}
     */
    private function freeSignPdfData($journal): array
    {
        $isFreeSignDoc = app(FreeSignRouteService::class)->isFreeSign($journal->doc_id);

        if (!$isFreeSignDoc) {
            return [false, null, null, collect()];
        }

        // Последняя ПРОШТАМПОВАННАЯ версия (*_signed_vN.pdf).
        // Ищем именно по метке версии, а не просто последний PDF:
        // у документа могут быть и другие PDF-вложения (приложения),
        // они не должны подменять основной документ.
        $stamped = \App\Models\Document\DocumentFiles::where('j_id', $journal->id)
            ->where('file_ext', 'pdf')
            ->where('file_name', 'ilike', '%_signed_v%')
            ->orderByDesc('id')
            ->first();

        // Оригинал — самый ранний PDF без метки версии.
        $original = \App\Models\Document\DocumentFiles::where('j_id', $journal->id)
            ->where('file_ext', 'pdf')
            ->where('file_name', 'not ilike', '%_signed_v%')
            ->orderBy('id')
            ->first();

        // Показываем проштампованную, если есть; иначе оригинал.
        $documentFile = $stamped ?: $original;

        // Номер версии = сколько штампов уже наложено (0 = оригинал).
        $pdfVersion = \App\Models\Document\DocumentFiles::where('j_id', $journal->id)
            ->where('file_ext', 'pdf')
            ->where('file_name', 'ilike', '%_signed_v%')
            ->count();

        $ghosts = $this->freeSignGhosts($journal);

        // ДИАГНОСТИКА: какой файл реально отдаём во viewer.
        \Illuminate\Support\Facades\Log::info('[free-sign] VIEWER file', [
            'journal_id'  => $journal->id,
            'stamped'     => $stamped?->file_name,
            'original'    => $original?->file_name,
            'showing'     => $documentFile?->file_name,
            'file_id'     => $documentFile?->id,
            'pdf_version' => $pdfVersion,
            'ghosts'      => $ghosts->count(),
        ]);

        return [true, $documentFile, $pdfVersion, $ghosts];
    }

    /**
     * Метки-призраки для PDF-viewer: «здесь БУДЕТ подпись такого-то».
     *
     * Показываем ТОЛЬКО метки тех, кто ещё НЕ подписал — у подписавших
     * реальный штамп уже впечатан в PDF, дублировать призраком не нужно.
     *
     * Координаты нормализованные (0..1) — фронт умножит на размер канваса.
     */
    private function freeSignGhosts($journal)
    {
        $palette = ['#794DFD', '#e0679a', '#3a9bdc', '#20a37b', '#e0872f', '#8b5cf6', '#0ea5a3', '#d4547e'];

        // Шаги маршрута → статус подписи по users.id.
        // to_user = work_history.id, поэтому конвертируем в users.id:
        // метки хранят signer_id = users.id (редактор строит список по users).
        $steps = DocumentExecutionSteps::where('j_id', $journal->id)->orderBy('id')->get();

        $signed = [];   // users.id => true (уже подписал)
        $color  = [];   // users.id => цвет
        $name   = [];   // users.id => ФИО

        foreach ($steps->values() as $i => $step) {
            $uid = DB::table('user_work_histories')->where('id', $step->to_user)->value('user_id');
            if (!$uid) continue;

            $color[$uid] = $palette[$i % count($palette)];
            // Завершён = 5 (подписал) ИЛИ 6 (подтвердил исполнение).
            // Иначе призрак не исчезал бы у подписавшего RESOL.
            $signed[$uid] = FreeSignRouteService::isStepCompleted($step->status);

            $name[$uid] = DB::table('users as u')
                ->leftJoin('user_personal_infos as pi', 'pi.user_id', '=', 'u.id')
                ->where('u.id', $uid)
                ->selectRaw("TRIM(CONCAT(COALESCE(pi.l_name,''),' ',COALESCE(pi.f_name,''))) as n")
                ->value('n');
        }

        // Человекочитаемые названия типов меток.
        $labels = [
            'signature'       => 'Подпись ЭЦП',
            'qr'              => 'QR-код',
            'full_name'       => 'ФИО',
            'position'        => 'Должность',
            'sign_date'       => 'Дата',
            'sign_time'       => 'Время',
            'cert_number'     => 'Номер сертификата',
            'cert_info'       => 'Инфо сертификата',
            'stamp_approved'  => 'Согласовано',
            'stamp_confirmed' => 'Утверждено',
            'free_text'       => 'Текст',
        ];

        return \App\Models\FreeSignDocumentField::where('document_id', $journal->doc_id)
            ->orderBy('page')
            ->get()
            ->filter(function ($f) use ($signed) {
                // Подписал → его штамп уже на PDF → призрак не нужен.
                return empty($signed[$f->signer_id]);
            })
            ->map(function ($f) use ($color, $name, $labels) {
                return [
                    'page'   => (int) $f->page,
                    'x'      => (float) $f->x,
                    'y'      => (float) $f->y,
                    'w'      => (float) $f->width,
                    'h'      => (float) $f->height,
                    'label'  => $labels[$f->type] ?? $f->type,
                    'color'  => $color[$f->signer_id] ?? '#794DFD',
                    'signer' => $name[$f->signer_id] ?? '',
                ];
            })
            ->values();
    }
}
