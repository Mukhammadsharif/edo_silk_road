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
        Schema::create('document_journals', function (Blueprint $table) {
            $table->id();
            $table->integer('doc_id');
            $table->integer('j_type');
            $table->integer('j_number');
            $table->integer('dep_j_id')->nullable();
            $table->integer('org_unit_id')->nullable();
            $table->date('j_date');
            $table->integer('confid_type');
            $table->integer('created_dep');
            $table->integer('created_user');
            $table->smallInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_journals');
    }
};
