<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User-токены Didox, привязанные к пользователю EDOLINE и ИНН компании.
 * Токен Didox живёт 360 минут (см. config/didox.php). По истечении
 * DidoxTokenService выполняет повторный логин (ЭЦП или пароль).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('didox_tokens', function (Blueprint $t) {
            $t->id();

            $t->unsignedBigInteger('user_id');            // владелец в EDOLINE
            $t->unsignedBigInteger('org_unit_id');        // организация (organization_units.id)
            $t->string('tin', 20);                        // ИНН организации (tax_number на момент логина)
            $t->string('token', 100);                     // сам UUID-токен Didox
            $t->string('auth_method', 16)->default('ecp'); // ecp | password | company

            $t->timestamp('expires_at')->nullable();      // когда протухнет
            $t->timestamp('last_used_at')->nullable();
            $t->unsignedInteger('used_count')->default(0);

            $t->timestamps();

            $t->unique(['user_id', 'org_unit_id']);       // один активный токен на пару user+org
            $t->index('org_unit_id');
            $t->index('tin');
            $t->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('didox_tokens');
    }
};
