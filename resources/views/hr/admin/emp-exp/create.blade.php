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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.emp_exp') }}</h1>
                        <p>{{ __('action.add') }} {{ __('field.experience') }}</p>
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
                    <a href="#" class="breadcrumb-item">{{ __('menu.emp_exp') }}</a>
                    <svg class="breadcrumb-separator" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $user->userPersonal->l_name??'' }} {{ $user->userPersonal->f_name??'' }}
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="filters-form-modern">
                        <form method="POST" action="{{ route('exp.store') }}" class="forms-sample">
                            @csrf
                            <input type="number" name="user_id" value="{{ $id }}" hidden>

                            <!-- Is Organization Checkbox -->
                            <div class="checkbox-modern-container mb-3">
                                <label class="checkbox-modern">
                                    <input type="checkbox" name="is_org">
                                    <span class="checkbox-custom">
                                        <svg class="checkbox-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="checkbox-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 16px; height: 16px; margin-right: 0.25rem;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ __('field.is_org') }}
                                    </span>
                                </label>
                            </div>

                            <!-- Company Name and Position Row -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ __('field.exp_name') }}
                                        </label>
                                        <input type="text" name="exp_name" value="{{ old('exp_name') }}" class="form-control-modern" placeholder="{{ __('field.exp_name') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.post_name') }}
                                        </label>
                                        <input type="text" name="post_name" value="{{ old('post_name') }}" class="form-control-modern" placeholder="{{ __('field.post_name') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Dates and Description Row -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.from_date') }}
                                        </label>
                                        <input type="text" name="from_date" class="form-control-modern my-bootstrap-datepicker" autocomplete="off" placeholder="dd.mm.yyyy">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('field.to_date') }}
                                        </label>
                                        <input type="text" name="to_date" class="form-control-modern my-bootstrap-datepicker" autocomplete="off" placeholder="dd.mm.yyyy">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="filter-item">
                                        <label>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                            </svg>
                                            {{ __('field.exp_descr') }}
                                        </label>
                                        <textarea name="exp_descr" class="form-control-modern textarea-modern" rows="3" placeholder="{{ __('field.exp_descr') }}"></textarea>
                                    </div>
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

        /* Modern Checkbox */
        .checkbox-modern-container {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .checkbox-modern {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-modern input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkbox-custom {
            position: relative;
            width: 22px;
            height: 22px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            background: white;
            transition: all 0.3s ease;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkbox-icon {
            width: 14px;
            height: 14px;
            color: white;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
        }

        .checkbox-modern input[type="checkbox"]:checked ~ .checkbox-custom {
            background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
            border-color: #794DFD;
        }

        .checkbox-modern input[type="checkbox"]:checked ~ .checkbox-custom .checkbox-icon {
            opacity: 1;
            transform: scale(1);
        }

        .checkbox-modern:hover .checkbox-custom {
            border-color: #794DFD;
            box-shadow: 0 0 0 3px rgba(121, 77, 253, 0.1);
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
        }

        /* Modern Textarea */
        .textarea-modern {
            min-height: 80px;
            resize: vertical;
            font-family: inherit;
        }

        .textarea-modern::placeholder {
            color: #94a3b8;
        }

        /* Datepicker Icon Styling */
        .my-bootstrap-datepicker {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23794DFD'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 18px;
            padding-right: 2.5rem;
        }
    </style>
@endsection
