@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-right.css') }}">

@section('content')
    <div class="work-view-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="send" class="text-white"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.outgoing') }}</h1>
                        <p>{{ __('message.view_outgoing_document') ?? 'Просмотр исходящего документа' }}</p>
                    </div>
                </div>
                <div class="header-center">
                    @include('templates.alert')
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <a href="{{ route('doc-controller.work-docs') }}" class="breadcrumb-item">
                            <span>{{ __('title.work_docs') }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('action.view') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <div class="document-view-layout">

            @include('templates.mini_editor_js')

            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <div class="document-content-section">
                <div class="work-doc-page" id="pdf2">
                    @include('office.templates.sent-doc-body')
                </div>
            </div>

            <div class="view-sidebar view-sidebar-right">
                @if($directorDocument)
                    <div class="execution-card-modern">
                        <div class="card-header-modern">
                            <i data-lucide="pen-tool" class="text-white"></i>
                            <h3>{{ __('title.signing_status') }}</h3>
                        </div>

                        <div class="execution-status-modern">
                            <div class="status-item-modern">
                                <div class="status-label">{{ __('status.exec_status') }}</div>
                                <span class="status-badge-modern {{ $journal->statusOut->badgeClass??'' }}">
                                    {{ $journal->statusOut->lang($journal->statusOut->id)??'unk' }}
                                </span>
                            </div>
                            <div class="status-item-modern">
                                <div class="status-label">
                                    @if($journal->status === 1)
                                        {{ __('field.created_at') }}
                                    @else
                                        {{ __('field.date_sent_for_signature') }}
                                    @endif
                                </div>
                                <div class="status-value">
                                    <i data-lucide="clock"></i>
                                    {{ \Carbon\Carbon::parse($directorDocument->updated_at)->format('d.m.y, H:i') }}
                                </div>
                            </div>
                            <div class="status-item-modern">
                                <div class="status-label">
                                    {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                                </div>
                                <div
                                    class="status-value font-bold">{{ $directorDocument->toUser->personal->shortName()??'' }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($agreement_users && $agreement_users->count())
                    <div class="execution-card-modern">
                        <div class="card-header-modern">
                            <i data-lucide="check-circle" class="text-white"></i>
                            <h3>{{ __('title.coordination') }}</h3>
                        </div>

                        <div class="performers-list-modern">
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
                                                    <span
                                                        class="badge badge-pill {{ $doc->statusReply->bg_class??'' }}">
                                                        {{ __($doc->statusReply->lang_code??'unk') }}
                                                    </span>
                                                    @if($doc && $doc->status == 5 && $journal->workEcpInfo($document->id, $doc->from_user)
                                                        && $journal->workEcpInfo($document->id, $doc->from_user)->docInfo)
                                                        <i data-lucide="feather"
                                                           style="font-size: 18px; cursor: pointer"
                                                           onclick="showEspInfo({{ $doc->id }})"></i>

                                                        @include('templates.ecp.ecp-information')
                                                    @endif
                                                </div>
                                                <div class="mt-10">
                                                <span class="sl-date pull-right"><i data-lucide="clock"></i>
                                                    {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
                                                </span>
                                                </div>
                                                <hr>
                                            @endforeach
                                        @else
                                            <div class="mt-10">
                                                <span class="badge badge-pill {{ $item->statusSign->badgeClass??'' }}">
                                                    {{ $item->statusSign->lang($item->statusSign->id)??'unk' }}
                                                </span>
                                                @php($sign_user = $item)
                                                @if($sign_user && $sign_user->status == 5 && $journal->workEcpInfo($document->id, $sign_user->to_user)
                                                    && $journal->workEcpInfo($document->id, $sign_user->to_user)->docInfo)
                                                    <i data-lucide="feather" style="font-size: 18px; cursor: pointer"
                                                       onclick="showEspInfo({{ $sign_user->id }})"></i>

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

                <div class="execution-card-modern actions-card">
                    <div class="card-header-modern">
                        <i data-lucide="settings" class="text-white"></i>
                        <h3>{{ __('action.action') }}</h3>
                    </div>

                    <div class="actions-body-modern-print">
                        <button type="button" onclick="printContent()" class="btn-modern btn-primary">
                            <i data-lucide="printer" class="icon text-white"></i>
                            <span>{{ __('action.print') }}</span>
                        </button>

                        <button type="button" onclick="downloadAsPDF()" class="btn-modern btn-primary">
                            <i data-lucide="download" class="icon text-white"></i>
                            <span>{{ __('action.download') }}</span>
                        </button>
                        <input id="downloadName"
                               value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}"
                               hidden/>
                    </div>

                    @if((in_array('IT_Admin', $authrole) || in_array('Super_Admin', $authrole)) && $journal->status != 13)
                        <div class="actions-body-modern">
                            <button type="button" class="btn-modern btn-danger" data-toggle="modal"
                                    data-target="#cancelModel" data-id="{{ $journal->doc_id }}">
                                <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                            </button>
                        </div>
                    @endif
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
                let modal_bg_class = "btn-success";
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
