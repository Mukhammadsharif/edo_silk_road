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
        Schema::create('hr_emp_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('w_user_id')->nullable();
            $table->string('ord_name', 200);
            $table->string('ord_num', 20)->nullable();
            $table->integer('ord_code');
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
        Schema::dropIfExists('hr_emp_orders');
    }
};
