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
        Schema::create('protocol_doc_comment_users', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_com_id');
            $table->integer('user_id');
            $table->smallInteger('is_read')->default(1);
            $table->dateTime('is_read_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_doc_comment_users');
    }
};
