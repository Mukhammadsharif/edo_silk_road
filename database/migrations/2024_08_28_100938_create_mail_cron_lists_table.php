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
        Schema::create('mail_cron_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('to_user');
            $table->string('journal_type', 100)->nullable();
            $table->string('from_user_name', 200)->nullable();
            $table->string('document_link', 200)->nullable();
            $table->string('doc_name')->nullable();
            $table->smallInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_cron_lists');
    }
};
