<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\TelegramMiniAppToken;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramDocumentController extends Controller
{
    private array $allowedRoles = ['SIGN', 'RESOL', 'AGREE'];

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
     * Определить doc_code роли для шага
     * role() — hasMany, поэтому ищем через DocumentExecutionRoles
     */
    private function getStepRole(DocumentExecutionSteps $step): ?string
    {
        $role = DocumentExecutionRoles::where('id', $step->exec_role)->first();
        return $role?->doc_code;
    }

    /**
     * GET /api/telegram/documents
     */
    public function forSign(Request $request): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        $user    = Auth::user();
        $workIds = $user->userWorkIDs();

        $documents = DocumentJournals::whereHas('executionSteps', function ($q) use ($workIds) {
            $q->whereIn('to_user', $workIds)
                ->where('status', 2)
                ->whereHas('role', fn($r) => $r->whereIn('doc_code', $this->allowedRoles));
        })
            ->where('status', '>', 1)
            ->with('doc')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $result = $documents->map(function ($journal) use ($workIds) {
            $doc = $journal->doc;

            // Найти шаг и его роль
            $step = DocumentExecutionSteps::where('j_id', $journal->id)
                ->whereIn('to_user', $workIds)
                ->where('status', 2)
                ->whereHas('role', fn($r) => $r->whereIn('doc_code', $this->allowedRoles))
                ->first();

            $roleCode = $step ? $this->getStepRole($step) : null;

            return [
                'id'         => $journal->id,
                'doc_number' => $doc?->doc_number ?? '—',
                'doc_name'   => $doc?->doc_name ?? '—',
                'created_at' => $journal->created_at?->format('d.m.Y'),
                'role'       => $roleCode,
                'role_label' => $this->roleLabel($roleCode),
            ];
        });

        return response()->json(['count' => $result->count(), 'documents' => $result]);
    }

    /**
     * GET /api/telegram/documents/{j_id}
     */
    public function show(Request $request, int $jId): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        $user    = Auth::user();
        $workIds = $user->userWorkIDs();

        $journal = DocumentJournals::with(['doc', 'files', 'journal'])->find($jId);
        if (!$journal) return response()->json(['error' => 'Document not found'], 404);

        // Найти шаг пользователя
        $step = DocumentExecutionSteps::where('j_id', $jId)
            ->whereIn('to_user', $workIds)
            ->where('status', 2)
            ->whereHas('role', fn($r) => $r->whereIn('doc_code', $this->allowedRoles))
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

        return response()->json([
            'id'         => $journal->id,
            'doc_id'   => $journal->doc_id,
            'doc_number' => $journal->doc?->doc_number,
            'doc_name'   => $journal->doc?->doc_name,
            'created_at' => $journal->created_at?->format('d.m.Y H:i'),
            'role'       => $roleCode,
            'role_label' => $this->roleLabel($roleCode),
            'sign_type'  => $signType,
            'step_id'    => $step->id,
            'html_doc'   => $htmlDoc,
            'files'      => $files,
        ]);
    }

    /**
     * POST /api/telegram/documents/{j_id}/sign-token
     */
    public function createSignToken(Request $request, int $jId): JsonResponse
    {
        $record = $this->resolveUser($request);
        if (!$record) return response()->json(['error' => 'Unauthorized'], 401);

        $user    = Auth::user();
        $workIds = $user->userWorkIDs();

        $journal = DocumentJournals::with('journal')->find($jId);
        if (!$journal) return response()->json(['error' => 'Document not found'], 404);

        $step = DocumentExecutionSteps::where('j_id', $jId)
            ->whereIn('to_user', $workIds)
            ->where('status', 2)
            ->whereHas('role', fn($r) => $r->whereIn('doc_code', $this->allowedRoles))
            ->first();

        if (!$step) return response()->json(['error' => 'Document not available'], 403);

        $roleCode  = $this->getStepRole($step);
        $signType  = $this->getSignType($roleCode, $journal);
        $signToken = $record->createSignToken($journal->doc_id, $signType);

        return response()->json([
            'sign_url'   => url("/mobile/sign/{$signToken}"),
            'sign_type'  => $signType,
            'expires_in' => 900,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

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

        \Illuminate\Support\Facades\Log::info('[TG] getSignType', [
            'role'     => $role,
            'docCode'  => $docCode,
            'isOutDoc' => $isOutDoc,
            'isHrDoc'  => $isHrDoc,
            'hasAgree' => $hasAgree,
            'hasResol' => $hasResol,
            'j_id'     => $journal->id,
            'j_status' => $journal->status,
        ]);

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
