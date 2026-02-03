<?php

namespace App\Http\Controllers;

use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\StatusList;
use App\Models\Comment\DocumentComment;
use App\Models\Comment\DocumentCommentUser;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\Document\Documents;
use App\Models\Journal\JournalTypes;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\Protocol\ProtocolDocFile;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocText;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocument;
use App\Models\QrCodeKeys;
use App\Models\User;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function workDocs(Request $request): Factory|View|Application
    {
        $doc_name = $request->input('doc_name');
        $doc_type = $request->input('doc_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $status = $request->input('status');
        $created_user = $request->input('created_user');
        $org_id = $request->input('org_id');

        $cr_users = User::where('status', 1)->get();

        $isSuperUser = $this->isSuperUser();

        if($isSuperUser) {
            $search = DocumentJournals::whereNotIn('status', [1, 4, 13]);
        } else {
            $search = DocumentJournals::where('org_unit_id', $this->organizationId())->whereNotIn('status', [1, 4, 13]);
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
        } elseif ($status) {
            $search->where('status', $status);
        }

        if ($doc_type && $doc_type != 'all') {
            $search->where('j_type', $doc_type);
        }

        if (trim('' . $doc_name . '')) {
            $search->whereHas('doc', function ($query) use ($doc_name) {
                $query->where('doc_name', 'LIKE', '%' . $doc_name . '%')
                    ->orWhere('doc_number', 'LIKE', '%' . $doc_name . '%');
            });
        }

        if ($created_user) {
            $search->whereIn('created_user', $this->getWorkUserIds($created_user));
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

        if ($org_id) {
            $search->where('org_unit_id', $org_id);
        }

        $models = $search->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'doc_name' => $doc_name,
            'doc_type' => $doc_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'status' => $status,
            'created_user' => $created_user,
            'org_id' => $org_id,
        ]);

        $models->withQueryString();

        if ($isSuperUser) {
            $journal_types = JournalTypes::whereDoesntHave('childs')->where('status', 1)->orderBy('order_number')->get();
        } else {
            $journal_types = JournalTypes::where('org_id', $this->organizationId())->whereDoesntHave('childs')->where('status', 1)->orderBy('order_number')->get();
        }

        $statusNames = StatusList::where('type', 'work_document')->whereNotIn('code', [1, 4, 13])->orderBy('order_number')->get();

        $default_status = StatusList::where('type', 'work_document')->orderBy('code')->first();

        $organizations = OrganizationUnits::all();

        return view('doc-controller.work-docs.index',
            compact('models', 'doc_name', 'journal_types', 'doc_type', 'status', 'date_from', 'date_till', 'statusNames', 'default_status', 'created_user', 'cr_users',
                'org_id', 'organizations', 'isSuperUser'));
    }

    public function actionRoute($id, $hash): View|Factory|Application
    {
        $journal = DocumentJournals::findOrFail($id);

        if ($journal->journal->doc_code === 'BASE_INCOMING') {
            return $this->inboxView($id, $hash);
        } else if ($journal->journal->doc_code === 'BASE_OUTGOING') {
            return $this->sentView($id, $hash);
        } else {
            return $this->workView($id, $hash);
        }
    }

    public function workView($id, $hash): Factory|View|Application
    {
        //
        $authUser = $this->authActiveUser();

        $authrole = $this->authActiveUserRoleNames();

        $journal = DocumentJournals::findOrFail($id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->firstOrFail();

        $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user ?? 0)->first();

        $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', $directorDocument->to_user ?? 0)->get();

        $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', '!=', $directorDocument->to_user ?? 0)->get();

        $isAddResolution = [];

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();


        $agUserCount = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->whereIn('status', [-6, -5, 0])->count();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $edoCancelMessages = [];

        $view = 'view';

        if (str_contains($journal->journal->doc_code, 'APP')) {
            $view = 'view-app';
        } elseif ($journal->journal->doc_code == 'COMMAND') {
            $view = 'view-order';
        }

        $qrKey = QrCodeKeys::where('doc_type', 'work_doc')->where('doc_id', $document->id)->first();

        $execution_step_ids = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->pluck('id')->toArray();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $journal->id)->get();

        $comment_inputs = ['doc_id' => $journal->id, 'doc_hash' => $document->doc_hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->where('document_comment_users.doc_id', $journal->id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        return view('doc-controller.work-docs.' . $view, compact('authUser', 'journal', 'document', 'edoCancelMessages', 'documentFiles',
            'directorDocument', 'directorResolution', 'selectedPerformers', 'isAddResolution', 'selectedAddPerformers', 'model',
            'agreement_users', 'replyDocuments', 'agUserCount', 'qrKey', 'signer', 'execution_step_ids', 'authrole', 'documentComments', 'comment_inputs', 'html_doc'));

    }

    public function inboxView($id, $hash): Factory|View|Application
    {
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->firstOrFail();

        $authrole = $this->authActiveUserRoleNames();

        $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user ?? 0)->first();

        $selectedPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', $directorDocument->to_user ?? 0)->get();

        $selectedAddPerformers = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->where('from_user', '!=', $directorDocument->to_user ?? 0)->get();

        $signer = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getSignerRole()->id)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $replyDocuments = DocumentReplyComments::where('j_id', $journal->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'work_doc')->where('doc_id', $document->id)->first();

        $execution_step_ids = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getExecutiveRole()->id)->pluck('id')->toArray();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $journal->id)->get();

        $comment_inputs = ['doc_id' => $journal->id, 'doc_hash' => $document->doc_hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->where('document_comment_users.doc_id', $journal->id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        return view('doc-controller.work-docs.inbox-doc-view', compact('authUser', 'journal', 'document', 'documentFiles',
            'directorDocument', 'directorResolution', 'selectedPerformers', 'selectedAddPerformers',
            'agreement_users', 'replyDocuments', 'qrKey', 'signer', 'execution_step_ids', 'authrole', 'documentComments', 'comment_inputs'));
    }

    public function sentView($id, $hash): Factory|View|Application
    {
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($id);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->firstOrFail();

        $authrole = $this->authActiveUserRoleNames();

        $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

        $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)->first();

        $directorResolution = DocumentResolutions::where('j_id', $journal->id)
            ->where('from_user', $directorDocument->to_user ?? 0)->first();

        $agreement_users = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'out_doc')->where('doc_id', $document->id)->first();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 1)->where('doc_id', $journal->id)->get();

        $comment_inputs = ['doc_id' => $journal->id, 'doc_hash' => $document->doc_hash, 'doc_type' => 1];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 1);
        })->where('document_comment_users.doc_id', $journal->id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
            ->update([
                'document_comment_users.is_read' => 2,
                'document_comment_users.is_read_date' => Carbon::now()
            ]);

        $model = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $journal->doc_id)->first();

        $html_doc = $this->documentService->getHtmlDoc($journal, $document);

        return view('doc-controller.work-docs.sent-doc-view', compact('authUser', 'journal', 'document', 'documentFiles',
            'directorDocument', 'directorResolution', 'agreement_users', 'qrKey', 'authrole', 'documentComments', 'comment_inputs', 'html_doc', 'model'));
    }

    public function protocols(Request $request): View|Factory|Application
    {
        $s_text = $request->input('s_text');
        $s_status = $request->input('s_status');
        $s_type = $request->input('s_type');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $created_user = $request->input('created_user');
        $org_id = $request->input('org_id');

        $isSuperUser = $this->isSuperUser();

        // Базовый запрос с учетом прав доступа
        if ($isSuperUser) {
            $search = ProtocolDocument::whereNotIn('status', [1]);
        } else {
            $search = ProtocolDocument::where('org_id', $this->organizationId())
                ->whereNotIn('status', [1]);
        }

        // Фильтр по тексту (название или номер документа)
        if (trim($s_text)) {
            $search->where(function($query) use ($s_text) {
                $query->where('doc_name', 'LIKE', '%' . $s_text . '%')
                    ->orWhere('doc_number', 'LIKE', '%' . $s_text . '%');
            });
        }

        // Фильтр по типу протокола
        if ($s_type) {
            $search->where('doc_type_id', (int)$s_type);
        }

        // Фильтр по статусу
        if ($s_status) {
            $search->where('status', $s_status);
        }

        // Фильтр по датам
        if ($date_from && $date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y-m-d');
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y-m-d');
            $search->whereBetween('doc_date', [$tmp_date_from, $tmp_date_till]);
        } elseif ($date_from && !$date_till) {
            $tmp_date_from = Carbon::createFromFormat('d.m.Y', $date_from)->format('Y-m-d');
            $search->where('doc_date', '>=', $tmp_date_from);
        } elseif (!$date_from && $date_till) {
            $tmp_date_till = Carbon::createFromFormat('d.m.Y', $date_till)->format('Y-m-d');
            $search->where('doc_date', '<=', $tmp_date_till);
        }

        // Фильтр по создателю
        if ($created_user) {
            $search->where('created_user', $created_user);
        }

        // Фильтр по организации (только для superUser)
        if ($org_id && $isSuperUser) {
            $search->where('org_id', $org_id);
        }

        // Исключаем удаленные документы и сортируем
        $models = $search->where('status', '!=', 6)
            ->orderBy('id', 'DESC')
            ->paginate(25);

        // Сохраняем параметры для пагинации
        $models->appends([
            's_text' => $s_text,
            's_status' => $s_status,
            's_type' => $s_type,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'created_user' => $created_user,
            'org_id' => $org_id,
        ]);

        $models->withQueryString();

        // Типы протоколов
        if ($isSuperUser) {
            $protocolTypes = ProtocolDocType::where('status', 1)->get();
        } else {
            $protocolTypes = ProtocolDocType::where('org_id', $this->organizationId())
                ->where('status', 1)
                ->get();
        }

        // Список статусов
        $statusList = StatusList::where('type', 'prt_status')
            ->whereNotIn('code', [1, 5, 6, 7])
            ->orderBy('order_number')
            ->get();

        // Список пользователей для фильтра
        if ($isSuperUser) {
            $cr_users = User::where('status', 1)->get();
        } else {
            $cr_users = User::where('status', 1)
                ->where('org_unit_id', $this->organizationId())
                ->get();
        }

        // Список организаций (только для superUser)
        $organizations = $isSuperUser ? OrganizationUnits::all() : collect();

        return view('doc-controller.protocols.index', compact(
            'models', 's_text', 's_status', 's_type', 'statusList',
            'protocolTypes', 'date_from', 'date_till', 'cr_users',
            'created_user', 'org_id', 'organizations', 'isSuperUser'
        ));
    }

    public function protocolView($id, $doc_hash): View|Factory|Application
    {
        $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();

        $documentMember = ProtocolDocMember::where('id', $id)->where('doc_id', $document->id)->first();

        $documentText = ProtocolDocText::where('doc_id', $document->id)->first();

        $authrole = $this->authActiveUserRoleNames();

        $documentFiles = ProtocolDocFile::where('doc_id', $document->id)->orderBy('file_ext', 'DESC')->get();

        $documentMembers = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', '!=', 1)->orderBy('order_number')->get();

        $documentHrEmp = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->orderBy('order_number')->get();

        $present_roles = ProtocolDocMember::where('doc_id', $document->id)->pluck('role_id')->unique()->toArray();

        $documentMemberRoles = StatusList::where('type', 'prt_sign_type')
            ->where('code', '!=', 1)
            ->whereIn('code', $present_roles)
            ->orderBy('code')
            ->get();

        $documentReplyComments = ProtocolDocReplyComment::where('doc_id', $document->id)->get();

        $qrKey = QrCodeKeys::where('doc_type', 'PRT_DOC')->where('doc_id', $document->id)->first();

        //Comment
        $documentComments = DocumentComment::where('doc_type', 2)->where('doc_id', $id)->get();

        $comment_inputs = ['doc_id' => $id, 'doc_hash' => $doc_hash, 'doc_type' => 2];

        DocumentCommentUser::leftJoin('document_comments', function ($join) {
            $join->on('document_comments.id', '=', 'document_comment_users.comment_id')->where('document_comments.doc_type', 2);
        })->where('document_comment_users.doc_id', $id)->whereIn('document_comment_users.user_id', $this->authActiveUserIds())->where('document_comment_users.is_read', 1)
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

        return view('doc-controller.protocols.view', compact('document', 'documentMember',
            'documentText', 'documentFiles', 'documentMembers', 'documentHrEmp', 'documentReplyComments',
            'documentMemberRoles', 'qrKey', 'authrole', 'documentComments', 'comment_inputs', 'html_doc'));
    }


    public function delWorkDoc(Request $request): JsonResponse
    {
        $docId = $request->input('doc_id');

        DocumentJournals::where('doc_id', $docId)->delete();

        DocumentCommentUser::where('doc_id', $docId)->delete();

        DocumentComment::where('doc_id', $docId)->delete();

        DocumentFiles::where('doc_id', $docId)->delete();

        DocumentExecutionSteps::where('j_id', $docId)->delete();

        DocumentReplyComments::where('j_id', $docId)->delete();

        DocumentResolutions::where('j_id', $docId)->delete();

        Documents::where('id', $docId)->delete();

        $comments = DocumentComment::where('doc_id', $docId)->where('doc_type', 1)->get();
        if ($comments) {
            foreach ($comments as $comment) {
                DocumentCommentUser::where('comment_id', $comment->id)->where('doc_id', $docId)->delete();
            }

            DocumentComment::where('doc_id', $docId)->where('doc_type', 1)->delete();
        }

        return response()->json([
            'status' => 200,
            'msg' => "Deletion was successful",
            'redirect' => route('doc-controller.work-docs'),
        ]);
    }

    public function delProtocol(Request $request): JsonResponse
    {
        $docId = $request->input('doc_id');

        ProtocolDocMember::where('doc_id', $docId)->delete();

        ProtocolDocument::where('id', $docId)->delete();

        ProtocolDocFile::where('doc_id', $docId)->delete();

        ProtocolDocText::where('doc_id', $docId)->delete();

        ProtocolDocReplyComment::where('doc_id', $docId)->delete();

        $comments = DocumentComment::where('doc_id', $docId)->where('doc_type', 2)->get();
        if ($comments) {
            foreach ($comments as $comment) {
                DocumentCommentUser::where('comment_id', $comment->id)->where('doc_id', $docId)->delete();
            }

            DocumentComment::where('doc_id', $docId)->where('doc_type', 2)->delete();
        }

        return response()->json([
            'status' => 200,
            'msg' => "Deletion was successful",
            'redirect' => route('home'),
        ]);
    }

    public function publicProtocols(Request $request): View|Factory|Application
    {
        $s_text = $request->input('s_text');
        $s_status = $request->input('s_status');
        $s_type = $request->input('s_type');

        // Публичные протоколы доступны всем
        $search = ProtocolDocument::whereNotIn('status', [1])->where('visibility', 2);

        // Фильтр по тексту (название или номер документа)
        if (trim($s_text)) {
            $search->where(function($query) use ($s_text) {
                $query->where('doc_name', 'LIKE', '%' . $s_text . '%')
                    ->orWhere('doc_number', 'LIKE', '%' . $s_text . '%');
            });
        }

        // Фильтр по типу протокола
        if ($s_type) {
            $search->where('doc_type_id', (int)$s_type);
        }

        // Фильтр по статусу
        if ($s_status) {
            $search->where('status', $s_status);
        }

        // Исключаем удаленные документы
        $models = $search->where('status', '!=', 6)
            ->orderBy('id', 'DESC')
            ->paginate(25);

        // Сохраняем параметры для пагинации
        $models->appends([
            's_text' => $s_text,
            's_status' => $s_status,
            's_type' => $s_type
        ]);

        $models->withQueryString();

        // Типы протоколов
        $protocolTypes = ProtocolDocType::where('status', 1)->get();

        // Список статусов
        $statusList = StatusList::where('type', 'prt_status')
            ->whereNotIn('code', [1, 5, 6, 7])
            ->orderBy('order_number')
            ->get();

        return view('doc-controller.protocols.public-index', compact(
            'models', 's_text', 's_status', 's_type', 'statusList', 'protocolTypes'
        ));
    }

    public function getFilterDataByOrg(Request $request): JsonResponse
    {
        $org_id = $request->input('org_id');

        if (!$org_id) {
            return response()->json(['success' => false, 'message' => 'Не указана организация']);
        }

        // Типы журналов (документов)
        $journal_types = JournalTypes::where('org_id', $org_id)
            ->whereDoesntHave('childs')
            ->where('status', 1)
            ->orderBy('order_number')
            ->get();

        // Статусы
        $statusNames = StatusList::where('type', 'work_document')
            ->whereNotIn('code', [1, 4, 13])
            ->orderBy('order_number')
            ->get();

        // Пользователи-создатели
        $cr_users = User::where('status', 1)
            ->where('org_unit_id', $org_id)
            ->get();

        return response()->json([
            'success'       => true,
            'journal_types' => $journal_types->map(fn($t) => [
                'id'   => $t->id,
                'name' => $t->lang($t->lang_id),
            ]),
            'statuses'      => $statusNames->map(fn($s) => [
                'code' => $s->code,
                'name' => $s->lang($s->id),
            ]),
            'users'         => $cr_users->map(fn($u) => [
                'id'   => $u->id,
                'name' => $u->userPersonal->shortName() ?? $u->name ?? '—',
            ]),
        ]);
    }

    /**
     * Получение данных фильтров для протоколов по организации
     */
    public function getProtocolFilterDataByOrg(Request $request): JsonResponse
    {
        $org_id = $request->input('org_id');

        if (!$org_id) {
            return response()->json(['success' => false, 'message' => 'Не указана организация']);
        }

        // Типы протоколов
        $protocolTypes = ProtocolDocType::where('org_id', $org_id)
            ->where('status', 1)
            ->get();

        // Статусы
        $statusList = StatusList::where('type', 'prt_status')
            ->whereNotIn('code', [1, 5, 6, 7])
            ->orderBy('order_number')
            ->get();

        // Пользователи-создатели
        $cr_users = User::where('status', 1)
            ->where('org_unit_id', $org_id)
            ->get();

        return response()->json([
            'success'        => true,
            'protocol_types' => $protocolTypes->map(fn($t) => [
                'id'   => $t->id,
                'name' => $t->lang($t->id),
            ]),
            'statuses'       => $statusList->map(fn($s) => [
                'code' => $s->code,
                'name' => $s->lang($s->id),
            ]),
            'users'          => $cr_users->map(fn($u) => [
                'id'   => $u->id,
                'name' => $u->userPersonal->shortName() ?? $u->name ?? '—',
            ]),
        ]);
    }
}
