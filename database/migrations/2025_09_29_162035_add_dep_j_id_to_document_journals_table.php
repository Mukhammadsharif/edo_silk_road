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
        Schema::table('document_journals', function (Blueprint $table) {
            //
            $table->integer('dep_j_id')->nullable()->after('j_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_journals', function (Blueprint $table) {
            //
            $table->dropColumn('dep_j_id');
        });
    }
};
