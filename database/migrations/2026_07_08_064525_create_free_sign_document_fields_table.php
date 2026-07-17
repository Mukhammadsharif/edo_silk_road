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
        Schema::create('free_sign_document_fields', function (Blueprint $table) {
            $table->id();

            // К какому документу относится метка.
            $table->unsignedBigInteger('document_id')->index();

            // Чья метка (пользователь-подписант). Nullable — общий документный
            // элемент без владельца (например, единый штамп).
            $table->unsignedBigInteger('signer_id')->nullable()->index();

            // Тип метки из реестра:
            // signature|qr|full_name|position|sign_date|sign_time|
            // cert_number|cert_info|stamp_approved|stamp_confirmed|free_text
            $table->string('type', 40)->index();

            // Страница (1-based).
            $table->unsignedSmallInteger('page');

            // Нормализованные координаты 0..1 (доли страницы), высокая точность.
            $table->decimal('x', 10, 8);
            $table->decimal('y', 10, 8);
            $table->decimal('width', 10, 8);
            $table->decimal('height', 10, 8);

            // Доп. параметры типа: font_size, align, text (для free_text) и т.п.
            $table->jsonb('params')->nullable();

            // Порядок отрисовки при перекрытии меток.
            $table->unsignedSmallInteger('z_index')->default(0);

            $table->timestamps();

            // Частый запрос при штампе: метки документа по странице.
            $table->index(['document_id', 'page']);
            // Частый запрос: метки конкретного подписанта.
            $table->index(['document_id', 'signer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_sign_document_fields');
    }
};
