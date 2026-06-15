<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key', 64)->unique();   // публичный ключ (для поиска/верификации)
            $table->integer('org_unit_id');                 // организация, для которой выпущена
            $table->integer('max_users');                   // лимит активных сотрудников
            $table->integer('max_documents');               // лимит документов
            $table->integer('duration_days');               // срок действия в днях
            $table->string('signature', 128);               // HMAC подпись payload (защита от подделки)
            $table->smallInteger('status')->default(1);     // 1=выпущена(не активирована), 2=активирована, 0=отозвана
            $table->unsignedBigInteger('subscription_id')->nullable(); // ссылка на subscription после активации
            $table->integer('generated_by')->nullable();    // user_id суперадмина
            $table->integer('activated_by')->nullable();     // user_id клиента
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->index('org_unit_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
