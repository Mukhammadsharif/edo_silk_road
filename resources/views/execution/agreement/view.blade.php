@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">

@section('content')
    <div class="work-view-page-modern">
        @include('office.journal.work.templates.header')

        <div class="document-view-layout">

            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <div class="document-content-section" id="pdf1">
                @if(str_contains($journal->journal->doc_code, 'APP'))
                    @include('execution.agreement.templates.app-page')
                @elseif(str_contains($journal->journal->doc_code, 'COMMAND'))
                    @include('execution.agreement.templates.order-page')
                @elseif(str_contains($journal->journal->doc_code, 'BASE_INCOMING'))
                    @include('execution.confirm.templates.inbox-doc-page')
                @elseif(str_contains($journal->journal->doc_code, 'BASE_OUTGOING'))
                    @include('execution.agreement.templates.sent-doc-page')
                @elseif(str_contains($journal->journal->doc_code, 'HR_ORD'))
                    @include('execution.confirm.templates.hr-order-page')
                @elseif(str_contains($journal->journal->doc_code, 'REPORT_FORM'))
                    @include('execution.agreement.templates.report-form')
                @else
                    @include('execution.agreement.templates.work-doc-page')
                @endif
            </div>

            @include('templates.mini_editor_js')

            <div class="view-sidebar view-sidebar-right">
                <div class="sidebar-card-modern">
                    @include('execution.agreement.templates.view-right')
                </div>
            </div>
        </div>

    </div>

    @include('modals.delete-document')

    @include('office.journal.view-js')

    @include('print.print')

    @include('qr-code-doc.qr-code-view-js')

    @include('print.iframe-print')

    @include('e-imzo.e-imzo')

    <script type="text/javascript">
        $('.modal').each(function () {
            $(this).appendTo('body');
        });

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
                $('#ecp_confirm_id').val(btn_id);
            });
        });
    </script>

@endsection
