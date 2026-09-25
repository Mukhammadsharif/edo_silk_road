<?php

namespace App\Services\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\StatusList;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Чтение документов: счётчики, списки, карточки, отправка и удаление своих.
 *
 * Логика вынесена из TelegramDocumentController без изменений, чтобы мобильное
 * приложение и мини-апп отдавали одинаковые данные. Отличие только в двух
 * местах, и оба вынесены наружу:
 *   - пользователь приходит параметром (проверку токена делает контроллер);
 *   - ссылка на подпись выдаётся через SignLinkIssuer — у мини-аппа свой
 *     механизм токенов, у мобилки свой.
 */
class DocumentFeedService
{
    // Помощники базового контроллера: authActiveUser(), organizationId(),
    // getSignerRole(), createLog() и прочие — см. трейт
    use UsesControllerHelpers;

    /**
     * Роли в маршруте документа, которые показываются клиенту.
     * Перенесено из TelegramDocumentController — свойство класса,
     * а не метод, поэтому при выносе логики его легко было упустить.
     */
    private array $allowedRoles = ['SIGN', 'RESOL', 'AGREE'];

    public function __construct(private SignLinkIssuer $signLinks) {}


    public function myDocuments(Request $request): JsonResponse
    {

        $q          = trim((string) $request->query('q', ''));
        $status     = $request->query('status');
        $dateFrom   = trim((string) $request->query('date_from', ''));
        $dateTill   = trim((string) $request->query('date_till', ''));
        $page       = max(1, (int) $request->query('page', 1));

        $locale = $request->query('lang', 'uz');
        if ($locale === 'cyr') $locale = 'uz';
        if (!in_array($locale, ['uz', 'ru', 'en'])) $locale = 'uz';

        $user = Auth::user();
        $allWorkIds = \App\Models\Admin\UserWorkHistory::where('user_id', $user->id)
            ->pluck('id')->toArray();

        $search = DocumentJournals::whereHas('journal', function ($qb) {
            $qb->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING']);
        })
            ->whereIn('created_user', $allWorkIds);

        $today = Carbon::now()->format('Y-m-d');

        // Спец-статусы из web (12 = просроченные, 14 = просрочена резолюция)
        if ($status == '12') {
            $search->whereHas('doc', function ($qd) use ($today) {
                $qd->where('doc_term', '<', $today)
                    ->whereNotIn('status', [2, 9, 10, 11]);
            });
        } elseif ($status == '14') {
            $search->whereNotIn('status', [2, 10, 11])
                ->whereHas('directorResolution', function ($qr) use ($today) {
                    $qr->where('res_term', '<', $today);
                });
        } elseif ($status) {
            $search->where('status', $status);
        } else {
            $search->where('status', '!=', 2);
        }

        if ($q !== '') {
            $search->whereHas('doc', function ($qd) use ($q) {
                $qd->where('doc_name', 'ILIKE', '%' . $q . '%')
                    ->orWhere('doc_text', 'ILIKE', '%' . $q . '%');
            });
        }

        if ($dateFrom !== '' && $dateTill !== '') {
            try {
                $from = Carbon::createFromFormat('d.m.Y', $dateFrom)->format('Y-m-d');
                $till = Carbon::createFromFormat('d.m.Y', $dateTill)->format('Y-m-d');
                $search->whereHas('doc', function ($qd) use ($from, $till) {
                    $qd->whereBetween('doc_date', [$from, $till]);
                });
            } catch (\Throwable $e) {
                // ignore bad date input
            }
        } elseif ($dateFrom !== '') {
            try {
                $from = Carbon::createFromFormat('d.m.Y', $dateFrom)->format('Y-m-d');
                $search->whereHas('doc', fn($qd) => $qd->where('doc_date', '>=', $from));
            } catch (\Throwable $e) {}
        } elseif ($dateTill !== '') {
            try {
                $till = Carbon::createFromFormat('d.m.Y', $dateTill)->format('Y-m-d');
                $search->whereHas('doc', fn($qd) => $qd->where('doc_date', '<=', $till));
            } catch (\Throwable $e) {}
        }

        $paginator = $search->where('status', '!=', 13)
            ->orderByDesc('id')
            ->with(['doc', 'journal', 'statusWork'])
            ->paginate(25, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($journal) use ($locale) {
            $doc    = $journal->doc;
            $jrnl   = $journal->journal;
            $status = $journal->statusWork;

            $journalLabel = $jrnl?->title ?? '—';
            if ($jrnl && $jrnl->lang_id) {
                try {
                    $lng = $jrnl->langName;
                    if ($lng && isset($lng->text)) {
                        $text = is_array($lng->text) ? $lng->text : json_decode($lng->text, true);
                        if ($text) {
                            $journalLabel = $text[$locale] ?? $text['uz'] ?? $jrnl->title;
                        }
                    }
                } catch (\Throwable $e) {}
            }

            $statusLabel = null;
            $statusBadge = null;
            if ($status) {
                $statusLabel = \App\Models\Admin\StatusList::docLang($status->id, $locale);
                $statusLabel = trim(preg_replace('/\s*\((hujjat|документ|doc)\)\s*$/iu', '', (string) $statusLabel));
                $statusBadge = $status->badgeClass ?? null;
            }

            return [
                'id'            => $journal->id,
                'doc_hash'      => $doc?->doc_hash,
                'doc_number'    => $doc?->doc_number,
                'doc_name'      => $doc?->doc_name ?? '—',
                'doc_date'      => $doc?->doc_date ? Carbon::parse($doc->doc_date)->format('d.m.Y') : null,
                'journal'       => $journalLabel,
                'status'        => $statusLabel,
                'status_badge'  => $statusBadge,
                'created_at'    => $journal->created_at?->format('d.m.Y'),
            ];
        })->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ]);
    }


    /**
     * GET /api/telegram/counts
     *
     * 4 числа для главных карточек:
     *   - for_resolution (RESOL): сколько ждёт моей резолюции
     *   - for_sign (SIGN):        сколько ждёт моей подписи
     *   - for_approval (AGREE):   сколько ждёт моего согласования
     *   - my_docs:                сколько моих созданных
     */
    /**
     * GET /api/telegram/status-options?category=for_resolution|for_sign|for_approval|my_docs
     *
     * Возвращает список доступных статусов для категории (берётся из StatusList — как в web).
     * Поддерживаемые типы:
     *   - for_resolution (Tasdiqlash) → type='doc_exec'
     *   - for_sign / for_approval    → type='doc_signer'
     *   - my_docs                    → type='work_document' (исключая 1,4,5,6,7,13)
     */

    public function statusOptions(Request $request): JsonResponse
    {

        $category = $request->query('category');

        // Map both old (RESOL/SIGN/AGREE) and new (for_resolution/...) category names
        $type = 'doc_signer';
        $excludeCodes = [];
        $defaultCode = '2';

        switch ($category) {
            case 'RESOL':
            case 'for_resolution':
                $type = 'doc_exec';
                $defaultCode = '2';
                break;
            case 'SIGN':
            case 'for_sign':
            case 'AGREE':
            case 'for_approval':
                $type = 'doc_signer';
                $defaultCode = '2';
                break;
            case 'my_docs':
                $type = 'work_document';
                $excludeCodes = [1, 4, 5, 6, 7, 13];
                $defaultCode = '';  // Barchasi by default
                break;
        }

        $query = \App\Models\Admin\StatusList::where('type', $type);
        if (!empty($excludeCodes)) {
            $query->whereNotIn('code', $excludeCodes);
        }
        $rows = $query->orderBy('order_number')->get();

        // Явная локаль из ?lang= (resolveUser уже применил app locale, но docLang
        // надёжнее с явным параметром — не зависит от кэша переводов).
        $locale = $request->query('lang', 'uz');
        if ($locale === 'cyr') $locale = 'uz';
        if (!in_array($locale, ['uz', 'ru', 'en'])) $locale = 'uz';

        $options = $rows->map(function ($row) use ($locale) {
            $title = \App\Models\Admin\StatusList::docLang($row->id, $locale);
            $title = trim(preg_replace('/\s*\((hujjat|документ|doc)\)\s*$/iu', '', (string) $title));
            return [
                'code'  => (string) $row->code,
                'label' => $title,
            ];
        })->values();

        return response()->json([
            'category' => $category,
            'type'     => $type,
            'default'  => $defaultCode,
            'options'  => $options,
        ]);
    }

    public function counts(Request $request): JsonResponse
    {

        $user = Auth::user();

        // ВАЖНО: web menuCount() использует ВСЕ work_history записи юзера
        // (без фильтра org_id/status), поэтому счётчики на главной берут все
        // организации. Делаем точно так же чтобы числа совпадали с вебом.
        $workIds = \App\Models\Admin\UserWorkHistory::where('user_id', $user->id)
            ->pluck('id')->toArray();

        // DOC_SIGN: role SIGN, status = 2
        $signCount = DocumentExecutionSteps::whereIn('to_user', $workIds)
            ->whereHas('role', fn($q) => $q->where('doc_code', 'SIGN'))
            ->where('status', 2)
            ->count();

        // DOC_APPROVE: role AGREE, status = 2
        $approveCount = DocumentExecutionSteps::whereIn('to_user', $workIds)
            ->whereHas('role', fn($q) => $q->where('doc_code', 'AGREE'))
            ->where('status', 2)
            ->count();

        // DOC_CONF (resolution): role RESOL, status IN [2, 6]
        $resolCount = DocumentExecutionSteps::whereIn('to_user', $workIds)
            ->whereHas('role', fn($q) => $q->where('doc_code', 'RESOL'))
            ->whereIn('status', [2, 6])
            ->count();

        // My docs count — ДОЛЖЕН совпадать с длиной списка myDocuments():
        // status != 2 (не в работе) и != 13, свои work-документы (не base in/out).
        $myDocsCount = DocumentJournals::whereHas('journal', function ($q) {
            $q->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING']);
        })
            ->whereIn('created_user', $workIds)
            ->where('status', '!=', 2)
            ->where('status', '!=', 13)
            ->count();

        return response()->json([
            'for_resolution' => $resolCount,
            'for_sign'       => $signCount,
            'for_approval'   => $approveCount,
            'my_docs'        => $myDocsCount,
        ]);
    }

    /**
     * GET /api/telegram/documents
     *
     * Параметр ?category=RESOL|SIGN|AGREE отфильтровывает по конкретной роли.
     * Без параметра — все три (back-compat).
     */

    public function forSign(Request $request): JsonResponse
    {

        $user = Auth::user();

        // Явная локаль
        $locale = $request->query('lang', 'uz');
        if ($locale === 'cyr') $locale = 'uz';
        if (!in_array($locale, ['uz', 'ru', 'en'])) $locale = 'uz';

        // ВСЕ work_history записи (как в counts/menuCount) — числа на главной
        // и длина списка теперь совпадают.
        $workIds = \App\Models\Admin\UserWorkHistory::where('user_id', $user->id)
            ->pluck('id')->toArray();

        $category = $request->query('category');
        $roles = in_array($category, ['RESOL', 'SIGN', 'AGREE'])
            ? [$category]
            : $this->allowedRoles;

        // Дефолтный статус зависит от роли (как в web menuCount):
        //   RESOL → [2,6], SIGN/AGREE → [2]
        $isResol = ($roles === ['RESOL']);
        $defaultStatuses = $isResol ? [2, 6] : [2];

        // Step status filter: 'all' = no filter, '' = default, иначе конкретный int.
        $stepStatus = $request->query('step_status', '2');
        $applyStepFilter = ($stepStatus !== 'all' && $stepStatus !== '' && $stepStatus !== null);
        // Если фронт прислал именно дефолт '2' для резолюции — расширяем до [2,6]
        $statusList = ($applyStepFilter && $stepStatus === '2' && $isResol)
            ? [2, 6]
            : [(int) $stepStatus];

        $documents = DocumentJournals::whereHas('executionSteps', function ($q) use ($workIds, $roles, $statusList, $applyStepFilter) {
            $q->whereIn('to_user', $workIds)
                ->whereHas('role', fn($r) => $r->whereIn('doc_code', $roles));
            if ($applyStepFilter) {
                $q->whereIn('status', $statusList);
            }
        })
            ->where('status', '>', 1)
            ->with('doc')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $result = $documents->map(function ($journal) use ($workIds, $roles, $statusList, $applyStepFilter, $locale) {
            $doc = $journal->doc;

            $stepQuery = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->whereHas('role', fn($r) => $r->whereIn('doc_code', $roles));
            if ($applyStepFilter) {
                $stepQuery->whereIn('status', $statusList);
            }
            $step = $stepQuery->first();

            $roleCode = $step ? $this->getStepRole($step) : null;

            // Локализованный статус шага (как badge в вебе)
            $statusType = ($roles === ['RESOL']) ? 'doc_exec' : 'doc_signer';
            $statusLabel = null;
            if ($step) {
                $statusRow = \App\Models\Admin\StatusList::where('type', $statusType)
                    ->where('code', $step->status)
                    ->first();
                if ($statusRow) {
                    $statusLabel = \App\Models\Admin\StatusList::docLang($statusRow->id, $locale);
                    $statusLabel = trim(preg_replace('/\s*\((hujjat|документ|doc)\)\s*$/iu', '', (string) $statusLabel));
                }
            }

            return [
                'id'           => $journal->id,
                'doc_number'   => $doc?->doc_number ?? '—',
                'doc_name'     => $doc?->doc_name ?? '—',
                'created_at'   => $journal->created_at?->format('d.m.Y'),
                'role'         => $roleCode,
                'role_label'   => $this->roleLabel($roleCode),
                'step_status'  => $step?->status,
                'status_label' => $statusLabel,
            ];
        });

        return response()->json(['count' => $result->count(), 'documents' => $result]);
    }

    /**
     * GET /api/telegram/my-documents/{j_id}
     *
     * Детальный просмотр СВОЕГО документа. Доступ только если юзер автор
     * (created_user через work_history).
     *
     * Также возвращает массив доступных action'ов на основе статуса:
     *   - status in [1, 2, 3] → edit, delete
     *   - status == 1         → send_to_sign | send_to_agreement | send_to_create_res
     */

    public function showMine(Request $request, int $jId): JsonResponse
    {

        $journal = DocumentJournals::with(['doc', 'files', 'journal', 'statusWork'])->find($jId);
        if (!$journal) return response()->json(['error' => 'Document not found'], 404);

        $workIds = \App\Models\Admin\UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();
        if (!in_array($journal->created_user, $workIds)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // HTML тело документа
        $htmlDoc = null;
        try {
            $htmlDoc = app(DocumentService::class)->getHtmlDoc($journal, $journal->doc);
        } catch (\Throwable $e) {}

        $files = $journal->files->map(function ($f) {
            return [
                'name' => $f->original_name ?? $f->file_name,
                'url'  => url('/storage/' . $f->file_path),
            ];
        });

        // Журнал label с lang
        $journalLabel = $journal->journal?->title ?? '—';
        if ($journal->journal && $journal->journal->lang_id) {
            try {
                $lng = $journal->journal->langName;
                if ($lng && isset($lng->text)) {
                    $text = is_array($lng->text) ? $lng->text : json_decode($lng->text, true);
                    if ($text) {
                        $locale = app()->getLocale() ?: 'uz';
                        $journalLabel = $text[$locale] ?? $text['uz'] ?? $journalLabel;
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Status label
        $statusLabel = null;
        if ($journal->statusWork) {
            $s = $journal->statusWork;
            $statusLabel = $s->title ?? null;
            if ($s->lang_id) {
                try {
                    $lng = $s->langName ?? null;
                    if ($lng && isset($lng->text)) {
                        $text = is_array($lng->text) ? $lng->text : json_decode($lng->text, true);
                        if ($text) {
                            $locale = app()->getLocale() ?: 'uz';
                            $statusLabel = $text[$locale] ?? $text['uz'] ?? $statusLabel;
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        // ─── Расчёт доступных actions (зеркалирует view-right.blade.php логику) ───
        $signRole  = DocumentExecutionRoles::where('doc_code', 'SIGN')->where('status', 1)->first();
        $agreeRole = DocumentExecutionRoles::where('doc_code', 'AGREE')->where('status', 1)->first();

        $hasSigner = $signRole && DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $signRole->id)
                ->exists();

        $agreeCount = $agreeRole ? DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $agreeRole->id)
            ->count() : 0;

        $actions = [];

        // delete и edit — для статусов 1, 2, 3
        if (in_array($journal->status, [1, 2, 3])) {
            $actions[] = [
                'code'  => 'delete',
                'label' => __('action.delete') ?: 'O\'chirish',
                'style' => 'danger',
            ];
            $actions[] = [
                'code'    => 'edit',
                'label'   => __('action.edit') ?: 'Tahrirlash',
                'style'   => 'warning',
                // Editor работает в браузере, не в Mini App
                'web_url' => url('/office/edit-doc/internal/' . $journal->id . '/' . $journal->doc?->doc_hash),
            ];
        }

        // Send_to_* — только для status=1
        if ($journal->status === 1) {
            if ($hasSigner) {
                $actions[] = [
                    'code'      => 'send_to_sign',
                    'label'     => __('action.send_to_signer') ?: 'Imzolovchiga yuborish',
                    'style'     => 'success',
                    'sign_type' => 'send_to_sign',
                ];
            } elseif ($agreeCount > 0) {
                $actions[] = [
                    'code'      => 'send_to_agreement',
                    'label'     => __('action.send_to_ag') ?: 'Kelishishga yuborish',
                    'style'     => 'success',
                    'sign_type' => 'send_to_agreement',
                ];
            } else {
                $actions[] = [
                    'code'      => 'send_to_create_res',
                    'label'     => __('action.send_for_signature') ?: 'Imzolash uchun yuborish',
                    'style'     => 'success',
                    'sign_type' => 'send_to_create_res',
                ];
            }
        }

        // Рендер документа теми же шаблонами что и веб (по типу doc_code)
        $renderedHtml = null;
        try {
            $document = $journal->doc;
            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getResolutionRole()->id)->first();
            $signerStep = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getSignerRole()->id)->first();
            $agreementUsers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getAgreementRole()->id)->get();

            if ($document && $directorDocument) {
                $renderedHtml = view('telegram.doc-content', [
                    'journal'          => $journal,
                    'document'         => $document,
                    'directorDocument' => $directorDocument,
                    'signer'           => $signerStep,
                    'agreement_users'  => $agreementUsers,
                    'html_doc'         => $htmlDoc,
                ])->render();
            }
        } catch (\Throwable $e) {
            $renderedHtml = null;
        }

        return response()->json([
            'id'         => $journal->id,
            'doc_id'     => $journal->doc_id,
            'doc_number' => $journal->doc?->doc_number,
            'doc_name'   => $journal->doc?->doc_name,
            'doc_date'   => $journal->doc?->doc_date ? Carbon::parse($journal->doc->doc_date)->format('d.m.Y') : null,
            'doc_term'   => $journal->doc?->doc_term ? Carbon::parse($journal->doc->doc_term)->format('d.m.Y') : null,
            'doc_text'   => $journal->doc?->doc_text,
            'doc_hash'   => $journal->doc?->doc_hash,
            'journal'    => $journalLabel,
            'status'     => $statusLabel,
            'status_code'=> $journal->status,
            'created_at' => $journal->created_at?->format('d.m.Y H:i'),
            'html_doc'   => $htmlDoc,
            'rendered_html' => $renderedHtml,
            'files'      => $files,
            'actions'    => $actions,
        ]);
    }

    /**
     * POST /api/telegram/my-documents/{j_id}/send
     *
     * Создаёт sign-token для одного из трёх типов: send_to_sign /
     * send_to_agreement / send_to_create_res. Затем Mini App открывает sign URL,
     * мобильный sign flow обработает (тот же SignDocImzoMobileApiController).
     */

    public function sendForSign(Request $request, int $jId): JsonResponse
    {

        $request->validate([
            'sign_type' => 'required|in:send_to_sign,send_to_agreement,send_to_create_res',
        ]);

        $journal = DocumentJournals::with('doc')->find($jId);
        if (!$journal) return response()->json(['error' => 'Document not found'], 404);

        // Проверка авторства
        $workIds = \App\Models\Admin\UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();
        if (!in_array($journal->created_user, $workIds)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Статус должен быть 1 (черновик)
        if ($journal->status !== 1) {
            return response()->json([
                'error'   => 'invalid_status',
                'message' => 'Hujjat status bilan bu amalni bajarib bo\'lmaydi',
            ], 422);
        }

        $signType  = $request->input('sign_type');
        $link = $this->signLinks->issue(Auth::user(), $journal, $signType);

        return response()->json([
            'sign_url'   => $link['url'],
            'sign_type'  => $signType,
            'expires_in' => $link['expires_in'],
        ]);
    }

    /**
     * DELETE /api/telegram/my-documents/{j_id}
     *
     * Удаляет документ (всё связанное). Только статус [1, 2, 3] и только автор.
     */

    public function deleteOwn(Request $request, int $jId): JsonResponse
    {

        $journal = DocumentJournals::find($jId);
        if (!$journal) return response()->json(['error' => 'Document not found'], 404);

        $workIds = \App\Models\Admin\UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();
        if (!in_array($journal->created_user, $workIds)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        if (!in_array($journal->status, [1, 2, 3])) {
            return response()->json([
                'error'   => 'invalid_status',
                'message' => 'Bu statusda hujjatni o\'chirib bo\'lmaydi',
            ], 422);
        }

        $docId = $journal->doc_id;

        // Зеркалирует DocController::delWorkDoc()
        try {
            \DB::transaction(function () use ($docId, $journal) {
                DocumentJournals::where('doc_id', $docId)->delete();
                \App\Models\Comment\DocumentCommentUser::where('doc_id', $docId)->delete();
                \App\Models\Comment\DocumentComment::where('doc_id', $docId)->delete();
                \App\Models\Document\DocumentFiles::where('doc_id', $docId)->delete();
                DocumentExecutionSteps::where('j_id', $journal->id)->delete();
                \App\Models\Document\DocumentReplyComments::where('j_id', $journal->id)->delete();
                \App\Models\Resolution\DocumentResolutions::where('j_id', $journal->id)->delete();
                \App\Models\Document\Documents::where('id', $docId)->delete();
            });
        } catch (\Throwable $e) {
            \Log::error('TG deleteOwn failed', [
                'j_id' => $jId, 'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error'   => 'server_error',
                'message' => 'O\'chirishda xatolik',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Hujjat o'chirildi",
        ]);
    }

    /**
     * GET /api/telegram/documents/{j_id}
     */

    public function show(Request $request, int $jId): JsonResponse
    {

        $user    = Auth::user();
        $workIds = \App\Models\Admin\UserWorkHistory::where('user_id', $user->id)
            ->pluck('id')->toArray();

        $journal = DocumentJournals::with(['doc', 'files', 'journal'])->find($jId);
        if (!$journal) return response()->json(['error' => 'Document not found'], 404);

        // Найти шаг пользователя (любой статус — фильтр статуса только для списка)
        $step = DocumentExecutionSteps::where('j_id', $jId)
            ->whereIn('to_user', $workIds)
            ->whereHas('role', fn($r) => $r->whereIn('doc_code', $this->allowedRoles))
            ->orderByRaw("CASE WHEN status = 2 THEN 0 ELSE 1 END") // prefer pending step
            ->orderByDesc('id')
            ->first();

        if (!$step) return response()->json(['error' => 'Access denied'], 403);

        $roleCode = $this->getStepRole($step);
        $signType = $this->getSignType($roleCode, $journal);

        // HTML тело документа
        $htmlDoc = null;
        try {
            $htmlDoc = app(DocumentService::class)->getHtmlDoc($journal, $journal->doc);
        } catch (\Throwable $e) {}

        $files = $journal->files->map(fn($f) => [
            'name' => $f->original_name ?? $f->file_name,
            'url'  => url('/storage/' . $f->file_path),
        ]);

        // ── Info-карта «Hujjat haqida ma'lumot» (как в публичной веб-странице) ──
        $locale = app()->getLocale() ?: 'uz';

        // Тип журнала (локализованный)
        $journalType = null;
        try {
            $jrnl = $journal->journal;
            if ($jrnl) {
                $journalType = $jrnl->title;
                if ($jrnl->lang_id) {
                    $lng = $jrnl->langName;
                    if ($lng && isset($lng->text)) {
                        $txt = is_array($lng->text) ? $lng->text : json_decode($lng->text, true);
                        $journalType = $txt[$locale] ?? $txt['uz'] ?? $jrnl->title;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Подписант-резолютор (Kim tomonidan imzolangan)
        $signedBy = null; $signedAt = null;
        try {
            $resolStep = DocumentExecutionSteps::where('j_id', $jId)
                ->whereHas('role', fn($r) => $r->where('doc_code', 'RESOL'))
                ->whereIn('status', [5, 2, 6])
                ->orderByDesc('updated_at')
                ->first();
            if ($resolStep && $resolStep->toUser && $resolStep->toUser->personal) {
                $p = $resolStep->toUser->personal;
                $signedBy = trim(
                    mb_substr($p->f_name ?? '', 0, 1) . '. ' .
                    (($p->m_name ?? '') ? mb_substr($p->m_name, 0, 1) . '. ' : '') .
                    ($p->l_name ?? '')
                );
                $signedAt = $resolStep->updated_at?->format('d.m.Y H:i');
            }
        } catch (\Throwable $e) {}

        // Создатель документа (Hujjat yaratuvchisi)
        $creator = null;
        try {
            if ($journal->fromUser && $journal->fromUser->personal) {
                $p = $journal->fromUser->personal;
                $creator = trim(
                    mb_substr($p->f_name ?? '', 0, 1) . '. ' .
                    (($p->m_name ?? '') ? mb_substr($p->m_name, 0, 1) . '. ' : '') .
                    ($p->l_name ?? '')
                );
            }
        } catch (\Throwable $e) {}

        $docDate = null;
        try {
            $docDate = $journal->doc?->doc_date
                ? \Carbon\Carbon::parse($journal->doc->doc_date)->format('d.m.Y')
                : null;
        } catch (\Throwable $e) {}

        // Публичная страница документа с QR (доступна когда полностью подписан)
        $publicUrl = null;
        $isFullySigned = in_array($journal->status, [9, 10, 11]);
        if ($isFullySigned && $journal->doc?->doc_code) {
            $publicUrl = url('/d/' . $journal->doc->doc_code);
        }

        // Рендер документа теми же шаблонами что и веб (по типу doc_code)
        $renderedHtml = null;
        try {
            $document = $journal->doc;
            $directorDocument = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getResolutionRole()->id)->first();
            $signerStep = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getSignerRole()->id)->first();
            $agreementUsers = DocumentExecutionSteps::where('j_id', $journal->id)
                ->where('exec_role', $this->getAgreementRole()->id)->get();

            if ($document && $directorDocument) {
                $renderedHtml = view('telegram.doc-content', [
                    'journal'          => $journal,
                    'document'         => $document,
                    'directorDocument' => $directorDocument,
                    'signer'           => $signerStep,
                    'agreement_users'  => $agreementUsers,
                    'html_doc'         => $htmlDoc,
                ])->render();
            }
        } catch (\Throwable $e) {
            $renderedHtml = null;
        }

        return response()->json([
            'id'          => $journal->id,
            'doc_id'      => $journal->doc_id,
            'doc_code'    => $journal->doc?->doc_code,
            'doc_number'  => $journal->doc?->doc_number,
            'doc_name'    => $journal->doc?->doc_name,
            'created_at'  => $journal->created_at?->format('d.m.Y H:i'),
            'role'        => $roleCode,
            'role_label'  => $this->roleLabel($roleCode),
            'sign_type'   => $signType,
            'step_id'     => $step->id,
            'html_doc'    => $htmlDoc,
            'rendered_html' => $renderedHtml,
            'files'       => $files,
            // info-карта
            'info' => [
                'journal_type' => $journalType,
                'doc_name'     => $journal->doc?->doc_name,
                'doc_date'     => $docDate,
                'signed_by'    => $signedBy,
                'signed_at'    => $signedAt,
                'creator'      => $creator,
            ],
            'public_url'     => $publicUrl,
            'is_fully_signed'=> $isFullySigned,
        ]);
    }

    /**
     * POST /api/telegram/documents/{j_id}/sign-token
     */

    private function getStepRole(DocumentExecutionSteps $step): ?string
    {
        $role = DocumentExecutionRoles::where('id', $step->exec_role)->first();
        return $role?->doc_code;
    }

    /**
     * GET /api/telegram/my-documents
     *
     * Зеркалирует web workDocs(): список документов, созданных текущим юзером
     * (исключая BASE_INCOMING/BASE_OUTGOING — те идут отдельной inbox/sent логикой).
     *
     * Параметры:
     *  - q           — поиск по doc_name / doc_text (substring)
     *  - status      — фильтр по DocumentJournals.status (число)
     *  - date_from   — DD.MM.YYYY
     *  - date_till   — DD.MM.YYYY
     *  - page        — пагинация (по 25)
     */

    private function roleLabel(?string $code): string
    {
        return match($code) {
            'SIGN'  => '✍️ Imzolash',
            'RESOL' => '📋 Rezolyutsiya',
            'AGREE' => '✅ Kelishish',
            default => '📄 Ko\'rish',
        };
    }

    private function getSignType(?string $role, $journal): string
    {
        // Получаем doc_code типа журнала
        $docCode = '';
        try {
            $jType = $journal->journal;
            if ($jType && !is_iterable($jType)) {
                $docCode = $jType->doc_code ?? '';
            }
        } catch (\Throwable $e) {}

        // Классификация документа
        $isOutDoc = str_contains($docCode, 'BASE_OUTGOING') || str_contains($docCode, 'OUT');
        $isHrDoc  = str_starts_with($docCode, 'APP_MT') || $docCode === 'APP';

        // Наличие активных шагов
        $hasAgree = \App\Models\Document\DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereHas('role', fn($r) => $r->where('doc_code', 'AGREE')->where('status', 1))
            ->exists();

        $hasResol = \App\Models\Document\DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereHas('role', fn($r) => $r->where('doc_code', 'RESOL')->where('status', 1))
            ->exists();

        return match($role) {

            // ── SIGN ──────────────────────────────────────────────────────────────
            'SIGN' => match(true) {
                $isOutDoc && $hasAgree  => 'send_to_ag_out_doc',    // OUT → на согласование
                $isOutDoc && !$hasAgree => 'send_sign_out_doc',     // OUT → напрямую резолюция
                !$isOutDoc && $hasAgree => 'from_signer_to_ag',     // внутр → на согласование
                !$isOutDoc && $hasResol => 'from_signer_to_c_res',  // внутр → на резолюцию
                default                 => 'sign_out_doc',          // внутр → завершить
            },

            // ── RESOL ─────────────────────────────────────────────────────────────
            'RESOL' => match(true) {
                $isOutDoc => 'sign_out_doc',      // OUT → завершить (j→10)
                default   => 'sign_resolution',   // внутр → резолюция (j→9, exec→2)
            },

            // ── AGREE ─────────────────────────────────────────────────────────────
            'AGREE' => match(true) {
                $hasResol  => 'from_ag_to_res',      // есть резолюция → отправить на резолюцию
                $isOutDoc  => 'send_to_ag_out_doc',  // OUT без резолюции
                default    => 'send_to_agreement',   // внутр без резолюции
            },

            default => 'send_to_sign',
        };
    }
}
