<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Цели «фиксированного маршрута» для шагов типа журнала (v61).
 *
 * Шаг маршрута типа (journal_execution_steps) сегодня резолвится
 * в кандидатов по spatie-ролям, и автор выбирает людей вручную.
 * Запись здесь делает шаг ФИКСИРОВАННЫМ: при создании документа
 * участники подставляются автоматически (выбор скрыт), документ
 * сразу идёт по цепочке.
 *
 *   target_type = 'post' — должность (user_positions.id);
 *                 резолв: все активные сотрудники организации
 *                 с этим user_work_histories.post_id
 *   target_type = 'user' — конкретный человек (users.id!);
 *                 резолв: его активное рабочее место в организации
 *
 * Тип «с фиксированным маршрутом» = хотя бы у одного шага есть цели.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_execution_step_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('j_exec_step_id')
                ->comment('journal_execution_steps.id');
            $table->string('target_type', 10)
                ->comment("'post' — должность (user_positions.id), 'user' — человек (users.id)");
            $table->unsignedBigInteger('target_id');
            $table->timestamps();

            $table->index('j_exec_step_id');
            $table->unique(['j_exec_step_id', 'target_type', 'target_id'], 'jest_step_type_target_unique');

            $table->foreign('j_exec_step_id')
                ->references('id')->on('journal_execution_steps')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_execution_step_targets');
    }
};
