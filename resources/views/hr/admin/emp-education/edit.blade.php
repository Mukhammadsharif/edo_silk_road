@extends('layouts.app')

<link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
<link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <div class="document-page-modern">
        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('action.edit') }} {{ __('field.education') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <div class="breadcrumb-modern">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        {{ __('menu.home') }}
                    </a>
                    <svg class="breadcrumb-separator" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="#" class="breadcrumb-item">{{ __('title.emp') }}</a>
                    <svg class="breadcrumb-separator" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div class="row">
            <div class="col-md-12">
                @include('templates.alert')
            </div>
        </div>

        <!-- Content -->
        <div class="row">
            <!-- Left Menu -->
            <div class="col-md-3">
                @include('hr.admin.emp.left-menu')
            </div>

            <!-- Main Form -->
            <div class="col-md-9">
                <div class="table-card-modern">
                    <!-- Card Header -->
                    <div class="filters-header">
                        <div class="filters-title">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('action.edit') }}
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="filters-form-modern">
                        <form method="POST" action="{{ route('education.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')

                            <!-- Educational Institution Name -->
                            <div class="filter-item mb-3">
                                <label>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    {{ __('field.edu_name') }}
                                </label>
                                <input type="text" name="edu_name" value="{{ $model->edu_name }}" class="form-control-modern">
                            </div>

                            <!-- Faculty and Specialty Row -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            {{ __('field.fac_name') }}
                                        </label>
                                        <input type="text" name="fac_name" value="{{ $model->fac_name }}" class="form-control-modern">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ __('field.spec_name') }}
                                        </label>
                                        <input type="text" name="spec_name" value="{{ $model->spec_name }}" class="form-control-modern">
                                    </div>
                                </div>
                            </div>

                            <!-- Diploma and Dates Row -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                            </svg>
                                            {{ __('field.dip_name') }}
                                        </label>
                                        <input type="text" name="dip_name" value="{{ $model->dip_name }}" class="form-control-modern">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.from_date') }}
                                        </label>
                                        <select name="from_date" class="form-control-modern select2">
                                            <option value="{{ date('Y', strtotime($model->from_date)) }}">{{ date('Y', strtotime($model->from_date)) }}</option>
                                            @for ($i = 1900; $i <= date('Y'); $i++)
                                                <option value="{{ $i }}">{{$i}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.to_date') }}
                                        </label>
                                        <select name="to_date" class="form-control-modern select2">
                                            <option value="{{ date('Y', strtotime($model->to_date)) }}">{{ date('Y', strtotime($model->to_date)) }}</option>
                                            <option value="">{{ __('field.continuing') }}</option>
                                            @for ($i = date('Y'); $i >= 1900; $i--)
                                                <option value="{{ $i }}">{{$i}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Radio Buttons - Modern Design -->
                            <div class="status-radio-modern mb-3">
                                <label class="status-radio-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('field.status') }}
                                </label>
                                <div class="radio-buttons-container">
                                    <label class="radio-button-modern radio-success">
                                        <input type="radio" name="isActive" value="A" {{ ($model->isActive == 'A') ? 'checked':'' }}>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">{{ __('status.active') }}</span>
                                    </label>
                                    <label class="radio-button-modern radio-danger">
                                        <input type="radio" name="isActive" value="P" {{ ($model->isActive == 'P') ? 'checked':'' }}>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">{{ __('status.passive') }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="filter-actions" style="margin-top: 2rem;">
                                <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}" class="btn-modern btn-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    {{ __('action.cancel') }}
                                </a>
                                <button type="submit" class="btn-modern btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                    </svg>
                                    {{ __('action.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .mb-3 {
            margin-bottom: 1.5rem;
        }

        /* Modern Radio Buttons */
        .status-radio-modern {
            padding: 1.25rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .status-radio-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 1rem;
        }

        .status-radio-label svg {
            width: 18px;
            height: 18px;
            color: #794DFD;
        }

        .radio-buttons-container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .radio-button-modern {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
        }

        .radio-button-modern:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .radio-button-modern input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .radio-custom {
            position: relative;
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 50%;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .radio-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .radio-label {
            font-size: 14px;
            font-weight: 500;
            color: #475569;
        }

        /* Success Radio - Green */
        .radio-button-modern.radio-success input[type="radio"]:checked ~ .radio-custom {
            border-color: #10b981;
            background-color: #10b981;
        }

        .radio-button-modern.radio-success input[type="radio"]:checked ~ .radio-custom::after {
            background-color: white;
            transform: translate(-50%, -50%) scale(1);
        }

        .radio-button-modern.radio-success input[type="radio"]:checked ~ .radio-label {
            color: #10b981;
            font-weight: 600;
        }

        .radio-button-modern.radio-success:has(input[type="radio"]:checked) {
            border-color: #10b981;
            background-color: #ecfdf5;
        }

        /* Danger Radio - Red */
        .radio-button-modern.radio-danger input[type="radio"]:checked ~ .radio-custom {
            border-color: #ef4444;
            background-color: #ef4444;
        }

        .radio-button-modern.radio-danger input[type="radio"]:checked ~ .radio-custom::after {
            background-color: white;
            transform: translate(-50%, -50%) scale(1);
        }

        .radio-button-modern.radio-danger input[type="radio"]:checked ~ .radio-label {
            color: #ef4444;
            font-weight: 600;
        }

        .radio-button-modern.radio-danger:has(input[type="radio"]:checked) {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
    </style>
@endsection
