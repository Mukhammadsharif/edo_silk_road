<div class="work-doc-page" id="pdf2">
    <div class="work-doc-subpage" id="pdf3">
        <div id="printPage" class="printPage">

            @if($journal->docStatus->doc_code !== 'OPEN')
                <div class="doc-confidentiality">
                    {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                </div>
            @endif

            @if(!in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT', 'INF_DOC', 'HR_ORD_MT_META']))
                    @include('office.journal.work.templates.header-verifier')
            @endif

            <div class="sent-doc-date-number">
                {{ date('d.m.Y', strtotime($document->doc_date)) }}
            </div>

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

            <div class="doc-text" id="pdf4">
                @if($html_doc)
                    {!! $html_doc !!}
                @endif
            </div>

{{--            @include('office.journal.work.templates.document-files')--}}

            @if(in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT', 'HR_ORD_MT_META']))
                @include('office.journal.work.templates.verifier')
            @endif

            @include('office.journal.work.templates.signer')

            @include('office.journal.work.templates.agreement')

            @include('office.journal.work.templates.executor')

        </div>
    </div>
</div>
