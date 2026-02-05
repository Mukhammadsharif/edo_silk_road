@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/roles/roles-modern.css') }}" rel="stylesheet">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="key-round"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit') }} {{ __('menu.roles') }}</h1>
                        <p>{{ __('menu.roles') }} / {{ __('action.add-edit-permission') }}</p>
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
                    <a href="{{ route('roles.index') }}" class="breadcrumb-item">
                        <span>{{ __('menu.roles') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('action.create') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Permissions Form Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="key-round"></i>
                        <span>{{ __('action.add-edit-permission') }}</span>
                    </div>
                    <div class="permission-info">
                        <i data-lucide="shield"></i>
                        <span>{{ $role->lang($role->id) ?? $role->name }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form method="POST" action="{{ url('admin/roles/'.$role->id.'/give-permissions') }}" class="modern-form">
                        @csrf
                        @method('PUT')

                        <!-- Permissions Grid -->
                        <div class="permissions-grid">
                            @foreach($parentPermissions as $parent)
                                <div class="permission-group-card">
                                    <div class="permission-group-header">
                                        <div class="header-icon">
                                            <i data-lucide="folder"></i>
                                        </div>
                                        <h4>{{ $parent->name }}</h4>
                                    </div>

                                    <div class="permission-list">
                                        @foreach($parent->getChild as $child)
                                            <label class="permission-item">
                                                <input type="checkbox"
                                                       name="permission[]"
                                                       value="{{ $child->name }}"
                                                       id="roleID{{ $child->id }}"
                                                    {{ in_array($child->id, $rolePermissions) ? 'checked' : '' }}>
                                                <span class="checkmark"></span>
                                                <span class="permission-name">
                                                    <i data-lucide="key"></i>
                                                    {{ $child->name ?? '-' }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions" style="margin-top: 2rem;">
                            <a href="{{ route('roles.index') }}" class="btn-modern btn-secondary">
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
