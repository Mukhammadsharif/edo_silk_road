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
        Schema::create('only_office_docs', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 10);
            $table->integer('doc_id');
            $table->string('doc_key', 100)->unique();
            $table->string('doc_path', 50);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('only_office_docs');
    }
};
