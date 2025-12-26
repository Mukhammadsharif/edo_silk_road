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
        Schema::create('organization_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_uz', 100);
            $table->string('name_kr', 100)->nullable();
            $table->string('name_ru', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->smallInteger('level')->default(1);
            $table->integer('parent_id')->default(0);
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
        Schema::dropIfExists('organization_units');
    }
};
