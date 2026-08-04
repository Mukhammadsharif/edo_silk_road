<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\FreeSign\FreeSignRouteService;
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
use App\Models\FreeSignDocumentField;
use App\Models\Journal\JournalTypes;
use App\Models\Messages\OnlyOfficeDoc;
use App\Models\QrCodeKeys;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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

        $journal_types = JournalTypes::where('org_id', $this->organizationId())
            ->whereDoesntHave('childs')
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

        $journal = JournalTypes::where('org_id', $this->organizationId())->where('doc_code', 'BASE_INCOMING')->first();

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

        $journal = JournalTypes::where('org_id', $this->organizationId())->where('doc_code', 'BASE_OUTGOING')->first();

        $doc_organizations = DocumentOrganizations::where('for_doc_type', $journal->id)->where('status', 1)->get();

        return view('office.journal.sent.index',
            compact('models', 'doc_name', 'doc_org', 'status', 'date_from', 'date_till','statusNames', 'default_status',
                'doc_organizations'));
    }

    public function viewWork($id, $hash): Factory|View|Application|\Illuminate\Http\RedirectResponse
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

        /* ═══════════ FREE-SIGN: PDF вместо HTML-текста ═══════════
         | Документ считается free-sign, если у него есть расставленные метки
         | подписи. Вьюшка та же самая штатная (шапка, левая/правая панели,
         | подписи, резолюция, исполнение), но центр — PDF-viewer,
         | который грузит ПОСЛЕДНЮЮ версию файла со всеми штампами.
         */
        /* ═══════════ FREE-SIGN: PDF вместо HTML-текста ═══════════
         | Вьюшка остаётся ШТАТНОЙ ($view не меняем!) — внутри неё блок
         | документа подменяется на PDF-viewer по флагу $isFreeSignDoc,
         | так же как в execution-вьюшках.
         |
         | Раньше тут было $view = 'view-free-sign' — но такой вьюшки нет
         | (мы сменили тактику на подмену блока), это давало фатальную
         | ошибку при открытии free-sign из «Мои документы».
         */
        $isFreeSignDoc = app(FreeSignRouteService::class)->isFreeSign($journal->doc_id);
        $documentFile  = null;   // для обычных документов остаются null
        $pdfVersion    = null;
        $fsGhosts      = collect();

        if ($isFreeSignDoc) {
            // Последняя ПРОШТАМПОВАННАЯ версия (по метке _signed_v в имени):
            // у документа могут быть и другие PDF-вложения.
            $stamped = DocumentFiles::where('j_id', $journal->id)
                ->where('file_ext', 'pdf')
                ->where('file_name', 'ilike', '%_signed_v%')
                ->orderByDesc('id')
                ->first();

            $original = DocumentFiles::where('j_id', $journal->id)
                ->where('file_ext', 'pdf')
                ->where('file_name', 'not ilike', '%_signed_v%')
                ->orderBy('id')
                ->first();

            $documentFile = $stamped ?: $original;

            $pdfVersion = DocumentFiles::where('j_id', $journal->id)
                ->where('file_ext', 'pdf')
                ->where('file_name', 'ilike', '%_signed_v%')
                ->count();

            $fsGhosts = $this->freeSignGhostsForWork($journal);
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
            'model', 'type',
            // free-sign: PDF-вьювер вместо HTML (см. $view = 'view-free-sign')
            'isFreeSignDoc', 'documentFile', 'pdfVersion', 'fsGhosts'));

    }

    /**
     * Метки-призраки для PDF-viewer в штатной вьюшке «Мои документы».
     * Показываем только НЕподписавших — у подписавших штамп уже на PDF.
     * (Аналог ExecutionController::freeSignGhosts.)
     */
    private function freeSignGhostsForWork($journal)
    {
        $palette = ['#794DFD', '#e0679a', '#3a9bdc', '#20a37b', '#e0872f', '#8b5cf6', '#0ea5a3', '#d4547e'];

        $steps = DocumentExecutionSteps::where('j_id', $journal->id)->orderBy('id')->get();

        $signed = [];
        $color  = [];
        $name   = [];

        foreach ($steps->values() as $i => $step) {
            // to_user = work_history.id → users.id (метки хранят users.id)
            $uid = DB::table('user_work_histories')->where('id', $step->to_user)->value('user_id');
            if (!$uid) continue;

            $color[$uid]  = $palette[$i % count($palette)];
            // Завершён = 5 (подписал) ИЛИ 6 (подтвердил исполнение).
            // Иначе призрак метки не исчезал бы у того, кто уже подписал.
            $signed[$uid] = FreeSignRouteService::isStepCompleted($step->status);

            $name[$uid] = DB::table('users as u')
                ->leftJoin('user_personal_infos as pi', 'pi.user_id', '=', 'u.id')
                ->where('u.id', $uid)
                ->selectRaw("TRIM(CONCAT(COALESCE(pi.l_name,''),' ',COALESCE(pi.f_name,''))) as n")
                ->value('n');
        }

        $labels = [
            'signature' => 'Подпись ЭЦП', 'qr' => 'QR-код', 'full_name' => 'ФИО',
            'position' => 'Должность', 'sign_date' => 'Дата', 'sign_time' => 'Время',
            'cert_number' => 'Номер сертификата', 'cert_info' => 'Инфо сертификата',
            'stamp_approved' => 'Согласовано', 'stamp_confirmed' => 'Утверждено',
            'free_text' => 'Текст',
        ];

        return FreeSignDocumentField::where('document_id', $journal->doc_id)
            ->orderBy('page')
            ->get()
            ->filter(fn ($f) => empty($signed[$f->signer_id]))
            ->map(fn ($f) => [
                'page'   => (int) $f->page,
                'x'      => (float) $f->x,
                'y'      => (float) $f->y,
                'w'      => (float) $f->width,
                'h'      => (float) $f->height,
                'label'  => $labels[$f->type] ?? $f->type,
                'color'  => $color[$f->signer_id] ?? '#794DFD',
                'signer' => $name[$f->signer_id] ?? '',
            ])
            ->values();
    }

    /* ═══════════ ЛИСТ СОГЛАСОВАНИЯ (v62) ═══════════
     | Печатная страница А4: шапка с информацией о документе + список
     | согласующих (ТОЛЬКО роль AGREE) со статусами, датами и
     | комментариями (DocumentReplyComments типа DOC_SIGNED по шагу).
     | Дата согласования = updated_at шага в статусе 5/6.
     | Работает и для штатных, и для free-sign документов.
     */
    public function agreementSheet($id, $hash)
    {
        $journal  = DocumentJournals::findOrFail($id);
        $document = \App\Models\Document\Documents::where('id', $journal->doc_id)
            ->where('doc_hash', $hash)
            ->firstOrFail();

        $agreeRole = \App\Models\Document\DocumentExecutionRoles::where('doc_code', 'AGREE')
            ->where('status', 1)
            ->first();

        $agreeSteps = $agreeRole
            ? DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $agreeRole->id)
                ->with(['toUser.personal', 'toUser.userPost', 'toUser.department'])
                ->orderBy('id')
                ->get()
            : collect();

        // Комментарии согласования по шагам (DOC_SIGNED).
        $signedType = \App\Models\Resolution\ReplyCommentTypes::where('doc_code', 'DOC_SIGNED')->first();
        $comments = ($signedType && $agreeSteps->isNotEmpty())
            ? \App\Models\Document\DocumentReplyComments::whereIn('doc_exec_step_id', $agreeSteps->pluck('id'))
                ->where('reply_comment_type', $signedType->id)
                ->get()
                ->keyBy('doc_exec_step_id')
            : collect();

        return view('office.journal.agreement-sheet', compact('journal', 'document', 'agreeSteps', 'comments'));
    }

    public function viewFreeSign($id, $hash)
    {
        $journal = DocumentJournals::findOrFail($id);
        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->firstOrFail();
        // ПОСЛЕДНЯЯ версия PDF (со штампами уже подписавших), а не исходник.
        // Каждая подпись создаёт новую запись document_files (*_signed_vN.pdf),
        // поэтому берём orderByDesc('id') — иначе редактор покажет чистый PDF
        // без реальных подписей.
        $documentFile = DocumentFiles::where('j_id', $journal->id)
            ->where('file_ext', 'pdf')
            ->orderByDesc('id')
            ->first();

        // Исходник (без меток) — для ссылки «скачать оригинал».
        $originalFile = DocumentFiles::where('j_id', $journal->id)
            ->where('file_ext', 'pdf')
            ->orderBy('id')
            ->first();

        // PDF не приложен → редактор бесполезен (нечего размечать).
        // Возвращаем на карточку документа с понятным сообщением,
        // иначе покажется демо-документ и пользователь запутается.
        if (!$documentFile) {
            return redirect()
                ->route('office.view-work-doc', [$journal->id, $document->doc_hash])
                ->with('error', __('validate.file_mandatory') ?? 'К документу не приложен PDF-файл');
        }
        $orgUnitId = auth()->user()->org_unit_id;
        $lang = app()->getLocale(); // ru|uz|en

        $roles = DB::table('document_execution_roles as r')
            ->where('r.status', 1)
            ->leftJoin('language_lines as ll', 'll.id', '=', 'r.lang_id')
            ->select(
                'r.id',
                'r.title',
                // перевод по локали из jsonb, фолбэк: локаль → ru → title роли
                DB::raw("COALESCE(ll.text ->> '{$lang}', ll.text ->> 'ru', r.title) as label")
            )
            ->orderBy('r.id')
            ->get();

        /* ═══════ КАНДИДАТЫ В ПОДПИСАНТЫ ═══════
         | Показываем ТОЛЬКО тех, у кого есть АКТИВНОЕ рабочее место
         | (user_work_histories) в ТЕКУЩЕЙ организации.
         |
         | ПОЧЕМУ ЭТО КРИТИЧНО: шаги маршрута хранят to_user = work_history.id,
         | а списки документов фильтруют через authActiveUserIds(), который
         | возвращает work_history только текущей организации.
         |
         | Если назначить подписантом человека без work_history в этой org —
         | шаг создастся, но НИКТО его не увидит: документ зависнет.
         | Раньше тут был фильтр только по users.org_unit_id — этого мало.
         */
        $people = DB::table('users as u')
            ->join('user_work_histories as w', function ($j) use ($orgUnitId) {
                $j->on('w.user_id', '=', 'u.id')
                  ->where('w.org_id', '=', $orgUnitId)
                  ->where('w.status', '=', 1);
            })
            ->leftJoin('user_personal_infos as pi', 'pi.user_id', '=', 'u.id')
            ->where('u.status', 1)
            ->select(
                'u.id',
                DB::raw("TRIM(CONCAT(COALESCE(pi.l_name,''),' ',COALESCE(pi.f_name,''),' ',COALESCE(pi.m_name,''))) as full_name")
            )
            ->distinct()
            // PostgreSQL: при SELECT DISTINCT поля из ORDER BY обязаны быть
            // в списке выборки. pi.l_name там нет — сортируем по full_name,
            // который начинается с фамилии, т.е. порядок тот же.
            ->orderBy('full_name')
            ->get();
        $userIds = $this->authActiveUserIds();

        // Есть активный шаг на мне? → я участник маршрута, моя очередь подписывать.
        $hasActiveStep = DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereIn('to_user', $userIds)
            ->where('status', FreeSignRouteService::STEP_ACTIVE)
            ->exists();

        // Я автор и документ ещё не отправлен? → моя подпись ПОДТВЕРЖДАЕТ отправку
        // и запускает маршрут (сам автор в маршруте не участвует, метки не ставит).
        // ВАЖНО: created_user — это users.id, а authActiveUserIds() отдаёт
        // id из user_work_histories. Сравниваем с Auth::id(), а не с $userIds.
        $isAuthorConfirm = ((int) $journal->created_user === (int) auth()->id())
            && in_array((int) $journal->status, [1, 3], true)   // Yaratilgan / Tahrirlash
            && DocumentExecutionSteps::where('j_id', $journal->id)->exists(); // есть кого активировать

        /* v50: подписание в РЕДАКТОРЕ — ТОЛЬКО для автора при отправке
         | (его подпись запускает маршрут). После отправки участники
         | подписывают со ШТАТНЫХ страниц («На подпись», «Согласование»,
         | «Резолюция» — все адаптированы под free-sign); модалка и
         | кнопка подписи в редакторе больше не показываются.
         | Раньше: $canSign = $hasActiveStep || $isAuthorConfirm;
         | ($hasActiveStep больше не используется — оставлен для ясности логики)
         */
        $canSign = $isAuthorConfirm;

        /* ═══════════ ВОССТАНОВЛЕНИЕ СОХРАНЁННОГО СОСТОЯНИЯ ═══════════
         | Без этого при обновлении страницы подписанты и метки теряются:
         | редактор открывался с пустыми массивами-заглушками.
         | Читаем из БД: шаги маршрута → подписанты, поля → метки.
         */

        // Подписанты = шаги маршрута этого журнала (в порядке создания).
        $steps = DocumentExecutionSteps::where('j_id', $journal->id)
            ->orderBy('id')
            ->get();

        $palette = ['#794DFD', '#e0679a', '#3a9bdc', '#20a37b', '#e0872f', '#8b5cf6', '#0ea5a3', '#d4547e'];
        $lang    = app()->getLocale();

        $savedSigners = $steps->values()->map(function ($step, $i) use ($palette, $lang) {
            // ФИО подписанта.
            // to_user — это user_work_histories.id, а не users.id.
            // Получаем users.id через work_history, затем ФИО.
            $signerUserId = DB::table('user_work_histories')->where('id', $step->to_user)->value('user_id');

            $name = DB::table('users as u')
                ->leftJoin('user_personal_infos as pi', 'pi.user_id', '=', 'u.id')
                ->where('u.id', $signerUserId)
                ->selectRaw("TRIM(CONCAT(COALESCE(pi.l_name,''),' ',COALESCE(pi.f_name,''),' ',COALESCE(pi.m_name,''))) as full_name")
                ->value('full_name');

            // Название роли с переводом.
            $role = DB::table('document_execution_roles as r')
                ->leftJoin('language_lines as ll', 'll.id', '=', 'r.lang_id')
                ->where('r.id', $step->exec_role)
                ->selectRaw("COALESCE(ll.text ->> '{$lang}', ll.text ->> 'ru', r.title) as label")
                ->value('label');

            return [
                'id'     => (int) $signerUserId,             // users.id — совпадает с signer_id меток
                'name'   => trim(preg_replace('/\s+/', ' ', (string) $name)) ?: '—',
                'info'   => '',
                'role'   => $role ?? 'Подписант',
                'roleId' => $step->exec_role,
                'color'  => $palette[$i % count($palette)],
            ];
        })->values();

        // Метки этого документа.
        $savedFields = FreeSignDocumentField::where('document_id', $document->id)
            ->orderBy('page')
            ->get()
            ->map(function ($f) {
                return [
                    'page'      => (int) $f->page,
                    'type'      => $f->type,
                    'signer_id' => $f->signer_id,
                    'x'         => (float) $f->x,
                    'y'         => (float) $f->y,
                    'width'     => (float) $f->width,
                    'height'    => (float) $f->height,
                    'params'    => $f->params ?? [],
                ];
            })->values();

        // Режим: edit пока журнал в статусах создания/редактирования, иначе view.
        $mode = in_array((int) $journal->status, [1, 3], true) ? 'edit' : 'view';

        // Статусы подписи для карточек: 5=подписал, 2=его очередь, иначе ждёт.
        $signStatus = [];
        foreach ($steps as $step) {
            // Ключ = users.id, чтобы совпадал с id карточек подписантов.
            $uid = DB::table('user_work_histories')->where('id', $step->to_user)->value('user_id') ?? $step->to_user;
            $signStatus[$uid] = match (true) {
                // 5 = подписал, 6 = подтвердил исполнение — оба «signed».
                FreeSignRouteService::isStepCompleted($step->status)  => 'signed',
                (int) $step->status === FreeSignRouteService::STEP_ACTIVE => 'active',
                default                                              => 'pending',
            };
        }

        // Тип подписи пользователя (как на штатных страницах подписи):
        // 1 = ЭЦП (E-IMZO), 2 = логин/пароль.
        $signType = \App\Models\Admin\UserPersonalInfo::where('user_id', $this->authActiveUser()->user_id)->first();

        /* ═══════════ ДАННЫЕ ДЛЯ РЕЗОЛЮЦИИ И ИСПОЛНЕНИЯ ═══════════ */

        // Шаг резолюции (RESOL) в маршруте — есть ли он вообще.
        $resolutionRole = \App\Models\Document\DocumentExecutionRoles::where('doc_code', 'RESOL')
            ->where('status', 1)->first();

        $resolutionStep = $resolutionRole
            ? DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $resolutionRole->id)->first()
            : null;

        // Уже созданная резолюция.
        $directorResolution = \App\Models\Document\DocumentResolutions::where('j_id', $journal->id)->first();

        // Моя очередь накладывать резолюцию? (шаг RESOL активен и он мой)
        $canResolve = $resolutionStep
            && in_array($resolutionStep->to_user, $userIds)
            && (int) $resolutionStep->status === FreeSignRouteService::STEP_ACTIVE
            && !$directorResolution;

        // Справочники для формы резолюции.
        $resolutionTypes = $canResolve
            ? \App\Models\Resolution\ResolutionTypes::where('status', 1)->get()
            : collect();

        $resolutionExecRoles = $canResolve
            ? \App\Models\Resolution\ResolutionExecutionRoles::where('status', 1)->orderBy('id')->get()
            : collect();

        // Исполнители (шаги EXEC) — для панели исполнения.
        $execRole = \App\Models\Document\DocumentExecutionRoles::where('doc_code', 'EXEC')
            ->where('status', 1)->first();

        $performerSteps = $execRole
            ? DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $execRole->id)->get()
            : collect();

        // Мой шаг исполнения — могу ли отчитаться.
        $myExecStep = $performerSteps
            ->whereIn('to_user', $userIds)
            ->whereIn('status', [FreeSignRouteService::STEP_ACTIVE, 4])
            ->first();

        /* ═══════ ВИДИМОСТЬ БЛОКОВ ПО СТАТУСУ ЖУРНАЛА ═══════
         | Данные о резолюции и исполнении показываем ТОЛЬКО когда документ
         | реально дошёл до этих этапов — иначе на стадии подписи болтались
         | бы пустые/преждевременные панели.
         |
         |  1,3   — создан / редактирование
         |  5,6   — на подписи / на согласовании   → резолюции ещё нет
         |  7     — на контроле исполнения          ┐
         |  8     — в процессе (резолюция наложена) ├→ показываем резолюцию
         |  9     — резолюция подписана             ┘
         |  10    — завершён                        → показываем всё
         */
        $jStatus = (int) $journal->status;

        // Резолюция: этап наступил (7/8/9) либо документ уже завершён (10).
        $showResolution = in_array($jStatus, [7, 8, 9, 10], true);

        // Исполнение: шаги EXEC активируются после подписи резолюции (9),
        // и остаются видимыми до конца (10).
        $showExecution = in_array($jStatus, [9, 10], true);

        // Форму резолюции показываем только на своём этапе.
        $canResolve = $canResolve && $showResolution;

        /* v51: встроенный редактор ЗАПОЛНЕНИЯ резолюции на странице
         | free-sign УБРАН (роль RESOL в маршруте остаётся!). Резолюционер
         | работает со ШТАТНОЙ страницы «Резолюция» (execution/confirm):
         | там штатная форма создания резолюции (document.create-resolution,
         | семантика совместима с нашим маршрутом — journal 8, RESOL 2,
         | EXEC-шаги) и подпись уже с ecp_doc_type=free_sign_doc.
         | Blade-блок и FreeSignResolutionController не удалены — форма
         | просто не показывается; вернуть = убрать строку ниже.
         */
        $canResolve = false;

        /* ═══ Тумблер «обязательная резолюция» (v49) — данные для редактора ═══
         | fsResolRoleId       — id роли RESOL: по нему JS решает, показывать ли
         |                       тумблер (есть ли резолюционер среди подписантов)
         | fsControlEnabled    — фактический контроль журнала (выбор документа,
         |                       при отсутствии записи — по настройке типа):
         |                       при включённом тумблер заблокирован — проект
         |                       резолюции готовит контролёр
         | fsResolutionRequired — текущее значение тумблера
         */
        $fsResolRoleId = \App\Models\Document\DocumentExecutionRoles::where('doc_code', 'RESOL')->value('id');

        $fsControlEnabled = \App\Models\FreeSignJournalControl::choiceFor($journal->id)
            ?? \App\Models\Journal\JournalTypeControls::where('journal_type_id', $journal->j_type)
                ->where('status', 1)
                ->exists();

        $fsResolutionRequired = \App\Models\FreeSignJournalControl::resolutionRequiredFor($journal->id);

        return view('office.journal.work.free-sign', compact(
            'journal', 'document', 'documentFile', 'people', 'roles', 'canSign',
            'savedSigners', 'savedFields', 'mode', 'signStatus', 'signType',
            'isAuthorConfirm', 'originalFile',
            'canResolve', 'resolutionTypes', 'resolutionExecRoles',
            'directorResolution', 'resolutionStep', 'performerSteps', 'myExecStep',
            'showResolution', 'showExecution',
            'fsResolRoleId', 'fsControlEnabled', 'fsResolutionRequired'
        ));
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
