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
        Schema::create('document_reply_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_exec_step_id');
            $table->integer('j_id');
            $table->integer('from_dep');
            $table->integer('from_user');
            $table->integer('reply_comment_type');
            $table->text('res_text');
            $table->smallInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_reply_comments');
    }
};
