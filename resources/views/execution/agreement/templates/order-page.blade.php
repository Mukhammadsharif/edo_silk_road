<div class="work-doc-page">
    <div class="work-doc-subpage">
        <div id="printPage" class="printPage">

            @if($journal->docStatus->doc_code !== 'OPEN')
                <div class="doc-confidentiality">
                    {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                </div>
            @endif

            <div class="sent-doc-date-number">
                {{ date('d.m.Y', strtotime($document->doc_date)) }}
            </div>

            <div class="doc-text">
                @if($html_doc)
                    {!! $html_doc !!}
                @endif
            </div>

{{--            @include('office.journal.work.templates.document-files')--}}

            <div class="table-tr-3td">
                <table>
                    <tr>
                        <td>
                            {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
                        </td>
                        <td>
                            {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                            @if($directorDocument->status > 1)
                                <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??'' }}
                        </td>
                    </tr>
                </table>
            </div>

            @include('office.journal.work.templates.signer')

            @include('office.journal.work.templates.agreement')

            @include('office.journal.work.templates.executor')
        </div>
    </div>
</div>
