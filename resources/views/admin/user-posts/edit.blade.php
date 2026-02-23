@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/user-positions/user-positions-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

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
                        <h1>{{ __('action.edit') }} {{ __('menu.user_positions') }}</h1>
                        <p>{{ __('menu.user_positions') }} / {{ __('action.edit') }}</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="#" class="breadcrumb-item">
                        <span>{{ __('menu.user_positions') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Edit Form Card -->
            <div class="position-form-section" style="max-width: 800px; margin: 0 auto;">
                <div class="position-form-header">
                    <i data-lucide="briefcase"></i>
                    <h3>{{ __('action.edit') }} {{ __('menu.user_positions') }}</h3>
                </div>

                <div class="position-form-body">
                    <form method="POST" action="{{ route('user-posts.update', $model->id) }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <!-- Department Selection -->
                        <div class="position-form-group">
                            <label>
                                <i data-lucide="building"></i>
                                {{ __('field.department') }}
                                <span class="text-red">*</span>
                            </label>
                            <div class="multi-select-wrapper">
                                <select name="dep_ids[]" class="form-control-modern select2" multiple="multiple">
                                    @foreach($selected_deps as $key => $item)
                                        <option value="{{ $item->id }}" selected>
                                            {{ $item->name_uz ?? '' }}
                                        </option>
                                    @endforeach
                                    @foreach($un_selected_deps as $key => $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->name_uz ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {!! $errors->first('dep_ids', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                            <small class="position-helper-text">{{ __('field.select_departments_hint') }}</small>
                        </div>

                        <!-- Language Inputs Section -->
                        <div class="language-inputs-section">
                            <div class="language-section-title">
                                <i data-lucide="languages"></i>
                                <span>{{ __('field.multilingual_names') }}</span>
                            </div>

                            <div class="language-grid">
                                <!-- Name UZ -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-uz"></div>
                                        {{ __('field.name_uz') }}
                                        <span class="text-red">*</span>
                                    </label>
                                    <input type="text"
                                           name="name_uz"
                                           class="position-input-modern"
                                           value="{{ $model->name_uz }}"
                                           placeholder="Masalan: Bosh direktor"
                                           required>
                                    {!! $errors->first('name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.25rem;">:message</p>') !!}
                                </div>

                                <!-- Name KR -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-kr"></div>
                                        {{ __('field.name_kr') }}
                                    </label>
                                    <input type="text"
                                           name="name_kr"
                                           class="position-input-modern"
                                           value="{{ $model->name_kr }}"
                                           placeholder="Бош директор">
                                </div>

                                <!-- Name RU -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-ru"></div>
                                        {{ __('field.name_ru') }}
                                    </label>
                                    <input type="text"
                                           name="name_ru"
                                           class="position-input-modern"
                                           value="{{ $model->name_ru }}"
                                           placeholder="Генеральный директор">
                                </div>

                                <!-- Name EN -->
                                <div class="language-input-group">
                                    <label>
                                        <div class="language-flag lang-en"></div>
                                        {{ __('field.name_en') }}
                                    </label>
                                    <input type="text"
                                           name="name_en"
                                           class="position-input-modern"
                                           value="{{ $model->name_en }}"
                                           placeholder="General Director">
                                </div>
                            </div>
                        </div>

                        <!-- Order Number -->
                        <div class="position-form-group">
                            <label>
                                <i data-lucide="list-ordered"></i>
                                {{ __('field.order_number') }}
                            </label>
                            <input type="number"
                                   name="order_number"
                                   class="position-input-modern"
                                   value="{{ $model->order_number }}"
                                   min="1">
                            <small class="position-helper-text">{{ __('field.order_number_hint') }}</small>
                        </div>

                        <!-- Status Selection -->
                        <div class="position-form-group">
                            <label>
                                <i data-lucide="power"></i>
                                {{ __('field.status') }}
                            </label>
                            <div class="status-radio-group-modern">
                                <div class="status-radio-item success">
                                    <input type="radio"
                                           name="status"
                                           id="status_active"
                                           value="1"
                                        {{ ($model->status == 1) ? 'checked' : '' }}>
                                    <label for="status_active">{{ __('status.active') }}</label>
                                </div>
                                <div class="status-radio-item danger">
                                    <input type="radio"
                                           name="status"
                                           id="status_inactive"
                                           value="2"
                                        {{ ($model->status == 2) ? 'checked' : '' }}>
                                    <label for="status_inactive">{{ __('status.delete') }}</label>
                                </div>
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
                                    <strong style="color: #4338ca; font-size: 14px;">{{ __('field.position_info') }}</strong>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 13px; color: #6366f1;">
                                        {{ __('field.created_date') }}: {{ \Carbon\Carbon::parse($model->created_at)->format('d.m.Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('user-posts.index') }}" class="btn-modern btn-secondary">
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

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });
    </script>
@endsection
