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
        Schema::create('protocol_doc_decisions', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_id');
            $table->text('doc_text')->nullable();
            $table->date('doc_term')->nullable();
            $table->integer('doc_performer');
            $table->text('descr')->nullable();
            $table->smallInteger('status')->default(1);
            $table->integer('created_dep');
            $table->integer('created_user');
            $table->timestamps();

            $table->index(['doc_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_doc_decisions');
    }
};
