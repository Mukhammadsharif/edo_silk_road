@if($signer)
    <div class="signature-section-modern">
        <table class="signature-table">
            <tr>
                <td class="position-cell">
                    <span class="position-label">{{ __('field.position') ?? 'Должность' }}:</span>
                    <span class="position-value">{{ $signer->toSign->userPost->docLang($signer->toSign->userPost->id, $document->doc_lang)??'' }}</span>
                </td>
                <td class="qr-cell">
                    <div class="qr-wrapper">
                        {!! $signer->signQrCode($document->id, $signer->to_user, $signer->id) !!}
                    </div>
                </td>
                <td class="name-cell">
                    <span class="name-value">{{ $signer->toSign->personal->shortNamelang($document->doc_lang)??'' }}</span>
                    <span class="status-badge-print {{ $signer->statusSign->badgeClass??'' }}">
                        {{ $signer->statusSign->lang($signer->statusSign->id)??'' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/signer.css') }}">
@endif
