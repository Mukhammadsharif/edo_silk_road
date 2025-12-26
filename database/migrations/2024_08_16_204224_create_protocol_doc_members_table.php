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
        Schema::create('protocol_doc_members', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_id');
            $table->integer('user_id');
            $table->integer('role_id');
            $table->smallInteger('order_number')->default(1);
            $table->smallInteger('status')->default(1);
            $table->timestamps();

            $table->index(['doc_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_doc_members');
    }
};
