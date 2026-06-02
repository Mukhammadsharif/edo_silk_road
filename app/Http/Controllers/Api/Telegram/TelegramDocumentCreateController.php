<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Admin\Department;
use App\Models\Admin\Role;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentOrganizations;
use App\Models\Document\Documents;
use App\Models\Journal\ConfidentialTypes;
use App\Models\Journal\JournalExecutionStepRoles;
use App\Models\Journal\JournalExecutionSteps;
use App\Models\Journal\JournalTypes;
use App\Models\TelegramMiniAppToken;
use App\Services\Documents\DocumentFactory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Создание документа из Telegram Mini App.
 *
 * Зеркалирует функционал office.document.create (web), но возвращает JSON
 * вместо blade-шаблонов и поддерживает multipart/form-data загрузку файлов.
 *
 * Endpoints:
 *  GET  /api/telegram/create/init/{type}           — справочники для wizard'а
 *  GET  /api/telegram/create/journals/{j_type_id}  — подтипы журнала
 *  GET  /api/telegram/create/execution-users/{j_id}— исполнители для шагов
 *  GET  /api/telegram/create/doc-organizations/{j_id} — внешние орг. (in/out)
 *  POST /api/telegram/create/store                 — сохранение документа
 *  POST /api/telegram/create/web-editor            — выдать URL для OnlyOffice в браузере
 */
class TelegramDocumentCreateController extends Controller
{
    /**
     * Резолв юзера из Bearer токена. Тот же паттерн что в других Telegram API.
     */
    private function resolveUser(Request $request): ?TelegramMiniAppToken
    {
        $bearer = $request->bearerToken();
        if (!$bearer) return null;
        $record = TelegramMiniAppToken::findValidAuth($bearer);
        if (!$record) return null;
        Auth::setUser($record->user);
        return $record;
    }

    /**
     * GET /api/telegram/create/init/{type}
     *
     * Отдаёт справочники для wizard'а: журналы верхнего уровня, языки,
     * confidence list, doc_term_options, флаг подписки.
     */
    public function init(Request $request, string $type): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        if (!in_array($type, ['incoming', 'outgoing', 'internal'], true)) {
            return response()->json(['error' => 'invalid_type'], 422);
        }

        $orgId = $this->organizationId();
        $auth  = $this->authActiveUser();

        // Базовый журнал для type
        $docCode = match ($type) {
            'incoming' => 'BASE_INCOMING',
            'outgoing' => 'BASE_OUTGOING',
            'internal' => 'BASE_INTERNAL',
        };

        $baseJournal = JournalTypes::where('org_id', $orgId)
            ->where('doc_code', $docCode)
            ->where('status', 1)
            ->first();

        // Список журналов верхнего уровня для type
        if ($type === 'internal' && $baseJournal) {
            // Internal — берём детей base journal
            $journals = JournalTypes::where('org_id', $orgId)
                ->where('parent_id', $baseJournal->id)
                ->where('status', 1)
                ->orderBy('order_number')
                ->get(['id', 'title', 'doc_code', 'lang_id']);
        } else {
            // Incoming/Outgoing — корневые с org_id
            $journals = JournalTypes::where('org_id', $orgId)
                ->where('parent_id', 0)
                ->where('status', 1)
                ->orderBy('order_number')
                ->get(['id', 'title', 'doc_code', 'lang_id']);
        }

        $confidences = ConfidentialTypes::where('status', 1)
            ->get(['id', 'title', 'lang_id']);

        $departments = Department::whereNot('parent_id', 0)
            ->where('status', 1)
            ->get(['id', 'name_uz']);

        // У юзера активная подписка?
        $hasSubscription = (bool) (Auth::user()->currentSubscription ?? null);

        return response()->json([
            'type'            => $type,
            'has_subscription'=> $hasSubscription,
            'journals'        => $journals->map(fn($j) => [
                'id'       => $j->id,
                'title'    => $this->journalLabel($j),
                'doc_code' => $j->doc_code,
            ]),
            'confidences'     => $confidences->map(fn($c) => [
                'id'    => $c->id,
                'title' => $this->confidenceLabel($c),
            ]),
            'departments'     => $departments->map(fn($d) => [
                'id'    => $d->id,
                'title' => $d->name_uz,
            ]),
            'languages'       => [
                ['code' => 'uz', 'label' => "O'zbek"],
                ['code' => 'kr', 'label' => 'Кирил'],
                ['code' => 'ru', 'label' => 'Русский'],
                ['code' => 'en', 'label' => 'English'],
            ],
        ]);
    }

    /**
     * GET /api/telegram/create/journals/{j_type_id}
     *
     * Подтипы выбранного журнала.
     * Возвращает массив (может быть пустым — тогда формы под-журналов нет).
     */
    public function journals(Request $request, int $jTypeId): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        $children = JournalTypes::where('parent_id', $jTypeId)
            ->where('status', 1)
            ->orderBy('order_number')
            ->get(['id', 'title', 'doc_code', 'lang_id']);

        return response()->json([
            'journals' => $children->map(fn($j) => [
                'id'       => $j->id,
                'title'    => $this->journalLabel($j),
                'doc_code' => $j->doc_code,
            ]),
        ]);
    }

    /**
     * GET /api/telegram/create/execution-users/{j_id}
     *
     * Резолюторы / согласующие / подписанты для шагов journal type.
     */
    public function executionUsers(Request $request, int $jId): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        $orgId = $this->organizationId();

        // Super_Admin'ов исключаем из списков (как в DocumentController)
        $superAdminRole = Role::where('name', $this->admin)->first();
        $superAdminIds  = [];
        if ($superAdminRole) {
            $superAdminIds = UserWorkHistory::whereHas('user', function ($q) use ($superAdminRole) {
                $q->whereHas('userRoles', function ($q2) use ($superAdminRole) {
                    $q2->where('role_id', $superAdminRole->id);
                });
            })->pluck('user_id')->toArray();
        }

        $resolUsers = $this->collectUsersForRole($jId, 'RESOL', $orgId, $superAdminIds);
        $signUsers  = $this->collectUsersForRole($jId, 'SIGN',  $orgId, $superAdminIds);
        $agreeUsers = $this->collectUsersForRole($jId, 'AGREE', $orgId, $superAdminIds);

        return response()->json([
            'resolution_users' => $resolUsers,
            'signatory_users'  => $signUsers,
            'agreement_users'  => $agreeUsers,
        ]);
    }

    /**
     * GET /api/telegram/create/doc-organizations/{j_id}
     *
     * Внешние организации для incoming/outgoing документов.
     */
    public function docOrganizations(Request $request, int $jId): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        $orgs = DocumentOrganizations::where('for_doc_type', $jId)
            ->where('status', 1)
            ->get(['id', 'name_uz']);

        return response()->json([
            'organizations' => $orgs->map(fn($o) => [
                'id'    => $o->id,
                'title' => $o->name_uz,
            ]),
        ]);
    }

    /**
     * POST /api/telegram/create/store
     *
     * Создание документа. Принимает multipart/form-data:
     *  - journal_type, journal_type2 (nullable)
     *  - doc_name, doc_number, doc_date, doc_text, doc_lang, confidence
     *  - signing_term, j_number
     *  - resolution_user, to_agree[], to_emp (исполнители)
     *  - doc_organization (для in/out)
     *  - main_file (загружаемый основной файл)
     *  - doc_files[] (дополнительные)
     *
     * По окончании возвращает id журнала и URL для редиректа.
     */
    public function store(Request $request): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        $validator = validator($request->all(), [
            'journal_type'    => 'required|integer|exists:journal_types,id',
            'journal_type2'   => 'nullable|integer|exists:journal_types,id',
            'doc_name'        => 'nullable|string|max:500',
            'doc_number'      => 'nullable|string|max:100',
            'doc_date'        => 'nullable|string|max:20',
            'doc_text'        => 'nullable|string',
            'doc_lang'        => 'nullable|string|in:uz,kr,ru,en',
            'confidence'      => 'nullable|integer',
            'signing_term'    => 'nullable|string|max:50',
            'j_number'        => 'nullable|string|max:50',
            'resolution_user' => 'required|integer',
            'to_emp'          => 'nullable|integer',
            'to_agree'        => 'nullable|array',
            'to_agree.*'      => 'integer',
            'doc_organization'=> 'nullable|integer',
            'main_file'       => 'nullable|file|max:51200', // 50MB
            'doc_files'       => 'nullable|array',
            'doc_files.*'     => 'file|max:51200',
        ], [
            'resolution_user.required' => __('field.resolution_user_required'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'    => 'validation',
                'messages' => $validator->errors()->toArray(),
            ], 422);
        }

        // Проверка подписки
        $user = Auth::user();
        if (!($user->currentSubscription ?? null)) {
            return response()->json([
                'error'   => 'no_subscription',
                'message' => __('field.need_subscription_for_creating_docs'),
            ], 403);
        }

        DB::beginTransaction();
        try {
            $journalType  = JournalTypes::find($request->journal_type);
            $journalType2 = $request->journal_type2 ? JournalTypes::find($request->journal_type2) : null;

            // Нормализуем signing_term:
            //  - Пустая строка / null            → null
            //  - Только цифры ("10")             → сегодня + N дней (Mini App шлёт количество дней)
            //  - "DD.MM.YYYY" или "YYYY-MM-DD"   → парсим как дату
            //  - Что-то ещё                      → null (вместо взрыва БД)
            $signingTerm = null;
            $raw = trim((string) $request->input('signing_term', ''));
            if ($raw !== '') {
                if (ctype_digit($raw)) {
                    $signingTerm = now()->addDays((int) $raw)->format('Y-m-d');
                } else {
                    try {
                        if (str_contains($raw, '.')) {
                            $signingTerm = Carbon::createFromFormat('d.m.Y', $raw)->format('Y-m-d');
                        } else {
                            $signingTerm = Carbon::parse($raw)->format('Y-m-d');
                        }
                    } catch (\Throwable $e) {
                        $signingTerm = null;
                    }
                }
            }

            // doc_date — пытаемся парсить, при ошибке используем сегодня
            $docDate = now()->format('Y-m-d');
            $rawDate = trim((string) $request->input('doc_date', ''));
            if ($rawDate !== '') {
                try {
                    if (str_contains($rawDate, '.')) {
                        $docDate = Carbon::createFromFormat('d.m.Y', $rawDate)->format('Y-m-d');
                    } else {
                        $docDate = Carbon::parse($rawDate)->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    // оставляем сегодня
                }
            }

            // 1) Создаём Documents
            $document = Documents::create([
                'doc_number' => $request->doc_number,
                'doc_date'   => $docDate,
                'doc_name'   => $request->doc_name ?? $journalType->title,
                'doc_hash'   => $this->uniqueHash(8),
                'doc_code'   => $this->uniqueDocCode(),
                'doc_text'   => $request->doc_text ?? '',
                'doc_term'   => $signingTerm,
                'doc_lang'   => $request->doc_lang,
            ]);

            // 2) DocumentJournals
            $journal = DocumentJournals::create([
                'doc_id'      => $document->id,
                'j_type'      => $journalType2?->id ?? $journalType->id,
                'j_number'    => $request->j_number ?? 1,
                'org_unit_id' => $this->organizationId(),
                'j_date'      => now(),
                'confid_type' => $request->confidence,
                'created_dep' => $this->authActiveUser()->dep_id,
                'created_user'=> $this->authActiveUser()->id,
                'status'      => 1,
            ]);

            // 3) Specific handler через factory
            $handler = DocumentFactory::make($journalType);
            $handler->handle($document, $journal, $request);

            // 4) Saving files
            $this->saveFiles($document->id, $journal->id, $request);

            // 5) Execution users
            $this->assignExecutionUsers($journal->id, $request);

            // 6) Log
            $this->createLog($journal->doc_id, 1, $journal->journal->title ?? '', $document->doc_name ?? '');

            DB::commit();

            // Веб-URL для опционального открытия в браузере
            $routeName = match ($journalType->doc_code) {
                'BASE_OUTGOING' => 'office.view-sent-doc',
                'BASE_INCOMING' => 'office.view-inbox-doc',
                default         => 'office.view-work-doc',
            };
            $viewUrl = route($routeName, [$journal->id, $document->doc_hash]);

            return response()->json([
                'success'    => true,
                'journal_id' => $journal->id,
                'doc_hash'   => $document->doc_hash,
                'view_url'   => $viewUrl,
                'message'    => __('message.doc_created_success_message'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Telegram create-doc failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error'   => 'server_error',
                'message' => 'Не удалось создать документ. Попробуйте ещё раз.',
            ], 500);
        }
    }

    /**
     * POST /api/telegram/create/web-editor
     *
     * Сохраняет введённые данные формы в Cache на 10 минут,
     * возвращает URL который откроется в браузере через tg.openLink().
     *
     * При первом GET-запросе на этот URL — мы переадресуем юзера на сессию
     * web-приложения с подставленными данными формы (через специальную blade-страницу).
     *
     * Это упрощённый вариант: мы НЕ переносим draft в OnlyOffice editor через
     * Mini App, потому что OnlyOffice JS не работает в Telegram WebView.
     */
    public function webEditor(Request $request): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        // Сохраняем данные формы (без файлов — их юзер заново загрузит на компьютере)
        $draftToken = Str::random(48);
        $payload = [
            'user_id' => Auth::id(),
            'data'    => $request->except(['main_file', 'doc_files']),
        ];

        Cache::put("tg_create_draft:{$draftToken}", $payload, now()->addMinutes(10));

        $url = url('/telegram/handoff-create?draft=' . urlencode($draftToken));

        return response()->json([
            'success' => true,
            'url'     => $url,
            'expires' => now()->addMinutes(10)->toIso8601String(),
        ]);
    }

    // ==========================================================
    //  Helpers (копии private методов из DocumentController)
    // ==========================================================

    private function collectUsersForRole(int $jId, string $roleCode, int $orgId, array $superAdminIds): array
    {
        $step = JournalExecutionSteps::where('journal_type_id', $jId)
            ->whereHas('executionRole', fn($q) => $q->where('doc_code', $roleCode))
            ->first();

        if (!$step) return [];

        $rolesIds  = JournalExecutionStepRoles::where('j_exec_step_id', $step->id)
            ->pluck('role_id')->toArray();
        $userIds   = UserRole::whereIn('role_id', $rolesIds)
            ->pluck('model_id')->toArray();

        $works = UserWorkHistory::where('user_work_histories.org_id', $orgId)
            ->whereIn('user_work_histories.user_id', $userIds)
            ->whereHas('user', fn($q) => $q->where('status', 1))
            ->where('user_work_histories.status', 1)
            ->whereNotIn('user_work_histories.user_id', $superAdminIds)
            ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
            ->orderBy('user_personal_infos.sort')
            ->select('user_work_histories.*')
            ->with(['user.userPersonal', 'department', 'position'])
            ->get();

        return $works->map(function ($w) {
            $personal = $w->user?->userPersonal;
            $name = $personal?->fullName() ?: ($w->user?->username ?? '—');
            return [
                'id'         => $w->id,           // work_history_id (это то что хранится в to_user/to_emp)
                'user_id'    => $w->user_id,
                'name'       => $name,
                'department' => $w->department?->name_uz,
                'position'   => $w->position?->name_uz,
            ];
        })->values()->toArray();
    }

    private function saveFiles($documentId, $journalId, Request $request): void
    {
        $today = Carbon::today();
        $path  = 'doc/' . $today->year . '/' . $today->month . '/' . $today->day . '/';

        // main_file — основной файл документа (как в web flow через other_docs)
        if ($request->hasFile('main_file')) {
            $this->storeFileToDb($request->file('main_file'), $documentId, $journalId, $path);
        }

        // doc_files[] — дополнительные вложения
        if ($request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $file) {
                $this->storeFileToDb($file, $documentId, $journalId, $path);
            }
        }
    }

    private function storeFileToDb($file, $documentId, $journalId, string $path): void
    {
        $documentFile            = new DocumentFiles();
        $documentFile->j_id      = $journalId;
        $documentFile->doc_id    = $documentId;
        $documentFile->from_user = $this->authActiveUser()->id;
        $documentFile->file_name = $file->getClientOriginalName();
        $documentFile->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
        $documentFile->file_path = $path;
        $documentFile->file_ext  = $file->getClientOriginalExtension();
        $documentFile->file_size = $file->getSize();
        Storage::disk('ftp_13')->put($path . $documentFile->file_hash, file_get_contents($file->getRealPath()));
        $documentFile->save();
    }

    private function assignExecutionUsers(int $journalId, Request $request): void
    {
        $resolutionUser = $request->input('resolution_user');
        $agreementUsers = $request->input('to_agree', []);
        $signatoryUser  = $request->input('to_emp');

        $resolRole = DocumentExecutionRoles::where('doc_code', 'RESOL')->where('status', 1)->first();
        $signRole  = DocumentExecutionRoles::where('doc_code', 'SIGN')->where('status', 1)->first();
        $agreeRole = DocumentExecutionRoles::where('doc_code', 'AGREE')->where('status', 1)->first();

        if ($resolutionUser && $resolRole) {
            $this->extracted($journalId, $resolutionUser, $resolRole->id);
        }
        if (is_array($agreementUsers) && $agreeRole) {
            foreach ($agreementUsers as $u) {
                $this->extracted($journalId, $u, $agreeRole->id);
            }
        }
        if ($signatoryUser && $signRole) {
            $this->extracted($journalId, $signatoryUser, $signRole->id);
        }
    }

    /**
     * Создаём шаг исполнения — точная копия DocumentController::extracted().
     */
    private function extracted($journalId, $signatoryUser, $execRoleId): void
    {
        $step = new DocumentExecutionSteps();
        $step->j_id          = $journalId;
        $step->from_dep      = $this->authActiveUser()->dep_id;
        $step->from_user     = $this->authActiveUser()->id;
        $step->to_dep        = $this->userWork($signatoryUser)?->dep_id;
        $step->to_user       = $signatoryUser;
        $step->exec_role     = $execRoleId;
        $step->res_exec_role = 0;
        $step->status        = 1;
        $step->save();
    }

    private function journalLabel(JournalTypes $j): string
    {
        $locale = app()->getLocale() ?: 'uz';
        if (method_exists($j, 'langName') && $j->lang_id) {
            try {
                $lang = $j->langName;
                if ($lang && isset($lang->text)) {
                    $text = is_array($lang->text) ? $lang->text : json_decode($lang->text, true);
                    if ($text) {
                        return $text[$locale] ?? $text['uz'] ?? $j->title;
                    }
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }
        return $j->title ?? '—';
    }

    private function confidenceLabel(ConfidentialTypes $c): string
    {
        // Сначала пытаемся через lang_id и LanguageLine (как в web blade-шаблонах)
        if ($c->lang_id) {
            try {
                $label = ConfidentialTypes::lang($c->lang_id);
                if ($label && $label !== 'Lang') {
                    return $label;
                }
            } catch (\Throwable $e) {
                // ignore — fall back
            }
        }
        return $c->title ?? '—';
    }
}
