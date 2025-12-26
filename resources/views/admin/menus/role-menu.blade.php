@extends('layouts.app')

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('menu.role_menus') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('route.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('menu.role_menus') }}</li>
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

                            <form action="{{ url('/admin/role-menus-search') }}" method="POST" role="search">
                                @csrf
                                <div class="row ml-1">
                                    <div class="mb-2 mr-sm-2 w-25">
                                        <input type="text" class="form-control" name="text" value="{{ $text??''}}" placeholder="{{ __('input.search') }}.. %">
                                    </div>
                                    <div class="mb-2 mr-sm-2">
                                        <select name="user_role" class="form-control select2">
                                            <option value="">{{ __('option.select_user_role') }}</option>
                                            @forelse($roles as $key => $item)
                                                <option value="{{ $item->id }}" {{ ($item->id == $role_id ? "selected":"") }}>{{ $key+1 }}. {{ $item->name }}</option>
                                            @empty
                                                <option value="">{{ __('option.not_found') }}!</option>
                                            @endforelse
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-info mr-1 ">
                                        <i class="fa fa-search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                    </button>

                                    <a href="{{ route('role-menus.index') }}" class="btn btn-light mb-2">
                                        <i class="fa fa-refresh"></i> {{ __('action.clear') }}
                                    </a>

                                </div>

                            </form><hr>

                            <table id="example1" class="table table-bordered table-striped mailbox-messages">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('#') }}</th>
                                    <th>{{ __('field.role_name') }}</th>
                                    <th>{{ __('field.menu_name') }}</th>
                                    <th>{{ __('field.menu_type') }}</th>
                                    <th>{{ __('field.order') }}</th>
                                    <th>{{ __('field.parent_id') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>
                                        <button type="button" class="btn btn-default checkbox-toggle">
                                            <i class="ik ik-check-square"></i>
                                        </button>
                                        <div class="pull-left">
                                            <button type="button" class="btn btn-danger deleteMessage" data-url="">
                                                <i class="ik ik-trash-2"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th><i class="fa fa-pencil-alt"></i></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($models as $key => $model)
                                    <tr id="tr_{{$model->id}}">
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $model->roleName->name??'-' }}</td>
                                        <td>{{ $model->menuList->title??'-' }}</td>
                                        <td>
                                            @if($model->menu_type === 1)
                                                <span class="text-primary text-bold">MENYU</span>
                                            @else
                                                <span class="text-orange text-bold">BO`LIM</span>
                                            @endif
                                        </td>
                                        <td><span class="">{{ $model->order_number }}</span></td>
                                        <td>{{ $model->parent->menuList->title??'-' }}</td>
                                        <td>{{ $model->status }}</td>
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
                                    <tr><td colspan="8">Data not found!</td></tr>
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Yaratish</h4>
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
                                <td><label>{{ __('field.role_name') }}: <span class="text-red">*</span></label></td>
                                <td>
                                    <select class="form-control select2" name="role_id" id="create_role_id" style="width: 100%" required>
                                        <option value="">{{ __('option.select_user_role') }}</option>
                                        @foreach($roles as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ $value->name }} </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.menu_name') }}: <span class="text-red">*</span></label></td>
                                <td>
                                    <select class="form-control select2" name="menu_id" style="width: 100%" required>
                                        <option value="">{{ __('option.select_menu_list') }}</option>
                                        @foreach($menuLists as $key => $value)
                                            <option value="{{ $value->id }}">{{ $key+1 }}. {{ $value->title }} url:{{ $value->url_path }} </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td colspan="2">
                                    <div class="form-group clearfix">
                                        <div class="d-inline mr-3">
                                            <label>
                                                {{ __('field.menu_type') }}?
                                            </label>
                                        </div>
                                        <div class="icheck-primary d-inline mr-3">
                                            <input type="radio" name="menu_type" value="1" checked>
                                            <label class="text-primary">
                                                {{ __('field.menu') }}
                                            </label>
                                        </div>
                                        <div class="icheck-secondary d-inline mr-3">
                                            <input type="radio" name="menu_type" value="2">
                                            <label class="text-secondary">
                                                {{ __('field.department') }}
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.parent') }}:</label></td>
                                <td>
                                    <select id="create_parent_id" class="form-control select2" name="parent_id" style="width: 100%" required>
                                        <option value="0"> {{ __('option.no_parent') }} </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.sort') }}:</label></td>
                                <td><input type="number" name="order_number" class="form-control" min="0" max="1000" value="0"></td>
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
                                            <input type="radio" name="status" value="1" checked>
                                            <label class="text-success">
                                                {{ __('field.active') }}
                                            </label>
                                        </div>
                                        <div class="icheck-warning d-inline mr-3">
                                            <input type="radio" name="status" value="2">
                                            <label class="text-warning">
                                                {{ __('field.no_active') }}
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
                            <i class="ik ik-save"></i> {{ __('action.save') }}
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
                    <h4 class="modal-title"><i class="fa fa-pencil-alt"></i> {{ __('field.edit') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" action="">
                    @csrf
                    <div class="modal-body">
                        <input type="text" name="id" id="edit_id" hidden>
                        <table class="table">
                            <thead class="thead-light">
                            <tbody>
                            <tr>
                                <td><label>{{ __('field.role_name') }}: <span class="text-red">*</span></label></td>
                                <td>
                                    <select id="edit_role_id" class="form-control select2" name="role_id" style="width: 100%" required>
                                        @foreach($roles as $value)
                                            <option value="{{ $value->id }}"> {{ $value->name }} </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.menu_name') }}: <span class="text-red">*</span></label></td>
                                <td>
                                    <select id="edit_menu_id" class="form-control select2" name="menu_id" style="width: 100%" required>
                                        @foreach($menuLists as $value)
                                            <option value="{{ $value->id }}"> {{ $value->title }} {{ $value->url_path }} </option>
                                        @endforeach
                                    </select>
                                </td>

                            </tr>
                            <tr class="text-center">
                                <td colspan="2">
                                    <div class="form-group clearfix">
                                        <div class="d-inline mr-3">
                                            <label>
                                                {{ __('field.menu_type') }}?
                                            </label>
                                        </div>
                                        <div class="icheck-primary d-inline mr-3">
                                            <input type="radio" id="edit_menu_type1" name="menu_type" value="1" checked>
                                            <label class="text-primary">
                                                {{ __('field.menu') }}
                                            </label>
                                        </div>
                                        <div class="icheck-secondary d-inline mr-3">
                                            <input type="radio" id="edit_menu_type2" name="menu_type" value="2">
                                            <label class="text-secondary">
                                                {{ __('field.department') }}
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.order_number') }}:</label></td>
                                <td><input type="number" name="order_number" id="edit_order_number" class="form-control" min="0" max="1000" value="0"></td>
                            </tr>
                            <tr>
                                <td><label>{{ __('field.parent') }}:</label></td>
                                <td>
                                    <select id="edit_parent_id" class="form-control select2" name="parent_id" style="width: 100%" required>
                                        <option value="0">{{ __('option.no_parent') }}</option>
                                    </select>
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
                                                {{ __('field.no_active') }}
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
                            <i class="ik ik-save"></i> {{ __('action.save') }}
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
                    <p><i class="fa fa-trash-alt"></i> {{ __('field.Your selected recordings will be deleted') }}!</p>
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
                    <h4 class="modal-title">{{ __('success_message') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="responseModalLabel"></p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">{{ __('action.close') }}</button>
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
                    url: '/admin/role-menus/user-role/'+selectedValue,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN},
                    dataType: 'json',
                    success: function(res){
                        $.each(res.model, function(index, value){
                            let newOption = new Option(value.title, value.id, false, false)
                            $('#edit_parent_id').append(newOption).trigger('change')
                        })
                    },
                    error: function(e) {
                        console.log(e)
                    }
                })
            })

            $('#create_role_id').change(function() {

                let role_id = $(this).val();

                $('#create_parent_id option[value!="0"]').remove()

                $.ajax({
                    url: '/admin/role-menus/user-role/'+role_id,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN},
                    dataType: 'json',
                    success: function(res){
                        console.log(res)
                        $.each(res.model, function(index, value){
                            let newOption = new Option(value.title, value.id, false, false)
                            $('#create_parent_id').append(newOption).trigger('change')
                        })
                    },
                    error: function(e) {
                        console.log(e)
                    }
                })
            })

            $('#createForm').on('submit', function(e){

                e.preventDefault();

                $.ajax({
                    url: '/admin/role-menus',
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function(){

                    },
                    success: function (response) {
                        //console.log(response);
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

            $('.editButton').on('click', function(){

                let id = $(this).data('id')

                $.ajax({
                    url: '/admin/role-menus/'+id+'/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function(){
                        $( "#edit_menu_type1" ).prop( "checked", false )
                        $( "#edit_menu_type2" ).prop( "checked", false )
                        $( "#edit_status1" ).prop( "checked", false )
                        $( "#edit_status2" ).prop( "checked", false )
                    },
                    success: function(res){

                        $('#edit_id').val(res.model.id);

                        $('#edit_role_id').val(res.model.role_id).trigger('change');

                        $('#edit_menu_id').val(res.model.menu_id).trigger('change');

                        $('#edit_order_number').val(res.model.order_number);

                        $.each(res.parent, function(index, value){
                            let newOption = new Option(value.title, value.id, false, false);
                            $('#edit_parent_id').append(newOption).trigger('change');
                        });

                        $('#edit_parent_id').val(res.model.parent_id).trigger('change');


                        if(res.model.menu_type === 1){
                            $( "#edit_menu_type1" ).prop( "checked", true );
                        } else{
                            $( "#edit_menu_type2" ).prop( "checked", true );
                        }

                        if(res.model.status === 1){
                            $( "#edit_status1" ).prop( "checked", true );
                        } else{
                            $( "#edit_status2" ).prop( "checked", true );
                        }
                    }
                })
            })

            $('#editForm').on('submit', function(e){
                e.preventDefault();

                let id = $('#edit_id').val();

                $.ajax({
                    url: '/admin/role-menus/' + id,
                    method: 'PATCH',
                    data: $(this).serialize(),
                    beforeSend: function(){
                    },
                    success: function(response){
                        //console.log(response);
                        $('#editModal').modal('toggle');
                        if (response.status === 200) {
                            showSuccessMessageInTopCenter('top-center')
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showErrorMessageInTopCenter('top-center')
                        }
                    },
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

                    url: '/admin/role-menus/'+idsArr,
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
                    $('.checkbox-toggle .ik.ik-minus-square').removeClass('ik-minus-square').addClass('ik-check-square')
                } else {
                    //Check all checkboxes
                    $(".deleteMessage").show(300);
                    $('.mailbox-messages input[type=\'checkbox\']').prop('checked', true)
                    $('.checkbox-toggle .ik.ik-check-square').removeClass('ik-check-square').addClass('ik-minus-square')
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
        })
    </script>

@endsection
