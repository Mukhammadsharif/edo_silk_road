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
        Schema::create('document_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(0);
            $table->smallInteger('doc_type')->default(1);
            $table->integer('doc_id');
            $table->text('doc_text');
            $table->integer('created_id');
            $table->smallInteger('status')->default(1);
            $table->datetime('created_date');

            $table->index(['doc_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_comments');
    }
};
