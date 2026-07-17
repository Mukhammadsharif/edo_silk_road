<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Добавляет колонку owner (направление документа Didox: 0 входящий / 1 исходящий)
 * в таблицу didox_documents.
 *
 * Отдельная миграция нужна потому, что базовая (000002) уже была применена
 * без этой колонки — Laravel не перезапускает выполненные миграции, поэтому
 * колонку добавляем аддитивно. Безопасно для существующих данных.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('didox_documents', function (Blueprint $t) {
            if (!Schema::hasColumn('didox_documents', 'owner')) {
                $t->tinyInteger('owner')->nullable()->after('org_unit_id'); // 0 входящий / 1 исходящий
                $t->index(['org_unit_id', 'owner']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('didox_documents', function (Blueprint $t) {
            if (Schema::hasColumn('didox_documents', 'owner')) {
                $t->dropIndex(['org_unit_id', 'owner']);
                $t->dropColumn('owner');
            }
        });
    }
};
