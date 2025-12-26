<!DOCTYPE html>
<html>
@include('qr-code-doc.qr-head')

<body class="hold-transition skin-blue layout-top-nav">

<div class="wrapper">
    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-1">
                    <div class="card table-card" style="max-width: 794px;margin: 0 auto">

                        <div class="card-header">
                            <h3 class="text-center"></h3>
                        </div>

                        <div class="card-body">

                            @include('office.journal.work.templates.org_logo_banner')

                            <div class="sent-doc-date-number mt-10">
                                {{ date('d.m.Y', strtotime($document->doc_date)) }} yil.
                            </div>

                            <div class="doc-text">
                                @if($html_doc)
                                    {!! $html_doc !!}
                                @endif
                            </div>

                            <div class="table-tr-3td">
                                <table>
                                    <tr>
                                        <td>
                                            {{ $directorDocument->toUser->userPost->name_uz??'' }}
                                        </td>
                                        <td>
                                            <p>
                                                {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!}
                                            </p>
                                        </td>
                                        <td>
                                            {{ $directorDocument->toUser->personal->l_name??''}}
                                            {{ $directorDocument->toUser->personal->f_name??''}}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
