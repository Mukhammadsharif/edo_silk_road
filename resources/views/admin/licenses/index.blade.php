@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/tariffs/tariffs-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <div class="document-page-modern">

        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="key-round"></i>
                    </div>
                    <div class="header-text">
                        <h1>Litsenziyalar</h1>
                        <p>Litsenziya boshqaruvi / Ro'yxat</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">Litsenziyalar</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">

            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="list"></i>
                        <span>Litsenziyalar</span>
                    </div>
                    <a href="{{ route('admin.licenses.create') }}" class="btn-modern btn-primary" style="width: auto;">
                        <i data-lucide="circle-plus"></i>
                        Yangi litsenziya
                    </a>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th style="width: 60px;"><i data-lucide="hash"></i> #</th>
                            <th style="text-align: left;"><i data-lucide="key"></i> Kalit</th>
                            <th style="text-align: left;"><i data-lucide="building-2"></i> Tashkilot</th>
                            <th style="width: 110px;"><i data-lucide="users"></i> Xodimlar</th>
                            <th style="width: 120px;"><i data-lucide="file-text"></i> Hujjatlar</th>
                            <th style="width: 110px;"><i data-lucide="calendar"></i> Muddat</th>
                            <th style="width: 150px;"><i data-lucide="activity"></i> Holati</th>
                            <th style="width: 140px;"><i data-lucide="clock"></i> Sana</th>
                            <th style="width: 110px;"><i data-lucide="settings"></i> Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($licenses as $key => $lic)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $licenses->firstItem() + $key }}</td>
                                <td class="tariff-code" style="text-align: left;">{{ $lic->license_key }}</td>
                                <td style="text-align: left;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="building-2" style="color: #794DFD; width: 16px; height: 16px;"></i>
                                        {{ $lic->organization->name ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="limit-badge"><i data-lucide="users"></i> {{ $lic->max_users }}</span>
                                </td>
                                <td>
                                    <span class="limit-badge"><i data-lucide="file-text"></i> {{ $lic->max_documents }}</span>
                                </td>
                                <td class="price-cell">{{ $lic->duration_days }} <span class="currency">kun</span></td>
                                <td>
                                    @if($lic->status == 2)
                                        <span class="tariff-status-badge active"><i data-lucide="check-circle"></i> Faollashtirilgan</span>
                                    @elseif($lic->status == 1)
                                        <span class="tariff-status-badge" style="background: rgba(59,130,246,0.12); color: #2563eb;"><i data-lucide="circle-dashed"></i> Yaratilgan</span>
                                    @else
                                        <span class="tariff-status-badge inactive"><i data-lucide="x-circle"></i> Bekor qilingan</span>
                                    @endif
                                </td>
                                <td class="price-cell">{{ $lic->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="tariff-actions-cell">
                                        <a href="{{ route('admin.licenses.download', $lic->id) }}" class="tariff-edit-btn" title="Yuklab olish">
                                            <i data-lucide="download"></i>
                                        </a>
                                        @if($lic->status != 0)
                                            <button type="button" class="tariff-delete-btn revoke-btn"
                                                    data-id="{{ $lic->id }}"
                                                    data-name="{{ $lic->license_key }}" title="Bekor qilish">
                                                <i data-lucide="ban"></i>
                                            </button>
                                        @endif
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

                @if($licenses->hasPages())
                    <div class="pagination-wrapper-modern">
                        {!! $licenses->links() !!}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Revoke Confirmation Modal -->
    <div class="modal fade delete-modal-modern" id="revokeLicenseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i data-lucide="alert-octagon"></i>
                        Bekor qilishni tasdiqlash
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="font-size: 16px; color: #1e293b; margin-bottom: 0;">
                        <strong id="revokeLicenseName" style="color: #794DFD;"></strong> litsenziyasini bekor qilasizmi?
                    </p>
                    <div class="warning-box">
                        <p style="margin: 0; color: #92400e;">
                            <strong>Diqqat!</strong> Faollashtirilgan obuna ham o'chiriladi. Bu amalni ortga qaytarib bo'lmaydi.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-secondary" data-dismiss="modal" style="width: auto;">
                        <i data-lucide="x"></i>
                        Bekor qilish
                    </button>
                    <form id="revokeLicenseForm" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-modern" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; width: auto;">
                            <i data-lucide="ban"></i>
                            Ha, bekor qilish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.revoke-btn').on('click', function () {
                let id   = $(this).data('id');
                let name = $(this).data('name');
                $('#revokeLicenseName').text(name);
                $('#revokeLicenseForm').attr('action', '{{ route("admin.licenses.revoke", ":id") }}'.replace(':id', id));
                $('#revokeLicenseModal').modal('show');
            });
            if (window.lucide) lucide.createIcons();
        });
    </script>
@endsection
