<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Интеграция кадровых приказов с 1С — две таблицы.
 *
 * 1) onec_order_type_bindings — соответствие журнала EDOLINE типу приказа 1С.
 *
 *    Захардкодить doc_code нельзя: организаций много, doc_code и названия
 *    журналов каждая заводит сама. Поэтому соответствие живёт в БД и
 *    определяется автоматически по названию журнала (OnecOrderTypeClassifier),
 *    а администратор при необходимости правит руками.
 *
 *    source = auto   — определено классификатором, может быть переопределено
 *    source = manual — поставлено человеком, классификатор больше не трогает
 *
 * 2) onec_order_payloads — структурированные данные конкретного приказа.
 *
 *    Контракт 1С требует типизированные поля (vacation_type, employment_type,
 *    new_division_ID и т.д.). В EDOLINE приказ хранится как документ-шаблон плюс
 *    document_type_temp_meta_data с пятью датами — типа отпуска и новой должности
 *    там нет. Этот слой собирает всё, что известно, а недостающее пишет
 *    в missing_fields, чтобы неполнота была видна, а не маскировалась null'ами.
 *
 *    Локальные ID храним как есть, GUID'ы 1С резолвим на чтении через
 *    bitrix_hashes: справочник из 1С может доехать позже самого приказа.
 */
return new class extends Migration
{
    public function up(): void
    {
        /* ─────────── Соответствие журнал → тип приказа 1С ─────────── */
        Schema::create('onec_order_type_bindings', function (Blueprint $t) {
            $t->id();

            $t->unsignedBigInteger('journal_type_id')->unique();
            $t->unsignedInteger('org_id')->nullable();

            // Снимок на момент определения — чтобы в админке было видно,
            // что именно классифицировали, без джойна
            $t->string('doc_code', 50)->nullable();
            $t->string('title', 200)->nullable();

            // null = не распознано, приказ в 1С не уйдёт
            $t->string('order_type', 40)->nullable();

            // Уточнение внутри типа: {"vacation_type":"paid"} / {"absence_type":"sickLeave"}
            $t->jsonb('subtype')->nullable();

            // auto | manual
            $t->string('source', 10)->default('auto');

            // 0..100 — насколько уверен классификатор. Ниже порога просим проверить руками
            $t->unsignedTinyInteger('confidence')->default(0);

            // Какое правило сработало — для разбора спорных случаев
            $t->string('matched_by', 200)->nullable();

            // active | ignored — журнал сознательно исключён из выгрузки
            $t->string('status', 10)->default('active');

            $t->timestamps();

            $t->index(['org_id', 'order_type']);
            $t->index('status');
        });

        /* ─────────── Структурированные данные приказов ─────────── */
        Schema::create('onec_order_payloads', function (Blueprint $t) {
            $t->id();

            $t->unsignedBigInteger('j_id')->comment('document_journals.id');
            $t->unsignedBigInteger('doc_id')->nullable()->comment('documents.id');
            $t->unsignedBigInteger('journal_type_id')->nullable();
            $t->unsignedInteger('org_unit_id')->nullable();

            // hirings | terminations | transfers | business_trips | vacations |
            // absences | additional_duties | disciplinary_actions |
            // removal_of_disciplinary_actions
            $t->string('order_type', 40);

            $t->string('order_number', 50)->nullable()->comment('Регистрационный номер приказа');
            $t->date('created_date')->nullable();
            $t->date('sign_date')->nullable();

            // Локальные значения: user_id, dep_id, post_id, enum'ы контракта, даты
            $t->jsonb('data');

            // Поля контракта, которых нет в данных формы
            $t->jsonb('missing_fields')->nullable();

            // new | exported | error — 1С подтверждает приём через mark_exported
            $t->string('export_status', 20)->default('new');
            $t->timestamp('exported_at')->nullable();
            $t->text('export_error')->nullable();

            $t->timestamps();

            $t->unique(['j_id', 'order_type']);

            // Под основной фильтр /get_list
            $t->index(['order_type', 'org_unit_id', 'export_status'], 'onec_order_payloads_pull_idx');
            $t->index('sign_date');
            $t->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onec_order_payloads');
        Schema::dropIfExists('onec_order_type_bindings');
    }
};
