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
        Schema::create('qr_code_keys', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_id');
            $table->string('doc_type', 25);
            $table->string('key_code', 4);
            $table->date('key_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_code_keys');
    }
};
