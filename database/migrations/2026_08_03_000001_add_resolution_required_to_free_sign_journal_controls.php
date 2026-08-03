<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Тумблер «обязательное заполнение резолюции» для free-sign документа.
 * Колонка в НАШЕЙ таблице free_sign_journal_controls (штатные не трогаем).
 *
 *   true  (default) — текущее поведение: роль RESOL не может подписать,
 *                     пока не создана резолюция (исполнители, срок, текст)
 *   false           — RESOL может подписать без резолюции; исполнителей
 *                     не будет, документ завершится после резолюции
 *
 * Актуально только при ВЫКЛЮЧЕННОМ контроле исполнения: при включённом
 * проект резолюции готовит контролёр, и к моменту подписи она уже есть.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('free_sign_journal_controls', function (Blueprint $table) {
            $table->boolean('resolution_required')->default(true)
                ->comment('Обязательное заполнение резолюции ролью RESOL перед подписью');
        });
    }

    public function down(): void
    {
        Schema::table('free_sign_journal_controls', function (Blueprint $table) {
            $table->dropColumn('resolution_required');
        });
    }
};
