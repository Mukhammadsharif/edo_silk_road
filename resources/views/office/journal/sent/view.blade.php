@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/sent/view.css') }}">
@section('content')
    <div class="outgoing-view-page-modern">
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
                        <a href="{{ route('office.outgoing-docs') }}" class="breadcrumb-item">
                            <span>{{ __('title.outgoing') }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('action.view') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <div class="document-view-layout">
            <!-- Left Sidebar -->
            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <!-- Document Content -->
            <div class="document-content-section">
                <div class="document-card-modern">
                    @include('office.templates.sent-doc-body')
                </div>
            </div>

            @include('templates.mini_editor_js')

            <!-- Right Sidebar -->
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
                                <div class="status-value font-bold">{{ $directorDocument->toUser->personal->shortName()??'' }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($replyDocuments && count($replyDocuments) > 0)
                    <div class="execution-card-modern">
                        <div class="card-header-modern">
                            <i data-lucide="mail" class="text-white"></i>
                            <h3>{{ __('title.answer_letters') }}</h3>
                        </div>
                        <div class="replies-list-modern">
                            @foreach($replyDocuments as $key => $doc)
                                <div class="sl-item" id="doc_{{ $doc->id }}">
                                    <div class="sl-left fw-600 f-16">{{ $doc->fromUser->personal->shortName()??'' }}</div>
                                    <div class="sl-right">
                                        <p class="text-muted">
                                            {{ $doc->fromUser->department->lang($doc->fromUser->department->id)??'' }}
                                        </p>
                                        <p class="mt-2">
                                            {!! $doc->res_text??'' !!}
                                        </p>

                                        @if(is_array($doc->replyFiles))
                                            <p><i class="ik ik-paperclip"></i> {{ __('title.doc_files') }}</p>
                                        @endif
                                        @foreach($doc->replyFiles as $key => $file)
                                            @switch(strtolower($file->file_ext))
                                                @case('jpg')
                                                @case('jpeg')
                                                @case('png')
                                                    <div class="sl-item">
                                                        <div class="sl-right">
                                                            <div>
                                                                <a href="{{ route('r-file.pre-view',['id'=>$file->id]) }}"
                                                                   target="_blank" class="link"
                                                                   onclick="window.open('<?php echo('/file/r-pre-view/' . $file->id); ?>',
                                                                   'modal',
                                                                   'width=800,height=900,top=30,left=500');
                                                                   return false;"><i
                                                                        class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                                </a>
                                                                <div class="like-comm float-right">
                                                                    <a href="{{ route('r-file.download', ['id'=>$file->id]) }}"
                                                                       class="link mr-10 text-warning"><i
                                                                            class="ik ik-download"></i>{{ __('action.download') }}
                                                                    </a>
                                                                    <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                                        KB)</i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @break

                                                @case('pdf')
                                                    <div class="sl-item">
                                                        <div class="sl-right">
                                                            <div>
                                                                <a href="{{ route('r-file.pre-view',['id'=>$file->id]) }}"
                                                                   target="_blank" class="link"
                                                                   onclick="window.open('<?php echo('/file/r-pre-view/' . $file->id); ?>',
                                                                   'modal',
                                                                   'width=800,height=900,top=30,left=500');
                                                                   return false;">
                                                                    <i class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                                </a>
                                                                <div class="like-comm float-right">
                                                                    <a href="{{ route('r-file.download', ['id'=>$file->id]) }}"
                                                                       class="link mr-10 text-warning"><i
                                                                            class="ik ik-download"></i>{{ __('action.download') }}
                                                                    </a>
                                                                    <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                                        KB)</i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @break

                                                @default
                                                    <div class="sl-item">
                                                        <div class="sl-right">
                                                            <div>
                                                                <a href="{{ route('r-file.download', ['id'=>$file->id]) }}"
                                                                   class="link"><i
                                                                        class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                                </a>
                                                                <div class="like-comm float-right">
                                                                    <a href="{{ route('r-file.download', ['id'=>$file->id]) }}"
                                                                       class="link mr-10 text-warning"><i
                                                                            class="ik ik-download"></i>{{ __('action.download') }}
                                                                    </a>
                                                                    <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                                        KB)</i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @break

                                            @endswitch
                                        @endforeach

                                        <div class="mt-10">
                                            <span class="badge badge-pill {{ $doc->statusReply->badgeClass??'' }}">
                                                {{ $doc->statusReply->lang($doc->statusReply->id)??'unk' }}
                                            </span>
                                            @if($doc && $doc->status == 5 && $journal->workEcpInfo($document->id, $doc->from_user)
                                                    && $journal->workEcpInfo($document->id, $doc->from_user)->docInfo)
                                                <i data-lucide="feather" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $doc->id }})"></i>

                                                @include('templates.ecp.ecp-information')
                                            @endif
                                        </div>
                                        <div class="like-comm mt-20">
                                            <span class="mr-10">
                                                <i data-lucide="clock"></i> {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($agreement_users && $agreement_users->count())
                    <div class="execution-card-modern">
                        <div class="card-header-modern">
                            <i data-lucide="check-circle" class="text-white"></i>
                            <h3>{{ __('title.coordination') }}</h3>
                        </div>

                        <div class="coordination-list-modern">
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
                                                @php($sign_user = $item)
                                                @if($sign_user && $sign_user->status == 5 && $journal->workEcpInfo($document->id, $sign_user->to_user)
                                                    && $journal->workEcpInfo($document->id, $sign_user->to_user)->docInfo)
                                                    <i data-lucide="feather" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $sign_user->id }})"></i>

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
                            <i data-lucide="printer" class="icon"></i>
                            <span>{{ __('action.print') }}</span>
                        </button>

                        <button type="button" onclick="downloadAsPDF()" class="btn-modern btn-primary">
                            <i data-lucide="download" class="icon"></i>
                            <span>{{ __('action.download') }}</span>
                        </button>
                        <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>
                    </div>

                    <div class="actions-body-modern">
                        @if(in_array($journal->status, [1, 2, 3]))
                            <button type="button" class="btn-modern btn-danger deleteItem"
                                    data-id="{{$document->id}}">
                                <i data-lucide="trash-2" class="icon"></i>
                                <span>{{ __('action.delete') }}</span>
                            </button>

                            <a href="{{ route('office.doc.edit', ['type' => $type, 'j_id' => $journal->id, 'hash' => $document->doc_hash] ) }}"
                               class="btn-modern btn-warning">
                                <i data-lucide="edit" class="icon"></i>
                                <span>{{ __('action.edit') }}</span>
                            </a>
                        @endif

                        @if($agreement_users && $agreement_users->count())
                            @if($journal->status === (int)1)
                                <button type="button" class="btn-modern btn-success openConfirmForm">
                                    <i data-lucide="send" class="icon"></i>
                                    <span>{{ __('action.send_to_ag') }}</span>
                                </button>
                            @endif
                        @else
                            @if($journal->status === (int)1)
                                <button type="button" class="btn-modern btn-success openConfirmForm">
                                    <i data-lucide="send" class="icon"></i>
                                    <span>{{ __('action.send_for_signature') }}</span>
                                </button>
                            @endif
                        @endif

                        @if($journal->status === (int)1)
                            <div class="card-body confirmForm bg-light mt-5" style="display: none">
                                <div id="loading" class="loading-gif" style="display: none"></div>
                                <form name="testform" id="loginSignForm">
                                    <div class="form-group row">
                                        @if($signType->doc_sign_type === 1)
                                            <div class="col-sm-12">
                                                <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}
                                                </label><br>
                                                <label id="message"></label>
                                                <select name="key" class="form-control form-control-primary select2" style="width: 100%"
                                                        onchange="cbChanged(this)"></select>
                                            </div>
                                        @endif
                                        <div class="col-sm-12 mt-5">
                                            <label hidden><i data-lucide="key-round"></i> {{ __('action.to_sign_by_key') }}
                                            </label><br>
                                            @if($signType->doc_sign_type === 2)
                                                <button type="submit" class="btn-modern btn-success modal-bg-class"
                                                        onclick="signLogin()">
                                                    <i data-lucide="key" class="icon"></i>
                                                    <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                                                </button>
                                            @else
                                                <button type="button" class="btn-modern btn-success modal-bg-class"
                                                        onclick="sign()">
                                                    <i data-lucide="key" class="icon"></i>
                                                    <span class="confirm_type">{{ __('action.to_sign') }}</span> (ERI)
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mt-5">
                                        <img src="{{ url('loading.gif') }}" style="display: none" width="60px" class="loadingGif">
                                    </div>
                                    <div class="form-group">
                                        @if($agreement_users && $agreement_users->count())
                                            <input name="ecp_doc_type" value="send_to_ag_out_doc" hidden/>
                                        @else
                                            <input name="ecp_doc_type" value="send_sign_out_doc" hidden/>
                                        @endif
                                        <input name="ecp_doc_id" value="{{ $document->id }}" hidden/>
                                        <input name="ecp_confirm_id" id="ecp_confirm_id" value="{{ $authUser->id }}" hidden>
                                        <input name="ecp_r_text" id="ecp_r_text" hidden>
                                    </div>
                                    <div class="form-group">
                                        <label id="keyId" hidden></label>
                                        <textarea name="pkcs7" hidden></textarea>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>

                    @if($journal->status == 10)
                        @include('qr-code-doc.qr-code-view')
                        <input id="doc_id" value="{{ $document->id }}" hidden>
                        <input id="doc_type" value="out_doc" hidden>
                    @endif
                </div>

                @include('office.templates.doc-steps')
                </div>
            </div>
    </div>

    <script>
        // Initialize lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    @include('modals.delete-document')

    @include('office.journal.view-js')

    @include('print.print')

    @include('print.iframe-print')

    @include('qr-code-doc.qr-code-view-js')

    @include('e-imzo.e-imzo')

    @include('templates.ecp.login-sign')

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
