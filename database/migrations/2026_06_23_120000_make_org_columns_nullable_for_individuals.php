<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Физлицо (ЭЦП без поля O) регистрируется без организации.
 * Поэтому org-привязки должны допускать NULL вместо хардкода id=1 (демо).
 *
 * Требует doctrine/dbal для change() на Laravel <11.15 — на L11 change() работает нативно.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('org_unit_id')->nullable()->change();
        });

        Schema::table('user_work_histories', function (Blueprint $table) {
            $table->integer('org_id')->nullable()->change();
            $table->integer('dep_id')->nullable()->change();
            $table->integer('post_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Откат вернёт NOT NULL — перед этим строки с NULL надо заполнить, иначе упадёт.
        Schema::table('users', function (Blueprint $table) {
            $table->integer('org_unit_id')->nullable(false)->change();
        });

        Schema::table('user_work_histories', function (Blueprint $table) {
            $table->integer('org_id')->nullable(false)->change();
            $table->integer('dep_id')->nullable(false)->change();
            $table->integer('post_id')->nullable(false)->change();
        });
    }
};
