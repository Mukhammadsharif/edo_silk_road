<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Зеркало входящих документов Didox во вкладке «Внешний документооборот».
 * Мультиорганизационность: каждый документ принадлежит организации
 * (org_unit_id), пользователи видят только документы своей организации.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('didox_documents', function (Blueprint $t) {
            $t->id();

            $t->unsignedBigInteger('org_unit_id');    // владелец документа в EDOLINE (organization_units.id)
            $t->string('didox_id', 64)->unique();     // _id документа в Didox
            $t->string('doc_type', 8)->nullable();    // 002, 008, 041, 005, 006, 007 ...
            $t->string('doc_number')->nullable();
            $t->date('doc_date')->nullable();

            $t->string('partner_tin', 20)->nullable(); // ИНН контрагента (из списка v2: partnerTin)
            $t->string('seller_tin', 20)->nullable();
            $t->string('buyer_tin', 20)->nullable();
            $t->string('owner_tin', 20)->index();     // чей это входящий (наш ИНН)

            // Статус документа в Didox (числовой код из раздела «Статусы документов»)
            $t->integer('didox_status')->nullable();

            /*
             | Локальные статусы EDOLINE из легенды бизнес-процесса.
             | list_status  — «поле в списке входящих»:
             |    signed | on_internal_review | not_signed
             | approval_status — «поле в карточке» (внутреннее согласование):
             |    approved | on_review | not_sent
             */
            $t->string('list_status', 24)->default('not_signed');
            $t->string('approval_status', 24)->default('not_sent');

            $t->unsignedBigInteger('edo_document_id')->nullable(); // связь с documents, если заведён внутренний

            $t->json('raw')->nullable();              // полный JSON документа из Didox
            $t->timestamp('received_at')->nullable(); // когда поступил/подтянут

            $t->timestamps();

            $t->index(['org_unit_id', 'list_status']); // главный фильтр вкладки
            $t->index('doc_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('didox_documents');
    }
};
