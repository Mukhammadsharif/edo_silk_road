@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @include('office.journal.work.templates.header')

        <div class="row">

            @include('office.journal.view-left')
            <div class="col-md-6" id="pdf1">
                <div class="work-doc-page" id="pdf2">
                    <div class="work-doc-subpage" id="pdf3">
                        <div id="printPage" class="printPage">

                            @if($journal->docStatus->doc_code !== 'OPEN')
                                <div class="doc-confidentiality">
                                    {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                                </div>
                            @endif

                            @if(!in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT', 'INF_DOC', 'HR_ORD_MT_META']))
                                @include('office.journal.work.templates.header-verifier')
                            @endif

                            <div class="sent-doc-date-number">
                                {{ date('d.m.Y', strtotime($document->doc_date)) }}
                            </div>

                            @if($journal->journal->doc_code === 'ORDER_F' && $document->dep)
                                <div class="table-tr-3td">
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

                            <div class="doc-text" id="pdf4">
                                @if($html_doc)
                                    {!! $html_doc !!}
                                @endif
                            </div>

{{--                            <div class="doc-text" id="pdf4">--}}
{{--                                {!! str_replace('&nbsp;', ' ', $document->doc_text) !!}--}}
{{--                            </div>--}}

{{--                            @include('office.journal.work.templates.document-files')--}}

                            @if(in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT','HR_ORD_MT_META']))
                                @include('office.journal.work.templates.verifier')
                            @endif

                            @include('office.journal.work.templates.signer')

                            @include('office.journal.work.templates.agreement')

                            @include('office.journal.work.templates.executor')

                        </div>
                    </div>
                </div>
            </div>

            @include('templates.mini_editor_js')

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
                $('.modal-bg-class').removeClass().addClass(`btn-modern modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val();
            });
        });
    </script>

@endsection
