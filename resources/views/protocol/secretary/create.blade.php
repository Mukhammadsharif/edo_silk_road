@extends('layouts.app')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-book bg-blue"></i>
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="ik ik-plus-circle"></i> {{ __('action.create_document') }}</h3>
                    </div>
                    <div class="card-body">
                        <form id="createForm" method="POST" action="{{ route('prt-secretary-store', $protocolType->doc_hash) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <input name="document_key" value="{{ $document_key }}" hidden>

                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <label for="doc_lang">{{ __('field.doc_lang') }}</label><br>
                                            <select name="doc_lang" class="form-control">
                                                <option value="uz">1. {{ __('lang.uzbek') }}</option>
                                                <option value="kr">2. {{ __('lang.cyrillic') }}</option>
                                                <option value="ru">3. {{ __('lang.russian') }}</option>
                                                <option value="en">4. {{ __('lang.english') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('field.visibility') }}</label>
                                            <select name="doc_visibility" class="form-control">
                                                <option value="2">1. {{ __('field.public') }}</option>
                                                <option value="1">2. {{ __('field.secret') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('field.doc_date') }}</label>
                                            <input type="date" name="doc_date" class="form-control {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                                                   value="{{ date('Y-m-d') }}"/>
                                            {!! $errors->first('doc_date', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('field.doc_term') }}</label>
                                            <input type="date" name="doc_term" class="form-control"/>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-9">
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
                                        {!! $errors->first('user_id', '<p class="text-danger">:message</p>') !!}
                                        @if(count($protocolType->docTypeUsers))
                                            <ul id="sortable" style="max-height: 400px; overflow-x: auto">
                                                @foreach($protocolType->docTypeUsers as $key => $item)
                                                    <li class="form-group select-user" style="display: flex; flex-direction: row; align-items: center; margin-bottom: 0">
                                                        <input class="form-control checkbox w-10" type="checkbox"
                                                               id="{{ $item->id }}"
                                                               value="{{ $item->user_id }}" name="user_id[]" checked>
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
                                                            <select name="user_role_id[]" class="form-control mr-5" style="width: 160px">
                                                                @foreach($user_roles as $item)
                                                                    <option value="{{ $item->code }}" @if($item->code === 3) selected @endif>{{ $item->lang($item->id) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>

                                    @if($isFamiliarExists)
                                        <div class="form-group" style="max-height: 600px; overflow-x: auto">
                                            <h5>{{ __('field.select_hr_order_emp') }}</h5>
                                            <div class="input-group mb-2 mt-2">
                                                <input type="text" class="form-control" id="hrEmpSearch" onkeyup="searchHrEmpFunction()" placeholder="{{ __('action.search') }}...">
                                            </div>
                                            <ul id="sortable_hr_emp" style="max-height: 400px; overflow-x: auto">
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

        <script src="{{ asset('/assets/js/js-sortable.js') }}"></script>

        <script type="text/javascript">

            $('.protocol_members_div').hide();

            $('#doc_type').on('change', function () {

                let users_append = '';

                let user_roles = '';

                let selected_id = Number($(this).val());

                $.ajax({
                    url: '/protocol/get-doc-type-users/' + selected_id,
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

            var selected = @json([]); // Initialize selected array

            function searchHrEmpFunction() {
                let input = document.getElementById("hrEmpSearch");
                let filter = input.value.toLowerCase().trim(); // Trim to avoid spaces
                let ul = $('#sortable_hr_emp');
                let docId = $('#doc_id').val() || 0; // Default to 0 if not provided

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

            function checkEditorLoaded() {
                const editorWrapper = document.querySelector('.js-editor-wrapper');
                // Look for CKEditor-specific elements (e.g., .ck-editor__editable)
                const editorElement = editorWrapper.querySelector('.ck-editor__editable');

                const ckPlaceholder = document.querySelector('.ck-placeholder')
                if(ckPlaceholder) {
                    ckPlaceholder.setAttribute('data-placeholder', '{{ __('field.type_text') }}')
                }

                if (editorElement) {
                    // Editor is loaded, hide loader and show editor
                    document.getElementById('editor-loading').style.display = 'none';
                    document.getElementById('b-demo-editor').style.display = 'block';
                    return true;
                }
                return false;
            }

            const pollingInterval = setInterval(() => {
                if (checkEditorLoaded()) {
                    clearInterval(pollingInterval); // Stop polling once loaded
                }
            }, 100); // Check every 100ms

            setTimeout(() => {
                clearInterval(pollingInterval);
                if (!checkEditorLoaded()) {
                    // Show error if editor still not loaded
                    document.getElementById('editor-loading').innerHTML = '<p>Error loading editor. Please refresh the page.</p>';
                }
            }, 100000);

            $('.checkbox').on('change', function () {
                $('#userSearch').val('');

                let nodes = document.getElementsByClassName('select-user');

                for (i = 0; i < nodes.length; i++) {
                    nodes[i].style.display = "flex";
                }
            });

            // Counter to ensure unique IDs for file inputs
            let fileInputCounter = 1;

            document.addEventListener('DOMContentLoaded', function () {
                // Handle Add Button Click
                document.querySelector('.add-file-input').addEventListener('click', function () {
                    const container = document.getElementById('file-inputs-container');
                    const newFileInputGroup = document.createElement('div');
                    newFileInputGroup.className = 'file-input-group d-flex align-items-center mb-2';

                    // Use local counter to ensure correct ID
                    const currentCounter = fileInputCounter;

                    // Create new file input
                    const newFileInputDiv = document.createElement('div');
                    newFileInputDiv.className = 'custom-file';
                    newFileInputDiv.style.display = 'none';
                    newFileInputDiv.innerHTML = `
            <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-${currentCounter}">
            <label class="custom-file-label" for="customFile-${currentCounter}">{{ __('action.choose') }}</label>
        `;
                    newFileInputGroup.appendChild(newFileInputDiv);

                    // Create readonly text input to display file name
                    const textInput = document.createElement('input');
                    textInput.type = 'text';
                    textInput.className = 'form-control';
                    textInput.readOnly = true;
                    textInput.placeholder = "{{ __('action.choose') }}";
                    textInput.style.cursor = 'pointer';
                    newFileInputGroup.appendChild(textInput);

                    // Create Remove Button
                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-icon btn-outline-warning btn-sm ms-2 remove-file-input ml-2';
                    removeButton.innerHTML = '<i class="ik ik-minus"></i>';
                    removeButton.addEventListener('click', function () {
                        newFileInputGroup.remove();
                    });
                    newFileInputGroup.appendChild(removeButton);

                    // Append to container before attaching event listeners
                    container.appendChild(newFileInputGroup);

                    // Now attach event listeners
                    const fileInput = newFileInputDiv.querySelector(`#customFile-${currentCounter}`);
                    textInput.onclick = () => {
                        const input = document.getElementById(`customFile-${currentCounter}`);
                        if (input) input.click();
                    };

                    // Update text input when file is selected
                    fileInput.addEventListener('change', function () {
                        textInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.choose') }}";
                    });

                    // Increment counter after everything is set
                    fileInputCounter++;
                });

                // Update initial file input text when file is selected
                const initialFileInput = document.getElementById('customFile-0');
                const initialTextInput = document.querySelector('.file-input-group .form-control');
                initialFileInput.addEventListener('change', function () {
                    initialTextInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.choose') }}";
                });

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
                    console.error("Element #sortable not found");
                }

                searchHrEmpFunction(); // Load initial 5

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
            });

        </script>

    </div>
@endsection
