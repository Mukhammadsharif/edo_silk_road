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
        Schema::create('app_temps', function (Blueprint $table) {
            $table->id();
            $table->integer('journal_id');
            $table->string('t_name', 100);
            $table->string('t_code', 50)->unique();
            $table->text('t_text');
            $table->smallInteger('status')->default(1);
            $table->integer('t_category')->default(0);
            $table->integer('created_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_temps');
    }
};
