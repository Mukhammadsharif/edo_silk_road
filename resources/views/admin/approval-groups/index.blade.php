@extends('layouts.app')

@php
    $loc = session()->get('locale', app()->getLocale());
    $t = [
        'uz' => ['title'=>'Kelishuvchilar guruhlari','sub'=>'Ro\'yxat','members'=>'Ishtirokchilar','org'=>'Tashkilot','create'=>'Guruh yaratish','empty'=>'Guruhlar topilmadi','confirm'=>'Tanlangan guruh o\'chiriladi!','edit'=>'Tahrirlash','delete'=>'O\'chirish'],
        'ru' => ['title'=>'Группы согласующих','sub'=>'Список','members'=>'Участники','org'=>'Организация','create'=>'Создать группу','empty'=>'Группы не найдены','confirm'=>'Выбранная группа будет удалена!','edit'=>'Редактировать','delete'=>'Удалить'],
        'en' => ['title'=>'Approval groups','sub'=>'List','members'=>'Members','org'=>'Organization','create'=>'Create group','empty'=>'No groups found','confirm'=>'The selected group will be deleted!','edit'=>'Edit','delete'=>'Delete'],
    ];
    $L = $t[$loc] ?? $t['uz'];
@endphp

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/permissions/permissions-modern.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/select2.css') }}" rel="stylesheet">

    <div class="document-page-modern">

        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="users-round"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ $L['title'] }}</h1>
                        <p>{{ $L['title'] }} / {{ $L['sub'] }}</p>
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
                    <span class="breadcrumb-item active">{{ $L['title'] }}</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">

            {{-- Фильтры --}}
            <div class="table-card-modern" style="margin-bottom: 1.25rem;">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="search"></i>
                        <span>{{ __('action.search') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('approval-groups.index') }}" method="get" class="ag-filter-form">
                    @if($canPickOrg)
                        <div class="ag-filter-field">
                            <label class="form-label-modern"><i data-lucide="building"></i> {{ $L['org'] }}</label>
                            <select name="org" class="form-control-modern select2" onchange="this.form.submit()">
                                <option value="">—</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" {{ (int)$orgId === (int)$org->id ? 'selected':'' }}>
                                        {{ \App\Models\Admin\OrganizationUnits::lang($org->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="ag-filter-field ag-filter-grow">
                        <label class="form-label-modern"><i data-lucide="search"></i> {{ __('action.search') }}</label>
                        <input type="text" name="s_text" value="{{ $s_text ?? '' }}"
                               class="form-control-modern" placeholder="{{ __('action.search') }}...">
                    </div>

                    <div class="ag-filter-actions">
                        <button type="submit" class="btn-modern btn-primary" style="width:auto;">
                            <i data-lucide="search"></i> {{ __('action.search') }}
                        </button>
                        <a href="{{ route('approval-groups.index', ['org' => $orgId]) }}" class="btn-modern btn-secondary" style="width:auto;">
                            <i data-lucide="refresh-ccw"></i> {{ __('action.clear') }}
                        </a>
                    </div>
                </form>
            </div>

            {{-- Таблица --}}
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="list"></i>
                        <span>{{ $L['title'] }}</span>
                    </div>
                    <a href="{{ route('approval-groups.create', ['org' => $orgId]) }}" class="btn-modern btn-primary" style="width: auto;">
                        <i data-lucide="circle-plus"></i> {{ $L['create'] }}
                    </a>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th style="width: 60px;"><i data-lucide="hash"></i> #</th>
                            <th style="text-align: left;"><i data-lucide="users-round"></i> {{ __('field.name') }}</th>
                            @if($canPickOrg)
                                <th style="width: 280px; text-align:left;"><i data-lucide="building"></i> {{ $L['org'] }}</th>
                            @endif
                            <th style="width: 140px; text-align:center;"><i data-lucide="user-check"></i> {{ $L['members'] }}</th>
                            <th style="width: 140px; text-align:center;"><i data-lucide="settings"></i> {{ __('field.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $model)
                            <tr id="rowID{{ $model->id }}" class="table-row-modern">
                                <td class="td-number">{{ $i + $key + 1 }}</td>
                                <td class="td-doc-name" style="text-align:left;">
                                    <div style="display:flex; align-items:center; gap:.5rem;">
                                        <i data-lucide="users-round" style="color:#794DFD; width:16px; height:16px;"></i>
                                        <strong>{{ $model->name }}</strong>
                                    </div>
                                </td>
                                @if($canPickOrg)
                                    <td style="text-align:left;">{{ \App\Models\Admin\OrganizationUnits::lang($model->org_unit_id) }}</td>
                                @endif
                                <td style="text-align:center;">
                                    <span class="count-badge">{{ $model->members_count }}</span>
                                </td>
                                <td class="td-action">
                                    <div class="action-buttons-group">
                                        <a href="{{ route('approval-groups.edit', $model->id) }}"
                                           class="action-btn edit-btn" title="{{ $L['edit'] }}">
                                            <i data-lucide="square-pen"></i>
                                        </a>
                                        <button value="{{ $model->id }}"
                                                class="action-btn delete-btn delete_btn" title="{{ $L['delete'] }}">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canPickOrg ? 5 : 4 }}" style="text-align:center; padding:2rem; color:#9ca3af;">
                                    {{ $L['empty'] }}
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

    {{-- Кастомная модалка подтверждения удаления --}}
    <div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <div class="modal-icon-wrapper danger">
                        <i data-lucide="alert-triangle"></i>
                    </div>
                    <h4>{{ $L['delete'] }}</h4>
                    <button type="button" class="close-modal" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <p><i data-lucide="message-circle"></i> {{ $L['confirm'] }}</p>
                    <input type="hidden" id="deleteing_id">
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                        <i data-lucide="x"></i> {{ __('action.close') }}
                    </button>
                    <button type="button" class="btn-modern btn-danger yes_delete_btn">
                        <i data-lucide="trash-2"></i> {{ __('action.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .ag-filter-form { display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end; padding: 1rem 1.25rem 1.25rem; }
        .ag-filter-field { display:flex; flex-direction:column; gap:.4rem; min-width:240px; }
        .ag-filter-grow { flex:1 1 260px; }
        .ag-filter-actions { display:flex; gap:10px; align-items:center; }
        .form-label-modern { display:flex; align-items:center; gap:6px; font-weight:600; font-size:.85rem; color:#6b7280; margin:0; }
        .form-label-modern i { width:15px; height:15px; color:#794DFD; }
        @media (max-width: 640px) {
            .ag-filter-field, .ag-filter-actions { width:100%; }
            .ag-filter-actions .btn-modern { flex:1; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) lucide.createIcons();
            if (window.jQuery && $.fn.select2) $('.select2').select2({ width: '100%' });
        });

        $(document).on('click', '.delete_btn', function () {
            let model_id = $(this).val();
            $('#deleteing_id').val(model_id);
            $('#DeleteModal').modal('show');
        });

        $(document).on('click', '.yes_delete_btn', function () {
            let id = $('#deleteing_id').val();
            let $btn = $(this);
            let originalHtml = $btn.html();
            $btn.prop('disabled', true).html("<i data-lucide='loader-2' class='spinner'></i> ...");

            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $.ajax({
                type: "DELETE",
                url: "/admin/approval-groups/" + id,
                dataType: "json",
                success: function (response) {
                    $('#DeleteModal').modal('hide');
                    $btn.prop('disabled', false).html(originalHtml);
                    if (window.lucide) lucide.createIcons();

                    if (response.status === 200) {
                        $("#rowID" + id).addClass("table-danger").fadeOut(600, function () { $(this).remove(); });
                        if (typeof showSuccessMessageInTopCenter === 'function') showSuccessMessageInTopCenter('top-center');
                    } else {
                        if (typeof showErrorMessageInTopCenter === 'function') showErrorMessageInTopCenter('top-center');
                    }
                },
                error: function () {
                    $('#DeleteModal').modal('hide');
                    $btn.prop('disabled', false).html(originalHtml);
                    if (window.lucide) lucide.createIcons();
                    if (typeof showErrorMessageInTopCenter === 'function') showErrorMessageInTopCenter('top-center');
                }
            });
        });
    </script>
@endsection
