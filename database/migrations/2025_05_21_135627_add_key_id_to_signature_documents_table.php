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
        Schema::table('signature_documents', function (Blueprint $table) {
            //
            $table->integer('key_id')->after('user_id');
            $table->timestamp('signing_time')->after('key_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signature_documents', function (Blueprint $table) {
            //
            $table->dropColumn('key_id', 'signing_time');
        });
    }
};
