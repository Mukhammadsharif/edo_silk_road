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
        Schema::create('document_report_form_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_report_form_id')->constrained()->cascadeOnDelete();
            $table->jsonb('values')->nullable();
            $table->unsignedBigInteger('filled_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_report_form_entries');
    }
};
