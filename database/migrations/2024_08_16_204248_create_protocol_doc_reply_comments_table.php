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
        Schema::create('protocol_doc_reply_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_id');
            $table->integer('doc_member_id');
            $table->integer('user_id');
            $table->text('doc_text')->nullable();
            $table->smallInteger('status')->default(1);
            $table->timestamps();

            $table->index(['doc_id', 'doc_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_doc_reply_comments');
    }
};
