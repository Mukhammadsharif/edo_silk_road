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
        Schema::create('reply_comment_types', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->integer('lang_id');
            $table->string('doc_code', 50);
            $table->smallInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reply_comment_types');
    }
};
