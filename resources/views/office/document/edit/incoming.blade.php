@php($key = md5(uniqid()))
@extends('layouts.app_create_doc')
<link rel="stylesheet" href="{{ asset('css/templates/office/document/edit/incoming.css') }}">
@section('content')

    <div class="edit-document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="edit"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('action.edit_document') }}</h1>
                        <p>{{ __('message.edit_incoming_document') ?? 'Редактирование входящего документа' }}</p>
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
                    <!-- Left Sidebar - Document Settings -->
                    <div class="form-sidebar-left">
                        <div class="sidebar-section">
                            <div class="section-header">
                                <i data-lucide="settings"></i>
                                <h3>{{ __('field.document_settings') ?? 'Настройки документа' }}</h3>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="folder"></i>
                                    {{ __('field.department_journals') }}
                                    <span class="required">*</span>
                                </label>
                                <select name="department_journal" id="department_journal" class="form-control-modern select2" required>
                                    <option value="" selected>{{ __('action.choose') }}</option>
                                    @foreach($departmentJournals as $key => $departmentJournal)
                                        <option value="{{ $departmentJournal->id }}" {{ $journal->dep_j_id == $departmentJournal->id ? 'selected' : ''}}>
                                            {{ $key+1 }}. {{ $departmentJournal->lang($departmentJournal->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('department_journal', '<p class="error-text">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="building-2"></i>
                                    {{ __('document.organization') }}
                                    <span class="required">*</span>
                                </label>
                                <select name="doc_org" id="doc_org" class="form-control-modern select2" required>
                                    @foreach($organizations as $key => $organization)
                                        <option value="{{ $organization->id }}" {{ $journal->docOrg->doc_org_id === $organization->id ? "selected" : ''}}>
                                            {{ $key+1 }}. {{ $organization->lang($organization->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('doc_org', '<p class="error-text">:message</p>') !!}
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
                                            <select name="confidence" class="form-control select2">
                                                @foreach($confidences as $key => $confidence)
                                                    <option value="{{ $confidence->id }}" {{ $journal->confid_type === $confidence->id ? 'selected' : '' }}>{{ $key+1 }}.
                                                        {{ $confidence->lang($confidence->lang_id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-3">
                                            <label>{{ __('document.signing_term') }}</label><span class="text-red"></span>
                                            <input type="date" name="signing_term" class="form-control" value="{{ $document->doc_term??'' }}"/>
                                        </div>
                                        <div class="col-sm-3">
                                            <label>{{ __('document.doc_date') }}</label><span class="text-red">*</span>
                                            <input type="date" name="doc_date" class="form-control" value="{{ $document->doc_date??'' }}" />
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-9">
                                            <label>{{ __('document.doc_name') }} <span class="text-red">*</span></label>
                                            <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                                   value="{{ $document->doc_name??'' }}" required/>
                                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                        <div class="col-sm-3 doc_number">
                                            <label>{{ __('document.doc_number') }}</label><span class="text-red">*</span>
                                            <input type="text" name="doc_number" class="form-control" value="{{ $document->doc_number??'' }}">
                                        </div>
                                    </div>


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

                                    <div class="form-group">
                                        <button name="action" type="submit" value="file" id="submitBtn" class="btn btn-primary">
                                            <i data-lucide="save"></i> {{ __('action.save') }}
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
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2()

            let journalId = $("#journal_id").val();
            let journalTypeId = $('#journal_type_id').val();

            $('#execution_steps').html('');

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


            getExecutionUsers(journalTypeId, journalId)

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
        // Initialize lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

@endsection

