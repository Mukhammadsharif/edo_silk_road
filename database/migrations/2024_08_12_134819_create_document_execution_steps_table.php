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
        Schema::create('document_execution_steps', function (Blueprint $table) {
            $table->id();
            $table->integer('j_id');
            $table->integer('from_dep');
            $table->integer('from_user');
            $table->integer('to_dep');
            $table->integer('to_user');
            $table->integer('exec_role');
            $table->integer('res_exec_role');
            $table->smallInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_execution_steps');
    }
};
