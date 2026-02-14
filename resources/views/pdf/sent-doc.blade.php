<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ config('app.name', 'UzMRC QrCode Document') }}</title>
    <link rel="stylesheet" type="text/css"
          href="{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/dist/css/theme.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/dist/css/main.css') }}"/>
    <style>
        @page { margin: 2cm 1.5cm 2cm 3cm; padding: 0 }
        .sent-doc-subpage { margin: 0; padding: 0 }
        .table-tr-3td-print table { font-size: 12px}
    </style>
</head>
<body>

<div class="sent-doc-page1">
<div class="sent-doc-subpage">
    <div class="sent-doc-top-banner">
        <img src="{{ $logoImage }}" alt="" style="width: 10% !important">
    </div>

    <div class="sent-doc-date-number">
        {{ $journal->fromUser->department->id??'' }}/{{ $journal->j_number }}<br>
        {{ date('d.m.Y', strtotime($document->doc_date)) }}
    </div>

    <div class="doc-text">
        {!! $doc_text !!}
    </div>

    <div class="table-tr-3td-print">
        <table>
            <tr>
                <td>
                    {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                </td>
                <td>
                    {!! $directorDocument->printSignQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                    @if($directorDocument->status > 2)
                        <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                    @endif
                </td>
                <td>
                    {{ $directorDocument->toUser->personal->shortName()??''}}
                </td>
            </tr>
        </table>
    </div>

    <div class="table-tr-3td-print">
        <table>
            <tr>
                <td>
                    <div class="text-left">
                        <div class="doc-performer">
                            <span>{{ __('field.executor') }}:</span>
                            {{ $journal->fromUser->personal->shortName()??'' }}
                        </div>


                        @if($journal->fromUser->personal->phone)
                            <div class="doc-tel">
                                {{ __('field.tel_number') }} ({{ __('field.tel_in') }}
                                {{ $journal->fromUser->personal->phone??'-' }})
                            </div>
                        @endif
                    </div>
                </td>
                <td class="hr-prt-docs-qr">{!! $execQrCode !!}</td>
                <td></td>
            </tr>
        </table>
    </div>

    <br><br>

    <div class="sent-doc-footer-text">
        O`zbekiston, 100017, Toshkent shahri, Abdulla Qodiriy ko`chasi,
        3A, www.eai.uz | info@eai.uz | Tel: 1147
    </div>

</div>
</div>
</body>
</html>
