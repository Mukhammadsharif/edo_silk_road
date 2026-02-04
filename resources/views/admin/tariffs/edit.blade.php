@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/tariffs/tariffs-modern.css') }}" rel="stylesheet">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="square-pen"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit') }} {{ __('menu.tariff') }}</h1>
                        <p>{{ __('menu.tariffs') }} / {{ __('action.edit') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('tariffs.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.tariffs') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Edit Form Card -->
            <div class="table-card-modern" style="max-width: 900px; margin: 0 auto;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="package"></i>
                        <span>{{ __('action.edit') }} {{ __('menu.tariff') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    @include('templates.alert')

                    <form action="{{ route('tariffs.update', $model->id) }}" method="POST" class="modern-form">
                        @csrf
                        @method('PUT')

                        <div class="tariff-form-grid">
                            <!-- Code -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="code"></i>
                                    {{ __('field.code') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="hash"></i>
                                    <input type="text"
                                           name="code"
                                           class="tariff-input-modern"
                                           value="{{ old('code', $model->code) }}"
                                           placeholder="Masalan: BASIC"
                                           required>
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="tag"></i>
                                    {{ __('field.name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="package"></i>
                                    <input type="text"
                                           name="name"
                                           class="tariff-input-modern"
                                           value="{{ old('name', $model->name) }}"
                                           placeholder="Masalan: Boshlang'ich tarif"
                                           required>
                                </div>
                            </div>

                            <!-- Max Users -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="users"></i>
                                    {{ __('field.max_users') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="user-plus"></i>
                                    <input type="number"
                                           name="max_users"
                                           class="tariff-input-modern"
                                           value="{{ old('max_users', $model->max_users) }}"
                                           min="1"
                                           placeholder="10"
                                           required>
                                </div>
                            </div>

                            <!-- Max Documents -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="file-text"></i>
                                    {{ __('field.max_documents') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i data-lucide="files"></i>
                                    <input type="number"
                                           name="max_documents"
                                           class="tariff-input-modern"
                                           value="{{ old('max_documents', $model->max_documents) }}"
                                           min="1"
                                           placeholder="1000"
                                           required>
                                </div>
                            </div>

                            <!-- Price Monthly -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="calendar"></i>
                                    {{ __('field.price_monthly') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-currency-wrapper">
                                    <input type="number"
                                           name="price_monthly"
                                           class="tariff-input-modern"
                                           value="{{ old('price_monthly', $model->price_monthly) }}"
                                           min="0"
                                           placeholder="100000"
                                           required>
                                </div>
                            </div>

                            <!-- Price Yearly -->
                            <div class="tariff-form-group">
                                <label>
                                    <i data-lucide="calendar-range"></i>
                                    {{ __('field.price_yearly') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-currency-wrapper">
                                    <input type="number"
                                           name="price_yearly"
                                           class="tariff-input-modern"
                                           value="{{ old('price_yearly', $model->price_yearly) }}"
                                           min="0"
                                           placeholder="1000000"
                                           required>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="tariff-form-group full-width">
                                <label>
                                    <i data-lucide="power"></i>
                                    {{ __('field.status') }}
                                </label>
                                <div class="status-radio-modern">
                                    <div class="status-radio-option success">
                                        <input type="radio"
                                               name="status"
                                               id="status_active"
                                               value="1"
                                            {{ old('status', $model->status ?? 1) == 1 ? 'checked' : '' }}>
                                        <label for="status_active">{{ __('status.active') }}</label>
                                    </div>
                                    <div class="status-radio-option danger">
                                        <input type="radio"
                                               name="status"
                                               id="status_inactive"
                                               value="0"
                                            {{ old('status', $model->status ?? 1) == 0 ? 'checked' : '' }}>
                                        <label for="status_inactive">{{ __('status.inactive') }}</label>
                                    </div>
                                </div>
                                @error('status')
                                <span class="text-danger" style="font-size: 13px; margin-top: 0.5rem; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
                                    padding: 1rem 1.25rem;
                                    border-radius: 10px;
                                    border-left: 4px solid #794DFD;
                                    margin: 1.5rem 0;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i data-lucide="info" style="width: 20px; height: 20px; color: #4338ca;"></i>
                                <div>
                                    <strong style="color: #4338ca; font-size: 14px;">Tarif ma'lumotlari</strong>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 13px; color: #6366f1;">
                                        Kod: <strong>{{ $model->code }}</strong> |
                                        Yaratilgan: {{ \Carbon\Carbon::parse($model->created_at)->format('d.m.Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('tariffs.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </a>
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
