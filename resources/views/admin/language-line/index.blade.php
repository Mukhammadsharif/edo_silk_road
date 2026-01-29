@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.language') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('title.language') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        @can('language-create')
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#AddModal">
                                <i class="ik ik-plus-circle"></i> {{ __('action.create') }}
                            </button>
                        @endcan
                    </div>

                    <div class="card-body template-demo table-responsive">
                        <form action="{{ route('language.index') }}" method="post" role="search" class="form-horizontal">
                            @csrf
                            <div class="row ml-1">
                                <div class="mb-2 mr-sm-2 w-25">
                                    <input type="text" name="s_text" value="{{ $s_text??'' }}" class="form-control" placeholder="{{ __('action.search') }}...">
                                </div>

                                <div class="mb-2 mr-sm-2 w-25">
                                    <select name="s_field" class="form-control select2" style="width: 100%">
                                        <option value="">{{ __('field.fields') }}</option>
                                        @foreach($groups as $key => $item)
                                            <option value="{{ $item->group }}" {{ $s_field == $item->group ? 'selected':''}}>
                                                {{ $key+1 }}. {{ $item->group ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-info">
                                    <i class="ik ik-search"></i> {{ __('action.search') }} ({{ $models->total() }})
                                </button>
                                <a href="{{ route('language.index') }}" class="btn btn-light">
                                    <i class="ik ik-refresh-ccw"></i> {{ __('action.clear') }}
                                </a>
                            </div>
                        </form><hr>

                        <div class="dt-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.group') }}</th>
                                    <th>{{ __('field.key') }}</th>
                                    <th>{{ __('field.lang_uz') }}</th>
                                    <th>{{ __('field.lang_ru') }}</th>
                                    <th>{{ __('field.lang_en') }}</th>
                                    <th colspan="2">{{ __('action.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @forelse($models as $key => $item)
                                        <tr class="text-center">
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $item->group??'' }}</td>
                                            <td>{{ $item->key??'' }}</td>
                                            <td>{{ $item->text['uz']??'' }}</td>
                                            <td>{{ $item->text['ru']??'' }}</td>
                                            <td>{{ $item->text['en']??'' }}</td>
                                            <td>
                                                <button type="button" value="{{ $item->id }}" class="btn btn-info edit_btn btn-sm">
                                                    <i class="ri-edit-line me-1"></i> <i class="ik ik-edit-1"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" value="{{ $item->id }}" class="btn btn-danger delete_btn btn-sm">
                                                    <i class="ri-delete-bin-line me-1"></i> <i class="ik ik-trash-2"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">{{ __('field.not_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {!! $models->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="AddModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> {{ __('action.create') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                    <div class="modal-body">
                        <ul id="save_msgList"></ul>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.group') }}</label>
                            <input type="text" required class="group_input form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.key') }}</label>
                            <input type="text" required class="key_input form-control" id="key_input_c">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.lang_uz') }}</label>
                            <input type="text" required class="text_uz_input form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.lang_ru') }}</label>
                            <input type="text" required class="text_ru_input form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.lang_en') }}</label>
                            <input type="text" required class="text_en_input form-control" id="text_en_input_c">
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-light" data-dismiss="modal"><i class="ik ik-x"></i>{{ __('action.cancel') }}</button>
                        <button type="submit" class="btn btn-outline-info save_btn"><i data-lucide="save"></i>{{ __('action.save') }}</button>
                    </div>

            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-pencil-alt"></i> {{ __('field.edit') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                    <div class="modal-body">
                        <ul id="update_msgList"></ul>

                        <input type="hidden" id="model_id" />

                        <div class="form-group mb-3">
                            <label for="">{{ __('field.group') }}</label>
                            <input type="text" id="group_input" required class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.key') }}</label>
                            <input type="text" id="key_input" required class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.lang_uz') }}</label>
                            <input type="text" id="text_uz_input" required class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.lang_ru') }}</label>
                            <input type="text" id="text_ru_input" required class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ __('field.lang_en') }}</label>
                            <input type="text" id="text_en_input" required class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('action.cancel') }}</button>
                        <button type="button" class="btn btn-outline-info update_btn">{{ __('action.save_change') }}</button>
                    </div>
            </div>
        </div>
    </div>


    {{-- Delete Modal --}}
    <div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <i class="ik ik-x-circle text-white cancel_delete_btn" style="font-size: 20px; cursor: pointer"></i>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <lord-icon src="/build/json/trash-bin.json" trigger="loop" colors="primary:#0ab39c,secondary:#405189" style="width:120px;height:120px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15"><h4>{{ __('text.delete_confirm') }}</h4>
                            <p class="text-muted mx-4 mb-0"><span class="ri-question-line me-1"></span>{{ __('text.delete_confirm_text') }}</p>
                        </div>
                    </div>
                    <input type="hidden" id="deleteing_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light cancel_delete_btn" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>{{ __('action.cancel') }}
                    </button>
                    <button type="button" class="btn btn-soft-danger yes_delete_btn">
                        <i class="ri-check-line me-1"></i>{{ __('action.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- End - Delete Modal --}}

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">

        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // fetchLang();

            function fetchLang() {
                $.ajax({
                    type: "GET",
                    url: "/admin/lang/fetch-lang",
                    dataType: "json",
                    beforeSend: function () {
                        //$('.overlay-icon').show();
                    },
                    complete: function(){
                        //$('.overlay-icon').hide();
                    },
                    success: function (response) {
                        tableData(response)
                    },
                    error: function (s) {
                        console.log(s);
                    }
                });
            }

            function searchLang(text) {
                if (text.length > 2) {
                    $.ajax({
                        type: "GET",
                        url: "/admin/lang/search-lang/" + text,
                        success: function (response) {
                            tableData(response)
                        }
                    });
                } else if(text.length == '') {
                    fetchLang();
                }
            }

            function tableData(response) {
                $('tbody').html("");
                $.each(response.models.data, function (key, item) {
                    let text = JSON.parse(JSON.stringify(item.text));
                    //console.log(JSON.parse(JSON.stringify(item.text)))
                    let edit_lang = "{{ __('action.edit') }}";
                    let delete_lang = "{{ __('action.delete') }}";
                    $('tbody').append('<tr class="text-center">\
                            <td>' + item.group + '</td>\
                            <td>' + item.key + '</td>\
                            <td>' + text.uz + '</td>\
                            <td>' + text.ru + '</td>\
                            <td>' + text.en + '</td>\
                            <td><button type="button" value="' + item.id + '" class="btn btn-info edit_btn btn-sm">\
                            <i class="ri-edit-line me-1"></i> <i class="ik ik-edit-1"></i>'+edit_lang+'</button>\
                            </td>\
                            <td><button type="button" value="' + item.id + '" class="btn btn-danger delete_btn btn-sm">\
                            <i class="ri-delete-bin-line me-1"></i> <i class="ik ik-trash-2"></i>'+delete_lang+'</button>\
                            </td>\
                        \</tr>');
                });
            }

            $(document).on('click', '.save_btn', function(e){

                e.preventDefault();

                var data = {
                    'group_input': $('.group_input').val(),
                    'key_input': $('.key_input').val(),
                    'text_uz_input': $('.text_uz_input').val(),
                    'text_ru_input': $('.text_ru_input').val(),
                    'text_en_input': $('.text_en_input').val(),
                }

                $.ajax({
                    url: '/admin/lang/store',
                    method: 'POST',
                    data: data,
                    beforeSend: function(){

                    },
                    success: function(response){
                        if (response.status === 400) {
                            $('#save_msgList').html("");
                            $('#save_msgList').addClass('alert alert-danger');
                            $.each(response.errors, function (key, err_value) {
                                $('#save_msgList').append('<li>' + err_value + '</li>');
                            });
                            $('.create_btn').text('Save');
                        } else {
                            $('#save_msgList').html("");
                            $('#success_message').addClass('alert alert-success');
                            $('#success_message').text(response.message);
                            $('#AddModal').find('input').val('');
                            $('.save_btn').text('Save');
                            $('#AddModal').modal('hide');
                            // fetchLang();
                            setTimeout(() => window.location.reload(), 500)
                        }
                    },
                }).error(function(err){
                    console.log(err)
                })

            })

            $(document).on('click', '.edit_btn', function (e) {
                e.preventDefault();
                let model_id = $(this).val();
                $('#editModal').modal('show');
                $.ajax({
                    type: "GET",
                    url: "/admin/lang/edit-lang/" + model_id,
                    success: function (response) {

                        let text = JSON.parse(JSON.stringify(response.model.text));

                        if (response.status === 404) {
                            $('#success_message').addClass('alert alert-success');
                            $('#success_message').text(response.message);
                            $('#editModal').modal('hide');
                        } else {
                            $('#group_input').val(response.model.group);
                            $('#key_input').val(response.model.key);
                            $('#text_uz_input').val(text.uz);
                            $('#text_ru_input').val(text.ru);
                            $('#text_en_input').val(text.en);
                            $('#model_id').val(model_id);
                        }
                    }
                });
                $('.btn-close').find('input').val('');

            });

            $(document).on('click', '.update_btn', function(e){
                e.preventDefault();

                $(this).text('Updating..');

                let id = $('#model_id').val();

                var data1 = {
                    'group_input': $('#group_input').val(),
                    'key_input': $('#key_input').val(),
                    'text_uz_input': $('#text_uz_input').val(),
                    'text_ru_input': $('#text_ru_input').val(),
                    'text_en_input': $('#text_en_input').val(),
                }

                $.ajax({
                    type: "PUT",
                    url: "/admin/lang/update-lang/" + id,
                    data: data1,
                    dataType: "json",
                    success: function (response) {
                        // console.log(response);
                        if (response.status === 400) {
                            $('#update_msgList').html("");
                            $('#update_msgList').addClass('alert alert-danger');
                            $.each(response.errors, function (key, err_value) {
                                $('#update_msgList').append('<li>' + err_value +
                                    '</li>');
                            });
                            $('.update_btn').text('Update');
                        } else {
                            $('#update_msgList').html("");

                            $('#success_message').addClass('alert alert-success');
                            $('#success_message').text(response.message);
                            $('#editModal').find('input').val('');
                            $('.update_btn').text('Update');
                            $('#editModal').modal('hide');
                            let text = $("#lang_search").val();
                            // searchLang(text);
                            setTimeout(() => window.location.reload(), 500)
                        }
                    }
                });
            });

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
                    url: "/admin/lang/delete-lang/" + id,
                    dataType: "json",
                    success: function (response) {
                        // console.log(response);
                        if (response.status === 404) {
                            $('#success_message').addClass('alert alert-success');
                            $('#success_message').text(response.message);
                            $('.delete_btn').text('Yes Delete');
                        } else {
                            $('#success_message').html("");
                            $('#success_message').addClass('alert alert-success');
                            $('#success_message').text(response.message);
                            $('.delete_btn').text('Yes Delete');
                            $('#DeleteModal').modal('hide');
                            setTimeout(() => window.location.reload(), 500)
                        }
                    }
                });
            });

            $(document).on('click', '.cancel_delete_btn', function (e){
                $('#DeleteModal').modal('hide');
            })

            $(document).on('keyup', '#lang_search', function (e) {
                e.preventDefault();
                let text = $(this).val();
                searchLang(text)
            });

            const input_c = document.getElementById("key_input_c");
            const duplicateFieldC = document.getElementById("text_en_input_c");

            input_c.addEventListener('keyup',() => {
                duplicateFieldC.value = capitalizeFirstLetter(input_c.value);
            });

            const input = document.getElementById("key_input");
            const duplicateField = document.getElementById("text_en_input");

            input.addEventListener('keyup',() => {
                duplicateField.value = capitalizeFirstLetter(input.value);
            });

            function capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

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
