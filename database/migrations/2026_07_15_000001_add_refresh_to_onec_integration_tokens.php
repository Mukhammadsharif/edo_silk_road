<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refresh-токен для интеграции 1С.
 *
 * Схема: короткий access-токен (token, 60 минут) + долгий refresh-токен
 * (refresh_token, по умолчанию 30 дней). 1С сама обновляет access-токен по
 * refresh-токену — без участия человека. Refresh ротируется при каждом
 * обмене (refresh_used_at), повторное использование = признак компрометации.
 *
 * Аддитивная миграция — существующие токены не трогает.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onec_integration_tokens', function (Blueprint $t) {
            $t->string('refresh_token', 80)->nullable()->unique(); // долгоживущий ключ обмена
            $t->timestamp('refresh_expires_at')->nullable();       // до какого момента можно обновлять
            $t->timestamp('refresh_used_at')->nullable();          // когда обменян (ротация)
            $t->unsignedBigInteger('parent_id')->nullable();       // из какого токена получен (цепочка)

            $t->index('refresh_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('onec_integration_tokens', function (Blueprint $t) {
            $t->dropIndex(['refresh_expires_at']);
            $t->dropColumn(['refresh_token', 'refresh_expires_at', 'refresh_used_at', 'parent_id']);
        });
    }
};
