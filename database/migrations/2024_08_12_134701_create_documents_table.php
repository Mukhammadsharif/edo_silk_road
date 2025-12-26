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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('doc_number')->nullable();
            $table->date('doc_date');
            $table->string('doc_name');
            $table->text('doc_text');
            $table->string('doc_hash', 12);
            $table->string('doc_code', 10)->unique();
            $table->string('doc_lang', 2);
            $table->date('doc_term')->nullable();

            $table->index(['doc_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
