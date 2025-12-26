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
        Schema::table('protocol_doc_types', function (Blueprint $table) {
            //
            $table->tinyInteger('sign_type')->default(1)->after('member_role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_doc_types', function (Blueprint $table) {
            //
            $table->dropColumn('sign_type');
        });
    }
};
