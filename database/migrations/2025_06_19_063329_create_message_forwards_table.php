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
        Schema::create('message_forwards', function (Blueprint $table) {
            $table->id();
            $table->integer('message_id');
            $table->integer('from_w_user_id');
            $table->integer('to_w_user_id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('read_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_forwards');
    }
};
