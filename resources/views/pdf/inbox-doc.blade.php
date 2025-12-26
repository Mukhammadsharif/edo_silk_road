<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ config('app.name', 'EAI QrCode Document') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/dist/css/theme.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/dist/css/main.css') }}"/>
    <style>
        @page { margin: 2cm 1.5cm 2cm 3cm; padding: 0 }
        .work-doc-subpage { margin: 0; padding: 0 }
        .table-tr-3td-print table { font-size: 12px}
    </style>
</head>
<body>
<div class="work-doc-subpage">
    <div id="printPage" class="printPage">

        @if($journal->docStatus->doc_code !== 'OPEN')
            <div class="doc-confidentiality">
                {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'' }}
            </div>
        @endif

        <div class="sent-doc-date-number">
            {{ date('d.m.Y', strtotime($document->doc_date)) }}
        </div>

        <div class="doc-text">
            {!! $doc_text !!}
        </div>
    </div>
</div>
</body>
</html>
