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
        @page { margin: 2cm 1.5cm 2cm 3cm; padding: 0}
        .work-doc-subpage { padding: 0; margin: 0}
        .table-tr-3td-print table { font-size: 12px}
    </style>
</head>
<body>

<div class="work-doc-subpage">
    <div id="printPage" class="printPage">

        @if($journal->docStatus->doc_code !== 'OPEN')
            <div class="doc-confidentiality">
                {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
            </div>
        @endif

        <div class="sent-doc-date-number">
            {{ date('d.m.Y', strtotime($document->doc_date)) }}
        </div>

        <div class="table-tr-3td-print">
            <table>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                        <p>{{ $directorDocument->toUser->personal->shortName()??''}}</p>

                        <p>
                            {!! $directorDocument->printSignQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                            @if($directorDocument->status > 2)
                                <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                            @endif
                        </p>

                        {{ $journal->fromUser->userPost->lang($journal->fromUser->userPost->id)??'' }}
                        <p>{{ $journal->fromUser->personal->shortName()??'' }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="doc-text">
            {!! $document->doc_text !!}
        </div>

            <div class="table-tr-3td-print doc-ag-users">
                <table>
                    @foreach($agreement_users as $key => $item)
                        <tr>
                            <td>
                                {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                            </td>
                            <td class="hr-prt-docs-qr">
                                {!! $item->printSignQrCode($document->id, $item->to_user, $item->id) !!}
                            </td>
                            <td>
                                {{ $item->toUser->personal->shortName()??'' }}<br>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <div class="table-tr-3td-print exec-user">
                <table>
                    <tr>
                        <td>
                            <div class="text-left">
                                <div class="doc-performer">
                                    @if($journal->journal->doc_type === 'NOTIFICATION')
                                        <span>{{ __('doc_role.inserter') }}:</span>
                                    @elseif(str_contains($journal->journal->doc_code, 'APP'))
                                        <span>{{ __('doc_role.applicant') }}:</span>
                                    @else
                                        <span>{{ __('doc_role.executor') }}:</span>
                                    @endif
                                    {{ $journal->fromUser->personal->shortName()??'' }}
                                </div>

                                <div class="doc-tel">
                                    {{ __('data.tel_number') }} ({{ __('data.tel_in') }}
                                    {{ $journal->fromUser->personal->phone??'-' }})
                                </div>
                            </div>
                        </td>
                        <td class="hr-prt-docs-qr">
                            {!! $execQrCode !!}
                        </td>
                        <td></td>
                    </tr>
                </table>
            </div>

    </div>
</div>

</body>
</html>
