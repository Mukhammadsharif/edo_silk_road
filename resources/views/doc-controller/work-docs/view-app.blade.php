@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @include('office.journal.work.templates.header')

        <div class="row">

            @include('office.journal.view-left')

            <div class="col-md-6">
                <div class="work-doc-page">
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

                            <div class="table-tr-3td">
                                <table>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <p>
                                                {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!} <br>
                                                @if($directorDocument->status > 2)
                                                    <span class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                                @endif
                                            </p>
                                        </td>
                                        <td>
                                            {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                                            <p>{{ $directorDocument->toUser->personal->shortName()??''}}</p>



                                            {{ $journal->fromUser->userPost->lang($journal->fromUser->userPost->id)??'' }}
                                            <p>{{ $journal->fromUser->personal->shortName()??'' }}</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="doc-text" id="pdf4">
                                @if($html_doc)
                                    {!! $html_doc !!}
                                @endif
                            </div>

{{--                            <div class="doc-text">--}}
{{--                                {!! $document->doc_text !!}--}}
{{--                            </div>--}}

{{--                            @include('office.journal.work.templates.document-files')--}}

                            @include('office.journal.work.templates.agreement')

                            @include('office.journal.work.templates.executor')
                        </div>
                    </div>
                </div>
            </div>
            @include('doc-controller.work-docs.view-right')
        </div>
    </div>

    @include('modals.delete-document')

    @include('office.journal.view-js')

    @include('print.print')

    @include('qr-code-doc.qr-code-view-js')

    @include('print.iframe-print')

    @include('e-imzo.e-imzo')

    @include('templates.download')

    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.openConfirmForm').on('click', function () {
                $('.confirmForm').show();
                let btn_id = $(this).data('id');
                let confirm_type = $(this).html();
                let modal_bg_class = "btn-outline-success";
                if (btn_id === -5) {
                    confirm_type = "{{ __('cancel') }}";
                    modal_bg_class = "btn-outline-warning";
                } else if (btn_id === -6) {
                    confirm_type = "{{ __('re-editing') }}";
                    modal_bg_class = "btn-outline-warning";
                }
                $('.modal-bg-class').removeClass().addClass(`btn modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val();
            });
        });
    </script>
@endsection
