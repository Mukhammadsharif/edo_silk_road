<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('org_storage_folders', function (Blueprint $table) {
            $table->id();

            // Изоляция между организациями. Эта колонка — главный фильтр.
            $table->unsignedBigInteger('org_unit_id');

            // Родитель — null для корневых папок.
            $table->unsignedBigInteger('parent_id')->nullable();

            // Имя в текущем уровне.
            $table->string('name', 255);

            // Денормализованный материализованный путь, удобно для breadcrumbs
            // и быстрых операций "найти всё в поддереве". Хранится без префикса
            // org_unit_id и без trailing slash. Пример: "/Контракты/2026/Q1".
            // Для корневой папки: "/" или просто пустая строка.
            $table->string('path', 1024)->default('/');

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_unit_id', 'parent_id'], 'osf_org_parent_idx');
            $table->index(['org_unit_id', 'deleted_at'], 'osf_org_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_storage_folders');
    }
};
