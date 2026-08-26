<div class="table-tr-3td">
    <table>
        <tr>
            <td></td>
            <td></td>
            <td>
                {{ $directorDocument?->toUser?->userPost?->docLang($directorDocument?->toUser?->userPost?->id, $document->doc_lang) ?? '' }}
                <br>
                {{ $directorDocument?->toUser?->personal?->shortNameLang($document->doc_lang) ?? ''}}
                <p>
                    {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                    @if($directorDocument->status > 2)
                        <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                    @endif
                </p>

                @if(str_contains($journal->journal->doc_code, 'APP'))
                    {{ $journal->fromUser->userPost->docLang($journal->fromUser->userPost->id, $document->doc_lang)??'' }}
                    <p>{{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>
