<div class="header-verifier-modern">
    <table class="header-table">
        <tr>
            <td class="empty-cell"></td>
            <td class="qr-cell">
                <div class="qr-wrapper">
                    {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!}
                </div>
                @if($directorDocument->status > 2)
                    <div class="sign-date">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</div>
                @endif
            </td>
            <td class="info-cell">
                <span class="position-text">{{ $directorDocument->toUser->userPost->doclang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}</span>
                <span class="name-text">{{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}</span>
            </td>
        </tr>
    </table>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header-verifier.css') }}">
