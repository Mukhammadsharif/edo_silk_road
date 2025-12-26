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
        Schema::create('document_execution_step_his', function (Blueprint $table) {
            $table->id();
            $table->integer('j_id');
            $table->integer('from_user');
            $table->integer('exec_type');
            $table->string('exec_text');
            $table->smallInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_execution_step_his');
    }
};
