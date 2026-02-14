@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
@section('content')
    <div class="work-view-page-modern">
        @include('office.journal.work.templates.header')

        <div class="document-view-layout">
            <!-- Left Sidebar -->
            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <!-- Document Content -->
            <div class="document-content-section" id="pdf1">
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

                            @if(in_array($journal->journal->doc_code, ['ORDER_F', 'STATEMENT', 'HR_ORD_MT_META']))
                                @include('office.journal.work.templates.verifier')
                            @endif

                            @include('office.journal.work.templates.signer')

                            @include('office.journal.work.templates.agreement')

                            @include('office.journal.work.templates.executor')

                        </div>
                    </div>
                </div>

                {{--<a href="{{ route('download-only-office.pdf', ['onlyDocId' => $model->id]) }}" class="btn btn-primary">
                    Download PDF
                </a>--}}
            </div>

            <!-- Right Sidebar -->
            <div class="view-sidebar view-sidebar-right">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-right')
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
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

<style>
    @media screen and (max-width: 1030px) {
        .work-doc-page {
            margin-left: 0 !important;
        }
    }

     @media screen and (max-width: 1024px) {
        .work-doc-page {
            margin-left: 0 !important;
        }
    }
</style>
