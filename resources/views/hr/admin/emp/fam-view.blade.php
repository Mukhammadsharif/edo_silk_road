@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/hr/hr-employee-common.css') }}">

@section('content')
    <div class="document-page-modern">
        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="heart"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.emp') }}</h1>
                        <p>{{ __('menu.emp_fam') }}</p>
                    </div>
                </div>
                <div class="header-right">
                    @include('hr.admin.emp.breadcrumb')
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <div class="col-md-12">
            @include('templates.alert')
        </div>

        <div class="hr-page-layout">
            <!-- Left Menu -->
            @include('hr.admin.emp.left-menu')

            <!-- Content Card -->
            <div class="hr-content-card">
                <div class="hr-card-header">
                    <h3><i data-lucide="heart"></i> {{ __('menu.emp_fam') }}</h3>
                </div>
                <div class="hr-card-body">
                    @if($user->status == 1)
                        <a href="{{ route('family.show', $user->id) }}" class="create-btn-modern">
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
                                    <th><i data-lucide="users"></i> {{ __('field.fam_code') }}</th>
                                    <th><i data-lucide="user"></i> {{ __('field.fam_name') }}</th>
                                    <th><i data-lucide="cake"></i> {{ __('field.br_date') }}</th>
                                    <th><i data-lucide="home"></i> {{ __('field.live_address') }}</th>
                                    <th><i data-lucide="mail"></i> {{ __('field.post_address') }}</th>
                                    <th><i data-lucide="settings"></i> {{ __('field.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $item)
                                    <tr class="table-row-modern">
                                        <td class="td-number">{{ $key+1 }}</td>
                                        <td>
                                                <span class="badge-modern badge-info">
                                                    {{ $item->familyName($item->fam_code) }}
                                                </span>
                                        </td>
                                        <td>{{ $item->fam_name }}</td>
                                        <td class="td-date">{{ date('d.m.Y', strtotime($item->br_date)) }}</td>
                                        <td>{{ $item->live_address }}</td>
                                        <td>{{ $item->post_address }}</td>
                                        <td class="td-action">
                                            @if($user->status == 1)
                                                <a href="{{ route('family.edit', $item->id) }}" class="action-btn view-btn">
                                                    <i data-lucide="edit"></i>
                                                    <span>{{ __('action.edit') }}</span>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            <div class="empty-state-content">
                                                <i data-lucide="heart"></i>
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
