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
        Schema::create('protocol_doc_types', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(0);
            $table->string('title', 100);
            $table->integer('lang_id')->nullable();
            $table->string('doc_code', 50)->unique();
            $table->string('doc_hash', 100)->unique();
            $table->smallInteger('order_number')->default(1);
            $table->smallInteger('level_number')->default(1);
            $table->integer('role_id');
            $table->integer('secretary_role_id');
            $table->integer('member_role_id');
            $table->smallInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_doc_types');
    }
};
