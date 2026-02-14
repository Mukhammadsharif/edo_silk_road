@extends('layouts.app')

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Asosiy Menyu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Bosh sahifa</a></li>
                        <li class="breadcrumb-item active">Asosiy Menyu</li>
                    </ol>
                </div>

                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        {{ $message }}
                    </div>
                @endif

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Xatolik!</strong> Ma`lumotlarni qaytadan tekshiring.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#createModal">
                                <i class="fa fa-plus-circle"></i> Yaratish
                            </button>
                        </div>

                        <div class="card-body">

                            <form action="{{ url('/admin/menus-search') }}" method="POST" role="search">
                                @csrf

                                <div class="row">
                                    <div class="col-3">
                                        <input type="text" class="form-control " name="input" value="{{ $input??''}}" placeholder="izlash.. %">
                                    </div>

                                    <button type="submit" class="btn btn-outline-info mr-1">
                                        <i class="fa fa-search"></i> Izlash ({{ $models->total() }} ta.)
                                    </button>

                                    <a href="{{ route('menus.index') }}" class="btn btn-outline-secondary">
                                        <i class="fa fa-refresh"></i> reset
                                    </a>

                                </div>

                            </form><hr>

                            <table id="example1" class="table table-bordered table-striped mailbox-messages">
                                <thead>
                                <tr>
                                    <th>{{ __('#') }}</th>
                                    <th><i class="fa fa-text-height"></i> Nomi</th>
                                    <th><i class="fa fa-language"></i> Lang code</th>
                                    <th><i class="fa fa-fonticons"></i> Icon class</th>
                                    <th><i class="fa fa-font"></i> Text class</th>
                                    <th><i class="fa fa-star-half-o"></i> Status</th>
                                    <th><i class="fa fa-clock-o"></i> Sana</th>
                                    <th class="text-center">
                                        <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i>
                                        </button>

                                        <div class="pull-left">
                                            <button type="button" class="btn btn-outline-danger deleteMessage" data-url="">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th><i class="fa fa-pencil-alt"></i></th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($i = 1)
                                @forelse ($models as $key => $model)
                                    <tr id="tr_{{$model->id}}">
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $model->title }}</td>
                                        <td>{{ $model->lang_code }}</td>
                                        <td>
                                            @if($model->icon_code)
                                                <span class="">{{ $model->icon_code }}</span>
                                            @else
                                                <span class="text-red text-bold">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($model->text_class)
                                                <span class="">{{ $model->text_class }}</span>
                                            @else
                                                <span class="text-red text-bold">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-pill {{ $model->statusName->bg_class??'' }} mb-1">
                                                <i class="{{ $model->statusName->icon_code??'' }}"></i>
                                                {{ $model->statusName->title??'-' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y H:i') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="icheck-danger">
                                                <input type="checkbox" class="checkbox checkbox-checked" id="{{$model->id}}" data-id="{{$model->id}}">
                                                <label for="{{$model->id}}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-info editButton" data-toggle="modal" data-target="#editModal" data-id="{{ $model->id }}">
                                                <i class="fa fa-pencil-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <p>Not found!</p>
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
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Yaratish</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createForm" role="form">
                    @csrf
                    <div class="modal-body">
                        <table id="" class="table">
                            <tbody>
                            <tr>
                                <td>
                                    <label for="create-title">Title: <span class="text-red">*</span> </label>
                                </td>
                                <td>
                                    <input type="text" name="title" id="create-title" class="form-control create-model-input-title" required>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="create-lang_code">Lang code: <span class="text-red">*</span> </label>
                                </td>
                                <td>
                                    <input type="text" name="lang_code" id="create-lang_code" class="form-control create-model-input-lang_code" required>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="create-icon_code">Icon class: </label>
                                </td>
                                <td>
                                    <input type="text" name="icon_code" id="create-icon_code" class="form-control create-model-input-icon_code">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="create-text_class">Text class: </label>
                                </td>
                                <td>
                                    <input type="text" name="text_class" id="create-text_class" class="form-control create-model-input-text_class">
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td colspan="2">
                                    <div class="form-group clearfix">
                                        <div class="d-inline mr-3">
                                            <label>
                                                HOLATI?
                                            </label>
                                        </div>
                                        <div class="icheck-success d-inline mr-3">
                                            <input type="radio" id="createIsActive1" name="isActive" value="A" checked>
                                            <label for="createIsActive1" class="text-success">
                                                FAOL
                                            </label>
                                        </div>
                                        <div class="icheck-warning d-inline mr-3">
                                            <input type="radio" id="createIsActive2" name="isActive" value="P">
                                            <label for="createIsActive2" class="text-warning">
                                                FAOL EMAS
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Yopish</button>
                        <button type="submit" class="btn btn-outline-info">Saqlash</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
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
                        <input type="text" name="id" id="edit-menu_id" hidden>
                        <table id="" class="table">
                            <thead class="thead-light">
                            <tbody>
                            <tr>
                                <td>
                                    <label for="edit-title">Title: <span class="text-red">*</span></label>
                                </td>
                                <td>
                                    <input type="text" name="title" id="edit-title" class="form-control edit-model-input-title" required>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="edit-lang_code">Lang code: <span class="text-red">*</span></label>
                                </td>
                                <td>
                                    <input type="text" name="lang_code" id="edit-lang_code" class="form-control edit-model-input-lang_code" required>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="edit-icon_code">Icon class: </label>
                                </td>
                                <td>
                                    <input type="text" name="icon_code" id="edit-icon_code" class="form-control edit-model-input-icon_code">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="edit-text_class">Text class: </label>
                                </td>
                                <td>
                                    <input type="text" name="text_class" id="edit-text_class" class="form-control edit-model-input-text_class">
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td colspan="2">
                                    <div class="form-group clearfix">
                                        <div class="d-inline mr-3">
                                            <label>
                                                HOLATI?
                                            </label>
                                        </div>
                                        <div class="icheck-success d-inline mr-3">
                                            <input type="radio" id="editIsActive1" name="isActive" value="A" checked>
                                            <label for="editIsActive1" class="text-success">
                                                FAOL
                                            </label>
                                        </div>
                                        <div class="icheck-warning d-inline mr-3">
                                            <input type="radio" id="editIsActive2" name="isActive" value="P">
                                            <label for="editIsActive2" class="text-warning">
                                                FAOL EMAS
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Yopish</button>
                        <button type="submit" class="btn btn-outline-info">O`zgarishni Saqlash</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div id="ConfirmModal" class="modal fade">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">O`chirishni tasdiqlash</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p><i class="fa fa-trash-alt"></i> Siz, tanlagan yozuvlar o`chiriladi!</p>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Yopish</button>
                    <button type="button" class="btn btn-danger" id="yesDelete">Ha, O`chirish</button>
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

            $('#createForm').on('submit', function(e){

                e.preventDefault();

                $.ajax({
                    url: '/admin/menus',
                    method: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function(){

                    },
                }).done(function(response){

                    $('#createModal').modal('toggle');

                    $('#responseModalLabel').html(response.message);

                    $('#responseModal').modal('toggle');

                    $('#responseModal').on('hidden.bs.modal', function () {
                        location.reload();
                    })

                    setTimeout(function() {
                        location.reload();
                    }, 2500);

                }).error(function(err){
                    console.log(err)
                })

            })

            $('.editButton').unbind().on('click', function(){

                let id = $(this).data('id')

                $.ajax({
                    url: '/admin/menus/'+id+'/edit',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function(){
                        $( "#editIsActive1" ).prop( "checked", false )
                        $( "#editIsActive2" ).prop( "checked", false )
                    },
                    success: function(model){

                        $('#edit-menu_id').val(model.id)
                        $('.edit-model-input-title').val(model.title)
                        $('.edit-model-input-lang_code').val(model.lang_code)
                        $('.edit-model-input-icon_code').val(model.icon_code)
                        $('.edit-model-input-text_class').val(model.text_class)

                        if(model.isActive === 'A'){
                            $( "#editIsActive1" ).prop( "checked", true )
                        }
                        else{
                            $( "#editIsActive2" ).prop( "checked", true )
                        }
                    },
                    complete:function(res){
                    }

                })
            })

            $('#editForm').on('submit', function(e){
                e.preventDefault();

                let $this = $(this);

                let menu = $('#edit-menu_id').val();

                $.ajax({
                    url: '/admin/menus/'+menu,
                    method: 'PATCH',
                    data: $(this).serialize(),
                    beforeSend: function(){

                    },
                }).done(function(res){

                    $('#editModal').modal('toggle');

                    $this = $('#tr_' + res.model.id).find("td")

                    $this.eq(1).html(res.model.title)
                    $this.eq(2).html(res.model.lang_code)
                    if(res.model.icon_class){
                        $this.eq(3).html(res.model.icon_class)
                    }else{
                        $this.eq(3).html('<span class="text-red text-bold">-</span>')
                    }
                    if(res.model.text_class){
                        $this.eq(4).html(res.model.text_class)
                    }else{
                        $this.eq(4).html('<span class="text-red text-bold">-</span>')
                    }
                    if(res.model.isActive === 'A'){
                        $this.eq(5).html("<i class='fa fa-check text-green' aria-hidden='true'></i>")
                    }else{
                        $this.eq(5).html("<i class='fa fa-ban text-red' aria-hidden='true'></i>")
                    }

                    $('#responseModalLabel').html('Successfully updated!')

                    $('#responseModal').modal('toggle')

                    setTimeout(function() {
                        $('#responseModal').modal('toggle')
                        location.reload();
                    }, 1500)


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

                    url: '/admin/menus/'+idsArr,
                    type: 'DELETE',
                    data: {_token: CSRF_TOKEN},
                    dataType: 'JSON',
                    beforeSend: function(){

                    },
                    success: function(res){

                        $('#ConfirmModal').modal('toggle')
                        $('#responseModalLabel').text(res)
                        $('#responseModal').modal('toggle')

                        setTimeout(function () {
                            location.reload();
                        }, 2000);

                    },
                    complete:function(res){
                        $("#loading").hide();
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
        })
    </script>

@endsection

