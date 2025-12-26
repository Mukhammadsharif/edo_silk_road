<div class="sent-doc-page">
    <div class="sent-doc-subpage">
        <div id="printPage" class="printPage">
            <div class="sent-doc-top-banner">
                <img src="{{ $sentDocImage }}">
            </div>

            <div class="sent-doc-date-number">
                № {{ $document->doc_number }}<br>
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
{{--                                    <span>{{ __('field.performer') }}:</span>--}}
{{--                                    {{ $journal->fromUser->personal->shortName1()??'' }}--}}
                                </div>
                            </div>
                        </td>
                        <td class="hr-prt-docs-qr">
                        </td>
                        <td></td>
                    </tr>
                </table>
            </div>

        </div>

    </div>
</div>
