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
        Schema::create('hr_emp_families', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('fam_name');
            $table->integer('fam_code');
            $table->string('br_address')->nullable();
            $table->string('live_address')->nullable();
            $table->string('post_address')->nullable();
            $table->string('post_name')->nullable();
            $table->enum('gender', [1, 2])->default(1);
            $table->date('br_date');
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
        Schema::dropIfExists('hr_emp_families');
    }
};
