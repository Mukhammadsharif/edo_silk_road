<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()
                ->constrained('organization_units')->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('color', 20)->default('#7C3AED');
            $table->text('description')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['org_id', 'user_id']);
            $table->index('is_archived');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
