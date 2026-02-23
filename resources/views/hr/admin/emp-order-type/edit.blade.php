@extends('layouts.app')

<link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
<link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('hr_order_type') }}</h1>
                        <p>{{ __('edit') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <nav class="breadcrumb-modern">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </a>
                    <svg class="breadcrumb-separator" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <a href="{{ route('hr-order-type.index') }}" class="breadcrumb-item">{{ __('hr_order_type') }}</a>
                    <svg class="breadcrumb-separator" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                    <span class="breadcrumb-item active">{{ __('edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Alert Messages -->
        @include('alert.alert')

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">
            <div style="max-width: 800px; margin: 0 auto;">
                <!-- Form Card -->
                <div class="filters-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            {{ __('edit') }}
                        </div>
                    </div>

                    <div class="filters-form-modern">
                        <form method="POST" action="{{ route('hr-order-type.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')

                            <!-- Hidden Order Code -->
                            <div class="form-group" hidden>
                                <input type="text" name="ord_code" class="form-control-modern" value="{{ $model->ord_code }}" readonly>
                            </div>

                            <!-- Order Name (UZ) -->
                            <div class="filter-item" style="margin-bottom: 1.5rem;">
                                <label>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                    {{ __('name') }} <span class="text-red">*</span>
                                </label>
                                <input type="text" name="ord_name" class="form-control-modern" value="{{ $model->ord_name }}" placeholder="Buyruq nomi (O'zbek tilida)">
                                {!! $errors->first('ord_name', '<p class="text-danger" style="margin-top: 0.5rem; font-size: 13px;">:message</p>') !!}
                            </div>

                            <!-- Order Name (RU) -->
                            <div class="filter-item" style="margin-bottom: 1.5rem;">
                                <label>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                    {{ __('name_ru') }} <span class="text-red">*</span>
                                </label>
                                <input type="text" name="ord_name_ru" class="form-control-modern" value="{{ $model->ord_name_ru }}" placeholder="Название приказа (на русском)">
                                {!! $errors->first('ord_name_ru', '<p class="text-danger" style="margin-top: 0.5rem; font-size: 13px;">:message</p>') !!}
                            </div>

                            <!-- Order Name (EN) -->
                            <div class="filter-item" style="margin-bottom: 1.5rem;">
                                <label>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    </svg>
                                    {{ __('name_en') }}
                                </label>
                                <input type="text" name="ord_name_en" class="form-control-modern" value="{{ $model->ord_name_en }}" placeholder="Order name (in English)">
                            </div>

                            <!-- Status Radio Buttons -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 0.75rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    {{ __('status') }}
                                </label>
                                <div style="display: flex; gap: 1.5rem;">
                                    <div class="radio radio-inline radio-success">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                            <input type="radio" name="isActive" value="A" {{ ($model->isActive == 'A') ? 'checked':'' }}>
                                            <i class="helper"></i>
                                            <span class="badge-modern badge-success">{{ __('active') }}</span>
                                        </label>
                                    </div>
                                    <div class="radio radio-inline radio-danger">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                            <input type="radio" name="isActive" value="P" {{ ($model->isActive == 'P') ? 'checked':'' }}>
                                            <i class="helper"></i>
                                            <span class="badge-modern badge-danger">{{ __('delete') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; border-top: 2px solid #f1f5f9;">
                                <a href="{{ route('hr-order-type.index') }}" class="btn-modern btn-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                    {{ __('cancel') }}
                                </a>
                                <button type="submit" class="btn-modern btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    {{ __('save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
