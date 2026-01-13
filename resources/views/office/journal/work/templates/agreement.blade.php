@if(count($agreement_users))
    <div class="agreement-section-modern">
        <div class="section-title-print">{{ __('title.agreement_employees') }}:</div>
        <table class="signature-table">
            @foreach($agreement_users as $key => $item)
                <tr class="{{ $loop->even ? 'row-alt' : '' }}">
                    <td class="position-cell">
                        <span class="position-label">{{ __('field.position') ?? 'Должность' }}:</span>
                        <span class="position-value">{{ $item->toUser->userPost->docLang($item->toUser->userPost->id, $document->doc_lang)??'' }}</span>
                    </td>
                    <td class="qr-cell">
                        <div class="qr-wrapper">
                            {!! $item->signQrCode($document->id, $item->to_user, $item->id) !!}
                        </div>
                    </td>
                    <td class="name-cell">
                        <span class="name-value">{{ $item->toUser->personal->shortNameLang($document->doc_lang)??'' }}</span>
                        <span class="status-badge-print {{ $item->statusSign->badgeClass??'' }}">
                            {{ $item->statusSign->lang($item->statusSign->id)??'' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/agreement.css') }}">
