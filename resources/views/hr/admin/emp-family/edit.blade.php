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
                        <p>{{ __('action.edit') }} {{ __('field.family_member') }}</p>
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
                        <form method="POST" action="{{ route('family.update', $model->id) }}" class="forms-sample">
                            @csrf
                            @method('PUT')

                            <!-- Relation Type and Name Row -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            {{ __('field.fam_code') }}
                                        </label>
                                        <select name="fam_code" class="form-control-modern select2">
                                            @foreach($familyList as $key => $item)
                                                <option value="{{ $item->code }}" @if($item->code === $model->fam_code) selected @endif>{{ $key+1 }}. {{ $item->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ __('field.fam_name') }}
                                        </label>
                                        <input type="text" name="fam_name" value="{{ $model->fam_name }}" class="form-control-modern">
                                    </div>
                                </div>
                            </div>

                            <!-- Birth Date and Birth Address Row -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.br_date') }}
                                        </label>
                                        <input type="date" name="br_date" value="{{ date('Y-m-d', strtotime($model->br_date)) }}" class="form-control-modern date-input-modern">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ __('field.br_address') }}
                                        </label>
                                        <input type="text" name="br_address" value="{{ $model->br_address }}" class="form-control-modern">
                                    </div>
                                </div>
                            </div>

                            <!-- Living Address and Postal Address Row -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                            {{ __('field.live_address') }}
                                        </label>
                                        <input type="text" name="live_address" value="{{ $model->live_address }}" class="form-control-modern">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.post_address') }}
                                        </label>
                                        <input type="text" name="post_address" value="{{ $model->post_address }}" class="form-control-modern">
                                    </div>
                                </div>
                            </div>

                            <!-- Position Name -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.post_name') }}
                                        </label>
                                        <input type="text" name="post_name" value="{{ $model->post_name }}" class="form-control-modern">
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

        /* Modern Date Input */
        .date-input-modern {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23794DFD'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 18px;
            padding-right: 2.5rem;
        }

        /* Native date input styling */
        input[type="date"].date-input-modern::-webkit-calendar-picker-indicator {
            opacity: 0;
            cursor: pointer;
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
