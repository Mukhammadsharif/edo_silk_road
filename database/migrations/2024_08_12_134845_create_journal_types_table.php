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
        Schema::create('journal_types', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('title', 100);
            $table->integer('lang_id')->nullable();
            $table->string('doc_code', 50);
            $table->smallInteger('order_number');
            $table->smallInteger('level_number');
            $table->smallInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_types');
    }
};
