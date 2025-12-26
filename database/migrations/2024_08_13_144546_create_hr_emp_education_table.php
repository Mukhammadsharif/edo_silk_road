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
        Schema::create('hr_emp_education', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('edu_code')->default(1);
            $table->string('edu_name');
            $table->string('fac_name')->nullable();
            $table->string('spec_name')->nullable();
            $table->string('dip_name')->nullable();
            $table->string('course')->nullable();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->integer('created_id');
            $table->string('isActive', 1)->default('A');
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_emp_education');
    }
};
