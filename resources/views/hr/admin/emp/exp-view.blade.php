@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="briefcase"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('menu.emp_exp') }}</p>
                    </div>
                </div>
                <div class="header-right">
                    @include('hr.admin.emp.breadcrumb')
                </div>
            </div>
        </div>

        <div class="col-md-12">
            @include('templates.alert')
        </div>

        <div class="hr-page-layout">
            @include('hr.admin.emp.left-menu')

            <div class="hr-content-card">
                <div class="hr-card-header">
                    <h3><i data-lucide="briefcase"></i> {{ __('menu.emp_exp') }}</h3>
                </div>
                <div class="hr-card-body">
                    @if($user->status == 1)
                        <a href="{{ route('exp.show', $user->id) }}" class="create-btn-modern">
                            <i data-lucide="plus-circle"></i>
                            {{ __('action.create') }}
                        </a>
                    @endif

                    <div class="table-card-modern">
                        <div class="table-wrapper-modern">
                            <table class="modern-table">
                                <thead>
                                <tr>
                                    <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>
                                    <th><i data-lucide="building"></i> {{ __('field.exp_name') }}</th>
                                    <th><i data-lucide="user-check"></i> {{ __('field.post_name') }}</th>
                                    <th><i data-lucide="file-text"></i> {{ __('field.exp_descr') }}</th>
                                    <th><i data-lucide="calendar"></i> {{ __('field.from_date') }}</th>
                                    <th><i data-lucide="calendar-x"></i> {{ __('field.to_date') }}</th>
                                    <th><i data-lucide="map-pin"></i> {{ __('field.is_org') }}</th>
                                    <th><i data-lucide="settings"></i> {{ __('field.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="table-row-modern">
                                        <td class="td-number">{{ $key+1 }}</td>
                                        <td>{{ $model->exp_name }}</td>
                                        <td>{{ $model->post_name }}</td>
                                        <td>{{ $model->exp_descr }}</td>
                                        <td class="td-date">{{ date('d.m.Y', strtotime($model->from_date)) }}</td>
                                        <td class="td-date">
                                            @if($model->to_date)
                                                {{ date('d.m.Y', strtotime($model->to_date)) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($model->is_org === 'Y')
                                                <span class="badge-modern badge-success">
                                                        {{ __('field.in_org') }}
                                                    </span>
                                            @else
                                                <span class="badge-modern badge-info">
                                                        {{ __('field.other_org') }}
                                                    </span>
                                            @endif
                                        </td>
                                        <td class="td-action">
                                            @if($model->is_org === 'N' and $user->status == 1)
                                                <a href="{{ route('exp.edit', $model->id) }}" class="action-btn view-btn">
                                                    <i data-lucide="edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="empty-state">
                                            <div class="empty-state-content">
                                                <i data-lucide="briefcase"></i>
                                                <p>{{ __('field.not_found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
