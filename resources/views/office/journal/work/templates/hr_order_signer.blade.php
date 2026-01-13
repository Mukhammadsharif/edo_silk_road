<div class="hr-signer-modern">
    <table class="signature-table">
        <tr>
            <td class="position-cell">
                <span class="position-label">{{ __('field.position') ?? 'Должность' }}:</span>
                <span class="position-value">{{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}</span>
            </td>
            <td class="qr-cell">
                <div class="qr-wrapper">
                    {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!}
                </div>
            </td>
            <td class="name-cell">
                <span class="name-value">{{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}</span>
            </td>
        </tr>
    </table>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/hr_order_signer.css') }}">
