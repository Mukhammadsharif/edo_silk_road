@extends('layouts.app')

<link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
<link href="{{ asset('css/templates/document-types/document-types-modern.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

@section('content')
    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="list"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.list') }}</h1>
                        <p>{{ __('route.role_menu') }} / {{ __('menu.list') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('route.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('route.role_menu') }}</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="search"></i>
                        <span>{{ __('action.search') }} & {{ __('field.filters') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ url('/admin/menu-list-search') }}" method="POST" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item" style="grid-column: span 2;">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('input.search') }}
                            </label>
                            <input type="text"
                                   name="input"
                                   value="{{ $input ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('input.search') }}">
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('menu-list.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i>
                                {{ __('action.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="table"></i>
                        <span>{{ __('menu.list') }}</span>
                    </div>
                    <button type="button" class="btn-modern btn-primary" data-toggle="modal" data-target="#createModal"
                            style="width: auto;">
                        <i data-lucide="circle-plus"></i>
                        {{ __('action.create') }}
                    </button>
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table mailbox-messages">

                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                            </th>
                            <th>
                                <i data-lucide="type"></i>
                                {{ __('field.title') }}
                            </th>
                            <th>
                                <i data-lucide="globe"></i>
                                {{ __('field.lang_id') }}
                            </th>
                            <th>
                                <i data-lucide="link"></i>
                                {{ __('field.url_path') }}
                            </th>
                            <th>
                                <i data-lucide="code"></i>
                                {{ __('field.code') }}
                            </th>
                            <th>
                                <i data-lucide="layout"></i>
                                {{ __('field.icon_id') }}
                            </th>
                            <th>
                                <i data-lucide="folder"></i>
                                {{ __('field.parent_id') }}
                            </th>
                            <th style="width: 100px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.count') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </th>
                            <th style="width: 150px;">
                                <i data-lucide="calendar"></i>
                                {{ __('field.date') }}
                            </th>
                            <th class="text-center" style="width: 100px; position: relative">
                                <button type="button" class="btn btn-default btn-sm checkbox-toggle"
                                        style="background: transparent; border: none;">
                                    <i class="far fa-square text-white"></i>
                                </button>

                                <div style="position: absolute; top: 1rem; right: 6rem; z-index: 10;">
                                    <button type="button" class="btn-modern btn-secondary deleteMessage"
                                            style="width: auto; display: none; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                                        <i data-lucide="trash-2"></i>
                                        {{ __('action.delete') }}
                                    </button>
                                </div>
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="settings"></i>
                                {{ __('field.edit') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($models as $key => $model)
                            <tr id="rowID{{$model->id}}" class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td class="td-doc-name">
                                    <strong>{{ $model->title }}</strong>
                                </td>
                                <td>
                                    <span class="badge-modern badge-info">{{ $model->lang($model->id) }}</span>
                                </td>
                                <td>
                                    <code
                                        style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 12px;">{{ $model->url_path }}</code>
                                </td>
                                <td>
                                    <span class="badge-modern badge-primary">{{ $model->code }}</span>
                                </td>
                                <td class="text-center">
                                    <i data-lucide="{{ $model->iconClass->name ?? 'minus' }}"
                                       style="width: 28px; height: 28px; color: #794DFD;"></i>
                                </td>
                                <td>
                                    @if($model->parent)
                                        <span class="badge-modern badge-info">{{ $model->parent->title }}</span>
                                    @else
                                        <span class="badge-modern badge-warning">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($model->count)
                                        <span class="badge-modern badge-success">{{ __('option.yes') }}</span>
                                    @else
                                        <span class="badge-modern badge-danger">{{ __('option.no') }}</span>
                                    @endif
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
                                            {{ __('status.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="datetime-cell">
                                        <span
                                            class="date">{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.Y') }}</span>
                                        <span
                                            class="time">{{ \Carbon\Carbon::parse($model->created_at)->format('H:i') }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="icheck-danger">
                                        <input type="checkbox" class="checkbox checkbox-checked" id="{{$model->id}}"
                                               data-id="{{$model->id}}">
                                        <label for="{{$model->id}}"></label>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="action-btn view-btn editButton" data-toggle="modal"
                                            data-target="#editModal" data-id="{{ $model->id }}">
                                        <i data-lucide="square-pen"></i>
                                        {{ __('action.edit') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="empty-state">
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

        <!-- Create Modal -->
        <div class="modal fade" id="createModal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius: 16px; border: none;">
                    <div class="modal-header"
                         style="background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%); color: white; border-radius: 16px 16px 0 0;">
                        <h4 class="modal-title">
                            <i data-lucide="plus-circle"
                               style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
                            {{ __('field.create') }}
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                style="color: white; opacity: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="createForm" role="form">
                        @csrf
                        <div class="modal-body" style="padding: 2rem;">
                            <div class="modern-form">
                                <div class="form-grid">
                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="type"></i>
                                            {{ __('field.title') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="title" class="form-control-modern" required>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="globe"></i>
                                            {{ __('field.lang_id') }}
                                        </label>
                                        <select class="form-control-modern select2" style="width: 100%" name="lang_id">
                                            <option value="">{{ __('field.empty') }}</option>
                                            @foreach($languages as $key => $value)
                                                <option value="{{ $value->id }}">{{ $key+1 }}
                                                    . {{ __($value->group.'.'.$value->key) }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="link"></i>
                                            {{ __('field.url_path') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="url_path" class="form-control-modern" required>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="code"></i>
                                            {{ __('field.code') }}
                                        </label>
                                        <input type="text" name="code" class="form-control-modern">
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="folder"></i>
                                            {{ __('field.core_menu') }}
                                        </label>
                                        <select class="form-control-modern select2" name="parent_id"
                                                style="width: 100% !important;">
                                            <option value="">{{ __('field.empty') }}</option>
                                            @foreach($parentMenu as $key => $value)
                                                <option value="{{ $value->id }}">{{ $key+1 }}
                                                    . {{ $value->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="layout"></i>
                                        {{ __('field.icon_id') }}
                                    </label>
                                    <input type="text" id="searchIconCreate" onkeyup="searchIconsCreate()"
                                           placeholder="Search..." class="form-control-modern mb-10">
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            @foreach($icons as $key => $item)
                                                <div class="radio radio-inline icon_div_list_create mt-1">
                                                    <label>
                                                        <input type="radio" name="icon_id"
                                                               id="create_icon_id{{ $item->id }}"
                                                               value="{{ $item->id }}">
                                                        <i class="helper"></i>
                                                        <div class="icon-list-item">
                                                            <i data-lucide="{{ $item->name }}"></i>
                                                            <div class="name">{{ $item->name }}</div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="form-grid" style="margin-top: 1rem;">
                                    <div class="form-radio-modern">
                                        <label>
                                            <i data-lucide="hash"></i>
                                            {{ __('field.count') }}
                                        </label>
                                        <div class="radio-group-modern">
                                            <div class="radio-option-modern success">
                                                <input type="radio" name="count" id="count1" value="1">
                                                <label for="count1">{{ __('option.yes') }}</label>
                                            </div>
                                            <div class="radio-option-modern danger">
                                                <input type="radio" name="count" id="count2" value="0" checked>
                                                <label for="count2">{{ __('option.no') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions" style="border-top: 2px solid #f1f5f9; padding: 1rem 2rem;">
                            <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                                <i data-lucide="x"></i>
                                {{ __('action.cancel') }}
                            </button>
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius: 16px; border: none;">
                    <div class="modal-header"
                         style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 16px 16px 0 0;">
                        <h4 class="modal-title">
                            <i data-lucide="edit"
                               style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
                            {{ __('action.edit') }}
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                style="color: white; opacity: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editForm" action="">
                        @csrf
                        <input type="text" name="id" id="edit_id" hidden>
                        <div class="modal-body" style="padding: 2rem;">
                            <div class="modern-form">
                                <div class="form-grid">
                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="type"></i>
                                            {{ __('field.title') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="title" id="edit_title" class="form-control-modern"
                                               required>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="globe"></i>
                                            {{ __('field.lang_id') }}
                                        </label>
                                        <select class="form-control-modern select2" id="edit_lang_id" name="lang_id"
                                                style="width: 100%"></select>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="link"></i>
                                            {{ __('field.url_path') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="url_path" id="edit_url_path"
                                               class="form-control-modern" required>
                                    </div>

                                    <div class="form-group-modern">
                                        <label>
                                            <i data-lucide="code"></i>
                                            {{ __('field.code') }}
                                        </label>
                                        <input type="text" name="code" id="edit_code" class="form-control-modern">
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="layout"></i>
                                        {{ __('field.icon_id') }}
                                    </label>
                                    <input type="text" id="searchIconEdit" onkeyup="searchIconsEdit()"
                                           placeholder="Search..." class="form-control-modern mb-10">
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            <div id="edit_icon_id"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="folder"></i>
                                        {{ __('field.core_menu') }}
                                    </label>
                                    <select class="form-control-modern select2" id="edit_parent_id" name="parent_id"
                                            style="width: 100% !important;">
                                        <option value="0">{{ __('field.empty') }}</option>
                                        @foreach($parentMenu as $value)
                                            <option value="{{ $value->id }}"> {{ $value->title }} </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-grid" style="margin-top: 1rem;">
                                    <div class="form-radio-modern">
                                        <label>
                                            <i data-lucide="hash"></i>
                                            {{ __('field.count') }}
                                        </label>
                                        <div class="radio-group-modern">
                                            <div class="radio-option-modern success">
                                                <input type="radio" name="count" id="edit_count1" value="1">
                                                <label for="edit_count1">{{ __('option.yes') }}</label>
                                            </div>
                                            <div class="radio-option-modern danger">
                                                <input type="radio" name="count" id="edit_count2" value="0">
                                                <label for="edit_count2">{{ __('option.no') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-radio-modern">
                                        <label>
                                            <i data-lucide="power"></i>
                                            {{ __('field.status') }}
                                        </label>
                                        <div class="radio-group-modern">
                                            <div class="radio-option-modern success">
                                                <input type="radio" name="status" id="edit_status1" value="1">
                                                <label for="edit_status1">{{ __('field.active') }}</label>
                                            </div>
                                            <div class="radio-option-modern danger">
                                                <input type="radio" name="status" id="edit_status2" value="2">
                                                <label for="edit_status2">{{ __('status.inactive') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions" style="border-top: 2px solid #f1f5f9; padding: 1rem 2rem;">
                            <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                                <i class="ik ik-x"></i> {{ __('action.cancel') }}
                            </button>
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i> {{ __('action.update') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ConfirmModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px; border: none;">
                    <div class="modal-header"
                         style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border-radius: 16px 16px 0 0;">
                        <h5 class="modal-title">
                            <i data-lucide="alert-triangle"
                               style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
                            {{ __('action.confirm') }}
                        </h5>
                    </div>
                    <div class="modal-body text-center" style="padding: 2rem;">
                        <i data-lucide="alert-circle"
                           style="width: 64px; height: 64px; color: #ef4444; margin-bottom: 1rem;"></i>
                        <p style="font-size: 16px; color: #64748b;">{{ __('message.confirm_delete') }}</p>
                    </div>
                    <div class="form-actions" style="border-top: 2px solid #f1f5f9; padding: 1rem 2rem;">
                        <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                            <i data-lucide="x"></i>
                            {{ __('action.cancel') }}
                        </button>
                        <button type="button" id="yesDelete" class="btn-modern btn-primary"
                                style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i data-lucide="trash-2"></i>
                            {{ __('action.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!--Success Modal -->
        <div class="modal fade" id="responseModal">
            <div class="modal-dialog">
                <div class="modal-content bg-success">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('field.success_message') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="responseModalLabel"></p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-outline-light"
                                data-dismiss="modal">{{ __('action.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">

        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ready(function () {
                $('.select2').select2({
                    placeholder: "{{ __('action.choose') }}",
                    allowClear: true
                });
            });

            $('#createModal').on('shown.bs.modal', function () {
                $('.select2').select2({
                    dropdownParent: $('#createModal')
                });
            });

            $('#editModal').on('shown.bs.modal', function () {
                $('.select2').select2({
                    dropdownParent: $('#editModal')
                });
            });

            $("#responseCloseButton").click(function () {
                $(this).data('clicked', true);
            })

            $('#createForm').on('submit', function (e) {

                e.preventDefault();

                $.ajax({
                    url: '/admin/menu-list',
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function () {

                    },
                    success: function (response) {
                        console.log(response);
                        $('#createModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            showErrorMessageInTopCenter('top-center')
                        }
                    }
                }).error(function (err) {
                    console.log(err)
                })

            })

            $('.editButton').unbind().on('click', function () {

                let id = $(this).data('id')

                $.ajax({
                    url: '/admin/menu-list/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        $("#edit-count1").prop("checked", false)
                        $("#edit-count2").prop("checked", false)
                        $("#editIsActive1").prop("checked", false)
                        $("#editIsActive2").prop("checked", false)
                    },
                    success: function (response) {
                        console.log(response)
                        let model = response.model;
                        let languages = response.languages;
                        let iconClassList = response.iconClassList;
                        $("#edit_lang_id").empty();

                        $('#edit_id').val(model.id)
                        $('#edit_title').val(model.title)
                        //$('#edit_lang_id').val(model.lang_id)
                        $('#edit_url_path').val(model.url_path)
                        $('#edit_code').val(model.code)
                        //$('#edit_icon_id').val(model.icon_id)
                        $('#edit_parent_id').val(model.parent_id).trigger('change');

                        $("#edit_lang_id").attr('disabled', false);
                        $.each(languages, function (key, value) {
                            let lang_json_str = JSON.stringify(value.text);
                            let lang_text = JSON.parse(lang_json_str);
                            let lang_selected = '';
                            if (model.lang_id === value.id) {
                                lang_selected = 'selected';
                            }
                            $("#edit_lang_id").append('<option value=' + value.id + ' ' + lang_selected + '>' + lang_text.uz + ' / ' + lang_text.ru + ' / ' + lang_text.en + '</option>');
                        });

                        $("#edit_icon_id").attr('disabled', false);
                        $.each(iconClassList, function (key, value) {
                            let icon_checked = '';
                            if (model.icon_id === value.id) {
                                icon_checked = 'checked';
                            }
                            $("#edit_icon_id").append(`<div class="radio radio-inline icon_div_list_edit mt-1">
                                <label>
                                <input type="radio" name="icon_id" value="` + value.id + `" ` + icon_checked + `>
                                <i class="helper"></i>
                                <div class="icon-list-item">
                                <i data-lucide="` + value.name + `"></i>
                                <div class="name">` + value.name + `</div>
                                </div>
                                </label>
                                </div>`);
                        });

                        if (model.count === 1) {
                            $("#edit_count1").prop("checked", true)
                        } else {
                            $("#edit_count2").prop("checked", true)
                        }

                        if (model.status === 1) {
                            $("#edit_status1").prop("checked", true)
                        } else {
                            $("#edit_status2").prop("checked", true)
                        }

                        if (window.lucide) lucide.createIcons();
                    },
                    complete: function (res) {
                    }

                })
            })

            $('#editForm').on('submit', function (e) {
                e.preventDefault();

                let $this = $(this);

                let id = $('#edit_id').val();

                $.ajax({
                    url: '/admin/menu-list/' + id,
                    method: 'PATCH',
                    data: $(this).serialize(),
                    beforeSend: function () {

                    },
                    success: function (response) {
                        console.log(response);
                        $('#editModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            showErrorMessageInTopCenter('top-center')
                        }
                    }
                }).error(function (err) {
                    console.log(err)
                });
            });

            $('.deleteMessage').hide();

            $('.deleteMessage').click(function () {

                $('#ConfirmModal').modal('toggle')

            })

            $('#yesDelete').on('click', function (e) {

                let idsArr = [];

                $(".checkbox:checked").each(function () {

                    idsArr.push($(this).attr('data-id'));

                });

                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                $.ajax({

                    url: '/admin/menu-list/' + idsArr,
                    type: 'DELETE',
                    data: {_token: CSRF_TOKEN},
                    dataType: 'JSON',
                    beforeSend: function () {

                    },
                    success: function (response) {
                        $('#ConfirmModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            showErrorMessageInTopCenter('top-center')
                        }
                    },
                    error: function (data) {
                        console.log(data)
                    }
                });

            });

        });

        $(function () {
            //Enable check and uncheck all functionality
            $('.checkbox-toggle').click(function () {
                var clicks = $(this).data('clicks')
                if (clicks) {
                    $(".deleteMessage").hide(200);
                    //Uncheck all checkboxes
                    $('.mailbox-messages input[type=\'checkbox\']').prop('checked', false)
                    $('.checkbox-toggle .far.fa-check-square').removeClass('fa-check-square').addClass('fa-square')
                } else {
                    //Check all checkboxes
                    $(".deleteMessage").show(300);
                    $('.mailbox-messages input[type=\'checkbox\']').prop('checked', true)
                    $('.checkbox-toggle .far.fa-square').removeClass('fa-square').addClass('fa-check-square')
                }
                $(this).data('clicks', !clicks)
            })

            $(".checkbox-checked").click(function () {
                var numberNotChecked = $('input:checkbox:checked').length;
                if (numberNotChecked > 0) {
                    $(".deleteMessage").show(300);
                } else {
                    $(".deleteMessage").hide(200);
                }
            });
        });
    </script>

    <style>
        .radio-option-modern label:has(input:checked) {
            border-color: #794DFD !important;
            background: #f5f0ff !important;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: transparent;
        }

        .view-btn {
            color: #10b981;
            background: #f0fdf4;
        }

        .view-btn:hover {
            background: #dcfce7;
            transform: translateY(-2px);
        }

        .view-btn i {
            width: 16px;
            height: 16px;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 2px solid #f1f5f9;
        }
    </style>

@endsection

