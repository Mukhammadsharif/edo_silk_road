<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration1C\OnecOrderPayload;
use App\Models\Integration1C\OnecOrderTypeBinding;
use App\Services\Integration1C\OnecOrderTypeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * Управление соответствием «журнал EDOLINE → тип приказа 1С».
 *
 * Соответствия проставляет классификатор автоматически. Здесь администратор
 * видит результат по всем организациям и правит спорные случаи. После ручной
 * правки запись помечается source = manual, и классификатор её не трогает.
 */
class OnecOrderMappingController extends Controller
{
    public function __construct(private OnecOrderTypeResolver $types) {}

    /**
     * GET /admin/onec/order-mapping
     *
     * Параметры: org_id, only_review=1 (только требующие проверки)
     */
    public function index(Request $request)
    {
        $orgId    = $request->input('org_id') ? (int) $request->input('org_id') : null;
        $journals = $this->types->hrOrderJournals($orgId);

        $rows = [];

        foreach ($journals as $journal) {
            // binding() сам создаст соответствие, если его ещё нет —
            // поэтому страница всегда показывает полную картину
            $binding = $this->types->binding($journal);

            if (!$binding) {
                continue;
            }

            $rows[] = [
                'journal_type_id' => $journal->id,
                'org_id'          => $journal->org_id,
                'doc_code'        => $journal->doc_code,
                'title'           => $journal->title,
                'order_type'      => $binding->order_type,
                'subtype'         => $binding->subtype,
                'source'          => $binding->source,
                'confidence'      => $binding->confidence,
                'matched_by'      => $binding->matched_by,
                'status'          => $binding->status,
                'needs_review'    => $binding->needsReview(),
                'orders_count'    => OnecOrderPayload::where('journal_type_id', $journal->id)->count(),
            ];
        }

        if ($request->boolean('only_review')) {
            $rows = array_values(array_filter($rows, fn ($r) => $r['needs_review']));
        }

        $payload = [
            'success'      => true,
            'data'         => $rows,
            'total'        => count($rows),
            'review_count' => count(array_filter($rows, fn ($r) => $r['needs_review'])),
            'order_types'  => OnecOrderPayload::TYPES,
        ];

        // Blade-страницы может не быть — тогда отдаём JSON, а не 500.
        // Админка на jQuery всё равно ходит сюда за данными.
        if ($request->wantsJson() || !View::exists('admin.onec.order_mapping')) {
            return response()->json($payload);
        }

        return view('admin.onec.order_mapping', $payload);
    }

    /**
     * POST /admin/onec/order-mapping/update
     *
     * Body: journal_type_id, order_type (или null), vacation_type|absence_type|action_type, status
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'journal_type_id' => 'required|integer',
            'order_type'      => 'nullable|string|in:' . implode(',', OnecOrderPayload::TYPES),
            'vacation_type'   => 'nullable|string|in:paid,unpaid,parentalLeave,educational,militaryService',
            'absence_type'    => 'nullable|string|in:sickLeave,other',
            'action_type'     => 'nullable|string|in:remark,reprimand,severeReprimand',
            'status'          => 'nullable|string|in:active,ignored',
        ]);

        $subtype = array_filter([
            'vacation_type' => $validated['vacation_type'] ?? null,
            'absence_type'  => $validated['absence_type'] ?? null,
            'action_type'   => $validated['action_type'] ?? null,
        ]);

        $binding = $this->types->setManual(
            (int) $validated['journal_type_id'],
            $validated['order_type'] ?? null,
            $subtype,
            $validated['status'] ?? OnecOrderTypeBinding::STATUS_ACTIVE
        );

        return response()->json([
            'success' => true,
            'message' => 'Соответствие сохранено',
            'data'    => $binding,
        ]);
    }

    /**
     * POST /admin/onec/order-mapping/reset
     *
     * Вернуть запись под управление классификатора (снять ручную правку).
     */
    public function reset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'journal_type_id' => 'required|integer',
        ]);

        $binding = OnecOrderTypeBinding::where('journal_type_id', $validated['journal_type_id'])->first();

        if (!$binding) {
            return response()->json(['success' => false, 'message' => 'Соответствие не найдено'], 404);
        }

        $binding->delete();
        $this->types->resetCache();

        return response()->json([
            'success' => true,
            'message' => 'Соответствие сброшено, будет определено автоматически при следующем обращении',
        ]);
    }
}
