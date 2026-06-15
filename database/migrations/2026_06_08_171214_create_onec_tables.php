<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы для интеграции с 1С (employee-cabinet).
 * UUID из 1С хранится в колонке onec_id (первичный бизнес-ключ для upsert).
 */
return new class extends Migration
{
    public function up(): void
    {
        // companies
        Schema::create('onec_companies', function (Blueprint $t) {
            $t->id();
            $t->uuid('onec_id')->unique();
            $t->string('name')->nullable();
            $t->string('tax_id', 20)->nullable();
            $t->boolean('is_branch')->default(false);
            $t->uuid('parent_organization_id')->nullable();
            $t->string('sync_status', 20)->default('synced'); // synced|pending|error
            $t->json('payload')->nullable();
            $t->timestamps();
        });

        // divisions (отделы)
        Schema::create('onec_divisions', function (Blueprint $t) {
            $t->id();
            $t->uuid('onec_id')->unique();
            $t->string('code')->nullable();
            $t->string('name')->nullable();
            $t->uuid('parent_division_id')->nullable();
            $t->uuid('company_id')->nullable();
            $t->string('sync_status', 20)->default('synced');
            $t->json('payload')->nullable();
            $t->timestamps();
        });

        // positions (должности)
        Schema::create('onec_positions', function (Blueprint $t) {
            $t->id();
            $t->uuid('onec_id')->unique();
            $t->string('name')->nullable();
            $t->string('sync_status', 20)->default('synced');
            $t->json('payload')->nullable();
            $t->timestamps();
        });

        // stafflist_positions (штатные позиции)
        Schema::create('onec_stafflist_positions', function (Blueprint $t) {
            $t->id();
            $t->uuid('onec_id')->unique();
            $t->string('name')->nullable();
            $t->uuid('company_id')->nullable();
            $t->uuid('division_id')->nullable();
            $t->uuid('position_id')->nullable();
            $t->string('sync_status', 20)->default('synced');
            $t->json('payload')->nullable();
            $t->timestamps();
        });

        // persons (физлица)
        Schema::create('onec_persons', function (Blueprint $t) {
            $t->id();
            $t->uuid('onec_id')->unique();
            $t->string('last_name')->nullable();
            $t->string('first_name')->nullable();
            $t->string('patronymic')->nullable();
            $t->string('gender', 10)->nullable();
            $t->date('birth_date')->nullable();
            $t->string('tax_id', 20)->nullable();
            $t->string('insurance_number', 30)->nullable();   // ИНПС
            $t->json('identity_document')->nullable();          // паспорт
            $t->string('birth_place')->nullable();
            $t->string('mobile_phone_number', 30)->nullable();
            $t->string('email')->nullable();
            $t->text('registration_address')->nullable();
            $t->text('residential_address')->nullable();
            $t->string('picture')->nullable();
            $t->string('citizenship', 5)->nullable();
            $t->json('salary_card_numbers')->nullable();
            $t->string('sync_status', 20)->default('synced');
            $t->json('payload')->nullable();
            $t->timestamps();

            $t->index('tax_id');
        });

        // employees (трудоустройства)
        Schema::create('onec_employees', function (Blueprint $t) {
            $t->id();
            $t->uuid('onec_id')->unique();
            $t->uuid('person_id')->nullable();
            $t->uuid('company_id')->nullable();
            $t->date('employment_date')->nullable();
            $t->string('employment_type', 30)->nullable();     // mainWork и т.д.
            $t->uuid('stafflist_position_id')->nullable();
            $t->uuid('division_id')->nullable();
            $t->uuid('position_id')->nullable();
            $t->date('transfer_date')->nullable();
            $t->decimal('wage_rate_value', 15, 2)->nullable();
            $t->string('wage_rate_presentation')->nullable();
            $t->uuid('work_schedule_id')->nullable();
            $t->boolean('dismissed')->default(false);
            $t->string('employee_number', 50)->nullable();
            $t->string('sync_status', 20)->default('synced');
            $t->json('payload')->nullable();
            $t->timestamps();

            $t->index('person_id');
            $t->index('company_id');
        });

        // vocations (отпуска) — с компонентами в JSON
        Schema::create('onec_vocations', function (Blueprint $t) {
            $t->id();
            $t->uuid('onec_id')->unique();
            $t->uuid('person_id')->nullable();
            $t->json('components')->nullable();                 // массив компонентов отпуска
            $t->string('sync_status', 20)->default('synced');
            $t->json('payload')->nullable();
            $t->timestamps();

            $t->index('person_id');
        });

        // users (учётки сотрудников из 1С)
        Schema::create('onec_users', function (Blueprint $t) {
            $t->id();
            $t->uuid('person_id')->unique();
            $t->string('username')->nullable();
            $t->string('sync_status', 20)->default('synced');
            $t->json('payload')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
                     'onec_users', 'onec_vocations', 'onec_employees', 'onec_persons',
                     'onec_stafflist_positions', 'onec_positions', 'onec_divisions', 'onec_companies',
                 ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
