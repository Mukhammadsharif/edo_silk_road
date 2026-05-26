<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('org_storage_folder_permissions', function (Blueprint $table) {
            $table->id();

            // На какую папку даётся / отзывается доступ
            $table->unsignedBigInteger('folder_id');

            // Кому
            $table->unsignedBigInteger('user_id');

            // Дублируем org_unit_id для быстрой фильтрации (всегда совпадает с folder.org_unit_id)
            // Также защищает: даже если кто-то подделает запрос — невозможно дать
            // юзеру одной org доступ к папке другой
            $table->unsignedBigInteger('org_unit_id');

            // 'allow' — явное разрешение (только корень дерева — потомки наследуют)
            // 'deny'  — явный запрет (override родительского allow)
            $table->enum('mode', ['allow', 'deny'])->default('allow');

            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamps();

            // Уникальность: один user не может иметь две записи на одну папку
            $table->unique(['folder_id', 'user_id'], 'osfp_folder_user_unique');

            $table->index(['user_id', 'org_unit_id'], 'osfp_user_org_idx');
            $table->index(['folder_id'], 'osfp_folder_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_storage_folder_permissions');
    }
};
