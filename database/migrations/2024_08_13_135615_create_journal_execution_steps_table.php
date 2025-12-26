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
        Schema::create('journal_execution_steps', function (Blueprint $table) {
            $table->id();
            $table->integer('journal_type_id');
            $table->integer('exec_role_id');
            $table->integer('exec_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_execution_steps');
    }
};
