@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.inbox') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="w-80">
                        @include('templates.alert')
                    </div>
                </div>
                <div class="col-md-3">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('office.inbox-docs') }}">{{ __('title.inbox') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            @include('office.journal.view-left')

            <div class="col-md-6 printPage" id="printPage">
                <div class="card">
                    <div class="card-header"><h3>{{ __('field.doc_text') }}</h3></div>
                    <div class="card-body">
                        @forelse($documentFiles as $key => $file)
                            @switch(strtolower($file->file_ext))
                                @case('jpg')
                                @case('jpeg')
                                @case('png')
                                @case('pdf')
                                    {{ $file->file_name }}
                                    <iframe src="{{ route('file.pre-view',['id'=>$file->id]) }}"
                                            style="width: 100%; height: 800px;"></iframe>
                                    <hr>
                                    @break
                                    @break
                            @endswitch
                        @empty

                        @endforelse
                    </div>
                </div>
            </div>

            @include('office.journal.view-right')
        </div>

    </div>

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
