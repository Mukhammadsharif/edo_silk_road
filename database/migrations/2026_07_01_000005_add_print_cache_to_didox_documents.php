<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Кэш печатной формы Didox-документа.
 * Заполняется при первой успешной загрузке из Didox; используется, когда
 * ни у кого в организации нет живого токена (внутреннее согласование
 * не должно зависеть от Didox-сессий). Аддитивная миграция.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('didox_documents', function (Blueprint $t) {
            $t->longText('print_html')->nullable();      // HTML печатной формы из Didox
            $t->timestamp('print_cached_at')->nullable(); // когда закэширована
        });
    }

    public function down(): void
    {
        Schema::table('didox_documents', function (Blueprint $t) {
            $t->dropColumn(['print_html', 'print_cached_at']);
        });
    }
};
