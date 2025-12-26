<div class="work-doc-subpage" id="doc_content">
    <div id="printPage" class="printPage">

        <div class="sent-doc-date-number">
            @if($document->doc_number)
                № {{ $document->doc_number }}<br>
            @endif
            {{ date('d.m.Y', strtotime($document->doc_date)) }}
        </div>

        <div class="doc-text">
            @if($html_doc)
                {!! $html_doc !!}
            @endif
        </div>

        <div class="doc-performer">
            <span>{{ __('field.executor') }}:</span>
            @php($secretaryShortName = $document->secretary->personal??0)
            @if($secretaryShortName)
                {{ $secretaryShortName->shortName() }}
            @endif
        </div>

        <div class="doc-tel">
            <span>{{ __('field.phone') }}:</span>
            {{ $document->secretary->personal->phone??'-' }}
        </div>

        <div class="doc-qrcode" style="width: 100px">
            <div class="hr-prt-docs-qr text-center">
                {!! $document->docQrCode($document->id, $document->doc_code) !!}
                <div class="pt-5">
                        <span class="badge badge-pill badge-blue text-white">
                            {{ $document->statusDoc($document->status) }}
                        </span>
                </div>
            </div>
        </div>

        @include('protocol.document_members')

        @include('protocol.document_hr_emp')

    </div>
</div>
