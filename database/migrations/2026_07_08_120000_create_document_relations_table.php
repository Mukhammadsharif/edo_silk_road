<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_relations', function (Blueprint $table) {
            $table->id();
            $table->integer('j_id');            // документ, из которого поставлена связь
            $table->integer('related_j_id');    // связанный документ
            $table->integer('created_by')->nullable();
            $table->smallInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['j_id', 'related_j_id']);
            $table->index('j_id');
            $table->index('related_j_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_relations');
    }
};
