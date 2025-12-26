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
        Schema::create('user_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz', 200);
            $table->string('name_kr', 200)->nullable();
            $table->string('name_ru', 200)->nullable();
            $table->string('name_en', 200)->nullable();
            $table->integer('order_number')->default(1);
            $table->smallInteger('status')->default(1);
            $table->json('dep_ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_positions');
    }
};
