<div class="work-doc-page">
    <div class="work-doc-subpage">
        <div id="printPage" class="printPage">

            @if($journal->docStatus->doc_code !== 'OPEN')
                <div class="doc-confidentiality">
                    {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                </div>
            @endif

            @include('office.journal.work.templates.org_logo_banner')

            <div class="sent-doc-date-number mt-10">
                {{ date('d.m.Y', strtotime($document->doc_date)) }} {{ __('field.year') }}
            </div>

            <div class="doc-text">
                @if($html_doc)
                    {!! $html_doc !!}
                @endif
            </div>

            @include('office.journal.work.templates.hr_order_signer')

            @include('office.journal.work.templates.agreement')
        </div>
    </div>
</div>
