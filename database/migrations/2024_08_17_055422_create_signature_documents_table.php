<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignatureDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signature_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('doc_id');
            $table->integer('doc_type');
            $table->integer('user_id');
            $table->integer('key_id');
            $table->timestamp('signing_time');
            $table->timestamps();

            $table->index(['doc_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signature_documents');
    }
}
