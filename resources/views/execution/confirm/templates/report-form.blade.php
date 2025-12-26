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

            @include('office.journal.work.templates.signer')

            @include('office.journal.work.templates.agreement')

            @include('office.journal.work.templates.executor')

        </div>
    </div>
</div>
