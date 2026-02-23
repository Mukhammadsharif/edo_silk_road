<div class="work-doc-page" id="pdf2">
    <div class="work-doc-subpage" id="pdf3">
        <div id="printPage" class="printPage">

            @if($journal->docStatus->doc_code !== 'OPEN')
                <div class="doc-confidentiality">
                    {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                </div>
            @endif

            @if(!in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT']))
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
                                {{ __('org_name') }}
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

            @if(in_array($journal->status, [9, 10]) && in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT']))
                <div class="table-tr-3td">
                    <table>
                        <tr>
                            <td>
                                {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
                            </td>
                            <td>
                                {!! $document->QrCode($document->id) !!} <br>
                                @if($directorDocument->status > 1)
                                    <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

            @include('office.journal.work.templates.signer')

            @include('office.journal.work.templates.agreement')

            @include('office.journal.work.templates.executor')

        </div>
    </div>
</div>
