<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Одноразовые токены страницы подписи мобильного приложения.
 *
 * Отдельная таблица, а не telegram_mini_app_tokens: там sign-токен пишется
 * в ту же строку, что и auth-токен, и удаляется вместе с ней при новом входе
 * в мини-апп. Мобильное приложение не должно зависеть от чужих входов.
 *
 * Токен одноразовый: used_at проставляется при завершении подписи, повторно
 * ссылка не откроется.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_sign_tokens', function (Blueprint $t) {
            $t->id();

            $t->foreignId('user_id')->constrained()->cascadeOnDelete();

            $t->string('token', 64)->unique();

            $t->unsignedBigInteger('j_id')->comment('document_journals.id');
            $t->unsignedBigInteger('doc_id')->comment('documents.id');

            // sign_resolution, from_signer_to_ag и т.п.
            $t->string('sign_type', 50);

            $t->timestamp('expires_at');
            $t->timestamp('used_at')->nullable();

            $t->timestamps();

            // Поиск действующего токена при открытии страницы
            $t->index(['token', 'expires_at']);
            $t->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_sign_tokens');
    }
};
