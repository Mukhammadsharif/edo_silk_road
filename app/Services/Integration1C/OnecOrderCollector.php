<?php

namespace App\Services\Integration1C;

use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use App\Models\Integration1C\OnecOrderPayload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Фиксация кадрового приказа для выгрузки в 1С.
 *
 * Тип приказа определяется динамически через OnecOrderTypeResolver — по названию
 * журнала, без ручных таблиц соответствий. Для новой организации ничего
 * настраивать не нужно: первый же приказ создаёт соответствие сам.
 *
 * ПРО ПОЛНОТУ ДАННЫХ.
 * Часть полей контракта форма приказа сегодня не собирает: employment_type,
 * новая должность при переводе, второй сотрудник при совмещении.
 * Собираем всё, что реально есть (организация, сотрудник, подразделение,
 * должность, даты), подтип подставляем из соответствия журнала,
 * а недостающее пишем в missing_fields — честной пометкой вместо тихого null.
 */
class OnecOrderCollector
{
    /** Обязательные поля контракта по типам приказа — проверяются в собранном data. */
    private const REQUIRED = [
        'hirings'                         => ['user_id', 'employment_type', 'dep_id', 'post_id'],
        'terminations'                    => ['user_id'],
        'transfers'                       => ['user_id', 'start_date', 'new_dep_id', 'new_post_id'],
        'business_trips'                  => ['user_id', 'start_date', 'end_date'],
        'vacations'                       => ['user_id', 'vacation_type', 'start_date', 'end_date'],
        'absences'                        => ['user_id', 'absence_type', 'start_date', 'end_date'],
        'additional_duties'               => ['replacing_user_id', 'replaced_user_id', 'start_date'],
        'disciplinary_actions'            => ['user_id', 'action_type'],
        'removal_of_disciplinary_actions' => ['user_id', 'disciplinary_action_order_id'],
    ];

    /**
     * Статус подписанного шага. Ставится через updateStepStatus($steps['resolution'], 5)
     * в момент подписания резолюции через E-IMZO.
     */
    private const STEP_SIGNED = 5;

    public function __construct(
        private OnecOrderTypeResolver $types,
        private OnecLogger $logger
    ) {}

    /**
     * Зафиксировать приказ для выгрузки.
     *
     * @param  int  $jId  document_journals.id
     * @param  array  $extra  Типизированные поля, если форма их уже собирает
     * @param  string|null  $forceType  Принудительный тип, в обход определения
     */
    public function capture(
        int $jId,
        array $extra = [],
        ?string $forceType = null,
        bool $allowUnsigned = false
    ): ?OnecOrderPayload {
        try {
            $journal = DocumentJournals::with(['doc', 'journal', 'fromUser'])->find($jId);

            if (!$journal) {
                return null;
            }

            $resolved = $forceType
                ? ['order_type' => $forceType, 'subtype' => []]
                : $this->types->resolve($journal->journal);

            if (!$resolved) {
                // Не кадровый приказ, не распознан классификатором либо
                // журнал исключён администратором вручную
                return null;
            }

            // Резолюция должна быть именно подписана, а не документ возвращён
            // на доработку — оба случая дают документу статус 9
            $signedAt = $this->signDate($jId);

            if (!$signedAt && !$allowUnsigned) {
                $this->logger->event('ORDER SKIPPED — резолюция не подписана', [
                    'j_id'   => $jId,
                    'status' => $journal->status,
                ]);

                return null;
            }

            // Подтип из соответствия журнала (vacation_type / absence_type /
            // action_type) идёт первым, явно переданные поля его перекрывают
            $data = array_merge(
                $this->baseData($journal),
                $resolved['subtype'] ?? [],
                $extra
            );

            $payload = OnecOrderPayload::updateOrCreate(
                ['j_id' => $jId, 'order_type' => $resolved['order_type']],
                [
                    'doc_id'          => $journal->doc_id,
                    'journal_type_id' => $journal->j_type,
                    'org_unit_id'     => $journal->org_unit_id,
                    'order_number'    => $this->orderNumber($journal),
                    'created_date'    => $journal->created_at?->format('Y-m-d'),
                    'sign_date'       => $signedAt ?? now()->format('Y-m-d'),
                    'data'            => $data,
                    'missing_fields'  => $this->missingFields($resolved['order_type'], $data),
                    'export_status'   => OnecOrderPayload::STATUS_NEW,
                    'exported_at'     => null,
                ]
            );

            $this->logger->event('ORDER CAPTURED', [
                'j_id'    => $jId,
                'type'    => $resolved['order_type'],
                'missing' => $payload->missing_fields,
            ]);

            return $payload;
        } catch (\Throwable $e) {
            // Подписание документа не должно падать из-за интеграции
            Log::channel('onec')->error('[1C order capture] ' . $e->getMessage(), [
                'j_id' => $jId,
                'line' => $e->getLine(),
            ]);

            return null;
        }
    }

    /**
     * Что EDOLINE знает о приказе без доработки формы:
     * автор заявления, его место работы и даты из meta_data.
     */
    private function baseData(DocumentJournals $journal): array
    {
        $meta = DocumentTypeTempMetaData::where('doc_id', $journal->doc_id)->first();
        $json = $meta ? json_decode($meta->meta_data, true) : [];
        $json = is_array($json) ? $json : [];

        $work = $journal->fromUser; // user_work_histories

        return array_filter([
            // doc_code нужен, чтобы найти PDF приказа:
            // GenerateHrOrderPdfJob кладёт его как hr_orders/{doc_code}.pdf
            'doc_code' => $journal->doc?->doc_code,

            // user_work_histories: dep_id → departments, post_id → user_positions
            'work_id' => $work?->id,
            'user_id' => $work?->user_id,
            'dep_id'  => $work?->dep_id,
            'post_id' => $work?->post_id,

            // FROM_DATE_A / TO_DATE_A — даты из заявления-основания,
            // FROM_DATE_O / TO_DATE_O — даты самого приказа
            'start_date'  => $json['FROM_DATE_A'] ?? $json['FROM_DATE_O'] ?? null,
            'end_date'    => $json['TO_DATE_A'] ?? $json['TO_DATE_O'] ?? null,
            'base_doc_id' => $json['DOCUMENT_ID'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** Поля контракта, которых нет в собранных данных. */
    private function missingFields(string $orderType, array $data): array
    {
        $missing = [];

        foreach (self::REQUIRED[$orderType] ?? [] as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === []) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * Подписана ли резолюция.
     *
     * Статус 9 у документа сам по себе неоднозначен: его ставит и подписание
     * резолюции (SignDocImzoController, ветка sign_resolution), и возврат
     * на доработку (ExecutionController::controlSendToEdit, DOC_RETURNED).
     *
     * Различаем по шагу резолюции: при подписании он переводится в статус 5,
     * при возврате остаётся в прежнем. Без этой проверки возвращённый
     * на доработку документ уехал бы в 1С как подписанный приказ.
     */
    private function resolutionStep(int $jId): ?DocumentExecutionSteps
    {
        return DocumentExecutionSteps::where('j_id', $jId)
            ->whereHas('role', fn ($q) => $q->where('doc_code', 'RESOL'))
            ->where('status', self::STEP_SIGNED)
            ->latest('updated_at')
            ->first();
    }

    /** Дата подписания резолюции — она же дата приказа. */
    private function signDate(int $jId): ?string
    {
        return $this->resolutionStep($jId)?->updated_at?->format('Y-m-d');
    }

    /** Регистрационный номер: j_number — номер в журнале регистрации. */
    private function orderNumber(DocumentJournals $journal): string
    {
        if ($journal->j_number) {
            $year = $journal->j_date
                ? Carbon::parse($journal->j_date)->format('Y')
                : now()->format('Y');

            return $journal->j_number . '/' . $year;
        }

        return 'EDO-' . $journal->id;
    }
}
