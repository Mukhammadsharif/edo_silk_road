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
        Schema::table('protocol_documents', function (Blueprint $table) {
            //
            $table->string('doc_lang', 2)->default('uz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_documents', function (Blueprint $table) {
            //
            $table->dropColumn('doc_lang');
        });
    }
};
