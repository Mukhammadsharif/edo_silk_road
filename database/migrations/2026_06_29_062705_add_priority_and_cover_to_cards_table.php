<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('priority', 10)->nullable()->after('label_color'); // low / medium / high
            $table->string('cover_color', 20)->nullable()->after('priority');  // цвет шапки карточки
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['priority', 'cover_color']);
        });
    }
};
