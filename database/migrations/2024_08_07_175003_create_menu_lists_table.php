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
        Schema::create('menu_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('url_path', 100);
            $table->integer('lang_id')->nullable();
            $table->integer('icon_id')->nullable();
            $table->integer('parent_id')->default(0);
            $table->string('code', 50)->nullable();
            $table->tinyInteger('count')->default(0);
            $table->smallInteger('status')->default(1);
            $table->timestamps();

            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_lists');
    }
};
