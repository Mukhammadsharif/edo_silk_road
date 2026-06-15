<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Токены интеграции 1С, привязанные к организации.
 * По токену определяется org_unit_id, для которой 1С присылает данные.
 * Срок жизни — 1 час (expires_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onec_integration_tokens', function (Blueprint $t) {
            $t->id();
            $t->string('token', 80)->unique();
            $t->unsignedBigInteger('org_unit_id');         // для какой организации
            $t->unsignedBigInteger('created_by')->nullable(); // кто сгенерировал (user id)
            $t->timestamp('expires_at')->nullable();        // срок жизни
            $t->timestamp('last_used_at')->nullable();      // когда последний раз использовался
            $t->unsignedInteger('used_count')->default(0);  // сколько раз использовался
            $t->timestamps();

            $t->index('org_unit_id');
            $t->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onec_integration_tokens');
    }
};
