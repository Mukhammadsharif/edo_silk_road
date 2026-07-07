<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Адрес проживания сотрудника в личной информации.
 * Используется как параметр шаблонов: [USER_ADDRESS].
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_personal_infos') && !Schema::hasColumn('user_personal_infos', 'address')) {
            Schema::table('user_personal_infos', function (Blueprint $table) {
                $table->string('address', 500)->nullable()->after('m_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_personal_infos') && Schema::hasColumn('user_personal_infos', 'address')) {
            Schema::table('user_personal_infos', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};
