@extends('layouts.app')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ $protocolType->lang($protocolType->id) }}</h5>
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
                            <li class="breadcrumb-item"><a href="{{ route('prt-secretary', $protocolType->doc_hash) }}">
                                    {{ $protocolType->lang($protocolType->id) }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('action.create_document') }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-1">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-plus-circle"></i> {{ __('action.create_document') }}</h3>
                    </div>
                    <div class="card-body">
                        <form id="createForm" method="POST" action="{{ route('prt-secretary-store', $protocolType->doc_hash) }}" enctype="multipart/form-data">
                            @csrf

                            <input name="document_key" value="{{ $document_key }}" hidden>

                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label>{{ __('field.doc_name') }} <span class="text-red">*</span></label>
                                    <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                           value="{{ old('doc_name') }}" placeholder="{{__('field.doc_name')}}"/>
                                    {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.doc_number') }}</label>
                                    <input type="text" name="doc_number" class="form-control"
                                           value="{{ old('doc_number') }}" placeholder="{{ __('field.doc_number') }}">
                                </div>
                                <div class="col-sm-3">
                                    <label>{{ __('field.doc_date') }}</label>
                                    <input type="date" name="doc_date" class="form-control {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                                           value="{{ date('Y-m-d') }}"/>
                                    {!! $errors->first('doc_date', '<p class="text-danger">:message</p>') !!}
                                </div>
                            </div>

{{--                            <div class="form-group row">--}}
{{--                                <div class="col-sm-12">--}}
{{--                                    <label>{{ __('field.doc_text') }} <span class="text-red">*</span></label>--}}
{{--                                    <div class="u-flow-xs">--}}
{{--                                        <div class="b-demo__editor" id="b-demo-editor">--}}
{{--                                            <div class="js-editor-wrapper" data-demo-type="feature-rich">--}}
{{--                                                <textarea name="doc_text" id="cke5-feature-rich-demo" style="display: none;">{{ old('doc_text') }}</textarea>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    {!! $errors->first('doc_text', '<p class="text-danger">:message</p>') !!}--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.attach_file') }}</label>
                                    <div class="custom-file">
                                        <input type="file" name="doc_files[]" class="custom-file-input"
                                               id="customFile" multiple>
                                        <label class="custom-file-label"
                                               for="customFile">{{ __('field.upload_files') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <a href="{{ route('home') }}" class="btn btn-light">
                                        <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                    </a>
                                    <button type="submit" id="submitBtn" class="btn btn-primary float-right">
                                        <i data-lucide="save"></i> {{ __('action.save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

{{--        @include('rich-editor.ckeditor5')--}}

        <script src="{{ asset('/assets/js/js-sortable.js') }}"></script>

        <script type="text/javascript">

            $('.protocol_members_div').hide();

            $('#doc_type').on('change', function () {

                let users_append = '';

                let user_roles = '';

                let selected_id = Number($(this).val());

                $.ajax({
                    url: '/protocol/secretary/get-doc-type-users/' + selected_id,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        //console.log(response);
                        $('.protocol_members_div').show();
                        $(".protocol_members").empty();
                        let users = response.users;
                        let roles = response.user_roles;
                        if (users.length) {
                            $.each(users,function(key, value)
                            {
                                key++;
                                let fullName = value.user_work.personal.l_name+' '+value.user_work.personal.f_name;
                                let position = value.user_work.position.name;
                                let department = value.user_work.department.name;
                                users_append =`<li class="form-group" style="display: flex; flex-direction: row; align-items: center">
                                <input class="form-control w-10" type="checkbox" id="`+key+`" value="`+value.user_work.id+`" name="user_id[]" checked>
                                <div class="pr-5 text-primary">`+fullName+`<br>
                                    <small class="text-muted">`+department+' '+position+`</small>
                                </div>
                                <div class="d-flex">
                                    <select name="user_role_id[]" class="form-control mr-5 user_roles" style="width: 120px"></select>
                                </div>
                                </li>`;
                                $(".protocol_members").append(users_append);
                            });
                        }
                        if (roles.length) {
                            $.each(roles,function(key, value) {
                                key++;
                                if (key === 2 && value.code === 2) {
                                    user_roles = `<option value="`+value.code+`" selected>`+value.title+`</option>`;
                                } else {
                                    user_roles = `<option value="`+value.code+`">`+value.title+`</option>`;
                                }
                                $(".user_roles").append(user_roles);
                            });
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
                        nodes[i].style.display = "flex";
                    } else {
                        nodes[i].style.display = "none";
                    }
                }
            }

            function searchHrEmpFunction() {
                let input = document.getElementById("hrEmpSearch");
                let filter = input.value.toLowerCase();
                let nodes = document.getElementsByClassName('select-hr-emp');

                for (i = 0; i < nodes.length; i++) {
                    if (nodes[i].innerText.toLowerCase().includes(filter)) {
                        nodes[i].style.display = "flex";
                    } else {
                        nodes[i].style.display = "none";
                    }
                }
            }

            $('.checkbox').on('change', function () {
                $('#userSearch').val('');

                let nodes = document.getElementsByClassName('select-user');

                for (i = 0; i < nodes.length; i++) {
                    nodes[i].style.display = "flex";
                }
            })

        </script>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let sortableElement = document.getElementById('sortable');
                let sortableHrElement = document.getElementById('sortable');

                if (sortableElement) {
                    new Sortable(sortableElement, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                    });
                } else {
                    console.error("Element #sortable not found");
                }

                if (sortableHrElement) {
                    new Sortable(sortableElement, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                    });
                } else {
                    console.error("Element #sortable not found");
                }
            });
        </script>

    </div>
@endsection
