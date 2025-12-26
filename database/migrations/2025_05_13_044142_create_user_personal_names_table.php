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
        Schema::create('user_personal_names', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('f_name_kr', 50)->nullable();
            $table->string('l_name_kr', 50)->nullable();
            $table->string('m_name_kr', 50)->nullable();
            $table->string('f_name_ru', 50)->nullable();
            $table->string('l_name_ru', 50)->nullable();
            $table->string('m_name_ru', 50)->nullable();
            $table->string('f_name_en', 50)->nullable();
            $table->string('l_name_en', 50)->nullable();
            $table->string('m_name_en', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_personal_names');
    }
};
