<div class="table-tr-3td">
    <table>
        <tr>
            <td>
                {{ $directorDocument?->toUser?->userPost?->docLang($directorDocument?->toUser?->userPost?->id, $document->doc_lang) ?? '' }}
            </td>
            <td>
                {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                @if($directorDocument->status > 1)
                    <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                @endif
            </td>
            <td>
                {{ $directorDocument?->toUser?->personal?->shortNameLang($document->doc_lang) ?? ''}}
            </td>
        </tr>
    </table>
</div>
