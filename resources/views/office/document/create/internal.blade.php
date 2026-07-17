@extends('layouts.app_create_doc')

<link rel="stylesheet" href="{{ asset('css/templates/office/document/create/internal.css') }}">

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
                        <p>{{ __('action.create_internal_doc') }}</p>
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
                    <span class="current">{{ __('action.create_document') }}</span>
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
                            <h3>{{ __('field.main_information') }}</h3>
                        </div>

                        <!-- Document Type 1 -->
                        <div class="form-group-modern">
                            <label>
                                <i data-lucide="file-type"></i>
                                {{ __('field.document_type') }}
                                <span class="required">*</span>
                            </label>
                            <select name="journal_type" id="journal_type" class="form-control-modern select2" required>
                                <option value="" selected>{{ __('action.choose') }}</option>
                                @foreach($journals as $key => $journal)
                                    <option value="{{ $journal->id }}" data-code="{{ $journal->doc_code }}">
                                        {{ $key + 1 }}. {{ $journal->lang($journal->lang_id) }}
                                    </option>
                                @endforeach
                            </select>
                            {!! $errors->first('journal_type', '<p class="error-text">:message</p>') !!}
                        </div>

                        <!-- Document Type 2 (Sub-type) -->
                        <div class="form-group-modern journal_type2" style="display: none;">
                            <label>
                                <i data-lucide="layers"></i>
                                {{ __('field.document_type') }} (turi)
                                <span class="required">*</span>
                            </label>
                            <select name="journal_type2" id="journal_type2" class="form-control-modern select2" disabled>
                                <option value="">{{ __('action.choose') }}</option>
                            </select>
                            {!! $errors->first('journal_type2', '<p class="error-text">:message</p>') !!}
                        </div>

                        <!-- Execution Steps Container -->
                        <div id="execution_steps"></div>
                    </div>

                    <!-- Right Column -->
                    <div class="form-section">
                        <div class="section-header">
                            <i data-lucide="file-text"></i>
                            <h3>{{ __('field.doc_details') }}</h3>
                        </div>

                        <!-- Dynamic Form Body -->
                        <div id="formBody">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i data-lucide="inbox"></i>
                                </div>
                                <p class="empty-title">{{ __('validate.j_number_mandatory') }}</p>
                                <p class="empty-description">{{ __('field.to_continue_left') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @include('office.document.partials.related-documents-input')
            </form>
        </div>
    </div>

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
            applySelect2Styles();

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
                setTimeout(applySelect2Styles, 10);
            });

            const $journalType = $('#journal_type');
            const $journalType2 = $('#journal_type2');
            const $formBody = $('#formBody');
            const $executionSteps = $('#execution_steps');

            // Initial state
            $('.journal_type2').hide();
            $executionSteps.html('');

            // === On main document type change ===
            $journalType.on('change', function () {
                const selectedId = this.value;
                const docCode = $(this).find('option:selected').data('code');

                if (!selectedId) {
                    showEmptyState();
                    return;
                }

                $('.journal_type2').hide();
                $journalType2.empty();

                $.ajax({
                    url: `/office/get-journals/${selectedId}`,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        $journalType2.prop('disabled', false).empty();
                        $journalType2.append(`<option value="">{{ __('action.choose') }}</option>`);

                        if (response?.length) {
                            // If sub-types exist
                            $.each(response, function (key, value) {
                                let docNameLocal = value.title;

                                if (value.lang_name) {
                                    const docName = value.lang_name.text;
                                    if (locale === 'ru') docNameLocal = docName.ru;
                                    else if (locale === 'en') docNameLocal = docName.en;
                                    else docNameLocal = docName.uz;
                                }

                                $journalType2.append(`<option data-code="${value.doc_code}" value="${value.id}">${++key}. ${docNameLocal}</option>`);
                            });

                            $('.journal_type2').show();
                            showEmptyState();

                            // Reinitialize Select2 for journal_type2
                            setTimeout(function() {
                                $('#journal_type2').select2({
                                    width: '100%',
                                    placeholder: '{{ __('action.choose') }}',
                                    minimumResultsForSearch: 5
                                });
                                applySelect2Styles();
                                lucide.createIcons();
                            }, 100);

                        } else {
                            // If no sub-types - load form directly
                            loadForm(docCode);
                            getExecutionUsers(selectedId);
                            getDocOrganizations(selectedId);
                            getJournalNumber(selectedId);
                        }
                    },
                    error: function (xhr) {
                        console.error('Xatolik:', xhr.responseText);
                    }
                });
            });

            // === On sub document type change ===
            $journalType2.on('change', function () {
                const selectedId = this.value;
                const docCode = $(this).find('option:selected').data('code');

                if (!selectedId) {
                    showEmptyState();
                    return;
                }

                loadForm(docCode);
                getJournalNumber(selectedId);
                getExecutionUsers(selectedId);
                getDocOrganizations(selectedId);
            });

            // === Load document form ===
            function loadForm(docCode) {
                $formBody.html('<div class="loading-state"><div class="spinner"></div><p>Yuklanmoqda...</p></div>');

                fetch(`/office/document/form/${docCode}`)
                    .then(res => res.text())
                    .then(html => {
                        $formBody.html(html);
                        lucide.createIcons();

                        // Reinitialize Select2 for dynamically loaded selects
                        setTimeout(function() {
                            $('.select2').select2({
                                width: '100%',
                                placeholder: '{{ __('action.choose') }}',
                                minimumResultsForSearch: 5
                            });
                            applySelect2Styles();
                        }, 100);
                    })
                    .catch(err => {
                        console.error('Form yuklashda xatolik:', err);
                        $formBody.html('<div class="error-state"><i data-lucide="alert-circle"></i><p>Xatolik yuz berdi</p></div>');
                        lucide.createIcons();
                    });
            }

            // === Get journal number ===
            function getJournalNumber(id) {
                $.ajax({
                    url: `/office/get-journal-number/${id}`,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        let inNumber = parseInt(response.lastNumber);

                        let numInput = `
                            <label><i data-lucide='hash'></i> {{ __('journal_#') }}:<span class='required'> *</span></label>
                            <input type='number' class='form-control-modern' name='j_number' value='${inNumber}' readonly />
                        `;

                        let numSelect = `
                            <label><i data-lucide='code'></i> {{ __('department_code') }}:<span class='required'> *</span></label>
                            <select name='j_number2' class='form-control-modern'>
                                ${Array.from({length: 9}, (_, i) => `<option value="0${i+1}">0${i+1}</option>`).join('')}
                            </select>
                        `;

                        $("#last_j_number").html(numInput);
                        $("#last_j_number_2").html(numSelect);

                        lucide.createIcons();
                    },
                    error: function (xhr) {
                        console.error('getJournalNumber error:', xhr.responseText);
                    }
                });
            }

            // === Get execution users ===
            function getExecutionUsers(id) {
                $.ajax({
                    url: `/office/get-execution-users/${id}`,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        if (response.success) {
                            $executionSteps.html(response.blade);
                            lucide.createIcons();
                        }
                    },
                    error: function (xhr) {
                        console.error('getExecutionUsers error:', xhr.responseText);
                    }
                });
            }

            // === Get organizations ===
            function getDocOrganizations(id) {
                $.ajax({
                    url: `/office/get-doc-organizations/${id}`,
                    type: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        if (response.success) {
                            $('#doc_org').html(response.blade);

                            // Reinitialize Select2
                            setTimeout(function() {
                                $('.select2').select2({
                                    width: '100%',
                                    placeholder: '{{ __('action.choose') }}',
                                    minimumResultsForSearch: 5
                                });
                                applySelect2Styles();
                            }, 100);
                        }
                    },
                    error: function (xhr) {
                        console.error('getDocOrganizations error:', xhr.responseText);
                    }
                });
            }

            // === Helper Functions ===
            function showEmptyState() {
                $formBody.html(`
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i data-lucide="inbox"></i>
                        </div>
                        <p class="empty-title">Hujjat turini tanlang</p>
                        <p class="empty-description">Davom etish uchun hujjat turini tanlang</p>
                    </div>
                `);
                lucide.createIcons();
            }

            function applySelect2Styles() {
                $('.select2-container--default .select2-selection--single').css({
                    'border': '2px solid #e2e8f0',
                    'border-radius': '10px',
                    'min-height': '48px'
                });
            }
        });
    </script>
@endsection
