<div class="work-doc-subpage" id="doc_content">
    <div id="printPage" class="printPage">

        <div class="sent-doc-top-banner">
            <img src="{{ $logoImage }}" alt="" style="width: 10% !important">
        </div>

        <div class="sent-doc-date-number">
            № {{ $journal->createdUser->department->dep_code_doc??'' }}/{{ $journal->j_number }}<br>
            {{ date('d.m.Y', strtotime($document->doc_date)) }}
        </div>

        <div class="doc-text">
            @if($html_doc)
                {!! $html_doc !!}
            @endif
        </div>

        <div class="table-tr-3td">
            <table>
                <tr>
                    <td>
                        {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
                    </td>
                    <td>
                        {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!}
                    </td>
                    <td>
                        {{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??'' }}
                    </td>
                </tr>
            </table>
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
                                {{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}
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
                                {{ $journal->fromUser->personal->phone??'-' }})
                            </div>
                        </div>
                    </td>
                    <td class="hr-prt-docs-qr">{!! $document->execQrCode($document->id) !!}</td>
                    <td></td>
                </tr>
            </table>
        </div>

        <br><br>

        <div class="sent-doc-footer-text">
            O`zbekiston, 100017, Toshkent shahri, Abdulla Qodiriy ko`chasi, 3A, www.uzmrc.uz | info@eai.uz | Tel: 1147
        </div>
    </div>
</div>
