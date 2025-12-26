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
        Schema::create('status_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->string('type', 20)->nullable();
            $table->integer('code');
            $table->integer('lang_id')->nullable();
            $table->integer('icon_id')->nullable();
            $table->integer('class_id')->nullable();
            $table->smallInteger('order_number')->nullable();

            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_lists');
    }
};
