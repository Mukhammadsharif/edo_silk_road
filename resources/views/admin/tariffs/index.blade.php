@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/tariffs/tariffs-modern.css') }}" rel="stylesheet">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="package"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.tariffs') }}</h1>
                        <p>{{ __('menu.tariffs') }} / {{ __('menu.list') }}</p>
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
                    <span class="breadcrumb-item active">{{ __('menu.tariffs') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Main Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="list"></i>
                        <span>{{ __('menu.tariffs') }}</span>
                    </div>
                    <a href="{{ route('tariffs.create') }}" class="btn-modern btn-primary" style="width: auto;">
                        <i data-lucide="circle-plus"></i>
                        {{ __('action.create') }}
                    </a>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table tariff-table-modern">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                #
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="code"></i>
                                {{ __('field.code') }}
                            </th>
                            <th style="text-align: left;">
                                <i data-lucide="tag"></i>
                                {{ __('field.name') }}
                            </th>
                            <th style="width: 130px;">
                                <i data-lucide="users"></i>
                                {{ __('field.max_users') }}
                            </th>
                            <th style="width: 140px;">
                                <i data-lucide="file-text"></i>
                                {{ __('field.max_documents') }}
                            </th>
                            <th style="width: 150px;">
                                <i data-lucide="calendar"></i>
                                {{ __('field.price_monthly') }}
                            </th>
                            <th style="width: 150px;">
                                <i data-lucide="calendar-range"></i>
                                {{ __('field.price_yearly') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </th>
                            <th style="width: 100px;">
                                <i data-lucide="settings"></i>
                                {{ __('field.action') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $item)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $models->firstItem() + $key }}</td>
                                <td class="tariff-code">{{ $item->code }}</td>
                                <td class="tariff-name" style="text-align: left;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="package" style="color: #794DFD; width: 16px; height: 16px;"></i>
                                        {{ $item->name }}
                                    </div>
                                </td>
                                <td>
                                    <span class="limit-badge">
                                        <i data-lucide="users"></i>
                                        {{ $item->max_users }}
                                    </span>
                                </td>
                                <td>
                                    <span class="limit-badge">
                                        <i data-lucide="file-text"></i>
                                        {{ $item->max_documents }}
                                    </span>
                                </td>
                                <td class="price-cell">
                                    {{ number_format($item->price_monthly) }}
                                    <span class="currency">so'm</span>
                                </td>
                                <td class="price-cell">
                                    {{ number_format($item->price_yearly) }}
                                    <span class="currency">so'm</span>
                                </td>
                                <td>
                                    @if($item->status == 1)
                                        <span class="tariff-status-badge active">
                                            <i data-lucide="check-circle"></i>
                                            Faol
                                        </span>
                                    @else
                                        <span class="tariff-status-badge inactive">
                                            <i data-lucide="x-circle"></i>
                                            No faol
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tariff-actions-cell">
                                        <a href="{{ route('tariffs.edit', $item->id) }}" class="tariff-edit-btn">
                                            <i data-lucide="square-pen"></i>
                                        </a>
                                        <button type="button" class="tariff-delete-btn delete-btn"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="folder-x"></i>
                                        <p>{{ __('field.not_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($models->hasPages())
                    <div class="pagination-wrapper-modern">
                        {!! $models->links() !!}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade delete-modal-modern" id="deleteTariffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i data-lucide="alert-octagon"></i>
                        O'chirishni tasdiqlash
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="font-size: 16px; color: #1e293b; margin-bottom: 0;">
                        <strong id="deleteTariffName" style="color: #794DFD;"></strong> tarifini o'chirishni tasdiqlaysizmi?
                    </p>
                    <div class="warning-box">
                        <p style="margin: 0; color: #92400e;">
                            <strong>Diqqat!</strong> Bu amalni ortga qaytarib bo'lmaydi.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-secondary" data-dismiss="modal" style="width: auto;">
                        <i data-lucide="x"></i>
                        Bekor qilish
                    </button>
                    <form id="deleteTariffForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; width: auto;">
                            <i data-lucide="trash-2"></i>
                            Ha, o'chirish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.delete-btn').on('click', function () {
                let id   = $(this).data('id');
                let name = $(this).data('name').trim() || 'Tarif';

                $('#deleteTariffName').text(name);
                $('#deleteTariffForm').attr('action', '{{ route("tariffs.destroy", ":id") }}'.replace(':id', id));

                $('#deleteTariffModal').modal('show');
            });
        });
    </script>
@endsection
