<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ config('app.name', 'UzMRC QrCode Document') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/dist/css/theme.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('/assets/dist/css/main.css') }}"/>
    <style>
        @page { margin: 2cm 1.5cm 2cm 3cm; padding: 0; }
        .work-doc-subpage { margin: 0; padding: 0 }
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

        @if(!in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT']))
            <div class="table-tr-3td-print">
                <table>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>
                            {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                            <br>
                            {{ $directorDocument->toUser->personal->shortName()??''}}
                            <p>
                                {!! $directorDocument->printSignQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                                @if($directorDocument->status > 2)
                                    <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        @endif


        <div class="sent-doc-date-number">
            {{ date('d.m.Y', strtotime($document->doc_date)) }}
        </div>


        @if($journal->journal->doc_code === 'ORDER_F' && $document->dep)
            <div class="table-tr-3td-print">
                <table>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>
                            @switch($document->doc_lang)
                                @case('uz')
                                    {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}  ga
                                    @break
                                @case('kr')
                                    {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}  га
                                    @break
                                @case('ru')
                                    к {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}
                                    @break
                                @case('en')
                                    to {{ $document->dep->department->docLang($document->dep->department->id, $document->doc_lang)??'' }}
                                    @break
                            @endswitch
                        </td>
                    </tr>
                </table>
            </div>
        @endif


        <div class="doc-text">
            {!! $doc_text !!}
        </div>

            @if(in_array($journal->status, [9, 10]) && in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT']))
                <div class="table-tr-3td-print">
                    <table>
                        <tr>
                            <td>
                                {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                            </td>
                            <td>
                                {!! $directorDocument->printSignQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                                @if($directorDocument->status > 1)
                                    <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $directorDocument->toUser->personal->shortName()??''}}
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

            @if($signer)
                <div class="table-tr-3td-print doc-ag-users">
                    <table>
                        <tr>
                            <td>
                                {{ $signer->toSign->userPost->lang($signer->toSign->userPost->id)??'' }}
                            </td>
                            <td class="hr-prt-docs-qr">
                                {!! $signer->printSignQrCode($document->id, $signer->to_user, $signer->id) !!}
                            </td>
                            <td>
                                {{ $signer->toSign->personal->shortName()??'' }}<br>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

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
                                    @else
                                        <span>{{ __('doc_role.executor') }}:</span>
                                    @endif
                                    {{ $journal->fromUser->personal->shortName()??'' }}
                                </div>

                                @if($journal->fromUser->personal->phone)
                                    <div class="doc-tel">
                                        {{ __('data.tel_number') }} ({{ __('data.tel_in') }}
                                        {{ $journal->fromUser->personal->phone??'-' }})
                                    </div>
                                @endif
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
