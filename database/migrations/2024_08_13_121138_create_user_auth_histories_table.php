<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_auth_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('ip_name', 100)->nullable();
            $table->string('device_name')->nullable();
            $table->jsonb('json_data')->nullable();
            $table->timestamp('auth_date')->useCurrent();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_auth_histories');
    }
};
