<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('journal_types', function (Blueprint $table) {
            $table->unsignedBigInteger('org_id')
                ->nullable()
                ->after('id');
        });

        Schema::table('protocol_doc_types', function (Blueprint $table) {
            $table->unsignedBigInteger('org_id')
                ->nullable()
                ->after('id');
        });

        Schema::table('protocol_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('org_id')
                ->nullable()
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_types', function (Blueprint $table) {
            $table->dropColumn('org_id');
        });

        Schema::table('protocol_doc_types', function (Blueprint $table) {
            $table->dropColumn('org_id');
        });

        Schema::table('protocol_documents', function (Blueprint $table) {
            $table->dropColumn('org_id');
        });
    }
};
