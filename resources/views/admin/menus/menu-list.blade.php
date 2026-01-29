@extends('layouts.app')

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('menu.list') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('route.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('route.role_menu') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                                <i class="ik ik-plus-circle"></i> {{ __('action.create') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <form action="{{ url('/admin/menu-list-search') }}" method="POST" role="search">
                                @csrf
                                <div class="row">
                                    <div class="col-3">
                                        <input type="text" class="form-control " name="input" value="{{ $input??''}}" placeholder={{ __('input.search') }}>
                                    </div>
                                    <button type="submit" class="btn btn-info mr-1">
                                        <i class="fa fa-search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                    </button>
                                    <a href="{{ route('menu-list.index') }}" class="btn btn-light">
                                        <i class="fa fa-refresh"></i> {{ __('action.clear') }}
                                    </a>
                                </div>
                            </form><hr>

                            <table class="table table-bordered table-striped mailbox-messages">
                                <thead>
                                <tr>
                                    <th>{{ __('#') }}</th>
                                    <th>{{ __('field.title') }}</th>
                                    <th>{{ __('field.lang_id') }}</th>
                                    <th>{{ __('field.url_path') }}</th>
                                    <th>{{ __('field.code') }}</th>
                                    <th>{{ __('field.icon_id') }}</th>
                                    <th>{{ __('field.parent_id') }}</th>
                                    <th>{{ __('field.count') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.created_date') }}</th>
                                    <th class="text-center">
                                        <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i>
                                        </button>
                                        <div class="pull-left">
                                            <button type="button" class="btn btn-outline-danger deleteMessage" data-url="">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th>{{ __('field.edit') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($models as $key => $model)
                                    <tr id="rowID{{$model->id}}">
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $model->title }}</td>
                                        <td>{{ $model->lang($model->id) }}</td>
                                        <td>{{ $model->url_path }}</td>
                                        <td>{{ $model->code }}</td>
                                        <td><i data-lucide="{{ $model->iconClass->name??'-' }}" style="font-size: xx-large"></i></td>
                                        <td>{{ $model->parent->title??'-' }}</td>
                                        <td>
                                            @if($model->count)
                                                <span class="text-primary">{{ __('option.yes') }}</span>
                                            @else
                                                <span class="text-secondary">{{ __('option.no') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $model->status }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y H:i') }}
                                        </td>
                                        <td>
                                            <div class="icheck-danger">
                                                <input type="checkbox" class="checkbox checkbox-checked" id="{{$model->id}}" data-id="{{$model->id}}">
                                                <label for="{{$model->id}}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info editButton" data-toggle="modal" data-target="#editModal" data-id="{{ $model->id }}">
                                                <i class="fa fa-pencil-alt"></i>{{ __('action.edit') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">{{ __('field.not_found') }}</td>
                                    </tr>
                                @endforelse

                                </tbody>
                            </table>

                            <span>{{ $models->links() }}</span>

                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> {{ __('field.create') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createForm" role="form">
                    @csrf
                    <div class="modal-body">
                        <table class="table">
                            <tbody>
                            <tr>
                                <td><label>{{ __('field.title') }}: <span class="text-red">*</span></label></td>
                                <td><input type="text" name="title" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.lang_id') }}:</label></td>
                                <td>
                                    <select class="form-control select2" style="width: 100%" name="lang_id">
                                        <option value=""> {{ __('field.empty') }} </option>
                                        @foreach($languages as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ __($value->group.'.'.$value->key) }} </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.url_path') }}:<span class="text-red">*</span> </label></td>
                                <td><input type="text" name="url_path" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.code') }}:</label></td>
                                <td><input type="text" name="code" class="form-control"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.core_menu') }}:</label></td>
                                <td>
                                    <select class="form-control select2" name="parent_id" style="width: 100%" required>
                                        <option value="0"> {{ __('field.empty') }} </option>
                                        @foreach($parentMenu as $value)
                                            <option value="{{ $value->id }}"> {{ $value->title }} </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.icon_id') }}:</label></td>
                                <td>
                                    <input type="text" id="searchIconCreate" onkeyup="searchIconsCreate()" placeholder="Search..." class="form-control mb-10">
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            @foreach($icons as $key => $item)
                                                <div class="radio radio-inline icon_div_list_create">
                                                    <label>
                                                        <input type="radio" name="icon_id" id="create_icon_id{{ $item->id }}" value="{{ $item->id }}">
                                                        <i class="helper"></i>
                                                        <div class="icon-list-item">
                                                            <i data-lucide="{{ $item->name }}"></i><div class="name">{{ $item->name }}</div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td colspan="2">
                                    <div class="form-group clearfix">
                                        <div class="d-inline mr-3">
                                            <label>
                                                {{ __('field.total_number') }}?
                                            </label>
                                        </div>
                                        <div class="icheck-primary d-inline mr-3">
                                            <input type="radio" name="count" value="1">
                                            <label for="create-count1" class="text-primary">
                                                {{ __('option.yes') }}
                                            </label>
                                        </div>
                                        <div class="icheck-secondary d-inline mr-3">
                                            <input type="radio" name="count" value="0" checked>
                                            <label for="create-count2" class="text-secondary">
                                                {{ __('option.no') }}
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-light" data-dismiss="modal">
                            <i class="ik ik-x"></i> {{ __('action.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save"></i> {{ __('action.save') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-pencil-alt"></i> O`zgartirish</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" action="">
                    @csrf
                    <div class="modal-body">
                        <input type="text" name="id" id="edit_id" hidden>
                        <table id="" class="table">
                            <thead class="thead-light">
                            <tbody>
                            <tr>
                                <td><label>Title: <span class="text-red">*</span> </label> </td>
                                <td><input type="text" name="title" id="edit_title" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.lang_id') }}:</label></td>
                                <td>
                                    <select class="form-control select2" id="edit_lang_id" style="width: 100%" name="lang_id">
                                        <option value=""> {{ __('field.empty') }} </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.url_path') }}: <span class="text-red">*</span> </label></td>
                                <td><input type="text" name="url_path" id="edit_url_path" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.code') }}:</label></td>
                                <td><input type="text" name="code" id="edit_code" class="form-control"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.icon_id') }}:</label></td>
                                <td>
                                    <input type="text" id="searchIconEdit" onkeyup="searchIconsEdit()" placeholder="{{ __('input.search') }}..." class="form-control mb-10">
                                    <div class="row icon-list-wrap">
                                        <div class="form-radio">
                                            <div id="edit_icon_id"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.parent_id') }}:</label></td>
                                <td>
                                    <select class="form-control select2" id="edit_parent_id" name="parent_id" style="width:100%">
                                        <option value="0">{{ __('field.empty') }}</option>
                                        @foreach($parentMenu as $value)
                                            <option value="{{ $value->id }}"> {{ $value->title }} </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td colspan="2">
                                    <div class="form-group clearfix">
                                        <div class="d-inline mr-3">
                                            <label>
                                                {{ __('field.total_number') }}?
                                            </label>
                                        </div>
                                        <div class="icheck-primary d-inline mr-3">
                                            <input type="radio" id="edit_count1" name="count" value="1" checked>
                                            <label class="text-primary">
                                                {{ __('option.yes') }}
                                            </label>
                                        </div>
                                        <div class="icheck-secondary d-inline mr-3">
                                            <input type="radio" id="edit_count2" name="count" value="0">
                                            <label class="text-secondary">
                                                {{ __('field.no') }}
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td colspan="2">
                                    <div class="form-group clearfix">
                                        <div class="d-inline mr-3">
                                            <label>
                                                {{ __('field.status') }}?
                                            </label>
                                        </div>
                                        <div class="icheck-success d-inline mr-3">
                                            <input type="radio" id="edit_status1" name="status" value="1" checked>
                                            <label class="text-success">
                                                {{ __('field.active') }}
                                            </label>
                                        </div>
                                        <div class="icheck-warning d-inline mr-3">
                                            <input type="radio" id="edit_status2" name="status" value="2">
                                            <label class="text-warning">
                                                {{ __('field.not_active') }}
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-light" data-dismiss="modal">
                            <i class="ik ik-x"></i> {{ __('action.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save"></i> {{ __('action.save') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div id="ConfirmModal" class="modal fade">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">{{ __('field.confirm_deletion') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p><i class="fa fa-trash-alt"></i> {{ __('field.The_entries_you_selected_will_be_deleted!') }}</p>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ __('action.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="yesDelete">{{ __('action.yes_delete') }}</button>
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
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">{{ __('action.cancel') }}</button>
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

            $("#responseCloseButton").click(function(){
                $(this).data('clicked', true);
            })

            $('#createForm').on('submit', function(e){

                e.preventDefault();

                $.ajax({
                    url: '/admin/menu-list',
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function(){

                    },
                    success: function (response) {
                        console.log(response);
                        $('#createModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showErrorMessageInTopCenter('top-center')
                        }
                    }
                }).error(function(err){
                    console.log(err)
                })

            })

            $('.editButton').unbind().on('click', function(){

                let id = $(this).data('id')

                $.ajax({
                    url: '/admin/menu-list/'+id+'/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function(){
                        $( "#edit-count1" ).prop( "checked", false )
                        $( "#edit-count2" ).prop( "checked", false )
                        $( "#editIsActive1" ).prop( "checked", false )
                        $( "#editIsActive2" ).prop( "checked", false )
                    },
                    success: function(response){
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
                        $.each(languages, function(key, value)
                        {
                            let lang_json_str = JSON.stringify(value.text);
                            let lang_text = JSON.parse(lang_json_str);
                            let lang_selected = '';
                            if (model.lang_id === value.id) {
                                lang_selected = 'selected';
                            }
                            $("#edit_lang_id").append('<option value=' + value.id + ' '+lang_selected+'>'+ lang_text.uz+' / '+lang_text.ru+' / '+lang_text.en + '</option>');
                        });

                        $("#edit_icon_id").attr('disabled', false);
                        $.each(iconClassList, function(key, value)
                        {
                            let icon_checked = '';
                            if (model.icon_id === value.id) {
                                icon_checked = 'checked';
                            }
                            $("#edit_icon_id").append(`<div class="radio radio-inline icon_div_list_edit">
                                <label>
                                <input type="radio" name="icon_id" value="`+value.id+`" `+icon_checked+`>
                                <i class="helper"></i>
                                <div class="icon-list-item">
                                <i data-lucide="`+value.name+`"></i>
                                <div class="name">`+value.name+`</div>
                                </div>
                                </label>
                                </div>`);
                        });

                        if(model.count === 1){
                            $( "#edit_count1" ).prop( "checked", true )
                        }
                        else{
                            $( "#edit_count2" ).prop( "checked", true )
                        }

                        if(model.status === 1){
                            $( "#edit_status1" ).prop( "checked", true )
                        }
                        else{
                            $( "#edit_status2" ).prop( "checked", true )
                        }

                        if (window.lucide) lucide.createIcons();
                    },
                    complete:function(res){
                    }

                })
            })

            $('#editForm').on('submit', function(e){
                e.preventDefault();

                let $this = $(this);

                let id = $('#edit_id').val();

                $.ajax({
                    url: '/admin/menu-list/' + id,
                    method: 'PATCH',
                    data: $(this).serialize(),
                    beforeSend: function(){

                    },
                    success: function (response) {
                        console.log(response);
                        $('#editModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showErrorMessageInTopCenter('top-center')
                        }
                    }
                }).error(function(err){
                    console.log(err)
                });
            });

            $('.deleteMessage').hide();

            $('.deleteMessage').click(function () {

                $('#ConfirmModal').modal('toggle')

            })

            $('#yesDelete').on('click', function(e) {

                let idsArr = [];

                $(".checkbox:checked").each(function() {

                    idsArr.push($(this).attr('data-id'));

                });

                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                $.ajax({

                    url: '/admin/menu-list/'+idsArr,
                    type: 'DELETE',
                    data: {_token: CSRF_TOKEN},
                    dataType: 'JSON',
                    beforeSend: function(){

                    },
                    success: function(response){
                        $('#ConfirmModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function() {
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

            $(".checkbox-checked").click(function() {
                var numberNotChecked = $('input:checkbox:checked').length;
                if(numberNotChecked > 0) {
                    $(".deleteMessage").show(300);
                } else {
                    $(".deleteMessage").hide(200);
                }
            });
        });
    </script>

@endsection

