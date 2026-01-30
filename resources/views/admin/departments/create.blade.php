@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/departments/tree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/users/show.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/departments/create.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="plus"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.create') }} {{ __('menu.department') }}</h1>
                        <p>{{ __('menu.departments') }} / {{ __('action.create') }}</p>
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
                    <a href="{{ route('departments.index', ['id' => $organization_id]) }}" class="breadcrumb-item">
                        <span>{{ __('menu.departments') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Main Content Grid -->
            <div class="user-profile-layout">

                <!-- Department Tree Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-card-modern">
                        <div class="filters-header">
                            <div class="filters-title">
                                <i data-lucide="folder"></i>
                                <span>{{ __('menu.departments') }} {{ __('field.tree') }}</span>
                            </div>
                        </div>
                        <div style="padding: 1.5rem; max-height: 600px; overflow-y: auto;">
                            @php $lang_key = session()->get('locale') ?? 'uz'; @endphp
                            <section class="list-tree">
                                <ul>
                                    @foreach($parentDepartments as $key => $item)
                                        <li>
                                            <strong>{{ $item['name_'.$lang_key] }}</strong>
                                            @if(count($item->childs))
                                                @include('admin.departments.manageChild', ['childs' => $item->childs])
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        </div>
                    </div>
                </div>

                <!-- Create Form -->
                <div class="tabs-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="folder-plus"></i>
                            <span>{{ __('field.new_department') }}</span>
                        </div>
                    </div>

                    <div style="padding: 2rem;">
                        <form method="POST" action="{{ route('departments.store') }}" class="modern-form">
                            @csrf
                            <input name="organization_id" value="{{ $organization_id }}" hidden>

                            <div class="form-grid">
                                <!-- Parent Department -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="folder"></i>
                                        {{ __('field.parent_id') }}
                                    </label>
                                    <select name="parent_id" class="form-control-modern select2">
                                        <option value="">{{ __('action.choose') }} ({{ __('field.root') }})</option>
                                        @foreach($departments as $key => $item)
                                            <option value="{{ $item->id }}">
                                                {{ $key + 1 }}. {{ $item['name_'.$lang_key] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Name UZ -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="type-outline"></i>
                                        {{ __('field.name_uz') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="name_uz"
                                           class="form-control-modern {{ ($errors->first('name_uz') ? 'border-danger' : '') }}"
                                           placeholder="{{ __('field.enter') }} {{ __('field.name_uz') }}"
                                           value="{{ old('name_uz') }}"
                                           required>
                                    {!! $errors->first('name_uz', '<p class="text-danger" style="font-size: 13px; margin-top: 0.5rem;">:message</p>') !!}
                                </div>

                                <!-- Name KR -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="type-outline"></i>
                                        {{ __('field.name_kr') }}
                                    </label>
                                    <input type="text"
                                           name="name_kr"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.enter') }} {{ __('field.name_kr') }}"
                                           value="{{ old('name_kr') }}">
                                </div>

                                <!-- Name RU -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="type-outline"></i>
                                        {{ __('field.name_ru') }}
                                    </label>
                                    <input type="text"
                                           name="name_ru"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.enter') }} {{ __('field.name_ru') }}"
                                           value="{{ old('name_ru') }}">
                                </div>

                                <!-- Name EN -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="type-outline"></i>
                                        {{ __('field.name_en') }}
                                    </label>
                                    <input type="text"
                                           name="name_en"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.enter') }} {{ __('field.name_en') }}"
                                           value="{{ old('name_en') }}">
                                </div>

                                <!-- Order Number -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="list-ordered"></i>
                                        {{ __('field.order_number') }}
                                    </label>
                                    <input type="number"
                                           name="order_number"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.enter') }} {{ __('field.order_number') }}"
                                           value="{{ old('order_number', 0) }}"
                                           min="0">
                                    <small style="color: #64748b; font-size: 12px; margin-top: 0.25rem; display: block;">
                                        {{ __('field.order_hint') }}
                                    </small>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <a href="{{ route('departments.index', ['id' => $organization_id]) }}" class="btn-modern btn-secondary">
                                    <i class="ik ik-x"></i>
                                    {{ __('action.cancel') }}
                                </a>
                                <button type="submit" class="btn-modern btn-primary">
                                    <i class="ik ik-save"></i>
                                    {{ __('action.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });
    </script>
@endsection
