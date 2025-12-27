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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_uz', 100);
            $table->string('name_kr', 100)->nullable();
            $table->string('name_ru', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->string('type', 50)->nullable();
            $table->integer('unit_id')->default(0);
            $table->integer('parent_id')->default(0);
            $table->integer('order_number')->default(1);
            $table->smallInteger('level_number')->default(1);
            $table->integer('company_id')->nullable();
            $table->smallInteger('status')->default(1);
            $table->jsonb('meta_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
