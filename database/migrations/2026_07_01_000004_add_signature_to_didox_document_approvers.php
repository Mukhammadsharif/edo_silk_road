<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ЭЦП-подпись решения согласующего (внутреннее согласование Didox-документов).
 * Согласующий подписывает своё решение через E-IMZO; pkcs7 хранится как
 * юридически значимое подтверждение (по аналогии с внутренним ЭДО EDOLINE).
 * Аддитивная миграция — существующие данные не трогает.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('didox_document_approvers', function (Blueprint $t) {
            $t->longText('pkcs7')->nullable();          // подпись решения (PKCS#7, base64)
            $t->string('signature_hex', 512)->nullable(); // hex подписи из E-IMZO
            $t->string('signer_serial', 64)->nullable();  // серийный номер сертификата
            $t->timestamp('signed_at')->nullable();       // момент подписания решения
        });
    }

    public function down(): void
    {
        Schema::table('didox_document_approvers', function (Blueprint $t) {
            $t->dropColumn(['pkcs7', 'signature_hex', 'signer_serial', 'signed_at']);
        });
    }
};
