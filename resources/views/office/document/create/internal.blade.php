@extends('layouts.app_create_doc')
@section('content')

    <div class="container-fluid mt-40">

        {{-- === Page Header === --}}
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
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.create_document') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        {{-- === Form Section === --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form id="createForm" method="POST" action="{{ route('office.doc.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                {{-- === Left Column (selects) === --}}
                                <div class="col-md-4">
                                    {{-- Document Type 1 --}}
                                    <div class="form-group row">
                                        <div class="col-sm-6 doc_type">
                                            <label>{{ __('field.document_type') }} <span class="text-red">*</span></label>
                                            <select name="journal_type" id="journal_type" class="form-control" required>
                                                <option value="" selected>{{ __('action.choose') }}</option>
                                                @foreach($journals as $key => $journal)
                                                    <option value="{{ $journal->id }}" data-code="{{ $journal->doc_code }}">
                                                        {{ $key + 1 }}. {{ $journal->lang($journal->lang_id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            {!! $errors->first('journal_type', '<p class="text-danger">:message</p>') !!}
                                        </div>

                                        {{-- Document Type 2 (sub-type) --}}
                                        <div class="col-sm-6 journal_type2">
                                            <label for="journal_type2">{{ __('field.document_type') }} <span class="text-red">*</span></label>
                                            <select name="journal_type2" id="journal_type2" class="form-control" disabled></select>
                                            {!! $errors->first('journal_type2', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                    </div>

                                    {{-- Execution steps placeholder --}}
                                    <div class="form-group row" id="execution_steps"></div>
                                </div>

                                {{-- === Right Column (dynamic form) === --}}
                                <div class="col-md-8">
                                    <div id="formBody">
                                        {{-- Dynamic form will be loaded here --}}
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === Scripts === --}}
    {{--<script src="{{ asset('/ajax/jquery.min.js') }}"></script>--}}

    <script type="text/javascript">
        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            let locale = '{{ config('app.locale') }}';
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
                    $formBody.empty();
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
                        $journalType2.append(`<option value="">${'{{ __('action.choose') }}'}..</option>`);

                        if (response?.length) {
                            // Agar pastki turlar mavjud bo‘lsa
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

                        } else {
                            // Agar pastki turlar bo‘lmasa — formani yuklash
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
                    $formBody.empty();
                    return;
                }

                loadForm(docCode);
                getJournalNumber(selectedId);
                getExecutionUsers(selectedId);
                getDocOrganizations(selectedId);
            });

            // === Load document form ===
            function loadForm(docCode) {
                fetch(`/office/document/form/${docCode}`)
                    .then(res => res.text())
                    .then(html => $formBody.html(html))
                    .catch(err => console.error('Form yuklashda xatolik:', err));
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
                    <label>{{ __('journal_#') }}:</label><span class='text-red'> *</span>
                    <input type='number' class='form-control' name='j_number' value='${inNumber}' readonly />
                `;

                        let numSelect = `
                    <label>{{ __('department_code') }}:</label><span class='text-red'> *</span>
                    <select name='j_number2' class='form-control'>
                        ${Array.from({length: 9}, (_, i) => `<option value="0${i+1}">0${i+1}</option>`).join('')}
                    </select>
                `;

                        $("#last_j_number").html(numInput);
                        $("#last_j_number_2").html(numSelect);
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
                            $('.select2').select2();
                        }
                    },
                    error: function (xhr) {
                        console.error('getDocOrganizations error:', xhr.responseText);
                    }
                });
            }
        });
    </script>
@endsection

