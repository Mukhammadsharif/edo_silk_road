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
        Schema::create('hr_emp_experiences', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('w_user_id')->nullable();
            $table->string('exp_name');
            $table->string('post_name')->nullable();
            $table->string('is_org', 2)->default('Y');
            $table->integer('dep_id')->nullable();
            $table->integer('post_id')->nullable();
            $table->integer('hr_emp_order_id')->nullable();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->text('exp_descr')->nullable();
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
        Schema::dropIfExists('hr_emp_experiences');
    }
};
