@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/departments/tree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/users/show.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/organizations/edit.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="edit"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit') }} {{ __('menu.organization') }}</h1>
                        <p>{{ __('menu.organizations') }} / {{ __('action.edit') }}</p>
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
                    <a href="{{ route('organizations.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.organizations') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Main Content Grid -->
            <div class="user-profile-layout" style="grid-template-columns : 1fr !important;">

                <!-- Organizations Tree Sidebar -->
{{--                <div class="profile-sidebar">--}}
{{--                    <div class="profile-card-modern">--}}
{{--                        <div class="filters-header">--}}
{{--                            <div class="filters-title">--}}
{{--                                <i data-lucide="folder-tree"></i>--}}
{{--                                <span>{{ __('menu.organizations') }} {{ __('field.tree') }}</span>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div style="padding: 1.5rem; max-height: 600px; overflow-y: auto;">--}}
{{--                            @php $lang_key = session()->get('locale') ?? 'uz'; @endphp--}}
{{--                            <section class="list-tree">--}}
{{--                                <ul>--}}
{{--                                    @foreach($parentOrganizations as $key => $item)--}}
{{--                                        <li class="{{ $item->id == $model->id ? 'active-organization' : '' }}">--}}
{{--                                            <strong>{{ $item['name_'.$lang_key] ?? $item['name_uz'] }}</strong>--}}
{{--                                            @if(count($item->childs))--}}
{{--                                                @include('admin.organizations.manageChild', ['childs' => $item->childs])--}}
{{--                                            @endif--}}
{{--                                        </li>--}}
{{--                                    @endforeach--}}
{{--                                </ul>--}}
{{--                            </section>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

                <!-- Edit Form -->
                <div class="tabs-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="edit-3"></i>
                            <span>{{ __('field.edit_information') }}</span>
                        </div>
                    </div>

                    <div style="padding: 2rem;">
                        <form method="POST" action="{{ route('organizations.update', $model->id) }}" class="modern-form">
                            @csrf
                            @method('PUT')

                            <div class="form-grid">
                                <!-- Parent Organization -->
{{--                                <div class="form-group-modern full-width">--}}
{{--                                    <label>--}}
{{--                                        <i data-lucide="folder"></i>--}}
{{--                                        {{ __('field.parent_id') }}--}}
{{--                                    </label>--}}
{{--                                    <select name="parent_id" class="form-control-modern select2">--}}
{{--                                        <option value="0">{{ __('field.not_selected') }} ({{ __('field.root') }})</option>--}}
{{--                                        @foreach($organizations as $key => $item)--}}
{{--                                            @if($item->id != $model->id)--}}
{{--                                                <option value="{{ $item->id }}" {{ ($item->id == $model->parent_id ? "selected":"") }}>--}}
{{--                                                    {{ $key + 1 }}. {{ $item['name_'.$lang_key] ?? $item['name_uz'] }}--}}
{{--                                                </option>--}}
{{--                                            @endif--}}
{{--                                        @endforeach--}}
{{--                                    </select>--}}
{{--                                </div>--}}

                                <!-- Name UZ -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="type"></i>
                                        {{ __('field.name_uz') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="name_uz"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.name_uz') }}"
                                           value="{{ $model->name_uz }}"
                                           required>
                                </div>

                                <!-- Name KR -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="type"></i>
                                        {{ __('field.name_kr') }}
                                    </label>
                                    <input type="text"
                                           name="name_kr"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.name_kr') }}"
                                           value="{{ $model->name_kr }}">
                                </div>

                                <!-- Name RU -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="type"></i>
                                        {{ __('field.name_ru') }}
                                    </label>
                                    <input type="text"
                                           name="name_ru"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.name_ru') }}"
                                           value="{{ $model->name_ru }}">
                                </div>

                                <!-- Name EN -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="type"></i>
                                        {{ __('field.name_en') }}
                                    </label>
                                    <input type="text"
                                           name="name_en"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.name_en') }}"
                                           value="{{ $model->name_en }}">
                                </div>

                                <!-- Tax Number -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="file-text"></i>
                                        {{ __('field.tax_number') }}
                                    </label>
                                    <input type="number"
                                           name="tax_number"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.tax_number') }}"
                                           value="{{ $model->tax_number ?? '' }}">
                                </div>

                                <!-- Owner -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="user-circle"></i>
                                        {{ __('field.owner') }}
                                    </label>
                                    <select name="owner_user_id" class="form-control-modern select2">
                                        <option value="">{{ __('field.not_selected') }}</option>
                                        @foreach($users as $key => $user)
                                            <option value="{{ $user->id }}" {{ ($user->id == $model->owner_user_id ? "selected":"") }}>
                                                {{ $key + 1 }}. {{ $user->userPersonal->fullName() ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="activity"></i>
                                        {{ __('field.status') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div style="display: flex; gap: 2rem; padding: 1rem; background: #f8fafc; border-radius: 10px;">
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;">
                                            <input type="radio"
                                                   name="status"
                                                   value="1"
                                                   {{ ($model->status == 1) ? 'checked':'' }}
                                                   style="width: 18px; height: 18px; cursor: pointer;">
                                            <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #059669;">
                                                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                                                {{ __('status.active') }}
                                            </span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;">
                                            <input type="radio"
                                                   name="status"
                                                   value="2"
                                                   {{ ($model->status == 2) ? 'checked':'' }}
                                                   style="width: 18px; height: 18px; cursor: pointer;">
                                            <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #dc2626;">
                                                <i data-lucide="x-circle" style="width: 18px; height: 18px;"></i>
                                                {{ __('status.delete') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <a href="{{ route('organizations.index') }}" class="btn-modern btn-secondary">
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
