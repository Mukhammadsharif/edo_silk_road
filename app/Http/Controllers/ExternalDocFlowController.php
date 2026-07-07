<?php

namespace App\Http\Controllers;

use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\UserWorkHistory;
use App\Models\Didox\DidoxDocument;
use App\Models\Didox\DidoxDocType;
use App\Models\User;
use App\Services\Didox\DidoxApprovalService;
use App\Services\Didox\DidoxClient;
use App\Services\Didox\DidoxSyncService;
use App\Services\Didox\DidoxTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

/**
 * Вкладка «Внешний документооборот» (входящие из Didox).
 *
 * МУЛЬТИОРГАНИЗАЦИОННОСТЬ:
 * - организация НЕ выбирается на вкладке — берётся активная организация
 *   пользователя (users.org_unit_id, переключается штатным механизмом);
 * - ИНН для Didox — organization_units.tax_number этой организации;
 * - каждая организация видит ТОЛЬКО свои документы (org_unit_id);
 * - открытие чужого документа по прямой ссылке → 403;
 * - согласующие выбираются из сотрудников этой же организации
 *   (UserWorkHistory, status=1).
 */
class ExternalDocFlowController extends Controller
{
    public function __construct(
        private DidoxTokenService $tokens,
        private DidoxSyncService $sync,
        private DidoxApprovalService $approval
    ) {}

    /** Активная организация пользователя или abort(403). */
    private function currentOrg(): OrganizationUnits
    {
        $orgId = (int) (Auth::user()->org_unit_id ?? 0);
        $org   = $orgId > 0 ? OrganizationUnits::find($orgId) : null;

        abort_if(!$org, 403, 'У пользователя не выбрана активная организация.');

        return $org;
    }

    /** Документ, принадлежащий активной организации, или 403/404. */
    private function findOrgDocument(int $id): DidoxDocument
    {
        $doc = DidoxDocument::with(['approvers.user.personalInfo'])->findOrFail($id);

        abort_if(
            (int) $doc->org_unit_id !== (int) $this->currentOrg()->id,
            403,
            'Документ принадлежит другой организации.'
        );

        return $doc;
    }

    /**
     * Ответственное лицо за Didox: Super_Admin (глобально), либо Director /
     * IT_Admin своей организации. Только они подписывают документы в Didox
     * и авторизуются в нём; остальным панель подписи не показывается.
     */
    private function isResponsible(): bool
    {
        $roleIds   = \App\Models\Admin\UserRole::where('model_id', Auth::id())->pluck('role_id')->toArray();
        $roleNames = \App\Models\Admin\Role::whereIn('id', $roleIds)->pluck('name')->toArray();

        return in_array('Super_Admin', $roleNames, true)
            || in_array('Director', $roleNames, true)
            || in_array('IT_Admin', $roleNames, true);
    }

    /** GET /external-docs — список входящих активной организации. */
    public function index(Request $request): View
    {
        $org        = $this->currentOrg();
        $listStatus = (string) $request->query('list_status', '');
        $tinFilled  = trim((string) $org->tax_number) !== '';
        $authorized = $tinFilled && $this->tokens->hasFreshToken(Auth::id(), $org->id);

        $query = DidoxDocument::where('org_unit_id', $org->id)
            ->withCount(['approvers as pending_approvers_count' => fn ($q) => $q->where('status', 'pending')]);

        if ($listStatus !== '') {
            $query->where('list_status', $listStatus);
        }

        $documents = $query->orderByDesc('received_at')->paginate(20)->withQueryString();

        return view('external-docs.index', [
            'documents'  => $documents,
            'org'        => $org,
            'tinFilled'  => $tinFilled,
            'listStatus' => $listStatus,
            'authorized' => $authorized,
            'canSign'    => $this->isResponsible(),
            'typeLabels' => DidoxDocType::LABELS,
        ]);
    }

    /** GET /external-docs/{id} — карточка (только своя организация). */
    public function show(int $id): View
    {
        $org        = $this->currentOrg();
        $doc        = $this->findOrgDocument($id);
        $authorized = $this->tokens->hasFreshToken(Auth::id(), $org->id);

        /*
         | Кандидаты в согласующие — активные сотрудники организации,
         | с ФИО, должностью и подразделением для карточек (как в execution-steps).
         */
        $workRows = UserWorkHistory::with(['personal', 'department', 'position'])
            ->where('org_id', $org->id)
            ->where('status', 1)
            ->where('user_id', '!=', Auth::id())
            ->get()
            ->unique('user_id')
            ->values();

        $candidates = $workRows->map(function ($w) {
            $fio = $w->personal
                ? trim(($w->personal->l_name ?? '') . ' ' . ($w->personal->f_name ?? ''))
                : null;
            return (object) [
                'user_id'    => $w->user_id,
                'name'       => $fio ?: ('#' . $w->user_id),
                'position'   => $w->position ? ($w->position->lang($w->position->id) ?? '') : '',
                'department' => $w->department ? ($w->department->lang($w->department->id) ?? '') : '',
            ];
        })->sortBy('name')->values();

        $myApproval = $doc->approvers->firstWhere('user_id', Auth::id());

        return view('external-docs.show', [
            'doc'        => $doc,
            'org'        => $org,
            'authorized' => $authorized,
            'canSign'    => $this->isResponsible(),
            'candidates' => $candidates,
            'myApproval' => $myApproval,
            'typeLabels' => DidoxDocType::LABELS,
        ]);
    }

    /**
     * GET /external-docs/{id}/print — печатная форма (HTML из Didox).
     * Внутреннее согласование не связано с Didox напрямую: если у смотрящего
     * нет личного токена (например, он согласующий), используется любой живой
     * токен ЭТОЙ ЖЕ организации — документ и так принадлежит организации.
     */
    public function printForm(int $id): JsonResponse
    {
        $doc = $this->findOrgDocument($id);

        try {
            $token = $this->resolveOrgToken($doc->org_unit_id);

            if ($token) {
                $client = app(DidoxClient::class)->withUserKey($token);
                $resp   = $client->html($doc->didox_id);

                if ($resp['success'] ?? false) {
                    // Кэшируем печатную форму: согласующие смогут смотреть её
                    // даже когда все токены организации протухнут.
                    $html = is_string($resp['data']) ? $resp['data'] : ($resp['data']['data'] ?? null);
                    if (is_string($html) && $html !== '') {
                        $doc->forceFill(['print_html' => $html, 'print_cached_at' => now()])->save();
                    }
                    return response()->json($resp, 200);
                }
                // живой токен, но Didox вернул ошибку — падаем в кэш ниже
            }

            // Нет токена или запрос не удался → отдаём кэш, если он есть
            if ($doc->print_html) {
                return response()->json([
                    'success' => true,
                    'data'    => $doc->print_html,
                    'cached'  => true,
                    'cached_at' => optional($doc->print_cached_at)->format('d.m.Y H:i'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Печатная форма ещё не загружалась, а активной авторизации Didox ни у кого в организации нет. Попросите ответственного войти в Didox и открыть документ.',
            ], 422);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** Личный токен пользователя, иначе — любой живой токен организации. */
    private function resolveOrgToken(int $orgUnitId): ?string
    {
        try {
            return $this->tokens->getFreshToken(Auth::id(), $orgUnitId);
        } catch (Throwable $e) {
            $row = \App\Models\Didox\DidoxToken::where('org_unit_id', $orgUnitId)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', now()->addMinutes(5))
                ->orderByDesc('expires_at')
                ->first();

            return $row?->token;
        }
    }

    /** GET /external-docs/approvals — документы, назначенные мне на согласование. */
    public function approvals(Request $request): View
    {
        $org    = $this->currentOrg();
        $status = (string) $request->query('status', 'pending'); // pending | all

        $query = \App\Models\Didox\DidoxDocumentApprover::with(['document'])
            ->where('user_id', Auth::id())
            ->whereHas('document', fn ($q) => $q->where('org_unit_id', $org->id));

        if ($status === 'pending') {
            $query->where('status', 'pending');
        }

        $approvals = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('external-docs.approvals', [
            'approvals'  => $approvals,
            'org'        => $org,
            'status'     => $status,
            'typeLabels' => DidoxDocType::LABELS,
        ]);
    }

    /** POST /external-docs/auth/password — вход в Didox по паролю (активная организация). */
    public function loginPassword(Request $request): JsonResponse
    {
        $org  = $this->currentOrg();
        $data = $request->validate(['password' => ['required', 'string']]);

        try {
            $this->tokens->loginByPassword(Auth::id(), $org->id, $data['password']);
            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /external-docs/auth/ecp — вход в Didox по ЭЦП (signature = timeStampTokenB64). */
    public function loginEcp(Request $request): JsonResponse
    {
        $org  = $this->currentOrg();
        $data = $request->validate(['signature' => ['required', 'string']]);

        try {
            $this->tokens->loginByEcp(Auth::id(), $org->id, $data['signature']);
            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /external-docs/sync — кнопка «Обновить» (pull для активной организации). */
    public function syncNow(): JsonResponse
    {
        $org = $this->currentOrg();

        try {
            $count = $this->sync->pullIncoming(Auth::id(), $org->id);
            return response()->json(['success' => true, 'synced' => $count]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /external-docs/{id}/send-to-review — отправка на согласование. */
    public function sendToReview(Request $request, int $id): JsonResponse
    {
        $org = $this->currentOrg();
        $doc = $this->findOrgDocument($id);

        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer'],
        ]);

        // Только сотрудники этой организации могут быть согласующими.
        $allowed = UserWorkHistory::where('org_id', $org->id)
            ->where('status', 1)
            ->pluck('user_id')
            ->map(fn ($v) => (int) $v);

        $invalid = collect($data['user_ids'])->map(fn ($v) => (int) $v)->diff($allowed);
        if ($invalid->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Выбраны пользователи вне вашей организации.',
            ], 422);
        }

        try {
            $this->approval->sendToReview($doc, $data['user_ids'], Auth::id());
            return response()->json(['success' => true, 'approval_status' => $doc->fresh()->approval_status]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /external-docs/{id}/decide — решение согласующего.
     * Решение подписывается ЭЦП согласующего: pkcs7 обязателен.
     */
    public function decide(Request $request, int $id): JsonResponse
    {
        $doc = $this->findOrgDocument($id);

        $data = $request->validate([
            'approved'      => ['required', 'boolean'],
            'comment'       => ['nullable', 'string', 'max:2000'],
            'pkcs7'         => ['required', 'string'],
            'signature_hex' => ['nullable', 'string', 'max:512'],
            'signer_serial' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $this->approval->decide(
                $doc,
                Auth::id(),
                (bool) $data['approved'],
                $data['comment'] ?? null,
                $data['pkcs7'],
                $data['signature_hex'] ?? null,
                $data['signer_serial'] ?? null
            );
            return response()->json(['success' => true, 'approval_status' => $doc->fresh()->approval_status]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /external-docs/{id}/refresh-status — подтянуть статус после подписи. */
    public function refreshStatus(int $id): JsonResponse
    {
        $doc = $this->findOrgDocument($id);

        try {
            $updated = $this->sync->refreshStatus(Auth::id(), $doc->org_unit_id, $doc->didox_id);
            return response()->json([
                'success'     => (bool) $updated,
                'list_status' => $updated?->list_status,
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /* ==================================================================
     |  Web-прокси операций подписи (E-IMZO поток карточки).
     * ================================================================== */

    /** POST /external-docs/{id}/tosign — данные к подписанию из Didox. */
    public function toSign(Request $request, int $id): JsonResponse
    {
        $doc = $this->findOrgDocument($id);

        try {
            $token  = $this->tokens->getFreshToken(Auth::id(), $doc->org_unit_id);
            $client = app(DidoxClient::class)->withUserKey($token);
            $resp   = $client->toSign($doc->didox_id, $request->only(['action']));

            return response()->json($resp, ($resp['success'] ?? false) ? 200 : 422);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** GET /external-docs/{id}/document-base64 — base64 документа из Didox (шаг 1 подписи входящего). */
    public function documentBase64(int $id): JsonResponse
    {
        $doc = $this->findOrgDocument($id);

        try {
            $token  = $this->tokens->getFreshToken(Auth::id(), $doc->org_unit_id);
            $client = app(DidoxClient::class)->withUserKey($token);
            $resp   = $client->documentBase64($doc->didox_id);

            return response()->json($resp, ($resp['success'] ?? false) ? 200 : 422);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /external-docs/{id}/tosign-value — значение toSign из деталей документа
     * (подпись продавца; шаг 4 подписи входящего по разделу 7 документации).
     */
    public function toSignValue(int $id): JsonResponse
    {
        $doc = $this->findOrgDocument($id);

        try {
            $token  = $this->tokens->getFreshToken(Auth::id(), $doc->org_unit_id);
            $client = app(DidoxClient::class)->withUserKey($token);
            // owner берём из локальной записи (0 входящий / 1 исходящий) — иначе Didox
            // отвечает «Нет такого документа» на неверное направление.
            $resp   = $client->document($doc->didox_id, ['owner' => (int) ($doc->owner ?? 0)]);

            if (!($resp['success'] ?? false)) {
                return response()->json($resp, 422);
            }

            $data   = $resp['data']['data'] ?? $resp['data'] ?? [];
            $toSign = $data['toSign'] ?? $data['tosign'] ?? null;

            if (!$toSign) {
                return response()->json(['success' => false, 'message' => 'Поле toSign отсутствует в ответе Didox.'], 422);
            }

            return response()->json(['success' => true, 'toSign' => $toSign]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /** POST /external-docs/signature-join — объединение подписей (шаг 5 подписи входящего). */
    public function signatureJoin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'signature1' => ['required', 'string'],
            'signature2' => ['required', 'string'],
        ]);

        $resp = app(DidoxClient::class)->signatureJoin($data['signature1'], $data['signature2']);

        return response()->json($resp, ($resp['success'] ?? false) ? 200 : 422);
    }

    /** POST /external-docs/timestamp — прикрепить timestamp к подписи. */
    public function timestamp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pkcs7'        => ['required', 'string'],
            'signatureHex' => ['required', 'string'],
        ]);

        $resp = app(DidoxClient::class)->timestamp($data['pkcs7'], $data['signatureHex']);

        return response()->json($resp, ($resp['success'] ?? false) ? 200 : 422);
    }

    /** POST /external-docs/{id}/sign — отправить подпись в Didox. */
    public function sign(Request $request, int $id): JsonResponse
    {
        $doc  = $this->findOrgDocument($id);
        $data = $request->validate(['signature' => ['required', 'string']]);

        try {
            $token  = $this->tokens->getFreshToken(Auth::id(), $doc->org_unit_id);
            $client = app(DidoxClient::class)->withUserKey($token);
            $resp   = $client->signDocument($doc->didox_id, $data['signature']);

            if ($resp['success'] ?? false) {
                try {
                    $this->sync->refreshStatus(Auth::id(), $doc->org_unit_id, $doc->didox_id);
                } catch (Throwable $e) { /* статус подтянется кроном */ }
            }

            return response()->json($resp, ($resp['success'] ?? false) ? 200 : 422);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
