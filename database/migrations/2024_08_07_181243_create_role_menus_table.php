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
        Schema::create('role_menus', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id');
            $table->integer('menu_id');
            $table->tinyInteger('menu_type')->default(1);
            $table->integer('parent_id')->default(0);
            $table->smallInteger('order_number')->default(1);
            $table->smallInteger('status')->default(1);

            $table->index(['role_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menus');
    }
};
