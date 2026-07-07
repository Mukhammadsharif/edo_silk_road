<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Согласующие по входящему документу Didox (внутреннее согласование).
 *
 * По бизнес-процессу: «Отправить на внутреннее согласование» → «Выбор
 * согласующих» → «Процесс внутреннего согласования» → карточный статус
 * approved / on_review / not_sent.
 *
 * Лёгкая собственная таблица вместо жёсткой привязки к DocumentJournals/
 * DocumentExecutionSteps: входящий Didox-документ не проходит регистрацию
 * в журнале. Точка расширения — DidoxApprovalService::pushToExecutionEngine().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('didox_document_approvers', function (Blueprint $t) {
            $t->id();

            $t->unsignedBigInteger('didox_document_id'); // FK didox_documents.id
            $t->unsignedBigInteger('user_id');           // согласующий (users.id)
            $t->unsignedBigInteger('assigned_by');       // кто отправил на согласование

            // pending | approved | rejected
            $t->string('status', 16)->default('pending');
            $t->text('comment')->nullable();             // комментарий при решении
            $t->timestamp('decided_at')->nullable();

            $t->timestamps();

            $t->unique(['didox_document_id', 'user_id']);
            $t->index(['user_id', 'status']);            // «мои документы на согласовании»

            $t->foreign('didox_document_id')
                ->references('id')->on('didox_documents')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('didox_document_approvers');
    }
};
