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
        Schema::create('department_journals', function (Blueprint $table) {
            $table->id();
            $table->integer('dep_id');
            $table->string('type', 50);
            $table->string('name_uz', 100);
            $table->string('name_ru', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->string('name_kr', 100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_journals');
    }
};
