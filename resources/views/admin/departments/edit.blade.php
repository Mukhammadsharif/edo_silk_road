@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/departments/tree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/users/show.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/departments/edit.css') }}">

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
                        <h1>{{ __('action.edit') }} {{ __('menu.department') }}</h1>
                        <p>{{ __('menu.departments') }} / {{ __('action.edit') }}</p>
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
                    <i class="ik ik-chevron-right breadcrumb-separator"></i>
                    <a href="{{ route('departments.index', ['id' => $model->org_unit_id]) }}" class="breadcrumb-item">
                        <span>{{ __('menu.departments') }}</span>
                    </a>
                    <i class="ik ik-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.edit') }}</span>
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
                                        <li class="{{ $item->id == $model->id ? 'active-department' : '' }}">
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

                <!-- Edit Form -->
                <div class="tabs-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="square-pen"></i>
                            <span>{{ __('field.edit_information') }}</span>
                        </div>
                    </div>

                    <div style="padding: 2rem;">
                        <form method="POST" action="{{ route('departments.update', $model->id) }}" class="modern-form">
                            @csrf
                            @method('PUT')

                            <div class="form-grid">
                                <!-- Organization -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i class="ik ik-briefcase"></i>
                                        {{ __('field.organization') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="org_unit_id" class="form-control-modern select2">
                                        <option value="">{{ __('field.not_selected') }}</option>
                                        @foreach($organizations as $key => $item)
                                            <option value="{{ $item->id }}" {{ ($item->id == $model->org_unit_id ? "selected":"") }}>
                                                {{ $key + 1 }}. {{ $item['name_'.$lang_key] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Parent Department -->
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i data-lucide="folder"></i>
                                        {{ __('field.parent_id') }}
                                    </label>
                                    <select name="parent_id" class="form-control-modern select2">
                                        <option value="0">{{ __('field.not_selected') }} ({{ __('field.root') }})</option>
                                        @foreach($departments as $key => $item)
                                            @if($item->id != $model->id)
                                                <option value="{{ $item->id }}" {{ ($item->id == $model->parent_id ? "selected":"") }}>
                                                    {{ $key + 1 }}. {{ $item['name_'.$lang_key] }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Name UZ -->
                                <div class="form-group-modern">
                                    <label>
                                        <i class="ik ik-type"></i>
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
                                        <i class="ik ik-type"></i>
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
                                        <i class="ik ik-type"></i>
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
                                        <i class="ik ik-type"></i>
                                        {{ __('field.name_en') }}
                                    </label>
                                    <input type="text"
                                           name="name_en"
                                           class="form-control-modern"
                                           placeholder="{{ __('field.name_en') }}"
                                           value="{{ $model->name_en }}">
                                </div>

                                <!-- Order Number -->
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="list-ordered"></i>
                                        {{ __('field.order_number') }}
                                    </label>
                                    <input type="number"
                                           name="order_number"
                                           class="form-control-modern"
                                           value="{{ $model->order_number }}"
                                           min="0">
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
                                                <i data-lucide="circle-check-big" style="width: 18px; height: 18px;"></i>
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
                                                <i data-lucide="ban" style="width: 18px; height: 18px;"></i>
                                                {{ __('status.delete') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <a href="{{ route('departments.index', ['id' => $model->org_unit_id]) }}" class="btn-modern btn-secondary">
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
