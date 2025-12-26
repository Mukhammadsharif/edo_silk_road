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
        Schema::create('doc_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('doc_id');
            $table->integer('act_id');
            $table->date('act_date');
            $table->string('log_type')->nullable();
            $table->text('doc_name')->nullable();
            $table->string('computer_id')->nullable();
            $table->timestamps();

            $table->index(['doc_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_logs');
    }
};
