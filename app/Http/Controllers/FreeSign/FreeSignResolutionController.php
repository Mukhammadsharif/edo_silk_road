<?php

namespace App\Http\Controllers\FreeSign;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\Document\ReplyDocumentFiles;
use App\Services\FreeSign\FreeSignRouteService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Резолюция и исполнение для free-sign документов.
 *
 * Логика 1-в-1 повторяет ExecutionController::createResolution() и
 * replyDocument(), но работает внутри free-sign редактора.
 *
 * ВАЖНО про ID: to_user в document_execution_steps — это
 * user_work_histories.id (не users.id). Форма отдаёт work_id напрямую
 * (как штатная), поэтому конвертация тут не нужна.
 */
class FreeSignResolutionController extends Controller
{
    /**
     * POST /free-sign/{journal}/resolution
     * Создание резолюции: исполнители + тип + срок + текст.
     *
     * Повторяет ExecutionController::createResolution():
     *   - создаёт шаги EXEC для каждого исполнителя (status=1)
     *   - пишет document_resolutions
     *   - журнал → 8 (In process)
     *   - шаг RESOL → 2 (резолюция наложена)
     */
    public function store(Request $request, $journalId): RedirectResponse
    {
        $request->validate([
            'user_id'   => 'required|array|min:1',
            'user_role' => 'required|array',
            'dep_id'    => 'required|array',
            'perf_term' => 'nullable|date',
        ], [
            'user_id.required'   => __('validate.user_id_mandatory'),
            'user_role.required' => __('validate.user_role_mandatory'),
            'dep_id.required'    => __('validate.dep_id_mandatory'),
        ]);

        $userIds   = $request->user_id;
        $userRoles = $request->user_role;
        $depIds    = $request->dep_id;
        $resType   = $request->r_type;
        $perfTerm  = $request->perf_term;
        $resText   = $request->ecp_r_text;

        $journal = DocumentJournals::findOrFail($journalId);

        $executionRole = DocumentExecutionRoles::where('doc_code', 'EXEC')
            ->where('status', 1)
            ->first();

        if (!$executionRole) {
            return back()->with('error', 'Не найдена роль EXEC в document_execution_roles');
        }

        // Шаг резолюции — от кого идёт резолюция.
        $resolutionStep = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getResolutionRole()->id)
            ->first();

        if (!$resolutionStep) {
            return back()->with('error', 'В маршруте нет роли RESOL — резолюцию накладывать некому');
        }

        DB::transaction(function () use (
            $journal, $userIds, $userRoles, $depIds, $executionRole,
            $resolutionStep, $resType, $resText, $perfTerm
        ) {
            // 1. Шаги исполнителей (EXEC). status=1 — активирует sign_resolution.
            foreach ($userIds as $key => $userId) {
                $step = new DocumentExecutionSteps();
                $step->j_id          = $journal->id;
                $step->from_dep      = $this->userWork($resolutionStep->to_user)->dep_id;
                $step->from_user     = $this->userWork($resolutionStep->to_user)->id;
                $step->to_dep        = $depIds[$key] ?? null;
                $step->to_user       = $userId;                       // work_history.id
                $step->exec_role     = $executionRole->id;
                $step->res_exec_role = $userRoles[$key] ?? 0;         // роль внутри резолюции
                $step->status        = FreeSignRouteService::STEP_IDLE;
                $step->save();
            }

            // 2. Сама резолюция.
            $resolution = new DocumentResolutions();
            $resolution->doc_exec_step_id = $resolutionStep->id;
            $resolution->j_id      = $journal->id;
            $resolution->from_dep  = $this->userWork($resolutionStep->to_user)->dep_id;
            $resolution->from_user = $this->userWork($resolutionStep->to_user)->id;
            $resolution->res_type  = $resType;
            $resolution->res_text  = $resText;
            $resolution->res_term  = $perfTerm;
            $resolution->status    = 1;
            $resolution->save();

            // 3. Статусы: журнал → 8 (In process), шаг резолюции → 2.
            $journal->update(['status' => 8]);
            $resolutionStep->update(['status' => 2]);
        });

        // Лог (как в штатном createResolution).
        $actId = $resolutionStep->to_user === $this->authActiveUser()->id ? 9 : 24;
        $this->createLog(
            $journal->doc_id,
            $actId,
            $journal->journal->title ?? '',
            $journal->doc->doc_name ?? ''
        );

        return back()->with('success', __('message.task_completed_successfully'));
    }

    /**
     * POST /free-sign/reply/{step}
     * Отчёт исполнителя: текст + файлы.
     *
     * Повторяет ExecutionController::replyDocument():
     *   - пишет document_reply_comments
     *   - файлы в reply_document_files (диск ftp_13)
     *   - шаг → 4 (отчёт отправлен)
     */
    public function reply(Request $request, $stepId): RedirectResponse
    {
        $request->validate([
            'r_text' => 'required|string',
        ]);

        $step = DocumentExecutionSteps::findOrFail($stepId);

        DB::transaction(function () use ($request, $step, $stepId) {
            $comment = new DocumentReplyComments();
            $comment->doc_exec_step_id   = $stepId;
            $comment->j_id               = $step->j_id;
            $comment->from_dep           = $this->authActiveUser()->dep_id;
            $comment->from_user          = $this->authActiveUser()->id;
            $comment->reply_comment_type = $request->type ?: 0;
            $comment->res_text           = $request->r_text;
            $comment->status             = 1;
            $comment->save();

            // Файлы отчёта — путь и хэш как в saveFiles()/replyDocument().
            if ($request->hasFile('doc_files')) {
                $today = Carbon::today();
                $path  = 'doc/' . $today->year . '/' . $today->month . '/' . $today->day . '/';

                foreach ($request->file('doc_files') as $file) {
                    $rf = new ReplyDocumentFiles();
                    $rf->step_id   = $comment->id;
                    $rf->j_id      = $step->j_id;
                    $rf->file_name = $file->getClientOriginalName();
                    $rf->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
                    $rf->file_path = $path;
                    $rf->file_ext  = $file->getClientOriginalExtension();
                    $rf->file_size = $file->getSize();

                    Storage::disk('ftp_13')->put($path . $rf->file_hash, file_get_contents($file->getRealPath()));
                    $rf->save();
                }
            }

            // 4 = отчёт отправлен (как в штатном replyDocument).
            $step->update(['status' => 4]);
        });

        $journal = DocumentJournals::find($step->j_id);
        $this->createLog(
            $journal->doc_id,
            6,
            $journal->journal->title ?? '',
            $journal->doc->doc_name ?? ''
        );

        return back()->with('success', __('message.task_completed_successfully'));
    }

    /**
     * GET /free-sign/performers/search?q=...
     * Поиск исполнителей для формы резолюции (AJAX).
     * Возвращает work_history.id — именно он идёт в to_user.
     */
    public function searchPerformers(Request $request)
    {
        $q     = trim((string) $request->input('q'));
        $orgId = $this->authActiveUser()->org_id ?? auth()->user()->org_unit_id;
        $lang  = app()->getLocale();

        $rows = DB::table('user_work_histories as w')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->leftJoin('user_personal_infos as pi', 'pi.user_id', '=', 'u.id')
            ->leftJoin('user_positions as up', 'up.id', '=', 'w.post_id')
            ->leftJoin('departments as d', 'd.id', '=', 'w.dep_id')
            ->where('w.org_id', $orgId)
            ->where('w.status', 1)
            ->when($q !== '', function ($query) use ($q) {
                $needle = '%' . $q . '%';
                $query->where(function ($w) use ($needle) {
                    $w->where('pi.f_name', 'ilike', $needle)
                      ->orWhere('pi.l_name', 'ilike', $needle)
                      ->orWhere('pi.m_name', 'ilike', $needle);
                });
            })
            ->select(
                'w.id',                 // work_history.id → to_user
                'w.dep_id',
                DB::raw("TRIM(CONCAT(COALESCE(pi.l_name,''),' ',COALESCE(pi.f_name,''),' ',COALESCE(pi.m_name,''))) as full_name"),
                DB::raw("COALESCE(up.name_{$lang}, up.name_ru) as position"),
                DB::raw("COALESCE(d.name_{$lang}, d.name_ru) as department")
            )
            ->orderBy('pi.l_name')
            ->limit(50)
            ->get();

        return response()->json(['data' => $rows]);
    }
}
