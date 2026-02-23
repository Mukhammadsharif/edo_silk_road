@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
@section('content')
    <div class="work-view-page-modern">
        @include('office.journal.work.templates.header')

        <div class="document-view-layout">
            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <div class="document-content-section">
                <div class="work-doc-page">
                    <div class="work-doc-subpage">
                        <div id="printPage" class="printPage">
                            @if($journal->docStatus->doc_code !== 'OPEN')
                                <div class="doc-confidentiality">
                                    {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                                </div>
                            @endif

                            <div class="table-tr-3td">
                                <table>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <p>
                                                {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!}
                                                <br>
                                                @if($directorDocument->status > 2)
                                                    <span
                                                        class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                                @endif
                                            </p>
                                        </td>
                                        <td>
                                            {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
                                            <p>{{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="doc-text" id="pdf4">
                                {!! str_replace('&nbsp;', ' ', $document->doc_text) !!}
                            </div>

                            @if($document->documentReportForm && count($document->documentReportForm->fields))
                                <div class="table-responsive">
                                    <table class="table table table-bordered nowrap dataTable">
                                        <thead>
                                        <tr class="text-center">
                                            <th>T/r</th>
                                            @foreach($document->documentReportForm->fields as $fieldId)
                                                <th>{{ $fieldId['label'] }}</th>
                                            @endforeach
                                            <th>To`ldiruvchi xodim</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @if(count($document->documentReportForm->entries))
                                            @php $counter = 1; @endphp
                                            @foreach($document->documentReportForm->entries as $item)
                                                @php
                                                    $rowCount = count($item->values);
                                                    $firstRow = true;
                                                @endphp
                                                @foreach($item->values as $row)
                                                    <tr>
                                                        <td>{{ $counter++ }}</td>
                                                        @foreach($row as $fid)
                                                            <td>{{ $fid['value'] ?? '—' }}</td>
                                                        @endforeach
                                                        @if($firstRow)
                                                            <td rowspan="{{ $rowCount }}"
                                                                class="align-middle text-center fw-bold">
                                                                {{ $item->filledBy($item->filled_by) }}
                                                            </td>
                                                            @php $firstRow = false; @endphp
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @else
                                            <tr class="text-center">
                                                <td>-</td>
                                                @foreach($document->documentReportForm->fields as $fieldId)
                                                    <td>-</td>
                                                @endforeach
                                                <td>-</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @include('office.journal.work.templates.app_executor')

                            @include('office.journal.work.templates.agreement')

                        </div>
                    </div>
                </div>
            </div>

            <div class="view-sidebar view-sidebar-right">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-right')
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        });
    </script>

    @include('modals.delete-document')

    @include('office.journal.view-js')

    @include('print.print')

    @include('qr-code-doc.qr-code-view-js')

    @include('print.iframe-print')

    @include('e-imzo.e-imzo')

    <script type="text/javascript">
        //
        const fields = Object.values(@json($document->documentReportForm->fields));

        let fieldIndex = 1;

        document.getElementById('add-field').addEventListener('click', function() {
            const container = document.getElementById('fields-container');
            const fieldId = 'field_' + fieldIndex++;

            let inputsHtml = '';

            // Add row
            fields.forEach((field) => {
                if (field.type === 'textarea') {
                    inputsHtml += `
                    <td><textarea name="values[${fieldIndex}][${field.label}]" class="form-control" rows="2"></textarea></td>
                `;
                } else {
                    inputsHtml += `
                    <td><input type="${field.type}" name="values[${fieldIndex}][${field.label}]" class="form-control"></td>
                `;
                }
            });

            const newRow = document.createElement('tr');
            newRow.classList.add('table-warning');
            newRow.id = fieldId;
            newRow.innerHTML = `
            <td>${fieldIndex}.</td>
            ${inputsHtml}
            <td><button type="button" class="remove-field btn btn-danger btn-sm" data-id="${fieldId}">O'chirish</button></td>
        `;

            container.appendChild(newRow);
        });

        // ❌ remove-field — qatordagi elementni o‘chiradi
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-field')) {
                e.preventDefault();
                const fieldId = e.target.getAttribute('data-id');
                const tr = document.getElementById(fieldId);
                if (tr) tr.remove();
            }
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.openConfirmForm').on('click', function () {
                $('.confirmForm').show();
                let btn_id = $(this).data('id');
                let confirm_type = $(this).html();
                let modal_bg_class = "btn-success";
                if (btn_id === -5) {
                    confirm_type = "{{ __('cancel') }}";
                    modal_bg_class = "btn-outline-warning";
                } else if (btn_id === -6) {
                    confirm_type = "{{ __('re-editing') }}";
                    modal_bg_class = "btn-outline-warning";
                }
                $('.modal-bg-class').removeClass().addClass(`btn-modern modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val();
            });
        });
    </script>
@endsection
