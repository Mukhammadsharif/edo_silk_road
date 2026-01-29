@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-arrow-up-right bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('title.outgoing') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-3">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('office.outgoing-docs') }}">{{ __('title.outgoing') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">

            @include('templates.mini_editor_js')

            @include('office.journal.view-left')

            <div class="col-md-6">
                @include('office.templates.sent-doc-body')
            </div>

            <div class="col-md-4">
                @if($directorDocument)
                    <div class="card table-card">
                        <div class="card-header">
                            <h3>{{ __('title.signing_status') }}</h3>
                        </div>

                        <div class="p-4 border-top mt-15">
                            <div class="row text-center">
                                <div class="col-4 border-right">
                                    <p>{{ __('status.exec_status') }}</p>
                                    <span class="badge {{ $journal->statusOut->badgeClass??'' }}">
                                        {{ $journal->statusOut->lang($journal->statusOut->id)??'unk' }}
                                    </span>
                                </div>
                                <div class="col-4 border-right">
                                    <p>
                                        @if($journal->status === 1)
                                            {{ __('field.created_at') }}
                                        @else
                                            {{ __('field.date_sent_for_signature') }}
                                        @endif
                                    </p>
                                    <i data-lucide="clock"></i>
                                    {{ \Carbon\Carbon::parse($directorDocument->updated_at)->format('d.m.y, H:i') }}
                                </div>
                                <div class="col-4">
                                    <p>
                                        {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                                    </p>
                                    <h6 class="fw-700">{{ $directorDocument->toUser->personal->shortName()??'' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($agreement_users && $agreement_users->count())
                    <div class="card">
                        <div class="card-header">
                            <h3>{{ __('title.coordination') }}</h3>
                        </div>

                        <div class="card-block">
                            @foreach($agreement_users as $key => $item)
                                <div class="sl-item">
                                    <div class="sl-left fw-600 f-16">{{ $item->toUser->personal->fullName()??'' }}</div>
                                    <div class="sl-right">
                                        <p class="text-muted">
                                            {{ $item->toUser->department->lang($item->toUser->department->id)??'' }}
                                            - {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                                        </p>
                                        @if(count($item->replyDocs($item->doc_id, $item->to_user)) > 0)
                                            @foreach($item->replyDocs($item->doc_id, $item->to_user) as $key => $doc)
                                                @if($t->res_text)
                                                    <p class="mt-2">
                                                        {!! $doc->res_text !!}
                                                    </p>
                                                @endif
                                                <div>
                                                    <span class="badge badge-pill {{ $doc->statusReply->bg_class??'' }}">
                                                        {{ __($doc->statusReply->lang_code??'unk') }}
                                                    </span>
                                                    @if($doc && $doc->status == 5 && $journal->workEcpInfo($document->id, $doc->from_user)
                                                        && $journal->workEcpInfo($document->id, $doc->from_user)->docInfo)
                                                        <i data-lucide="feather" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $doc->id }})"></i>

                                                        @include('templates.ecp.ecp-information')
                                                    @endif
                                                </div>
                                                <div class="mt-10">
                                                <span class="sl-date pull-right"><i data-lucide="clock"></i>
                                                    {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
                                                </span>
                                                </div><hr>
                                            @endforeach
                                        @else
                                            <div class="mt-10">
                                                <span class="badge badge-pill {{ $item->statusSign->badgeClass??'' }}">
                                                    {{ $item->statusSign->lang($item->statusSign->id)??'unk' }}
                                                </span>
                                                @php($signer = $item)
                                                @if($signer && $signer->status == 5 && $journal->workEcpInfo($document->id, $signer->to_user)
                                                    && $journal->workEcpInfo($document->id, $signer->to_user)->docInfo)
                                                    <i data-lucide="feather" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $signer->id }})"></i>

                                                    @include('templates.ecp.signer-ecp-information')
                                                @endif
                                            </div>
                                            @if($item->status != (int)1)
                                                <div class="mt-10">
                                                    <span class="sl-date pull-right"><i data-lucide="clock"></i>
                                                        {{ date('d.m.y, H:i', strtotime($item->updated_at)) }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <hr>
                            @endforeach
                        </div>
                    </div>
                @endif

                @include('templates.comment_form')

                <div class="card table-card">
                    <div class="card-header">
                        <h3>{{ __('action.action') }}</h3>
                    </div>
                    <div class="card-body">
                        <button type="button" onclick="printContent()" class="btn btn-outline-secondary btn-rounded mr-5">
                            <i data-lucide="printer"></i> {{ __('action.print') }}
                        </button>

                        <button type="button" onclick="downloadAsPDF()" class="btn btn-outline-dark m-1 btn-rounded">
                            <i data-lucide="download"></i> {{ __('action.download') }}
                        </button>
                        <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>

                        @if(in_array('IT admin', $authrole) && $journal->status != 13)
                            <button type="button" class="btn btn-outline-danger btn-rounded mr-5" data-toggle="modal" data-target="#cancelModel" data-id="{{ $journal->doc_id }}">
                                <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                            </button>
                        @endif
                        <br><br>
                    </div>
                </div>

                @include('office.templates.doc-steps')
            </div>

        </div>

        <div class="modal fade" id="cancelModel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="cancelForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4>{{ __('action.delete') }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <!-- Modal Body -->
                        <div class="modal-body">
                            <input type="hidden" id="cancel_id" name="doc_id" value="">
                            <p><i class="ik ik-message-circle"></i> Siz, tanlagan yozuvlar o`chiriladi!</p>
                        </div>
                        <!-- Modal Footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">
                                <i class="ik ik-x"></i>{{ __('action.close') }}
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="ik ik-trash-2"></i>{{ __('action.yes_delete') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('modals.delete-document')

    @include('office.journal.view-js')

    @include('print.print')

    @include('print.iframe-print')

    @include('qr-code-doc.qr-code-view-js')

    @include('e-imzo.e-imzo')

    @include('office.journal.view-js')

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
                if (btn_id === 2) {
                    confirm_type = "{{ __('cancel') }}";
                    modal_bg_class = "btn-outline-warning";
                } else if (btn_id === 3) {
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
