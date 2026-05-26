<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('org_storage_files', function (Blueprint $table) {
            $table->id();

            // Изоляция
            $table->unsignedBigInteger('org_unit_id');

            // null = корень
            $table->unsignedBigInteger('folder_id')->nullable();

            // Имя как видит юзер (можно переименовать без потери оригинала)
            $table->string('name', 255);

            // Оригинальное имя при загрузке (для аудита)
            $table->string('original_name', 255)->nullable();

            // Расширение (без точки), нормализованное в lowercase
            $table->string('extension', 16);

            $table->string('mime_type', 191)->nullable();

            $table->unsignedBigInteger('size_bytes')->default(0);

            // Полный путь на FTP: /storage/{org_unit_id}/{uuid}.{ext}
            // Хранится для удобства, чтобы не пересчитывать
            $table->string('ftp_path', 512);

            // UUID-имя файла на FTP (без extension, без папок)
            $table->string('ftp_hash', 64);

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_unit_id', 'folder_id'], 'osfi_org_folder_idx');
            $table->index(['org_unit_id', 'deleted_at'], 'osfi_org_deleted_idx');
            $table->index('ftp_hash', 'osfi_hash_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_storage_files');
    }
};
