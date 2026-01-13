@php($key = md5(uniqid()))
@extends('layouts.app_create_doc')
<link rel="stylesheet" href="{{ asset('css/templates/office/document/edit/internal.css') }}">
@section('content')

    <div class="edit-document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-edit"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit_document') }}</h1>
                        <p>{{ __('message.edit_internal_document') ?? 'Редактирование внутреннего документа' }}</p>
                    </div>
                </div>
                <div class="header-center">
                    @include('templates.alert')
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <a href="#" class="breadcrumb-item">
                            <span>{{ __('field.document') }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('action.edit_document') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <input hidden id="journal_id" value="{{ $journal->id }}">
        <input hidden id="doc_hash" value="{{ $document->doc_hash }}">
        <input hidden id="journal_type_id" value="{{ $journal->j_type }}">
        <input hidden id="doc_code" value="{{ $journal->journal->doc_code??'' }}">

        <div class="form-card-modern">
            <form id="createForm" method="POST" action="{{ route('office.doc.update', ['j_id' => $journal->id, 'hash' => $document->doc_hash]) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-layout-modern">
                    <!-- Left Sidebar - Execution Steps -->
                    <div class="form-sidebar-left">
                        <div class="sidebar-section">
                            <div class="section-header">
                                <i data-lucide="users"></i>
                                <h3>{{ __('field.execution_steps') ?? 'Этапы исполнения' }}</h3>
                            </div>
                            <div class="execution-steps-container" id="execution_steps"></div>
                        </div>
                    </div>

                    <!-- Main Content - Document Body -->
                    <div class="form-main-content document_body">
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <label for="doc_lang">{{ __('field.doc_lang') }}</label><br>
                                            <select name="doc_lang" class="form-control select2">
                                                <option value="uz" {{ $document->doc_lang == 'uz' ? 'selected' : '' }}>{{ __('lang.uzbek') }}</option>
                                                <option value="kr" {{ $document->doc_lang == 'kr' ? 'selected' : '' }}>{{ __('lang.cyrillic') }}</option>
                                                <option value="ru" {{ $document->doc_lang == 'ru' ? 'selected' : '' }}>{{ __('lang.russian') }}</option>
                                                <option value="en" {{ $document->doc_lang == 'en' ? 'selected' : '' }}>{{ __('lang.english') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('document.confidentiality') }}</label>
                                            <select name="confidence" class="form-control">
                                                @foreach($confidences as $key => $confidence)
                                                    <option value="{{ $confidence->id }}" {{ $journal->confid_type === $confidence->id ? 'selected' : '' }}>{{ $key+1 }}.
                                                        {{ $confidence->lang($confidence->lang_id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    @if(str_contains($journal->journal->doc_code, 'APP_') || str_contains($journal->journal->doc_code, 'HR_ORD'))
                                        <div class="form-group row" id="appDocumentDateContainer">
                                            @if($fromDate)
                                                <div class="col-sm-3" id="fromDate">
                                                    <label>{{ __('field.from_date') }}</label><span class="text-red">*</span>
                                                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}" />
                                                </div>
                                            @endif
                                            @if($toDate)
                                                <div class="col-sm-3" id="toDate">
                                                    <label>{{ __('field.to_date') }}</label><span class="text-red">*</span>
                                                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}" />
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="form group row" id="baseDocumentDateContainer">
                                            <div class="col-sm-3">
                                                <label>{{ __('field.date') }}</label><span class="text-red">*</span>
                                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d', strtotime($document->doc_date)) }}" />
                                            </div>
                                        </div>
                                        <br>
                                    @endif


                                    @if(str_contains($journal->journal->doc_code, 'HR_ORD'))
                                        <div class="form-group row" id="labourApps">
                                            <div class="col-sm-6">
                                                <label>{{ __('document.apps') }}</label>
                                                <select name="app_id" class="form-control select2">
                                                    @foreach($labourApps as $key => $labourApp)
                                                        <option value="{{ $labourApp->doc_id }}" {{ $journal->doc_id == $labourApp->doc_id ? 'selected' :'' }}>
                                                            {{ $key+1 }}. {{ $labourApp->fromUser->personal->shortName()??'' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            @if(count($documentFiles))
                                                <p><i class="ik ik-paperclip"></i> {{ __('document.doc_files') }}</p>
                                                @foreach($documentFiles as $key => $file)
                                                    @switch(strtolower($file->file_ext))
                                                        @case('jpg')
                                                        @case('jpeg')
                                                        @case('png')
                                                            <div class="sl-item" id="file_id_{{ $file->id }}">
                                                                <div class="sl-right">
                                                                    <div>
                                                                        <a href="{{ route('file.pre-view',['id'=>$file->id]) }}"
                                                                           target="_blank" class="link"
                                                                           onclick="window.open('<?php echo('/file/pre-view/' . $file->id); ?>',
                                                                           'modal',
                                                                           'width=800,height=900,top=30,left=500');
                                                                           return false;"><i
                                                                                class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                                        </a>
                                                                        <div class="like-comm float-right">
                                                                            <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                                               class="link mr-10 text-warning"><i
                                                                                    class="ik ik-download"></i> {{ __('action.download') }}
                                                                            </a>
                                                                            <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                                                KB)</i>
                                                                            <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                                                <i class="ik ik-trash-2"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            @break

                                                        @case('pdf')
                                                            <div class="sl-item" id="file_id_{{ $file->id }}">
                                                                <div class="sl-right">
                                                                    <div>
                                                                        <a href="{{ route('file.pre-view',['id'=>$file->id]) }}"
                                                                           target="_blank" class="link"
                                                                           onclick="window.open('<?php echo('/file/pre-view/' . $file->id); ?>',
                                                                           'modal',
                                                                           'width=800,height=900,top=30,left=500');
                                                                           return false;">
                                                                            <i class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                                        </a>
                                                                        <div class="like-comm float-right">
                                                                            <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                                               class="link mr-10 text-warning"><i
                                                                                    class="ik ik-download"></i>{{ __('action.download') }}
                                                                            </a>
                                                                            <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                                                KB)</i>
                                                                            <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                                                <i class="ik ik-trash-2"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            @break

                                                        @default
                                                            <div class="sl-item" id="file_id_{{ $file->id }}">
                                                                <div class="sl-right">
                                                                    <div>
                                                                        <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                                           class="link"><i
                                                                                class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                                        </a>
                                                                        <div class="like-comm float-right">
                                                                            <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                                               class="link mr-10 text-warning"><i
                                                                                    class="ik ik-download"></i>{{ __('action.download') }}
                                                                            </a>
                                                                            <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                                                KB)</i>
                                                                            <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                                                <i class="ik ik-trash-2"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            @break

                                                    @endswitch
                                                @endforeach
                                            @endif
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
                                        <button name="action" value="file" id="submitBtn" class="btn btn-primary" onclick="setFormAction('file')">
                                            <i class="ik ik-save"></i> {{ __('action.save') }}
                                        </button>
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

            let doc_code = $('#doc_code').val();

            if(!(doc_code + '').includes('APP') && !(doc_code + '').includes('HR_ORD')) {
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
            }
        });

        function setFormAction(type) {
            const j_id = document.getElementById('journal_id').value
            const hash = document.getElementById('doc_hash').value
            const form = document.getElementById('createForm');

            if (type === 'file') {
                 let url  = "{{ route('office.doc.update', ['j_id' => ':j_id', 'hash' => ':hash']) }}";  // сюда нужный маршрут
                 url = url.replace(':j_id', j_id).replace(':hash', hash);
                 form.action = url;
            } else if (type === 'web') {
                let url  = "{{ route('office.doc.edit-only', ['j_id' => ':j_id', 'hash' => ':hash']) }}";  // сюда нужный маршрут
                url = url.replace(':j_id', j_id).replace(':hash', hash);
                form.action = url;
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

            let locale = '{{ config('app.locale') }}';

            $('.journal_type2').hide();

            let journalId = $("#journal_id").val();
            let journalTypeId = $('#journal_type_id').val();
            let doc_code = $('#doc_code').val();


            if((doc_code + '').includes('APP') || (doc_code + '').includes('HR_ORD')) {
                $('#textContainer').hide();
                $('#submitBtn').show();
            } else {
                $('#textContainer').show();
                $('#submitBtn').hide();
            }

            $('#execution_steps').html('');
            $('#doc_org').html('');
            $('.doc_dep').hide();

            function getExecutionUsers(id, journalId){
                $.ajax({
                    url: '/office/get-execution-selected-users/' + id + '/' + journalId,
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

            function getDocOrganizations(id){
                $.ajax({
                    url: '/office/get-selected-doc-organizations/' + id + '/' + journalId,
                    type: 'GET',
                    data: {j_id: id},
                    dataType: 'JSON',
                    success: function (response) {
                        if (response.success) {
                            $('#doc_org').html(response?.blade)
                        }
                        $('.select2').select2()
                    },
                    error: function () {
                    }
                });
            }

            getExecutionUsers(journalTypeId, journalId)
            getDocOrganizations(journalTypeId, journalId)

            $('.deleteItem').on('click', function () {

                let file_id = $(this).data('id');

                $('#ConfirmModal').modal('show');

                $('#item_conf_id').val(file_id);

            });

            $('.deleteItemConfirm').on('click', function () {

                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                let id = $('#item_conf_id').val();

                let url = '/file/del/'+id;

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN, id: id},
                    dataType: 'JSON',
                    beforeSend: function(){
                        $('#ConfirmModal').modal('toggle')
                    },
                    success: function(res){
                        $('#resModalMessage').text(res.message)

                        $('#responseModal').modal('toggle')

                        if (res.res === 2) {
                            $('#file_id_'+res.id).hide();
                            $('.modal-content-class').addClass('bg-success');
                        } else {
                            $('.modal-content-class').addClass('bg-yellow');
                            $('#resModalMessage').text('Error! Delete file');
                        }

                    },
                    complete:function(res){

                    },

                    error: function (data) {
                        console.log(data)
                    }
                });
            });
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
            <label class="custom-file-label" for="customFile-${currentCounter}">{{ __('action.upload_files') }}</label>
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
                removeButton.className = 'btn btn-icon btn-outline-warning btn-sm ms-2 ml-2 remove-file-input';
                removeButton.innerHTML = '<i class="fas fa-minus"></i>';
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
        });

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

@endsection

