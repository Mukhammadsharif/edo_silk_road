{{-- Telegram Mini App: self-contained document render.
     Reuses the SAME templates the web app uses, selected by doc_code.
     CSS is inlined so it renders correctly inside the Mini App webview. --}}
<style>
    .tg-doc { font-family: 'Times New Roman', Times, serif; color: #1a1a1a; background: #fff; }
    .tg-doc .printPage { padding: 4px; }
    .tg-doc .doc-confidentiality {
        text-align: right; font-weight: 700; text-transform: uppercase;
        color: #b91c1c; font-size: 12px; margin-bottom: 10px;
    }
    .tg-doc .work-doc-page, .tg-doc .work-doc-subpage { width: 100%; }
    .tg-doc .table-tr-3td table { width: 100%; border-collapse: collapse; margin: 8px 0; }
    .tg-doc .table-tr-3td td { vertical-align: top; padding: 4px 6px; font-size: 12px; }
    .tg-doc .table-tr-3td td:last-child { text-align: right; }
    .tg-doc .sent-doc-date-number { font-size: 12px; margin: 8px 0; }
    .tg-doc .doc-text { margin: 14px 0; font-size: 13px; line-height: 1.7; text-align: justify; }
    .tg-doc .doc-text p { margin: 0 0 8px; }
    .tg-doc .font-size-12 { font-size: 11px; color: #444; }
    .tg-doc img { max-width: 100%; height: auto; }
    .tg-doc .qr-wrapper img, .tg-doc .qr-cell img, .tg-doc table td img { max-width: none; }

    /* QR — fixed size, never compress */
    .tg-doc .qr-wrapper svg, .tg-doc .qr-wrapper img {
        width: 70px !important; height: 70px !important;
        min-width: 70px; min-height: 70px; max-width: none !important;
        display: block;
    }
    .tg-doc table svg, .tg-doc table img {
        width: 70px !important; height: 70px !important;
        min-width: 70px; max-width: none !important;
    }
    .tg-doc img[src*="signature"] { width: 56px !important; height: auto !important; min-width: 56px; max-width: none !important; }
    .tg-doc .qr-cell { text-align: center; width: 84px; min-width: 84px; }
    .tg-doc .qr-wrapper { display: inline-block; }

    /* Signature / agreement / executor tables */
    .tg-doc .signature-table, .tg-doc .executor-table, .tg-doc .app-table {
        width: 100%; border-collapse: collapse; margin: 10px 0;
    }
    .tg-doc .signature-table td, .tg-doc .executor-table td, .tg-doc .app-table td {
        vertical-align: middle; padding: 6px; font-size: 12px;
    }
    .tg-doc .position-cell { width: 45%; }
    .tg-doc .name-cell { text-align: right; }
    .tg-doc .position-label, .tg-doc .date-label, .tg-doc .position-label { color: #666; font-size: 11px; display: block; }
    .tg-doc .position-value, .tg-doc .name-value, .tg-doc .date-value { font-weight: 600; }
    .tg-doc .section-title-print { font-weight: 700; margin: 14px 0 6px; font-size: 13px; }
    .tg-doc .status-badge-print, .tg-doc .badge {
        display: inline-block; padding: 2px 8px; border-radius: 10px;
        font-size: 10px; font-weight: 600; margin-top: 4px;
    }
    .tg-doc .badge-success, .tg-doc .bg-success { background: #d1fae5; color: #065f46; }
    .tg-doc .badge-warning, .tg-doc .bg-warning { background: #fef3c7; color: #92400e; }
    .tg-doc .badge-danger,  .tg-doc .bg-danger  { background: #fee2e2; color: #991b1b; }
    .tg-doc .row-alt { background: #f8fafc; }
    .tg-doc .agreement-section-modern, .tg-doc .executor-section-modern,
    .tg-doc .app-executor-modern { margin-top: 14px; }
    .tg-doc .executor-role span { font-weight: 600; font-size: 12px; }

    /* Files (incoming) */
    .tg-doc .doc-file-row { display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 6px; font-size: 12px; }
</style>

<div class="tg-doc">
    @php
        $dc = $journal->journal->doc_code ?? '';
    @endphp

    @if(str_contains($dc, 'APP'))
        @include('execution.confirm.templates.app-page')
    @elseif(str_contains($dc, 'COMMAND'))
        @include('execution.confirm.templates.order-page')
    @elseif(str_contains($dc, 'BASE_INCOMING'))
        @include('execution.confirm.templates.inbox-doc-page')
    @elseif(str_contains($dc, 'BASE_OUTGOING'))
        @include('execution.confirm.templates.sent-doc-page')
    @elseif(str_contains($dc, 'HR_ORD'))
        @include('execution.confirm.templates.hr-order-page')
    @elseif(str_contains($dc, 'REPORT_FORM'))
        @include('execution.confirm.templates.report-form')
    @else
        @include('execution.confirm.templates.work-doc-page')
    @endif
</div>
