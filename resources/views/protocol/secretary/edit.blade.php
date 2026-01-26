@extends('layouts.app')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ $document->docType->lang($document->docType->id) }}</h5>
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
                            <li class="breadcrumb-item">
                                <a href="{{ url('protocol/secretary/view', [$document->id, $document->doc_hash]) }}">
                                    {{ $document->doc_name }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit_document') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-edit-1"></i> {{ __('action.edit_document') }}</h3>
                    </div>
                    <div class="card-body">
                        <form id="editForm" method="POST" action="{{ route('prt-secretary-update', [$document->id, $document->doc_hash]) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <input name="document_key" value="{{ $document_key }}" hidden>

                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <label for="doc_lang">{{ __('field.doc_lang') }}</label><br>
                                            <select name="doc_lang" class="form-control">
                                                <option value="uz" {{ $document->doc_lang == 'uz' ? 'selected': '' }}>1. {{ __('lang.uzbek') }}</option>
                                                <option value="kr" {{ $document->doc_lang == 'kr' ? 'selected': '' }}>2. {{ __('lang.cyrillic') }}</option>
                                                <option value="ru" {{ $document->doc_lang == 'ru' ? 'selected': '' }}>3. {{ __('lang.russian') }}</option>
                                                <option value="en" {{ $document->doc_lang == 'en' ? 'selected': '' }}>4. {{ __('lang.english') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('field.visibility') }}</label>
                                            <select name="doc_visibility" class="form-control">
                                                <option value="2" {{ $document->visibility == 2 ? 'selected': '' }}>{{ __('field.public') }}</option>
                                                <option value="1" {{ $document->visibility == 1 ? 'selected': '' }}>{{ __('field.secret') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label>{{ __('field.doc_date') }}</label>
                                                <input type="date" name="doc_date" class="form-control {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                                                       value="{{ date('Y-m-d', strtotime($document->doc_date)) }}"/>
                                                {!! $errors->first('doc_date', '<p class="text-danger">:message</p>') !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label>{{ __('field.doc_term') }}</label>
                                                <input type="date" name="doc_term" class="form-control"
                                                       value="{{ $document->doc_term ? date('Y-m-d', strtotime($document->doc_term)) : '' }}"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-9">
                                            <div class="form-group">
                                                <label>{{ __('field.doc_name') }} <span class="text-red">*</span></label>
                                                <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                                       value="{{ $document->doc_name }}" placeholder="{{__('field.doc_name')}}"/>
                                                {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label>{{ __('field.doc_number') }}</label>
                                                <input type="text" name="doc_number" class="form-control"
                                                       value="{{ $document->doc_number }}" placeholder="{{ __('field.doc_number') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <span class="text-muted mb-10">{{ __('field.doc_files') }}</span>
                                        @forelse($documentFiles as $key => $file)
                                            <div class="sl-item">
                                                <div class="sl-right" id="file_id_{{ $file->id }}">
                                                    @switch(strtolower($file->file_ext))
                                                        @case('jpg')
                                                        @case('jpeg')
                                                        @case('png')
                                                            <a href="#" target="_blank" class="link"
                                                               onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>',
                                                                   'modal',
                                                                   'width=800,height=900,top=30,left=500');
                                                                   return false;">
                                                                {{ $file->file_name }}
                                                            </a>
                                                            <div class="like-comm float-right">
                                                                <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}"
                                                                   class="link text-secondary"><i data-lucide="download"></i>{{ __('action.download') }}
                                                                </a>
                                                                <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                                                <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                                    <i class="ik ik-trash-2"></i>
                                                                </button>
                                                            </div>
                                                            @break
                                                        @case('pdf')
                                                            <a href="#"
                                                               target="_blank" class="link"
                                                               onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>',
                                                                   'modal',
                                                                   'width=800,height=900,top=30,left=500');
                                                                   return false;">
                                                                {{ $file->file_name }}
                                                            </a>
                                                            <div class="like-comm float-right">
                                                                <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}"
                                                                   class="link text-secondary"><i data-lucide="download"></i>{{ __('action.download') }}
                                                                </a>
                                                                <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                                                <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                                    <i class="ik ik-trash-2"></i>
                                                                </button>
                                                            </div>
                                                            @break
                                                        @default
                                                            <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}" class="link">
                                                                {{ $file->file_name }}
                                                            </a>
                                                            <div class="like-comm float-right">
                                                                <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}"
                                                                   class="link mr-10 text-secondary"> {{ $file->file_name }}
                                                                    <i data-lucide="download"></i> {{ __('action.download') }}
                                                                </a>
                                                                <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                                                <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                                    <i class="ik ik-trash-2"></i>
                                                                </button>
                                                            </div>
                                                    @endswitch
                                                </div><hr>
                                            </div>
                                        @empty
                                            <p class="text-left">{{ __('field.absent') }}</p>
                                        @endforelse
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label>{{ __('action.attach_file') }}</label>
                                            <div id="file-inputs-container">
                                                <div class="file-input-group d-flex flex-row align-items-baseline justify-content-between mb-2">
                                                    <div class="custom-file" style="display: none;">
                                                        <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-0">
                                                        <label class="custom-file-label" for="customFile-0">{{ __('action.choose') }}</label>
                                                    </div>
                                                    <input type="text" class="form-control" readonly placeholder="{{ __('action.choose') }}"
                                                           onclick="document.getElementById('customFile-0').click();" style="cursor: pointer;">
                                                    <button type="button" class="btn btn-icon btn-outline-secondary btn-sm ms-2 ml-2 add-file-input">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
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
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" style="max-height: 600px; overflow-x: auto">
                                        <h5>{{ __('field.select_members') }} <span class="text-red">*</span></h5>
                                        <div class="input-group mb-2 mt-2">
                                            <input type="text" class="form-control" id="userSearch" onkeyup="searchUserFunction()" placeholder="{{ __('action.search') }}...">
                                        </div>
                                        {!! $errors->first('users', '<p class="text-danger">:message</p>') !!}
                                        @if(count($protocolType->docTypeUsers))
                                            <ul id="sortable" style="max-height: 400px; overflow-x: auto">
                                                @foreach($protocolType->docTypeUsers as $item)
                                                    <li class="form-group select-user" style="display: flex; flex-direction: row; align-items: center; margin-bottom: 0">
                                                        <input class="form-control checkbox w-10" type="checkbox"
                                                               name="users[{{ $item->user_id }}][user_id]"
                                                               value="{{ $item->user_id }}"
                                                            {{ in_array($item->user_id, $selectedUsersToArray) ? 'checked' : '' }}>
                                                        <div class="p0 text-primary">
                                                            <h6>{{ $item->userWork->personal->l_name??'' }} {{ $item->userWork->personal->f_name??'' }}</h6>
                                                            <p class="text-muted pl-1">
                                                                @php($department = $item->userWork->department??0)
                                                                @if($department)
                                                                    {{ $department->lang($department->id) }}
                                                                @endif<br>
                                                                <i>
                                                                    @php($position = $item->userWork->position??0)
                                                                    @if($position)
                                                                        {{ $position->lang($position->id) }}
                                                                    @endif
                                                                </i>
                                                            </p>
                                                        </div>
                                                        <div class="d-flex">
                                                            <select name="users[{{ $item->user_id }}][role_id]" class="form-control mr-5" style="width: 160px">
                                                                @foreach($user_roles as $role)
                                                                    <option value="{{ $role->code }}"
                                                                        {{ in_array($item->user_id, $selectedUsersToArray) ? ($selectedUserRoles->where('user_id', $item->user_id)->first()?->role_id == $role->code ? 'selected' : '') : ($role->code == 3 ? 'selected' : '') }}>
                                                                        {{ $role->lang($role->id) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>

                                    @if($isFamiliarExists)
                                        <div class="form-group">
                                            <h5>{{ __('field.select_hr_order_emp') }}</h5>
                                            <div class="input-group mb-2 mt-2">
                                                <input type="text" class="form-control" id="hrEmpSearch" onkeyup="searchHrEmpFunction()" placeholder="{{ __('action.search') }}...">
                                            </div>
                                            <ul id="sortable_hr_emp" style="max-height: 600px; overflow-x: auto">
                                                @foreach($selectedHrEmp as $key => $item)
                                                    <li class="form-group select-hr-emp permanent"
                                                        style="display: flex; flex-direction: row; align-items: center">
                                                        <input class="form-control w-10 checkbox" type="checkbox"
                                                               id="{{ $item->userWork->id }}"
                                                               value="{{ $item->userWork->id }}" name="hr_user_id[]" checked>
                                                        <div class="p0 text-primary">
                                                            <h6>{{ $item->personal->userWork->l_name??'' }} {{ $item->userWork->personal->f_name??'' }}</h6>
                                                            <p class="text-muted">
                                                                @php($department = $item->userWork->department??0)
                                                                    @if($department)
                                                                        {{ $department->lang($department->id) }}
                                                                    @endif
                                                                    -
                                                                    @php($position = $item->userWork->position??0)
                                                                        @if($position)
                                                                            {{ $position->lang($position->id) }}
                                                                        @endif
                                                            </p>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="ConfirmModal" class="modal fade">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h4 class="modal-title"><i class="ik ik-trash-2"></i> {{ __('message.delete_confirm') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="item_conf_id" id="item_conf_id" hidden>
                        <h5><i class="ik ik-info"></i> {{ __('message.delete_you_want_doc') }}</h5>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary pull-left" data-dismiss="modal">{{ __('action.cancel') }}</button>
                        <button type="button" class="btn btn-warning deleteItemConfirm">{{ __('action.delete_yes') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="responseModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h4 class="modal-title">{{ __('message.success_message') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="responseModalLabel"></p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-light" data-dismiss="modal">
                            <i class="ik ik-x"></i> {{ __('action.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('/assets/js/js-sortable.js') }}"></script>

        <script type="text/javascript">
            var selected = @json($selectedHrEmpToArray ?? []);

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
                let filter = input.value.toLowerCase().trim(); // Trim to avoid spaces
                let ul = $('#sortable_hr_emp');
                let docId = $('#doc_id').val() || '{{ $document->id ?? 0 }}'; // Use document ID

                // Remove all non-permanent items to prevent accumulation
                ul.find('li.select-hr-emp:not(.permanent)').remove();

                // Debounce to prevent multiple rapid requests
                clearTimeout(window.searchHrEmpTimeout);
                window.searchHrEmpTimeout = setTimeout(function() {
                    $.ajax({
                        url: '/protocol/search-hr-emp/{{ $protocolType->doc_hash }}',
                        type: 'GET',
                        data: { query: filter, doc_id: docId }, // Include doc_id
                        success: function(response) {
                            console.log('AJAX Response:', response); // Log response for debugging
                            if (!Array.isArray(response)) {
                                console.error('Invalid response format:', response);
                                return;
                            }

                            let displayedIds = new Set(selected.map(String));

                            response.forEach(function(item) {
                                if (!displayedIds.has(item.id.toString())) {
                                    var li = `
                            <li class="form-group select-hr-emp" style="display: flex; flex-direction: row; align-items: center">
                                <input class="form-control w-10 checkbox" type="checkbox"
                                       id="${item.id}"
                                       value="${item.id}" name="hr_user_id[]">
                                <div class="p0 text-primary">
                                    <h6>${item.l_name || ''} ${item.f_name || ''}</h6>
                                    <p class="text-muted">
                                        ${item.department_name || ''} - ${item.position_name || ''}
                                    </p>
                                </div>
                            </li>`;
                                    ul.append(li);
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', status, error);
                        }
                    });
                }, 300); // 300ms debounce
            }

            // Attach event listener with debouncing
            $('#hrEmpSearch').on('input', searchHrEmpFunction);

            document.addEventListener('DOMContentLoaded', function() {
                let sortableElement = document.getElementById('sortable');
                let sortableHrElement = document.getElementById('sortable_hr_emp');

                if (sortableElement) {
                    new Sortable(sortableElement, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                    });
                } else {
                    console.error("Element #sortable not found");
                }

                if (sortableHrElement) {
                    new Sortable(sortableHrElement, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                    });
                } else {
                    console.error("Element #sortable_hr_emp not found");
                }

                searchHrEmpFunction(); // Load initial 5
            });

            $('#sortable_hr_emp').on('change', '.checkbox', function() {
                let id = $(this).val().toString();
                if ($(this).is(':checked')) {
                    selected.push(id);
                    $(this).closest('li').addClass('permanent');
                } else {
                    let index = selected.indexOf(id);
                    if (index > -1) {
                        selected.splice(index, 1);
                    }
                    $(this).closest('li').removeClass('permanent');
                }
            });

            $('.deleteItem').on('click', function () {
                let file_id = $(this).data('id');
                $('#ConfirmModal').modal('show');
                $('#item_conf_id').val(file_id);
            });

            $('.deleteItemConfirm').on('click', function () {
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                let id = $('#item_conf_id').val();
                let url = '/protocol/file/delete/' + id;

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN, id: id},
                    dataType: 'JSON',
                    beforeSend: function(){
                        $('#ConfirmModal').modal('toggle');
                    },
                    success: function(res){
                        $('#responseModalLabel').text(res.message);
                        $('#responseModal').modal('toggle');
                        if (res.res === 2) {
                            $('#file_id_' + res.id).hide();
                        } else {
                            $('#responseModalLabel').text('Error! Delete file');
                        }
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            });

            $('.checkbox').on('change', function () {
                $('#userSearch').val('');
                let nodes = document.getElementsByClassName('select-user');
                for (i = 0; i < nodes.length; i++) {
                    nodes[i].style.display = "flex";
                }
            });

            let fileInputCounter = 1;

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelector('.add-file-input').addEventListener('click', function () {
                    const container = document.getElementById('file-inputs-container');
                    const newFileInputGroup = document.createElement('div');
                    newFileInputGroup.className = 'file-input-group d-flex align-items-center mb-2';

                    const currentCounter = fileInputCounter;

                    const newFileInputDiv = document.createElement('div');
                    newFileInputDiv.className = 'custom-file';
                    newFileInputDiv.style.display = 'none';
                    newFileInputDiv.innerHTML = `
                        <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-${currentCounter}">
                        <label class="custom-file-label" for="customFile-${currentCounter}">{{ __('action.choose') }}</label>
                    `;
                    newFileInputGroup.appendChild(newFileInputDiv);

                    const textInput = document.createElement('input');
                    textInput.type = 'text';
                    textInput.className = 'form-control';
                    textInput.readOnly = true;
                    textInput.placeholder = "{{ __('action.choose') }}";
                    textInput.style.cursor = 'pointer';
                    newFileInputGroup.appendChild(textInput);

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-icon btn-outline-warning btn-sm ms-2 remove-file-input ml-2';
                    removeButton.innerHTML = '<i class="ik ik-minus"></i>';
                    removeButton.addEventListener('click', function () {
                        newFileInputGroup.remove();
                    });
                    newFileInputGroup.appendChild(removeButton);

                    container.appendChild(newFileInputGroup);

                    const fileInput = newFileInputDiv.querySelector(`#customFile-${currentCounter}`);
                    textInput.onclick = () => {
                        const input = document.getElementById(`customFile-${currentCounter}`);
                        if (input) input.click();
                    };

                    fileInput.addEventListener('change', function () {
                        textInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.choose') }}";
                    });

                    fileInputCounter++;
                });

                const initialFileInput = document.getElementById('customFile-0');
                const initialTextInput = document.querySelector('.file-input-group .form-control');
                initialFileInput.addEventListener('change', function () {
                    initialTextInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.choose') }}";
                });
            });
        </script>
    </div>
@endsection
