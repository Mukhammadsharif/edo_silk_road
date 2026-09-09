@php($key = md5(uniqid()))
@extends('layouts.app_create_doc')
<link rel="stylesheet" href="{{ asset('css/templates/office/document/edit/incoming.css') }}">
@section('content')

    <div class="modern-container">
        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="page-icon">
                        <i data-lucide="edit"></i>
                    </div>
                    <div class="page-info">
                        <h1 class="text-white">{{ __('action.edit_document') }}</h1>
                        <p class="text-white">{{ __('message.edit_incoming_document') ?? 'Редактирование входящего документа' }}</p>
                    </div>
                </div>
                <nav class="breadcrumb-modern">
                    <a href="{{ route('home') }}">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') ?? 'Главная' }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <a href="#">
                        <span>{{ __('field.document') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <span class="current">{{ __('action.edit_document') }}</span>
                </nav>
            </div>
        </div>

        @include('templates.alert')

        <!-- Hidden Inputs -->
        <input hidden id="journal_id" value="{{ $journal->id }}">
        <input hidden id="doc_hash" value="{{ $document->doc_hash }}">
        <input hidden id="journal_type_id" value="{{ $journal->j_type }}">
        <input hidden id="doc_code" value="{{ $journal->journal->doc_code??'' }}">

        <!-- Main Form Card -->
        <div class="form-card">
            <form id="createForm" method="POST" action="{{ route('office.doc.update', ['j_id' => $journal->id, 'hash' => $document->doc_hash]) }}" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">
                    <!-- Left Column - Settings -->
                    <div class="form-section">
                        <div class="section-header">
                            <i data-lucide="settings"></i>
                            <h3>{{ __('field.document_settings') ?? 'Настройки документа' }}</h3>
                        </div>

                        <!-- Department Journal -->
                        <div class="form-group-modern">
                            <label>
                                <i data-lucide="folder"></i>
                                {{ __('field.department_journals') }}
                                <span class="required">*</span>
                            </label>
                            <select name="department_journal" id="department_journal" class="form-control-modern select2" required>
                                <option value="">{{ __('action.choose') }}</option>
                                @foreach($departmentJournals as $key => $departmentJournal)
                                    <option value="{{ $departmentJournal->id }}" {{ $journal->dep_j_id == $departmentJournal->id ? 'selected' : ''}}>
                                        {{ $key+1 }}. {{ $departmentJournal->lang($departmentJournal->id) }}
                                    </option>
                                @endforeach
                            </select>
                            {!! $errors->first('department_journal', '<p class="error-text">:message</p>') !!}
                        </div>

                        <!-- Organization -->
                        <div class="form-group-modern mt-3">
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

                        <!-- Execution Steps Container -->
                        <div id="execution_steps"></div>
                    </div>

                    <!-- Right Column - Document Details -->
                    <div class="form-section">
                        <div class="section-header">
                            <i data-lucide="file-text"></i>
                            <h3>{{ __('field.doc_details') }}</h3>
                        </div>

                        <div class="modern-form-body">
                            <!-- Language, Confidentiality, Dates -->
                            <div class="form-row-modern">
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="globe"></i>
                                        {{ __('field.doc_lang') }}
                                    </label>
                                    <select name="doc_lang" class="form-control-modern select2">
                                        <option value="uz" {{ $document->doc_lang == 'uz' ? 'selected' : '' }}>{{ __('lang.uzbek') }}</option>
                                        <option value="kr" {{ $document->doc_lang == 'kr' ? 'selected' : '' }}>{{ __('lang.cyrillic') }}</option>
                                        <option value="ru" {{ $document->doc_lang == 'ru' ? 'selected' : '' }}>{{ __('lang.russian') }}</option>
                                        <option value="en" {{ $document->doc_lang == 'en' ? 'selected' : '' }}>{{ __('lang.english') }}</option>
                                    </select>
                                </div>

                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="shield"></i>
                                        {{ __('document.confidentiality') }}
                                    </label>
                                    <select name="confidence" class="form-control-modern select2">
                                        @foreach($confidences as $key => $confidence)
                                            <option value="{{ $confidence->id }}" {{ $journal->confid_type === $confidence->id ? 'selected' : '' }}>
                                                {{ $key+1 }}. {{ $confidence->lang($confidence->lang_id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-row-modern">
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="calendar"></i>
                                        {{ __('document.signing_term') }}
                                    </label>
                                    <input type="date" name="signing_term" class="form-control-modern" value="{{ $document->doc_term??'' }}"/>
                                </div>

                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="calendar"></i>
                                        {{ __('document.doc_date') }}
                                        <span class="required">*</span>
                                    </label>

                                    <div class="date-picker-container">
                                        <div class="input-wrapper">
                                            <svg class="calendar-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                            <input
                                                type="text"
                                                name="doc_date"
                                                class="date-input form-control-modern {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                                                placeholder="{{ __('action.choose_date') }}"
                                                value="{{ $document->doc_date??'' }}"
                                                readonly
                                                required
                                            />
                                            {!! $errors->first('doc_date', '<p class="error-text">:message</p>') !!}
                                        </div>

                                        <div class="calendar-popup">
                                            <div class="calendar-header">
                                                <button type="button" class="nav-btn prev">&lsaquo;</button>
                                                <div class="month-year"></div>
                                                <button type="button" class="nav-btn next">&rsaquo;</button>
                                            </div>
                                            <div class="days"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Name and Number -->
                            <div class="form-row-modern">
                                <div class="form-group-modern" style="flex: 2;">
                                    <label>
                                        <i data-lucide="type"></i>
                                        {{ __('document.doc_name') }}
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" name="doc_name" class="form-control-modern {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                           value="{{ $document->doc_name??'' }}" placeholder="{{ __('field.type_doc_title') }}" required/>
                                    {!! $errors->first('doc_name', '<p class="error-text">:message</p>') !!}
                                </div>

                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="hash"></i>
                                        {{ __('document.doc_number') }}
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" name="doc_number" class="form-control-modern" value="{{ $document->doc_number??'' }}" required>
                                </div>
                            </div>

                            <!-- Existing Files Display -->
                            @if(count($documentFiles))
                                <div class="form-group-modern">
                                    <label>
                                        <i data-lucide="paperclip"></i>
                                        {{ __('document.doc_files') }}
                                    </label>
                                    <div class="existing-files-list">
                                        @foreach($documentFiles as $key => $file)
                                            <div class="file-item-card" id="file_id_{{ $file->id }}">
                                                <div class="file-item-icon">
                                                    <i data-lucide="file-text" class="text-white"></i>
                                                </div>
                                                <div class="file-item-info">
                                                    <a href="{{ route('file.pre-view',['id'=>$file->id]) }}" target="_blank" class="file-item-name">
                                                        {{ $file->file_name }}
                                                    </a>
                                                    <span class="file-item-size">{{ number_format($file->file_size/1024, 2) }} KB</span>
                                                </div>
                                                <div class="file-item-actions">
                                                    <a href="{{ route('file.download', ['id'=>$file->id]) }}" class="btn-file-action download">
                                                        <i data-lucide="download"></i>
                                                    </a>
                                                    <button type="button" class="btn-file-action delete deleteItem" data-id="{{$file->id}}">
                                                        <i data-lucide="trash-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="form-row-modern">
                                <div class="form-group-modern">
                                    <label for="doc_text"><i data-lucide="file-text"></i> {{ __('field.description') }}</label>
                                    <textarea class="form-control-modern" name="doc_text" id="doc_text" rows="10">{!! $document->doc_text??'' !!}</textarea>
                                </div>
                            </div>

                            <!-- File Upload Section -->
                            <div class="form-group-modern file-upload-section">
                                <label>
                                    <i data-lucide="paperclip"></i>
                                    {{ __('action.attach_file') }}
                                </label>

                                <div id="file-inputs-container">
                                    <div class="file-input-wrapper">
                                        <div class="custom-file-hidden">
                                            <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-0">
                                        </div>

                                        <div class="file-display-area" onclick="document.getElementById('customFile-0').click();">
                                            <div class="file-icon">
                                                <i data-lucide="upload"></i>
                                            </div>
                                            <div class="file-text">
                                                <p class="file-name">{{ __('action.choose') }}</p>
                                                <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX (max 30MB)</span>
                                            </div>
                                        </div>

                                        <button type="button" class="btn-add-file add-file-input">
                                            <i data-lucide="plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-actions">
                                <button name="action" type="submit" value="file" id="submitBtn" class="btn-submit">
                                    <i data-lucide="save"></i>
                                    {{ __('action.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('modals.delete-document')

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            lucide.createIcons();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2({
                width: '100%',
                placeholder: '{{ __('action.choose') }}',
                minimumResultsForSearch: 5
            });

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
                            $('#execution_steps').html(response?.blade);
                            lucide.createIcons();
                        }
                    },
                    error: function () {
                        console.error('Failed to load execution users');
                    }
                });
            }

            getExecutionUsers(journalTypeId, journalId);

            // Delete file handlers
            $('.deleteItem').on('click', function () {
                let file_id = $(this).data('id');
                $('#ConfirmModal').modal('show');
                $('#item_conf_id').val(file_id);
            });

            $('.deleteConfItem').on('click', function () {
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
                        $('#resModalMessage').text(res.message);
                        $('#responseModal').modal('toggle');

                        if (res.res === 2) {
                            $('#file_id_'+res.id).fadeOut(300, function() { $(this).remove(); });
                            $('.modal-content-class').addClass('bg-success');
                        } else {
                            $('.modal-content-class').addClass('bg-yellow');
                            $('#resModalMessage').text('Error! Delete file');
                        }
                    },
                    error: function (data) {
                        console.error(data)
                    }
                });
            });
        });

        // File Upload Handler
        let fileInputCounter = 1;

        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();

            // Handle Add Button Click
            document.addEventListener('click', function (e) {
                if (e.target.closest('.add-file-input')) {
                    const container = document.getElementById('file-inputs-container');
                    const newWrapper = document.createElement('div');
                    newWrapper.className = 'file-input-wrapper mt-3';

                    const currentCounter = fileInputCounter;

                    newWrapper.innerHTML = `
                        <div class="custom-file-hidden">
                            <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-${currentCounter}">
                        </div>

                        <div class="file-display-area" onclick="document.getElementById('customFile-${currentCounter}').click();">
                            <div class="file-icon">
                                <i data-lucide="upload"></i>
                            </div>
                            <div class="file-text">
                                <p class="file-name">{{ __('action.choose') }}</p>
                                <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX (max 30MB)</span>
                            </div>
                        </div>

                        <button type="button" class="btn-remove-file">
                            <i data-lucide="trash-2"></i>
                        </button>
                    `;

                    container.appendChild(newWrapper);

                    const fileInput = newWrapper.querySelector(`#customFile-${currentCounter}`);
                    const fileNameDisplay = newWrapper.querySelector('.file-name');

                    fileInput.addEventListener('change', function () {
                        if (this.files.length > 0) {
                            fileNameDisplay.textContent = this.files[0].name;
                            newWrapper.querySelector('.file-display-area').classList.add('has-file');
                        }
                    });

                    newWrapper.querySelector('.btn-remove-file').addEventListener('click', function () {
                        newWrapper.remove();
                    });

                    lucide.createIcons();
                    fileInputCounter++;
                }
            });

            // Handle initial file input
            const initialFileInput = document.getElementById('customFile-0');
            if (initialFileInput) {
                const initialFileNameDisplay = document.querySelector('.file-name');
                initialFileInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        initialFileNameDisplay.textContent = this.files[0].name;
                        document.querySelector('.file-display-area').classList.add('has-file');
                    }
                });
            }
        });
    </script>

    @include('templates.calendar.modern-calendar-css')
    @include('templates.calendar.modern-calendar-script')

@endsection
