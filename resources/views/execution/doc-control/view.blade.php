@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @include('office.journal.work.templates.header')

        <div class="row">

            @include('office.journal.view-left')

            <div class="col-md-6" id="pdf1">
                @if(str_contains($journal->journal->doc_code, 'APP'))
                    @include('execution.confirm.templates.app-page')
                @elseif(str_contains($journal->journal->doc_code, 'COMMAND'))
                    @include('execution.confirm.templates.order-page')
                @elseif(str_contains($journal->journal->doc_code, 'BASE_INCOMING'))
                    @include('execution.confirm.templates.inbox-doc-page')
                @elseif(str_contains($journal->journal->doc_code, 'BASE_OUTGOING'))
                    @include('execution.confirm.templates.sent-doc-page')
                @elseif(str_contains($journal->journal->doc_code, 'HR_ORD'))
                    @include('execution.confirm.templates.hr-order-page')
                @elseif(str_contains($journal->journal->doc_code, 'REPORT_FORM'))
                    @include('execution.confirm.templates.report-form')
                @else
                    @include('execution.confirm.templates.work-doc-page')
                @endif
            </div>

            @include('execution.doc-control.templates.view-right')
        </div>

    </div>

    @include('execution.doc-control.templates.view-js')

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
                let modal_bg_class = "btn-outline-success";
                if (btn_id === 2) {
                    confirm_type = "{{ __('action.cancel') }}";
                    modal_bg_class = "btn-outline-warning";
                } else if (btn_id === 3) {
                    confirm_type = "{{ __('action.re-editing') }}";
                    modal_bg_class = "btn-outline-warning";
                }
                $('.modal-bg-class').removeClass().addClass(`btn modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val(btn_id);
            });
        });
    </script>

@endsection
