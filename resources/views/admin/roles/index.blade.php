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
                        <i data-lucide="shield"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.roles') }}</h1>
                        <p>{{ __('menu.roles') }} / {{ __('menu.list') }}</p>
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
                    <a href="#" class="breadcrumb-item">
                        <span>{{ __('menu.roles') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.list') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="shield"></i>
                        <span>{{ __('menu.roles') }} {{ __('menu.list') }}</span>
                    </div>
                    <a href="{{ route('roles.create') }}" class="btn-modern btn-primary" style="width: auto;">
                        <i data-lucide="circle-plus"></i>
                        {{ __('action.create') }}
                    </a>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.number') }}
                            </th>
                            <th>
                                <i data-lucide="code"></i>
                                {{ __('field.code') }}
                            </th>
                            <th style="text-align: left;">
                                <i data-lucide="tag"></i>
                                {{ __('field.name') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </th>
                            <th style="width: 180px;">
                                <i data-lucide="settings"></i>
                                {{ __('field.actions') }}
                            </th>
                            <th style="width: 150px;">
                                <i data-lucide="calendar"></i>
                                {{ __('field.created_at') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($models as $key => $model)
                            <tr id="rowID{{ $model->id }}" class="table-row-modern">
                                <td class="td-number">{{ $key + 1 }}</td>
                                <td>
                                    <span class="code-badge">{{ $model->name }}</span>
                                </td>
                                <td class="td-doc-name">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="shield" style="color: #794DFD; width: 16px; height: 16px;"></i>
                                        <strong>{{ $model->lang($model->id) }}</strong>
                                    </div>
                                </td>
                                <td class="td-status">
                                    @if($model->status == 1)
                                        <span class="status-badge-modern badge-success">
                                            <i data-lucide="check-circle"></i>
                                            {{ $model->isActive($model->status) }}
                                        </span>
                                    @else
                                        <span class="status-badge-modern badge-danger">
                                            <i data-lucide="x-circle"></i>
                                            {{ $model->isActive($model->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="td-action">
                                    <div class="action-buttons-group">
                                        <a href="{{ url('admin/roles/'.$model->id.'/give-permissions') }}"
                                           class="action-btn permission-btn"
                                           title="{{ __('action.add-edit-permission') }}">
                                            <i data-lucide="key"></i>
                                        </a>
                                        <a href="{{ url('admin/roles/'.$model->id.'/edit') }}"
                                           class="action-btn edit-btn"
                                           title="{{ __('action.edit') }}">
                                            <i data-lucide="square-pen"></i>
                                        </a>
                                        <button value="{{ $model->id }}"
                                                class="action-btn delete-btn delete_btn"
                                                title="{{ __('action.delete') }}">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span class="date">{{ date('d.m.Y', strtotime($model->created_at)) }}</span>
                                        <span class="time">{{ date('H:i', strtotime($model->created_at)) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
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

    {{-- Delete Modal --}}
    <div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <div class="modal-icon-wrapper danger">
                        <i data-lucide="alert-triangle"></i>
                    </div>
                    <h4>{{ __('action.delete') }}</h4>
                    <button type="button" class="close-modal" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <p><i data-lucide="message-circle"></i> Siz, tanlagan yozuvlar o`chiriladi!</p>
                    <input type="hidden" id="deleteing_id">
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                        <i data-lucide="x"></i>
                        {{ __('action.close') }}
                    </button>
                    <button type="button" class="btn-modern btn-danger yes_delete_btn">
                        <i data-lucide="trash-2"></i>
                        {{ __('action.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script>
        $(document).on('click', '.delete_btn', function () {
            let model_id = $(this).val();
            $('#DeleteModal').modal('show');
            $('#deleteing_id').val(model_id);
        });

        $(document).on('click', '.yes_delete_btn', function (e) {
            e.preventDefault();

            $(this).html("<i data-lucide='loader-2' class='spinner'></i> O`chirilmoqda...");
            let id = $('#deleteing_id').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "DELETE",
                url: "/admin/roles/" + id,
                dataType: "json",
                success: function (response) {
                    $(".yes_delete_btn").html("<i data-lucide='trash-2'></i> Ha, O`chirmoq");

                    $('#DeleteModal').modal('hide');

                    if (response.status === 200) {
                        $("#rowID"+id).addClass("table-danger").fadeOut(2000, function () {
                            this.remove();
                        });
                        showSuccessMessageInTopCenter('top-center')
                    } else {
                        showErrorMessageInTopCenter('top-center')
                    }
                }
            });
        });
    </script>
@endsection
