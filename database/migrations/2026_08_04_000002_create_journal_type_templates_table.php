<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Шаблоны OnlyOffice для типов журналов (v63).
 *
 * Тип с шаблоном (например, «Заявка на закуп»): при создании документа
 * этого типа OnlyOffice открывает КОПИЮ шаблонного docx (шапка заявки,
 * пустая таблица товаров — пользователь заполняет руками), а не пустой
 * лист. Шаблон загружается в CRUD типа, хранится на FTP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_type_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_type_id')->unique();
            $table->string('file_path')->comment('путь к docx на диске ftp');
            $table->string('orig_name')->comment('исходное имя файла для отображения');
            $table->timestamps();

            $table->foreign('journal_type_id')
                ->references('id')->on('journal_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_type_templates');
    }
};
