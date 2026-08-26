@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/view-app.css') }}">
@section('content')
    <div class="work-view-page-modern">
        @include('office.journal.work.templates.header')

        <div class="document-view-layout">
            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <div class="document-content-section">
                <div class="document-card-modern">
                    <div class="sent-doc-modern">
                        <div class="document-container-modern">
                            <div id="printPage" class="print-page-modern">
                                @if($journal->docStatus->doc_code !== 'OPEN')
                                    <div class="doc-confidentiality">
                                        {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                                    </div>
                                @endif

                                <div class="table-tr-3td">
                                    <table>
                                        <tr>
                                            <td></td>
                                            <td>
                                                <p>
                                                    {!! $directorDocument?->signQrCode($document->id, $directorDocument?->to_user, $directorDocument?->id) !!}
                                                    <br>
                                                    @if($directorDocument->status > 2)
                                                        <span
                                                            class="font-size-12">{{ date('d.m.Y', strtotime($directorDocument->updated_at)) }}</span>
                                                    @endif
                                                </p>
                                            </td>
                                            <td>
                                                {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
                                                <p>{{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}</p>


                                                {{ $journal->fromUser->userPost->docLang($journal->fromUser->userPost->id, $document->doc_lang)??'' }}
                                                <p>{{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}</p>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="doc-text">
                                    @if($html_doc)
                                        {!! $html_doc !!}
                                    @endif
                                </div>

                                @include('office.journal.work.templates.app_executor')

                                @include('office.journal.work.templates.agreement')

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="view-sidebar view-sidebar-right">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-right')
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        });
    </script>

    @include('modals.delete-document')

    @include('office.journal.view-js')

    @include('print.print')

    @include('qr-code-doc.qr-code-view-js')

    @include('print.iframe-print')

    @include('e-imzo.e-imzo')

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
                let modal_bg_class = "btn-success";
                if (btn_id === -5) {
                    confirm_type = "{{ __('cancel') }}";
                    modal_bg_class = "btn-outline-warning";
                } else if (btn_id === -6) {
                    confirm_type = "{{ __('re-editing') }}";
                    modal_bg_class = "btn-outline-warning";
                }
                $('.modal-bg-class').removeClass().addClass(`btn-modern modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val();
            });
        });
    </script>
@endsection
