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
        Schema::create('reply_document_files', function (Blueprint $table) {
            $table->id();
            $table->integer('step_id');
            $table->integer('j_id');
            $table->string('file_name');
            $table->string('file_hash', 100);
            $table->string('file_path', 50);
            $table->string('file_ext', 10);
            $table->integer('file_size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reply_document_files');
    }
};
