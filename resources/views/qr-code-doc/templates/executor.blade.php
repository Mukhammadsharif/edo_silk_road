<div class="table-tr-3td exec-user">
    <table>
        <tr>
            <td>
                <div class="text-left">
                    <div class="doc-performer">
                        @switch($document->doc_lang)
                            @case('uz')
                                @if($journal->journal->doc_type === 'NOTIFICATION')
                                    <span>Kirituvchi:</span>
                                @elseif(str_contains($journal->journal->doc_code, 'APP'))
                                    <span>Ariza beruvchi:</span>
                                @else
                                    <span>Yaratuvchi:</span>
                                @endif
                                @break
                            @case('kr')
                                @if($journal->journal->doc_type === 'NOTIFICATION')
                                    <span>Киритувчи:</span>
                                @elseif(str_contains($journal->journal->doc_code, 'APP'))
                                    <span>Ариза берувчи:</span>
                                @else
                                    <span>Яратувчи:</span>
                                @endif
                                @break
                            @case('ru')
                                @if($journal->journal->doc_type === 'NOTIFICATION')
                                    <span>Вводящий:</span>
                                @elseif(str_contains($journal->journal->doc_code, 'APP'))
                                    <span>Заявитель:</span>
                                @else
                                    <span>Создатель:</span>
                                @endif
                                @break
                            @case('en')
                                @if($journal->journal->doc_type === 'NOTIFICATION')
                                    <span>Inserter:</span>
                                @elseif(str_contains($journal->journal->doc_code, 'APP'))
                                    <span>Applicant:</span>
                                @else
                                    <span>Creator:</span>
                                @endif
                                @break
                        @endswitch
                        {{ $journal->fromUser->personal->shortName()??'' }}
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
            <td class="hr-prt-docs-qr">
                {!! $document->execQrCode($document->id) !!}
            </td>
            <td></td>
        </tr>
    </table>
</div>
