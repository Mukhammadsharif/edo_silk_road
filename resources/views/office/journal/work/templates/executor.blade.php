<div class="executor-section-modern">
    <table class="executor-table">
        <tr>
            <td class="executor-info-cell">
                <div class="executor-info">
                    <div class="executor-role">
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
                        <span class="executor-name">{{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}</span>
                    </div>

                    <div class="executor-phone">
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
                        <span class="phone-number">{{ $journal->fromUser->personal->phone??'-' }}</span>
                    </div>
                </div>
            </td>
            <td class="qr-cell">
                <div class="qr-wrapper">
                    {!! $document->execQrCode($document->id) !!}
                </div>
            </td>
            <td class="empty-cell"></td>
        </tr>
    </table>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/executor.css') }}">
