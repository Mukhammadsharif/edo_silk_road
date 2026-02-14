<div class="app-executor-modern">
    <table class="app-table">
        <tr>
            <td class="date-cell">
                <span class="date-label">{{ __('field.date') ?? 'Дата' }}:</span>
                <span class="date-value">{{ date('d.m.Y', strtotime($document->doc_date)) }}</span>
            </td>
            <td class="qr-cell">
                <div class="qr-wrapper">
                    {!! $document->execQrCode($document->id) !!}
                </div>
            </td>
            <td class="name-cell">
                <span class="name-value">{{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}</span>
            </td>
        </tr>
    </table>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/app_executor.css') }}">
