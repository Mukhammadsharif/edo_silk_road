@php($key = md5(uniqid()))
@extends('layouts.app_create_doc')

<link rel="stylesheet" href="{{ asset('css/templates/office/document/create/incoming.css') }}">

@section('content')

    <div class="modern-container">
        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="page-icon">
                        <i data-lucide="file-plus-2"></i>
                    </div>
                    <div class="page-info">
                        <h1>{{ __('action.create_document') }}</h1>
                        <p>{{ __('document.incoming_message') }}</p>
                    </div>
                </div>
                <nav class="breadcrumb-modern">
                    <a href="{{ route('home') }}">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <a href="#">{{ __('field.document') }}</a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <span class="current">{{ __('document.incoming_message') }}</span>
                </nav>
            </div>
        </div>

        @include('templates.alert')

        <!-- Main Form Card -->
        <div class="form-card">
            <form id="createForm" method="POST" action="{{ route('office.doc.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">
                    <!-- Left Column -->
                    <div class="form-section">
                        <div class="section-header">
                            <i data-lucide="settings"></i>
                            <h3>Asosiy ma'lumotlar</h3>
                        </div>

                        <input value="{{ $journal->id }}" name="journal_type" id="journal_type" hidden>

                        <!-- Department Journal -->
                        <div class="form-group-modern">
                            <label>
                                <i data-lucide="folder"></i>
                                {{ __('field.department_journals') }}
                                <span class="required">*</span>
                            </label>
                            <select name="department_journal" id="department_journal" class="form-control-modern select2" required>
                                <option value="" selected>{{ __('action.choose') }}</option>
                                @foreach($departmentJournals as $key => $departmentJournal)
                                    <option value="{{ $departmentJournal->id }}">
                                        {{ $key+1 }}. {{ $departmentJournal->lang($departmentJournal->id) }}
                                    </option>
                                @endforeach
                            </select>
                            {!! $errors->first('department_journal', '<p class="error-text">:message</p>') !!}
                        </div>

                        <!-- Organization -->
                        <div class="form-group-modern">
                            <label>
                                <i data-lucide="building-2"></i>
                                {{ __('document.organization') }}
                                <span class="required">*</span>
                            </label>
                            <select name="doc_org" class="form-control-modern select2" required>
                                <option value="" selected>{{ __('action.choose') }}</option>
                                @foreach($organizations as $key => $organization)
                                    <option value="{{ $organization->id }}">
                                        {{ $key+1 }}. {{ $organization->lang($organization->id) }}
                                    </option>
                                @endforeach
                            </select>
                            {!! $errors->first('doc_org', '<p class="error-text">:message</p>') !!}
                        </div>

                        <!-- Execution Steps Container -->
                        <div id="execution_steps"></div>
                    </div>

                    <!-- Right Column -->
                    <div class="form-section">
                        <div class="section-header">
                            <i data-lucide="file-text"></i>
                            <h3>Hujjat tafsilotlari</h3>
                        </div>

                        <!-- Document Info Row -->
                        <div class="form-row-modern">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    {{ __('field.doc_lang') }}
                                </label>
                                <select name="doc_lang" class="form-control-modern select2">
                                    <option value="uz">{{ __('lang.uzbek') }}</option>
                                    <option value="kr">{{ __('lang.cyrillic') }}</option>
                                    <option value="ru">{{ __('lang.russian') }}</option>
                                    <option value="en">{{ __('lang.english') }}</option>
                                </select>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="shield"></i>
                                    {{ __('document.confidentiality') }}
                                </label>
                                <select name="confidence" class="form-control-modern select2">
                                    @foreach($confidences as $key => $confidence)
                                        <option value="{{ $confidence->id }}">
                                            {{ $key+1 }}. {{ $confidence->lang($confidence->lang_id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="calendar"></i>
                                    {{ __('document.doc_date') }}
                                    <span class="required">*</span>
                                </label>
                                <input type="date" name="doc_date" class="form-control-modern" value="{{ date('Y-m-d') }}" required />
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
                                <input type="text" name="doc_name"
                                       class="form-control-modern {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                       value="{{ old('doc_name') }}"
                                       placeholder="Hujjat nomini kiriting..."
                                       required />
                                {!! $errors->first('doc_name', '<p class="error-text">:message</p>') !!}
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="hash"></i>
                                    {{ __('document.doc_number') }}
                                </label>
                                <input type="text" name="doc_number"
                                       class="form-control-modern"
                                       value="{{ old('doc_number') }}"
                                       placeholder="Raqam">
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
                                            <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX (max 10MB)</span>
                                        </div>
                                    </div>

                                    <button type="button" class="btn-add-file add-file-input">
                                        <i data-lucide="plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row-modern">
                            <div class="form-group-modern">
                                <label for="doc_text"><i data-lucide="file-text"></i> {{ __('field.description') }}</label>
                                <textarea class="form-control-modern" name="doc_text" id="doc_text" rows="10"></textarea>
                            </div>
                        </div>

                        <!-- Subscription Warning & Submit -->
                        <div class="form-actions">
                            @if(!Auth::user()->currentSubscription)
                                <div class="subscription-warning">
                                    <i data-lucide="alert-circle"></i>
                                    <span>Hujjat yaratish uchun faol obuna kerak</span>
                                </div>
                            @endif

                            <button type="submit"
                                    id="submitBtn"
                                    class="btn-submit"
                                    @if(!Auth::user()->currentSubscription) disabled @endif>
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            lucide.createIcons();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let locale = '{{ config('app.locale') }}';

            // Initialize Select2 with custom settings
            $('.select2').select2({
                width: '100%',
                placeholder: '{{ __('action.choose') }}',
                minimumResultsForSearch: 5,
                theme: 'default',
                language: {
                    noResults: function() {
                        return "Natija topilmadi";
                    },
                    searching: function() {
                        return "Qidirilmoqda...";
                    }
                }
            });

            // Re-apply styles after Select2 initialization
            $('.select2-container--default .select2-selection--single').css({
                'border': '2px solid #e2e8f0',
                'border-radius': '10px',
                'min-height': '48px'
            });

            // Watch for Select2 events and reapply styles
            $('.select2').on('select2:open', function() {
                setTimeout(function() {
                    $('.select2-container--default .select2-selection--single').css({
                        'border-color': '#794DFD',
                        'box-shadow': '0 0 0 3px rgba(121, 77, 253, 0.1)'
                    });
                }, 10);
            });

            $('.select2').on('select2:close', function() {
                setTimeout(function() {
                    $('.select2-container--default .select2-selection--single').css({
                        'border-color': '#e2e8f0',
                        'box-shadow': 'none'
                    });
                }, 10);
            });

            $('#execution_steps').html('');
            $('#doc_org').html('');

            let selected_id = $('#journal_type').val();

            getExecutionUsers(selected_id);
            getJournalNumber(selected_id);

            function getJournalNumber(id) {
                let last_number = '';
                let last_number2 = '';

                $.ajax({
                    url: '/office/get-journal-number/' + id,
                    type: 'GET',
                    data: {j_id: id},
                    dataType: 'JSON',
                    success: function (response) {
                        let inNumber = parseInt(response.lastNumber);

                        last_number += "<label><i data-lucide='hash'></i> {{ __('journal_#') }}:<span class='required'> *</span></label>" +
                            "<input type='number' class='form-control-modern' name='j_number' value='" + inNumber + "' readonly />";

                        last_number2 += "<label><i data-lucide='code'></i> {{ __('department_code') }}:<span class='required'> *</span></label>" +
                            "<select name='j_number2' class='form-control-modern'>";

                        for (let i = 1; i < 10; i++) {
                            last_number2 += "<option value=" + '0' + i + ">" + '0' + i + "</option>";
                        }
                        last_number2 += "</select>";

                        $("#last_j_number").empty().append(last_number);
                        $("#last_j_number_2").empty().append(last_number2);

                        lucide.createIcons();
                    },
                    error: function () {}
                });
            }

            function getExecutionUsers(id) {
                $.ajax({
                    url: '/office/get-execution-users/' + id,
                    type: 'GET',
                    data: {j_id: id},
                    dataType: 'JSON',
                    success: function (response) {
                        if (response.success) {
                            $('#execution_steps').html(response?.blade);
                            lucide.createIcons();
                        }
                    },
                    error: function () {}
                });
            }
        });

        // File Upload Handler
        let fileInputCounter = 1;

        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();

            // Handle Add Button Click
            document.querySelector('.add-file-input').addEventListener('click', function () {
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
                        <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX (max 10MB)</span>
                    </div>
                </div>

                <button type="button" class="btn-remove-file">
                    <i data-lucide="trash-2"></i>
                </button>
            `;

                container.appendChild(newWrapper);

                // Attach file change listener
                const fileInput = newWrapper.querySelector(`#customFile-${currentCounter}`);
                const fileNameDisplay = newWrapper.querySelector('.file-name');

                fileInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name;
                        newWrapper.querySelector('.file-display-area').classList.add('has-file');
                    }
                });

                // Remove button
                newWrapper.querySelector('.btn-remove-file').addEventListener('click', function () {
                    newWrapper.remove();
                });

                lucide.createIcons();
                fileInputCounter++;
            });

            // Handle initial file input
            const initialFileInput = document.getElementById('customFile-0');
            const initialFileNameDisplay = document.querySelector('.file-name');

            initialFileInput.addEventListener('change', function () {
                if (this.files.length > 0) {
                    initialFileNameDisplay.textContent = this.files[0].name;
                    document.querySelector('.file-display-area').classList.add('has-file');
                }
            });
        });
    </script>
@endsection
