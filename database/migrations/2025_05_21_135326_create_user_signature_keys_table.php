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
        Schema::create('user_signature_keys', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('serial_number', 50);
            $table->jsonb('json_data');
            $table->tinyInteger('key_status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_signature_keys');
    }
};
