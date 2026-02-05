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
                        <i data-lucide="shield"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.role_menus') }}</h1>
                        <p>Rollar / Menyular</p>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('route.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.role_menus') }}</span>
                </nav>
            </div>
        </div>

        <div class="content-wrapper-modern">

            <!-- Filters Card -->
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

                <form action="{{ url('/admin/role-menus-search') }}" method="POST" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('input.search') }}
                            </label>
                            <input type="text"
                                   name="text"
                                   value="{{ $text ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('input.search') }}">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="shield"></i>
                                {{ __('field.role_name') }}
                            </label>
                            <select name="user_role" class="form-control-modern select2">
                                <option value="">{{ __('option.select_user_role') }}</option>
                                @forelse($roles as $key => $item)
                                    <option value="{{ $item->id }}" {{ ($item->id == $role_id ? "selected":"") }}>
                                        {{ $key+1 }}. {{ $item->name }}
                                    </option>
                                @empty
                                    <option value="">{{ __('option.not_found') }}!</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('role-menus.index') }}" class="btn-modern btn-secondary">
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
                        <span>{{ __('menu.role_menus') }} {{ __('menu.list') }}</span>
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
                                #
                            </th>
                            <th>
                                <i data-lucide="shield"></i>
                                {{ __('field.role_name') }}
                            </th>
                            <th>
                                <i data-lucide="menu"></i>
                                {{ __('field.menu_name') }}
                            </th>
                            <th style="width: 140px;">
                                <i data-lucide="layout"></i>
                                {{ __('field.menu_type') }}
                            </th>
                            <th style="width: 100px;">
                                <i data-lucide="list-ordered"></i>
                                {{ __('field.order') }}
                            </th>
                            <th>
                                <i data-lucide="folder"></i>
                                {{ __('field.parent_id') }}
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </th>
                            <th class="text-center" style="width: 100px;">
                                <button type="button" class="btn btn-default btn-sm checkbox-toggle"
                                        style="background: transparent; border: none;">
                                    <i class="ik ik-check-square text-white"></i>
                                </button>
                            </th>
                            <th style="width: 120px;">
                                <i data-lucide="settings"></i>
                                {{ __('action.actions') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($models as $key => $model)
                            <tr id="tr_{{$model->id}}" class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="shield" style="width: 16px; height: 16px; color: #794DFD;"></i>
                                        <strong>{{ $model->roleName->name ?? '-' }}</strong>
                                    </div>
                                </td>
                                <td class="td-doc-name">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="menu" style="width: 16px; height: 16px; color: #794DFD;"></i>
                                        <strong>{{ $model->menuList->title ?? '-' }}</strong>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($model->menu_type === 1)
                                        <span class="badge-modern badge-primary">
                                            <i data-lucide="menu"
                                               style="width: 12px; height: 12px; display: inline-block; vertical-align: middle;"></i>
                                            MENYU
                                        </span>
                                    @else
                                        <span class="badge-modern badge-warning">
                                            <i data-lucide="folder"
                                               style="width: 12px; height: 12px; display: inline-block; vertical-align: middle;"></i>
                                            BO'LIM
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="order-number-badge">{{ $model->order_number }}</span>
                                </td>
                                <td>
                                    @if($model->parent && $model->parent->menuList)
                                        <span
                                            class="badge-modern badge-info">{{ $model->parent->menuList->title }}</span>
                                    @else
                                        <span class="badge-modern badge-danger">-</span>
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
                                            {{ __('field.inactive') }}
                                        </span>
                                    @endif
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

                <div style="position: absolute; top: 1rem; right: 6rem; z-index: 10;">
                    <button type="button" class="btn-modern btn-secondary deleteMessage"
                            style="width: auto; display: none; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                        <i data-lucide="trash-2"></i>
                        {{ __('action.delete') }}
                    </button>
                </div>

                @if($models->hasPages())
                    <div class="pagination-wrapper-modern">
                        {!! $models->links() !!}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <!-- Create Modal -->
    <div class="modal fade" id="createModal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%); color: white; border-radius: 16px 16px 0 0;">
                    <h4 class="modal-title">
                        <i data-lucide="plus-circle" style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
                        {{ __('action.create') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createForm" role="form">
                    @csrf
                    <div class="modal-body" style="padding: 2rem;">
                        <div class="modern-form">
                            <div class="">
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="shield"></i>
                                        {{ __('field.role_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control-modern select2" style="width: 100% !important;" name="role_id" id="create_role_id" required>
                                        <option value="">{{ __('option.select_user_role') }}</option>
                                        @foreach($roles as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group-modern mt-2">
                                    <label>
                                        <i data-lucide="menu"></i>
                                        {{ __('field.menu_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control-modern select2" style="width: 100% !important;" name="menu_id" required>
                                        <option value="">{{ __('option.select_menu_list') }}</option>
                                        @foreach($menuLists as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ $value->title }} url:{{ $value->url_path }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group-modern mt-2">
                                    <label>
                                        <i data-lucide="list-ordered"></i>
                                        {{ __('field.order_number') }}
                                    </label>
                                    <input type="number" name="order_number" class="form-control-modern" placeholder="1">
                                </div>

                                <div class="form-group-modern mt-2">
                                    <label>
                                        <i data-lucide="folder"></i>
                                        {{ __('field.parent_id') }}
                                    </label>
                                    <select class="form-control-modern select2" style="width: 100% !important;" name="parent_id" id="create_parent_id">
                                        <option value="">{{ __('field.empty') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-grid" style="margin-top: 1rem;">
                                <div class="form-radio-modern">
                                    <label>
                                        <i data-lucide="layout"></i>
                                        {{ __('field.menu_type') }}
                                    </label>
                                    <div class="radio-group-modern">
                                        <div class="radio-option-modern success">
                                            <input type="radio" name="menu_type" id="menu_type1" value="1" checked>
                                            <label for="menu_type1">MENYU</label>
                                        </div>
                                        <div class="radio-option-modern danger">
                                            <input type="radio" name="menu_type" id="menu_type2" value="2">
                                            <label for="menu_type2">BO'LIM</label>
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
                                            <input type="radio" name="status" id="status1" value="1" checked>
                                            <label for="status1">{{ __('field.active') }}</label>
                                        </div>
                                        <div class="radio-option-modern danger">
                                            <input type="radio" name="status" id="status2" value="2">
                                            <label for="status2">{{ __('field.inactive') }}</label>
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
                <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 16px 16px 0 0;">
                    <h4 class="modal-title">
                        <i data-lucide="edit" style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
                        {{ __('action.edit') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" role="form">
                    @csrf
                    <input type="hidden" id="edit_id">
                    <div class="modal-body" style="padding: 2rem;">
                        <div class="modern-form">
                            <div>
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="shield"></i>
                                        {{ __('field.role_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control-modern select2" style="width: 100% !important;" name="role_id" id="edit_role_id" required>
                                        <option value="">{{ __('option.select_user_role') }}</option>
                                        @foreach($roles as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group-modern mt-2">
                                    <label>
                                        <i data-lucide="menu"></i>
                                        {{ __('field.menu_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control-modern select2" style="width: 100% !important;" name="menu_id" id="edit_menu_id" required>
                                        <option value="">{{ __('option.select_menu_list') }}</option>
                                        @foreach($menuLists as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ $value->title }} url:{{ $value->url_path }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group-modern mt-2">
                                    <label>
                                        <i data-lucide="list-ordered"></i>
                                        {{ __('field.order_number') }}
                                    </label>
                                    <input type="number" name="order_number" id="edit_order_number" class="form-control-modern">
                                </div>

                                <div class="form-group-modern mt-2">
                                    <label>
                                        <i data-lucide="folder"></i>
                                        {{ __('field.parent_id') }}
                                    </label>
                                    <select class="form-control-modern select2" style="width: 100% !important;" name="parent_id" id="edit_parent_id">
                                        <option value="">{{ __('field.empty') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-grid" style="margin-top: 1rem;">
                                <div class="form-radio-modern">
                                    <label>
                                        <i data-lucide="layout"></i>
                                        {{ __('field.menu_type') }}
                                    </label>
                                    <div class="radio-group-modern">
                                        <div class="radio-option-modern success">
                                            <input type="radio" name="menu_type" id="edit_menu_type1" value="1">
                                            <label for="edit_menu_type1">MENYU</label>
                                        </div>
                                        <div class="radio-option-modern danger">
                                            <input type="radio" name="menu_type" id="edit_menu_type2" value="2">
                                            <label for="edit_menu_type2">BO'LIM</label>
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
                                            <label for="edit_status2">{{ __('field.inactive') }}</label>
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
                        <button type="submit" class="btn-modern btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i data-lucide="save"></i>
                            {{ __('action.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal fade" id="ConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title">
                        <i data-lucide="alert-triangle" style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
                        {{ __('action.confirm') }}
                    </h5>
                </div>
                <div class="modal-body text-center" style="padding: 2rem;">
                    <i data-lucide="alert-circle" style="width: 64px; height: 64px; color: #ef4444; margin-bottom: 1rem;"></i>
                    <p style="font-size: 16px; color: #64748b;">{{ __('message.confirm_delete') }}</p>
                </div>
                <div class="form-actions" style="border-top: 2px solid #f1f5f9; padding: 1rem 2rem;">
                    <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                        <i data-lucide="x"></i>
                        {{ __('action.cancel') }}
                    </button>
                    <button type="button" id="yesDelete" class="btn-modern btn-primary" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
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
                    <h4 class="modal-title">{{ __('success_message') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="responseModalLabel"></p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light"
                            data-dismiss="modal">{{ __('action.close') }}</button>
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


            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            $('#edit_role_id').on("select2:select", function (e) {

                $('#edit_parent_id option[value!="0"]').remove()

                let selectedValue = $("#edit_role_id").select2("val")
                $.ajax({
                    url: '/admin/role-menus/user-role/' + selectedValue,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN},
                    dataType: 'json',
                    success: function (res) {
                        $.each(res.model, function (index, value) {
                            let newOption = new Option(value.title, value.id, false, false)
                            $('#edit_parent_id').append(newOption).trigger('change')
                        })
                    },
                    error: function (e) {
                        console.log(e)
                    }
                })
            })

            $('#create_role_id').change(function () {

                let role_id = $(this).val();

                $('#create_parent_id option[value!="0"]').remove()

                $.ajax({
                    url: '/admin/role-menus/user-role/' + role_id,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN},
                    dataType: 'json',
                    success: function (res) {
                        console.log(res)
                        $.each(res.model, function (index, value) {
                            let newOption = new Option(value.title, value.id, false, false)
                            $('#create_parent_id').append(newOption).trigger('change')
                        })
                    },
                    error: function (e) {
                        console.log(e)
                    }
                })
            })

            $('#createForm').on('submit', function (e) {

                e.preventDefault();

                $.ajax({
                    url: '/admin/role-menus',
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function () {

                    },
                    success: function (response) {
                        //console.log(response);
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

            $('.editButton').on('click', function () {

                let id = $(this).data('id')

                $.ajax({
                    url: '/admin/role-menus/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        $("#edit_menu_type1").prop("checked", false)
                        $("#edit_menu_type2").prop("checked", false)
                        $("#edit_status1").prop("checked", false)
                        $("#edit_status2").prop("checked", false)
                    },
                    success: function (res) {

                        $('#edit_id').val(res.model.id);

                        $('#edit_role_id').val(res.model.role_id).trigger('change');

                        $('#edit_menu_id').val(res.model.menu_id).trigger('change');

                        $('#edit_order_number').val(res.model.order_number);

                        $.each(res.parent, function (index, value) {
                            let newOption = new Option(value.title, value.id, false, false);
                            $('#edit_parent_id').append(newOption).trigger('change');
                        });

                        $('#edit_parent_id').val(res.model.parent_id).trigger('change');


                        if (res.model.menu_type === 1) {
                            $("#edit_menu_type1").prop("checked", true);
                        } else {
                            $("#edit_menu_type2").prop("checked", true);
                        }

                        if (res.model.status === 1) {
                            $("#edit_status1").prop("checked", true);
                        } else {
                            $("#edit_status2").prop("checked", true);
                        }
                    }
                })
            })

            $('#editForm').on('submit', function (e) {
                e.preventDefault();

                let id = $('#edit_id').val();

                $.ajax({
                    url: '/admin/role-menus/' + id,
                    method: 'PATCH',
                    data: $(this).serialize(),
                    beforeSend: function () {
                    },
                    success: function (response) {
                        //console.log(response);
                        $('#editModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            showErrorMessageInTopCenter('top-center')
                        }
                    },
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

                    url: '/admin/role-menus/' + idsArr,
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
                    $('.checkbox-toggle .ik.ik-minus-square').removeClass('ik-minus-square').addClass('ik-check-square')
                } else {
                    //Check all checkboxes
                    $(".deleteMessage").show(300);
                    $('.mailbox-messages input[type=\'checkbox\']').prop('checked', true)
                    $('.checkbox-toggle .ik.ik-check-square').removeClass('ik-check-square').addClass('ik-minus-square')
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
        })
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
    </style>

@endsection
