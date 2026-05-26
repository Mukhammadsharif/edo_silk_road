<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_mini_app_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('telegram_user_id');
            $table->string('auth_token', 64)->unique();     // Bearer token для API
            $table->string('sign_token', 64)->nullable();   // one-time token для mobile sign page
            $table->integer('sign_doc_id')->nullable();     // документ для подписи
            $table->string('sign_doc_type', 50)->nullable();
            $table->timestamp('auth_token_expires_at');     // 24h
            $table->timestamp('sign_token_expires_at')->nullable(); // 15min
            $table->timestamps();

            $table->index(['auth_token']);
            $table->index(['sign_token']);
            $table->index(['telegram_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_mini_app_tokens');
    }
};
