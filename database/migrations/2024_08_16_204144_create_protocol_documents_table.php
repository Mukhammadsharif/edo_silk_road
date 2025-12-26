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
        Schema::create('protocol_documents', function (Blueprint $table) {
            $table->id();
            $table->string('doc_name', 512);
            $table->string('doc_number', 50)->nullable();
            $table->date('doc_date');
            $table->date('doc_term')->nullable();
            $table->string('doc_hash', 100)->unique();
            $table->string('doc_code', 50)->unique();
            $table->integer('doc_type_id');
            $table->smallInteger('visibility')->default(1);
            $table->smallInteger('status')->default(1);
            $table->integer('created_dep');
            $table->integer('created_user');
            $table->timestamps();

            $table->index(['doc_type_id', 'doc_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_documents');
    }
};
