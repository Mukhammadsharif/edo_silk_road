<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Маркер типов журналов «Внешаблонные документы» (free-sign) — v58.
 *
 * Тип создаётся для КАЖДОЙ компании отдельно (свой id), а doc_code
 * у типов свободный/разный — ни id, ни doc_code не годятся как
 * признак. Признак = ЗАПИСЬ в этой таблице: супер-админ помечает
 * тип внешаблонным, и модуль распознаёт его независимо от чего-либо.
 *
 * Пометить тип новой компании:
 *   INSERT INTO free_sign_journal_types (journal_type_id, created_at, updated_at)
 *   VALUES (<id типа>, now(), now());
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_sign_journal_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_type_id')->unique()
                ->comment('journal_types.id, помеченный как «Внешаблонные документы»');
            $table->timestamps();

            $table->foreign('journal_type_id')
                ->references('id')->on('journal_types')
                ->onDelete('cascade');
        });

        // Автопометка существующего типа первой компании (id 351),
        // чтобы после установки ничего не «пропало». Защищено проверками.
        if (DB::table('journal_types')->where('id', 351)->exists()) {
            DB::table('free_sign_journal_types')->insertOrIgnore([
                'journal_type_id' => 351,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('free_sign_journal_types');
    }
};
