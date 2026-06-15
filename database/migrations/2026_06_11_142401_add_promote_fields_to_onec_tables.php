<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Поля диагностики staging→promote в буфере onec_*:
 *   sync_error    — причина, почему запись не промоутнута в рабочую таблицу
 *   promoted_at   — когда успешно перенесена
 *   sync_attempts — счётчик попыток промоута (защита от циклов)
 */
return new class extends Migration
{
    private array $tables = [
        'onec_companies', 'onec_divisions', 'onec_positions',
        'onec_stafflist_positions', 'onec_persons', 'onec_employees',
        'onec_vocations', 'onec_users',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'sync_error')) {
                    $t->text('sync_error')->nullable();
                }
                if (!Schema::hasColumn($table, 'promoted_at')) {
                    $t->timestamp('promoted_at')->nullable();
                }
                if (!Schema::hasColumn($table, 'sync_attempts')) {
                    $t->unsignedInteger('sync_attempts')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($table) {
                foreach (['sync_error', 'promoted_at', 'sync_attempts'] as $col) {
                    if (Schema::hasColumn($table, $col)) $t->dropColumn($col);
                }
            });
        }
    }
};
