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
                                @if($directorDocument->status == 5)
                                    <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                @endif
                            </p>
                        </td>
                        <td>
                            {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
                            <p>{{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}</p>



                            {{ $journal->fromUser->userPost->docLang($journal->fromUser->userPost->id, $document->doc_lang)??'' }}
                            <p>{{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}</p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="doc-text">
                @if($html_doc)
                    {!! $html_doc !!}
                @endif
            </div>

            @include('office.journal.work.templates.app_executor')

            @include('office.journal.work.templates.agreement')

        </div>
    </div>
</div>
