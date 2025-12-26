@extends('layouts.app_create_doc')
@section('content')

    <div class="container-fluid">

        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-edit bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('action.edit_document') }}</h5>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <input hidden id="journal_id" value="{{ $journal->id }}">
            <input hidden id="journal_type_id" value="{{ $journal->j_type }}">
            <input hidden id="doc_code" value="{{ $journal->journal->doc_code??'' }}">
            <div class="col-md-10 offset-1">
                <div class="card">
                    <div class="card-body">
                        <form id="createForm" method="POST" action="{{ route('office.doc.update', ['j_id' => $journal->id, 'hash' => $document->doc_hash]) }}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <label>{{ __('document.confidentiality') }}</label>
                                            <select name="confidence" class="form-control">
                                                @foreach($confidences as $key => $confidence)
                                                    <option value="{{ $confidence->id }}" {{ $journal->confid_type === $confidence->id ? 'selected' : '' }}>
                                                        {{ $key+1 }}. {{ $confidence->lang($confidence->lang_id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-3 doc_number">
                                            <label>{{ __('document.doc_number') }}</label>
                                            <input type="text" name="doc_number" class="form-control" value="{{ $document->doc_number }}">
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('document.doc_date') }}</label>
                                            <input type="date" name="doc_date" class="form-control" value="{{ date('Y-m-d', strtotime($document->doc_date)) }}"/>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('document.signing_term') }} <span class="text-red"></span></label>
                                            <input type="date" name="signing_term" class="form-control" value="{{ $document->doc_term ?  date('Y-m-d', strtotime($document->doc_term)) : '' }}"/>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-10">
                                            <label>{{ __('document.doc_name') }} <span class="text-red">*</span></label>
                                            <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}" value="{{ $document->doc_name??'' }}" required/>
                                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="doc_lang">{{ __('field.doc_lang') }}</label><br>
                                            <select name="doc_lang" class="form-control select2">
                                                <option value="uz" {{ $document->doc_lang == 'uz' ? 'selected' : '' }}>{{ __('lang.uzbek') }}</option>
                                                <option value="kr" {{ $document->doc_lang == 'kr' ? 'selected' : '' }}>{{ __('lang.cyrillic') }}</option>
                                                <option value="ru" {{ $document->doc_lang == 'ru' ? 'selected' : '' }}>{{ __('lang.russian') }}</option>
                                                <option value="en" {{ $document->doc_lang == 'en' ? 'selected' : '' }}>{{ __('lang.english') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row">
                                        <div class="col-sm-12">
                                            <label>{{ __('document.doc_text') }}</label>
                                            <div class="u-flow-xs">
                                                <div id="editor-loading" style="display: flex; justify-content: center; align-items: center; min-height: 100px;">
                                                    <img src="{{ url('loading.gif') }}" width="60px" class="loadingGif" alt="loading-gif">
                                                </div>

                                                <div class="b-demo__editor" id="b-demo-editor">
                                                    <div class="js-editor-wrapper" data-demo-type="feature-rich">
                                                        <textarea  name="doc_text" id="cke5-feature-rich-demo" style="display: none;"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>--}}

                                    <div class="form-group row" id="doc_org"></div>

                                    <div class="form-group row doc_dep">
                                        <div class="col-sm-12">
                                            <label class="to_org_for_sent_doc">{{ __('document.to_department') }} <span class="text-red">*</span></label><br>
                                            <select name="to_dep" class="form-control select2" style="width: 400px">
                                                @foreach($departments as $key => $department)
                                                    <option value="{{ $department->id }}"
                                                        {{ $document->dep && $document->dep->department && $document->dep->department->id === $department->id ? 'selected': ''}}>
                                                        {{ $key+1 }}. {{ $department->lang($department->id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row" id="execution_steps"></div>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
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
                                        <div class="col-sm-12">
                                            <label>{{ __('action.attach_file') }}</label>
                                            <div id="file-inputs-container">
                                                <div class="file-input-group d-flex flex-row align-items-baseline justify-content-between mb-2">
                                                    <div class="custom-file" style="display: none;">
                                                        <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-0">
                                                        <label class="custom-file-label" for="customFile-0">{{ __('action.attach_file') }}</label>
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
                                </div>
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary float-right">
                                <i class="ik ik-save"></i> {{ __('action.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="ConfirmModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title"><i class="ik ik-trash-2"></i>  {{ __('action.delete_confirm') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="text" name="item_conf_id" id="item_conf_id" hidden>
                    <h5><i class="ik ik-info"></i> {{ __('message.delete_you_want_file') }}</h5>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary pull-left" data-dismiss="modal">{{ __('action.cancel') }}</button>
                    <button type="button" class="btn btn-warning deleteItemConfirm">{{ __('action.delete_yes') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!--Success Modal -->
    <div class="modal fade" id="responseModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-content-class">
                    <h5 class="modal-title" id="resModalTitle">{{ __('message.success_message') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 id="resModalMessage"></h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('action.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{--@include('rich-editor.ckeditor5')--}}

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
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


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let journalId = $("#journal_id").val();
            let journalTypeId = $('#journal_type_id').val();
            let doc_code = $('#doc_code').val();


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

            setTimeout(() => {
                getDocumentText(journalId)
            }, 1500)

            function getDocumentText(id) {

                $.ajax({
                    url: '/office/get-document-text/' + id,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {

                        const domEditableElement = document.querySelector( '.ck-editor__editable' );

                        const editorInstance = domEditableElement.ckeditorInstance;

                        editorInstance.setData(response.text);

                        getExecutionUsers(journalTypeId, journalId)
                        getDocOrganizations(journalTypeId, journalId)
                        if(doc_code === 'ORDER_F') {
                            $('.doc_dep').show();
                        }
                    },
                    error: function () {
                        console.error('Failed to load document text.');
                    }
                });
            }

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

@endsection
