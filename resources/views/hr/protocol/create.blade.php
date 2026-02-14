@extends('layouts.app')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-briefcase bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('hr_orders') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('hr_orders') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i data-lucide="circle-plus"></i> {{ __('create') }}</h3>
                    </div>
                    <div class="card-body">
                        <form id="createForm" method="POST" action="{{ route('protocol.create.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <label>{{ __('hr_order_type') }} <span class="text-red">*</span></label>
                                            <select name="doc_type" id="doc_type" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}">
                                                <option value="" selected>{{ __('choose') }}</option>
                                                @forelse($protocol_type as $key => $type)
                                                    <option value="{{ $type->id }}">{{ $key+1 }}.
                                                        {{ $type->title }}
                                                    </option>
                                                @empty
                                                    <option value="">{{ __('not_found') }}</option>
                                                @endforelse
                                            </select>
                                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label>{{ __('doc_name') }} <span class="text-red">*</span></label>
                                            <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                                   value="{{ old('doc_name') }}"/>
                                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('doc_#') }}</label>
                                            <input type="text" name="doc_number" class="form-control"
                                                   value="{{ old('doc_number') }}" placeholder="{{ __('doc_#') }}">
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('doc_date') }}</label>
                                            <input type="date" name="doc_date" class="form-control"
                                                   value="{{ date('Y-m-d') }}"/>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label>{{ __('doc_text') }} <span class="text-red">*</span></label>
                                            {{--<textarea name="doc_text" id="div_editor1"></textarea>--}}
                                            <div class="u-flow-xs">
                                                <div class="b-demo__editor" id="b-demo-editor">
                                                    <div class="js-editor-wrapper" data-demo-type="feature-rich">
                                                        <textarea name="doc_text" id="cke5-feature-rich-demo" style="display: none;">{{ old('doc_text') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            {!! $errors->first('doc_text', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label>{{ __('attach_file') }}</label>
                                            <div class="custom-file">
                                                <input type="file" name="doc_files[]" class="custom-file-input"
                                                       id="customFile" multiple>
                                                <label class="custom-file-label"
                                                       for="customFile">{{ __('upload_files') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                                {{ __('cancel') }}
                                            </a>
                                            <button type="submit" id="submitBtn" class="btn btn-outline-primary float-right">
                                                <i data-lucide="save"></i> {{ __('save') }}
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <div class="col-sm-12 p-members">
                                            <h5>{{ __('select_hr_order_member') }} <span class="text-red">*</span></h5>
                                            <div class="form-radio" id="members">
                                            </div>
                                            {!! $errors->first('p_members', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <hr>

                                        <div class="col-sm-12" style="display: none" id="toAllUser">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>{{ __('field.protocol_members') }}</label>
                                                    <ul id="sortable" style="max-height: 600px; overflow-x: auto">
                                                        @foreach($users as $key => $item)
                                                            <li class="form-group" style="display: flex; flex-direction: row; align-items: center">
                                                                <input class="form-control w-10" type="checkbox" id="{{ $item->id }}"
                                                                       value="{{ $item->id }}" name="users[]">
                                                                <div class="p0 text-primary">
                                                                    {{ $item->personal->l_name??'' }} {{ $item->personal->f_name??'' }}
                                                                    <p class="text-muted">
                                                                        @php($department = $item->department??0)
                                                                        @if($department)
                                                                            {{ $department->lang($department->id) }}
                                                                        @endif
                                                                        -
                                                                        @php($position = $item->position??0)
                                                                        @if($position)
                                                                            {{ $position->lang($position->id) }}
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12" style="display: none" id="toAllUser">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>{{ __('field.protocol_members') }}</label>
                                                    <ul id="sortable" >
                                                        @foreach($users as $key => $item)
                                                            <li class="form-group" style="display: flex; flex-direction: row; align-items: center">
                                                                <input class="form-control w-10" type="checkbox" id="{{ $item->id }}"
                                                                       value="{{ $item->id }}" name="users">
                                                                <div class="p0 text-primary">
                                                                    {{ $item->personal->l_name??'' }} {{ $item->personal->f_name??'' }}
                                                                    <p class="text-muted">
                                                                        @php($department = $item->department??0)
                                                                        @if($department)
                                                                            {{ $department->lang($department->id) }}
                                                                        @endif
                                                                        -
                                                                        @php($position = $item->position??0)
                                                                        @if($position)
                                                                            {{ $position->lang($position->id) }}
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @include('rich-editor.ckeditor5')

        <script type="text/javascript">

            $('.p-members').hide();

            $('.p-users').hide();

            // SELECT DOC
            $('#doc_type').on('change', function () {

                let members = null;

                let selected_id = Number($(this).val());

                if (selected_id === 3) {
                    $('.p-users').show();
                    $('.p-members').show();
                } else if (selected_id === 20) {
                    $('.p-members').show();
                    $('.p-users').show();
                }

                $.ajax({
                    url: '/hr-p/sec/get-members/' + selected_id,
                    type: 'GET',
                    data: {doc_id: selected_id},
                    dataType: 'JSON',
                    success: function (response) {
                        $("#members").empty();
                        if (response.length) {

                            $.each(response,function(key, value)
                            {
                                let bg_class = value.hr_protocol_role_name.bg_class;
                                let roleName = value.hr_protocol_role_name.title;
                                let fullName = value.user_work.personal.l_name+' '+value.user_work.personal.f_name;
                                let postName = value.user_work.user_post.post_name;
                                members =
                                    '<div class="checkbox-fade fade-in-primary"><label>' +
                                    '<span class="badge badge-pill '+bg_class+'">' +
                                    roleName+'' +
                                    '</span>' +
                                    '<input type="checkbox" name="p_members[]" value="'+value.user_work.id+'" checked />' +
                                    '<span class="cr"><i class="cr-icon ik ik-check txt-success"></i></span>' +
                                    '<span><b> '+fullName+'</b>' +
                                    ' - <i>'+postName+'</i>' +
                                    '</label>' +
                                    '</div><hr>';
                                $("#members").append(members);
                            });
                        } else {
                            members = `<p class="text-warning">{{ __('com_protocol_users') }} {{ __('not_found') }}!
                                           <a href="{{ route('home') }}" target="_blank"><i class="fa fa-plus-circle"></i> {{ __('create') }}<a>
                                       <p>`;

                            $("#members").append(members);
                        }
                    },
                    error: function () {
                    }
                });
            });

            function setAllUserCheckboxes(divId, sourceCheckbox) {
                let divElement = document.getElementById(divId);
                let inputElements = divElement.getElementsByTagName('input');
                for (let i = 0; i < inputElements.length; i++) {
                    if (inputElements[i].type !== 'checkbox')
                        continue;
                    inputElements[i].checked = sourceCheckbox.checked;
                }
            }

            function searchUserFunction() {
                let input = document.getElementById("userSearch");
                let filter = input.value.toLowerCase();
                let nodes = document.getElementsByClassName('select-user');

                for (i = 0; i < nodes.length; i++) {
                    if (nodes[i].innerText.toLowerCase().includes(filter)) {
                        nodes[i].style.display = "block";
                    } else {
                        nodes[i].style.display = "none";
                    }
                }
            }


            $('#doc_type').on('change', function () {
                //editor1.selectDoc()
                let selected_id = Number($(this).val());

                //editor1.delete();

                journal_number = selected_id;

                $("#journals").empty();

                $("#journals_app").empty();

                $('.journal_number').hide();

                $('.doc_org').hide();

                $('.to_users').hide();

                $('.ag_users').hide();

                $('.doc_dep').hide();

                let to_user = '{{ __('signatory') }}';

                $('.doc_number').hide();

                if (selected_id === 1) {
                    $('[id="toUser"]').show();
                    $('[id="toUsers"]').hide();
                    $('[id="toAllUser"]').hide();
                    $('.toEmp').hide();
                    $('.cor_users').hide();
                } else if(selected_id === 3 || selected_id === 14 || selected_id === 21 || selected_id === 23) {
                    // $('.toEmp').show();
                    $('[id="toUser"]').show();
                    $('[id="toAllUser"]').show()
                    $('[id="toUsers"]').hide();
                    $('.cor_users').hide();
                } else if(selected_id === 9 || selected_id === 19) {
                    $('.toEmp').hide();
                    $('[id="toUser"]').show();
                    $('[id="toUsers"]').hide();
                    $('.cor_users').hide();
                    $('[id="toAllUser"]').hide();
                } else if(selected_id === 22){
                    $('.toEmp').hide();
                    $('[id="toUser"]').hide();
                    $('[id="toUsers"]').hide();
                    $('.cor_users').show();
                } else if(selected_id === 20) {
                    $('[id="toUser"]').show();
                    $('.cor_users').hide();
                    $('[id="toUsers"]').hide();
                    $('[id="toAllUser"]').hide();
                    $('.toEmp').hide();
                } else {
                    $('[id="toUser"]').show();
                    $('.cor_users').hide();
                    $('[id="toUsers"]').hide();
                    $('.toEmp').hide();
                }

                if (selected_id === 1) {
                    to_user = '{{ __('signatory') }}';
                    $('.j_type').show();
                    $('.j_type_app').hide();
                    $('.doc_number').show();
                } else if (selected_id === 2) {
                    $('[id="toUsers"]').show();

                    $('.j_type').hide();

                    $('.j_type_app').show();

                    $.ajax({
                        url: '/office/get-journals/' + selected_id,
                        type: 'GET',
                        data: {doc_id: selected_id},
                        dataType: 'JSON',
                        success: function (response) {

                            $("#journals_app").attr('disabled', false);

                            $("#journals_app").append('<option value="">{{ __('choose') }}..</option>');

                            $.each(response,function(key, value)
                            {
                                key = key+1;
                                if (locale === 'ru') {
                                    $("#journals_app").append('<option value=' + value.id + '>' +key+ '. ' + value.j_name_ru + '</option>');
                                }
                                else {
                                    $("#journals_app").append('<option value=' + value.id + '>' +key+ '. ' + value.j_name + '</option>');
                                }
                            });
                        },
                        error: function () {
                        }
                    });

                    to_user = '{{ __('signatory') }}';
                } else if (selected_id === 3) {
                    $('.j_type').show();
                    $('.j_type_app').hide();
                    to_user = '{{ __('signatory') }}';
                } else if (selected_id === 20) {
                    $('.j_type').show();
                    $('.j_type_app').hide();
                    to_user = '{{ __('signatory') }}';
                } else {
                    $('.j_type_app').hide();
                    $('.j_type').show();
                }

                $('.to-user').empty().append(to_user);

                $.ajax({
                    url: '/office/get-journals/' + selected_id,
                    type: 'GET',
                    data: {doc_id: selected_id},
                    dataType: 'JSON',
                    success: function (response) {
                        //console.log(response);

                        $("#journals").attr('disabled', false);

                        $("#journals").append('<option value="">{{ __('choose') }}..</option>');

                        $.each(response,function(key, value)
                        {
                            if (locale === 'ru') {
                                $("#journals").append('<option value=' + value.id + '>' +value.j_code+ '. ' + value.j_name_ru + '</option>');
                            }
                            else {
                                $("#journals").append('<option value=' + value.id + '>' +value.j_code+ '. ' + value.j_name + '</option>');
                            }
                        });
                    },
                    error: function () {
                    }
                });
            });

        </script>

    </div>
@endsection
