<div class="work-doc-page" id="pdf2">
    <div class="work-doc-subpage" id="pdf3">
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
                                    {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                                    @if($directorDocument->status > 1)
                                        <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                    @endif
                                </p>
                            </td>
                            <td>
                                {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
                                <br>
                                {{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}

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
                                                <td rowspan="{{ $rowCount }}" class="align-middle text-center fw-bold">
                                                    {{ $item->filledBy($item->filled_by) }}
                                                    @if($current_agreement->status == 2 && $current_agreement->to_user == $item->filled_by)
                                                        <form action="{{ route('office.doc.report.form.delete', $item->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-warning btn-sm">O`chirish</button>
                                                        </form>
                                                    @endif
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

                @if($current_agreement->status == 2)
                    <div class="doc-report-form">
                        <form method="POST" action="{{ route('office.doc.report.form.store') }}">
                            @csrf
                            <input name="document_report_form_id" value="{{ $document->documentReportForm->id??0 }}" hidden>
                            <div class="table-responsive">
                                <table class="table table table-striped table-bordered nowrap dataTable">
                                    <thead>
                                    <tr class="text-center fw-600">
                                        <th>T/r</th>
                                        @foreach($document->documentReportForm->fields as $field)
                                            <th>{{ $field['label'] }}</th>
                                        @endforeach
                                        <th>Harakat</th>
                                    </tr>
                                    </thead>
                                    <tbody id="fields-container">
                                    <tr class="table-warning">
                                        <td>1.</td>
                                        @foreach($document->documentReportForm->fields as $key => $field)
                                            <td>
                                                @if($field['type'] !== 'textarea')
                                                    <input type="{{ $field['type'] }}"
                                                           name="values[0][{{ $field['label'] }}]"
                                                           class="form-control">
                                                @else
                                                    <textarea name="values[0][{{ $field['label'] }}]" class="form-control"
                                                              rows="2"></textarea>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td>-</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <button type="button" id="add-field" class="btn btn-primary">
                                    <i class="ik ik-plus"></i> Qator qo‘shish
                                </button>
                                <button type="submit" class="btn btn-success btn-sm ml-10">
                                    <i class="ik ik-save"></i> Saqlash
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            @include('office.journal.work.templates.signer')

            @include('office.journal.work.templates.agreement')

            @include('office.journal.work.templates.executor')

        </div>
    </div>
</div>

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
