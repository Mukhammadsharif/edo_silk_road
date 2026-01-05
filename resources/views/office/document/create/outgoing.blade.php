@php($key = md5(uniqid()))
@extends('layouts.app_create_doc')
@section('content')

    <div class="container-fluid mt-40">

        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-plus-circle bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('action.create_document') }}</h5>
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
                                <a href="#">{{ __('field.document') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('document.outgoing_message') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form id="createForm" method="POST" action="{{ route('office.doc.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <input value="{{ $journal->id }}" name="journal_type" id="journal_type" hidden>

                                    <div class="form-group row">
                                        <div class="col-sm-6 doc_type">
                                            <label class="to_org_for_sent_doc">{{ __('document.organization') }} <span class="text-red">*</span></label><br>
                                            <select name="doc_org" class="form-control select2" style="width: 400px">
                                                @foreach($organizations as $key => $organization)
                                                    <option value="{{ $organization->id }}">{{ $key+1 }}.
                                                        {{ $organization->lang($organization->id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            {!! $errors->first('doc_org', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                    </div>

                                    <div class="form-group row" id="execution_steps"></div>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <label for="doc_lang">{{ __('field.doc_lang') }}</label><br>
                                            <select name="doc_lang" class="form-control select2">
                                                <option value="uz">1. {{ __('lang.uzbek') }}</option>
                                                <option value="kr">2. {{ __('lang.cyrillic') }}</option>
                                                <option value="ru">3. {{ __('lang.russian') }}</option>
                                                <option value="en">4. {{ __('lang.english') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('document.confidentiality') }}</label>
                                            <select name="confidence" class="form-control">
                                                @foreach($confidences as $key => $confidence)
                                                    <option value="{{ $confidence->id }}">{{ $key+1 }}.
                                                        {{ $confidence->lang($confidence->lang_id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('document.doc_date') }}</label><span class="text-red">*</span>
                                            <input type="date" name="doc_date" class="form-control" value="{{ date('Y-m-d') }}" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label>{{ __('document.doc_name') }} <span class="text-red">*</span></label>
                                            <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}" value="{{ old('doc_name') }}" required/>
                                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('document.doc_number') }}</label>
                                            <input type="text" name="doc_number" class="form-control" value="{{ old('doc_number') }}">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-6">
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

                                    <div class="form-group row" id="textContainer">
                                        <div class="col-sm-10">
                                            <label>{{ __('document.doc_text') }}<span class="text-red">*</span></label>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div id="dropzone" class="dropzone">
                                                        <p>Faylni bu yerga qo`ying</p>
                                                        <i class="ik ik-upload-cloud"></i>
                                                        <input type="file" accept=".doc, .docx" name="main_file" id="fileInput" style="display: none;">
                                                    </div>
                                                    <div id="fileList"></div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <button name="action" value="office" style="display: contents" onclick="setFormAction('web')">
                                                        <div class="web-office">
                                                            <p>Web Word Office</p>
                                                            <i class="ik ik-file-text"></i>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        @if(!Auth::user()->currentSubscription)
                                            <small class="d-block text-warning mb-2">
                                                <i class="ik ik-info mr-1"></i>
                                                Hujjat yaratish uchun faol obuna kerak
                                            </small>
                                        @endif

                                        <button type="submit" id="submitBtn" class="btn btn-primary"
                                            @if(!Auth::user()->currentSubscription)
                                                disabled
                                            @endif
                                        >
                                            <i class="ik ik-save"></i> {{ __('action.save') }}
                                        </button>

                                        @if(!Auth::user()->currentSubscription)
                                            <div class="mt-2">
                                                <a href="{{ route('pricing') }}" class="btn btn-outline-warning btn-sm">
                                                    Tariflarni ko'rish
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false); // Prevent default browser behavior
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight dropzone on drag enter/over
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropzone.classList.add('hover');
        }

        function unhighlight() {
            dropzone.classList.remove('hover');
        }

        // Handle dropped files
        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        // Handle file input change
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        // Simulate click on hidden input when dropzone is clicked
        dropzone.addEventListener('click', () => {
            fileInput.click();
        });

        function handleFiles(files) {
            for (const file of files) {
                displayFile(file);
                uploadFile(file); // Placeholder for actual upload logic
            }
        }

        function displayFile(file) {
            $('#fileList').empty();
            const item = document.createElement('div');
            item.classList.add('file-item');
            item.innerHTML = `
                                                <span>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
                                                `;
            fileList.appendChild(item);
        }

        function uploadFile(file) {
            // Implement actual file upload logic here (e.g., using FormData and XMLHttpRequest/Fetch API)
            console.log('Uploading file:', file.name);
            // Update status in the fileList element
        }

        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('fileInput');
            const submitBtn = document.getElementById('submitBtn');

            // изначально скрываем кнопку
            submitBtn.style.display = 'none';

            fileInput.addEventListener('change', function () {
                if (fileInput.files.length > 0) {
                    submitBtn.style.display = 'inline-block'; // показать
                } else {
                    submitBtn.style.display = 'none'; // скрыть
                }
            });
        });

        function setFormAction(type) {
            const form = document.getElementById('createForm');

            if (type === 'file') {
                form.action = "{{ route('office.doc.store') }}";  // сюда нужный маршрут
            } else if (type === 'web') {
                form.action = "{{ route('document-only-office.create') }}";
            }

            form.submit();
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            $('#execution_steps').html('');

            let selected_id = $('#journal_type').val()

            getJournalNumber(selected_id)

            getExecutionUsers(selected_id)


            function getJournalNumber(id) {
                let last_number = '';
                let last_number2 = '';

                $.ajax({
                    url: '/office/get-journal-number/'+id,
                    type: 'GET',
                    data: {j_id: id},
                    dataType: 'JSON',
                    success: function (response) {
                        let inNumber = parseInt(response.lastNumber);

                        last_number += "<label>{{ __('journal_#') }}:</label><span class='text-red'> *</span>" +
                            "<input type='number' class='form-control' name='j_number' value='" + inNumber + "' readonly />";

                        last_number2 += "<label>{{ __('department_code') }}:</label><span class='text-red'> *</span>" +
                            "<select name='j_number2' class='form-control'>";

                        for (let i = 1; i < 10; i++) {
                            last_number2 +="<option value="+ '0'+i +">"+ '0'+i +"</option>";
                        }
                        last_number2 +="</select>";

                        $("#last_j_number").empty().append(last_number);
                        $("#last_j_number_2").empty().append(last_number2);
                    },
                    error: function () {}
                });
            }


            function getExecutionUsers(id){
                $.ajax({
                    url: '/office/get-execution-users/' + id,
                    type: 'GET',
                    data: {j_id: id},
                    dataType: 'JSON',
                    success: function (response) {
                        if (response.success) {
                            $('#execution_steps').html(response?.blade)
                        }
                    },
                    error: function () {
                    }
                });
            }
        })

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
                {{--removeButton.innerHTML = '<i class="fas fa-trash"></i> {{ __("action.remove") }}';--}}
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
                    textInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.upload_files') }}";
                });

                // Increment counter after everything is set
                fileInputCounter++;
            });

            // Update initial file input text when file is selected
            const initialFileInput = document.getElementById('customFile-0');
            const initialTextInput = document.querySelector('.file-input-group .form-control');
            initialFileInput.addEventListener('change', function () {
                initialTextInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.upload_files') }}";
            });
        });

    </script>

@endsection

