<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Индивидуальный контроль исполнения документа (free-sign) —
 * ОТДЕЛЬНАЯ таблица, чтобы НЕ трогать document_journals и её модель.
 *
 * Семантика:
 *   запись есть, enabled = true  — документ уходит на контроль
 *                                  (Ijro nazorati), независимо от
 *                                  настройки типа в journal_type_controls
 *   запись есть, enabled = false — НЕ уходит, даже если у типа включён
 *   записи НЕТ                   — поведение по настройке ТИПА
 *                                  (все штатные и старые документы)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_sign_journal_controls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('j_id')->unique()
                ->comment('document_journals.id — один выбор на журнал');
            $table->boolean('enabled')->default(false)
                ->comment('true — на контроль исполнения, false — мимо контроля');
            $table->timestamps();

            $table->foreign('j_id')
                ->references('id')->on('document_journals')
                ->onDelete('cascade');

            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_sign_journal_controls');
    }
};
