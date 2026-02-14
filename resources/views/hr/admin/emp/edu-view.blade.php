@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="graduation-cap"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('menu.emp_edu') }}</p>
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
                    <h3><i data-lucide="book-open"></i> {{ __('menu.emp_edu') }}</h3>
                </div>
                <div class="hr-card-body">
                    @if($user->status == 1)
                        <a href="{{ route('education.show', $user->id) }}" class="create-btn-modern">
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
                                    <th><i data-lucide="school"></i> {{ __('field.edu_name') }}</th>
                                    <th><i data-lucide="library"></i> {{ __('field.fac_name') }}</th>
                                    <th><i data-lucide="book"></i> {{ __('field.spec_name') }}</th>
                                    <th><i data-lucide="award"></i> {{ __('field.dip_name') }}</th>
                                    <th><i data-lucide="calendar"></i> {{ __('field.from_date') }}</th>
                                    <th><i data-lucide="calendar-x"></i> {{ __('field.to_date') }}</th>
                                    <th><i data-lucide="settings"></i> {{ __('field.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $model)
                                    <tr class="table-row-modern">
                                        <td class="td-number">{{ $key+1 }}</td>
                                        <td>{{ $model->edu_name }}</td>
                                        <td>{{ $model->fac_name }}</td>
                                        <td>{{ $model->spec_name }}</td>
                                        <td>{{ $model->dip_name }}</td>
                                        <td class="td-date">{{ date('Y', strtotime($model->from_date)) }}</td>
                                        <td class="td-date">
                                            @if($model->to_date)
                                                {{ date('Y', strtotime($model->to_date)) }}
                                            @endif
                                        </td>
                                        <td class="td-action">
                                            @if($user->status == 1)
                                                <a href="{{ route('education.edit', $model->id) }}"
                                                   class="action-btn view-btn">
                                                    <i data-lucide="edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="empty-state">
                                            <div class="empty-state-content">
                                                <i data-lucide="book-open"></i>
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
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
