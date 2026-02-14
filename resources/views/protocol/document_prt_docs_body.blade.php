<div class="work-doc-page">
    <div class="work-doc-subpage">
        <div id="printPage" class="printPage">

            <div class="sent-doc-date-number">
                @if($document->doc_number)
                    № {{ $document->doc_number }}<br>
                @endif
                {{ date('d.m.Y', strtotime($document->doc_date)) }}
            </div>

            <div class="doc-text">
                {!! str_replace('&nbsp;', ' ', $documentText->doc_text) !!}
            </div>

            <div class="doc-performer">
                <span>{{ __('field.creator') }}:</span>
                @php($secretaryShortName = $document->secretary->personal??0)
                @if($secretaryShortName)
                    {{ $secretaryShortName->shortName() }}
                @endif
            </div>

            <div class="doc-tel">
                <span>{{ __('data.tel_number') }}:</span>
                {{ $document->secretary->personal->phone??'-' }}
            </div>

        </div>
    </div>
</div>
