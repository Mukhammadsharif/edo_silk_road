<!DOCTYPE html>
<html>
<body class="hold-transition skin-blue layout-top-nav">

<div class="wrapper">
    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="work-doc-page">
                        <div class="work-doc-subpage">
                            <div class="printPage">

                                @include('office.journal.work.templates.org_logo_banner')

                                <div class="sent-doc-date-number mt-10" style="padding-left: 40px; margin-top: 20px">
                                    {{ date('d.m.Y', strtotime($document->doc->doc_date)) }} yil.
                                </div>

                                <div class="doc-text" style="padding-left: 40px">
                                    @if($html_doc)
                                        {!! $html_doc !!}
                                    @endif
                                </div>

                                <div class="table-tr-3td" style="width: 100%; padding-left: 80px">
                                    <table style="width: 100%">
                                        <tr>
                                            <td>
                                                {{ $directorDocument->toUser->userPost->name_uz??'' }}
                                            </td>
                                            <td>
                                                <p>
                                                    {!! $directorDocument->signQrCode($document->doc_id, $directorDocument->to_user, $directorDocument->id) !!}
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
</div>
</body>
</html>
