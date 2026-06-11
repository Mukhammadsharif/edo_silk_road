<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Добавляет org_unit_id в буферные таблицы onec_*,
 * чтобы показывать данные по конкретной организации (из токена 1С).
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
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'org_unit_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('org_unit_id')->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'org_unit_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('org_unit_id');
                });
            }
        }
    }
};
