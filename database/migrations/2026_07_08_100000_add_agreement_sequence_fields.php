<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Уровень очереди согласования на каждом шаге исполнения.
        // 0 — не задан / параллельно; 1..N — порядок при последовательном согласовании.
        Schema::table('document_execution_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('document_execution_steps', 'queue_number')) {
                $table->smallInteger('queue_number')->default(0)->after('res_exec_role');
            }
        });

        // Режим согласования документа: parallel (по умолчанию) | sequential.
        Schema::table('document_journals', function (Blueprint $table) {
            if (!Schema::hasColumn('document_journals', 'agreement_type')) {
                $table->string('agreement_type', 20)->default('parallel')->after('confid_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_execution_steps', function (Blueprint $table) {
            if (Schema::hasColumn('document_execution_steps', 'queue_number')) {
                $table->dropColumn('queue_number');
            }
        });

        Schema::table('document_journals', function (Blueprint $table) {
            if (Schema::hasColumn('document_journals', 'agreement_type')) {
                $table->dropColumn('agreement_type');
            }
        });
    }
};
