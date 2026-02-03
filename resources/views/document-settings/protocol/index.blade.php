@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/protocol-types-modern.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-signature"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.prt_doc_types') }}</h1>
                        <p>{{ __('menu.home') }} / {{ __('menu.prt_doc_types') }}</p>
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
                        <span>{{ __('menu.prt_doc_types') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.list') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 400px 1fr; gap: 1.5rem; align-items: start;">

                <!-- Protocol Types Tree Sidebar -->
                <div class="table-card-modern" style="position: sticky; top: 2rem;">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="git-branch"></i>
                            <span>{{ __('menu.prt_doc_types') }}</span>
                        </div>
                    </div>
                    <div class="protocol-tree-modern">
                        @foreach($parentDocTypes as $key => $item)
                            <div class="protocol-tree-item">
                                <strong>{{ $key + 1 }}. {{ $item->lang($item->id) }}</strong>
                                @if(count($item->child))
                                    @include('document-settings.protocol.manageChild', ['childs' => $item->child])
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Protocol Types Table -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="list"></i>
                            <span>{{ __('menu.prt_doc_types') }} {{ __('menu.list') }}</span>
                        </div>
                        <a href="{{ route('protocol-types.create') }}" class="btn-modern btn-primary" style="width: auto;">
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
                                <th style="text-align: left;">
                                    <i data-lucide="tag"></i>
                                    {{ __('field.title') }}
                                </th>
                                <th style="width: 120px;">
                                    <i data-lucide="code"></i>
                                    {{ __('field.doc_code') }}
                                </th>
                                <th>
                                    <i data-lucide="user-check"></i>
                                    {{ __('field.role_id') }}
                                </th>
                                <th style="width: 100px;">
                                    <i data-lucide="list-ordered"></i>
                                    {{ __('field.order_number') }}
                                </th>
                                <th style="width: 120px;">
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </th>
                                <th style="width: 120px;">
                                    <i data-lucide="settings"></i>
                                    {{ __('field.actions') }}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($models as $key => $model)
                                <tr id="rowID{{ $model->id }}" class="table-row-modern">
                                    <td class="td-number">{{ $key + 1 }}</td>
                                    <td class="td-doc-name">
                                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
{{--                                            @if($model->lang($model->parent_id))--}}
{{--                                                <div class="protocol-parent-badge">--}}
{{--                                                    <i data-lucide="corner-right-down"></i>--}}
{{--                                                    {{ $model->lang($model->parent_id) }}--}}
{{--                                                </div>--}}
{{--                                            @endif--}}
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <i data-lucide="file-signature" style="color: #794DFD; width: 16px; height: 16px;"></i>
                                                <strong>{{ $model->lang($model->id) }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="protocol-code-badge">{{ $model->doc_code }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($model->roleID->name ?? null)
                                            <span class="protocol-role-badge">{{ $model->roleID->name }}</span>
                                        @else
                                            <span class="badge-modern badge-info">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="order-number-badge">{{ $model->order_number }}</span>
                                    </td>
                                    <td class="td-status">
                                        @if($model->status == 1)
                                            <span class="status-badge-modern badge-success">
                                                <i data-lucide="check-circle"></i>
                                                {{ __('field.active') }}
                                            </span>
                                        @else
                                            <span class="status-badge-modern badge-danger">
                                                <i data-lucide="x-circle"></i>
                                                {{ __('field.inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="td-action">
                                        <a href="{{ route('protocol-types.edit', $model->doc_hash) }}" class="action-btn view-btn">
                                            <i data-lucide="square-pen"></i>
                                            {{ __('action.edit') }}
                                        </a>
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
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>O`chirish</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><i class="ik ik-message-circle"></i> Siz, tanlagan yozuvlar o`chiriladi!</p>
                    <input type="hidden" id="deleteing_id">
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ik ik-x"></i>{{ __('action.close') }}
                    </button>
                    <button type="button" class="btn btn-danger yes_delete_btn">
                        <i class="ik ik-trash-2"></i>{{ __('action.yes_delete') }}
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
            $(this).text('Deleting..');
            let id = $('#deleteing_id').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "DELETE",
                url: "/protocol/prt-doc-types/" + id,
                dataType: "json",
                success: function (response) {
                    $('#DeleteModal').modal('hide');
                    if (response.status === 200) {
                        $("#rowID" + id).addClass("table-danger").fadeOut(2000, function () {
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
