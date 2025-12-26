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
        Schema::create('user_personal_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->tinyInteger('is_emp')->default(1);
            $table->string('doc_pin', 14)->nullable();
            $table->string('doc_number', 9)->nullable();
            $table->tinyInteger('doc_type')->default(1);
            $table->date('birthday')->nullable();
            $table->enum('gender', [1, 2])->default(1);
            $table->string('phone', 9)->nullable();
            $table->string('f_name', 50)->nullable();
            $table->string('l_name', 50)->nullable();
            $table->string('m_name', 50)->nullable();
            $table->string('lang_code', 2)->default('uz');
            $table->tinyInteger('doc_sign_type')->default(1);
            $table->integer('sort')->default(99);
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_personal_infos');
    }
};
