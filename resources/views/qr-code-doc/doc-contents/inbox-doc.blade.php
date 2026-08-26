<div class="work-doc-subpage" id="doc_content">
    <div id="printPage" class="printPage">

        <div class="doc-text">
            @if($html_doc)
                {!! $html_doc !!}
            @endif
        </div>

        <div class="table-tr-3td exec-user">
            <table>
                <tr>
                    <td>
                        <div class="text-left">
                            <div class="doc-performer">
                                @switch($document->doc_lang)
                                    @case('uz')
                                        <span>Ijrochi:</span>
                                        @break
                                    @case('kr')
                                        <span>Ижрочи:</span>
                                        @break
                                    @case('ru')
                                        <span>Исполнитель:</span>
                                        @break
                                    @case('en')
                                        <span>Performer:</span>
                                        @break
                                @endswitch
                                {{ $journal->fromUser?->personal?->shortNameLang($document->doc_lang)??'' }}
                            </div>

                            <div class="doc-tel">
                                @switch($document->doc_lang)
                                    @case('uz')
                                        Telefon raqam:
                                        @break
                                    @case('kr')
                                        Телефон рақам:
                                        @break
                                    @case('ru')
                                        Номер телефона:
                                        @break
                                    @case('en')
                                        Phone number:
                                        @break
                                @endswitch
                                {{ $journal->fromUser?->personal?->phone??'-' }})
                            </div>
                        </div>
                    </td>
                    <td class="hr-prt-docs-qr">{!! $document->execQrCode($document->id) !!}</td>
                    <td></td>
                </tr>
            </table>
        </div>

        <br><br>
    </div>
</div>
