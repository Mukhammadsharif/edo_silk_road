<div class="work-doc-subpage" id="doc_content">
    <div id="printPage" class="printPage">

        @if($journal->docStatus->doc_code !== 'OPEN')
            <div class="doc-confidentiality">
                {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
            </div>
        @endif

        <div class="sent-doc-date-number">
            {{ date('d.m.Y', strtotime($document->doc_date)) }}
        </div>

        @if(!in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT', 'COMMAND']))
            @include('qr-code-doc.templates.header-verifier')
        @endif

        @if($journal->journal->doc_code === 'ORDER_F' && $document->dep)
            <div class="table-tr-3td">
                <table>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>
                            @switch($document->doc_lang)
                                @case('uz')
                                    {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}  ga
                                    @break
                                @case('kr')
                                    {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}  га
                                    @break
                                @case('ru')
                                    к {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}
                                    @break
                                @case('en')
                                    to {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}
                                    @break
                            @endswitch
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="doc-text">
            @if($html_doc)
                {!! $html_doc !!}
            @endif
        </div>

        @if(in_array($journal->status, [9, 10]) && in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT', 'COMMAND']))
            @include('qr-code-doc.templates.verifier')
        @endif

        @include('qr-code-doc.templates.signer')

        @include('qr-code-doc.templates.agreement')

        @include('qr-code-doc.templates.executor')
    </div>
</div>
