<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Группы согласующих (approval groups).
 *
 * Идея: администратор организации (Super_Admin / IT_Admin) заранее собирает
 * готовые группы пользователей-согласующих. При создании/редактировании
 * документа такую группу можно выбрать одним кликом — и ВСЕ её участники
 * попадают в список согласующих (to_agree[]).
 *
 * Изоляция по организациям: каждая группа привязана к org_unit_id.
 * Участники хранятся по user_id (стабильно при смене места работы).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_unit_id');           // организация-владелец группы
            $table->string('name', 255);                         // название группы
            $table->unsignedBigInteger('created_by')->nullable();// кто создал (work_history id)
            $table->smallInteger('status')->default(1);          // 1 - активна, 3 - удалена
            $table->timestamps();

            $table->index(['org_unit_id', 'status'], 'ag_org_status_idx');
        });

        Schema::create('approval_group_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('user_id');               // участник (users.id)
            $table->timestamps();

            $table->unique(['group_id', 'user_id'], 'agu_group_user_uniq');
            $table->index('group_id', 'agu_group_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_group_users');
        Schema::dropIfExists('approval_groups');
    }
};
