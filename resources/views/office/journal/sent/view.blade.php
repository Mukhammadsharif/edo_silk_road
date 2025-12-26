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

            @include('office.journal.view-left')

            <div class="col-md-6">
                @include('office.templates.sent-doc-body')
            </div>

            @include('templates.mini_editor_js')

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
                                    <i class="ik ik-clock"></i>
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

                @if($replyDocuments && count($replyDocuments) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h3>{{ __('title.answer_letters') }}</h3>
                        </div>
                        <div class="card-body chat-box scrollable_div">
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
                                                <i class="ik ik-feather ml-10" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $doc->id }})"></i>

                                                @include('templates.ecp.ecp-information')
                                            @endif
                                        </div>
                                        <div class="like-comm mt-20">
                                            <span class="mr-10">
                                                <i class="ik ik-clock"></i> {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
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
                                                        <i class="ik ik-feather ml-10" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $doc->id }})"></i>

                                                        @include('templates.ecp.ecp-information')
                                                    @endif
                                                </div>
                                                <div class="mt-10">
                                                <span class="sl-date pull-right"><i class="ik ik-clock"></i>
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
                                                    <i class="ik ik-feather ml-10" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $sign_user->id }})"></i>

                                                    @include('templates.ecp.signer-ecp-information')
                                                @endif
                                            </div>
                                            @if($item->status != (int)1)
                                                <div class="mt-10">
                                                    <span class="sl-date pull-right"><i class="ik ik-clock"></i>
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
                            <i class="ik ik-printer"></i> {{ __('action.print') }}
                        </button>

                        <button type="button" onclick="downloadAsPDF()" class="btn btn-outline-dark m-1 btn-rounded">
                            <i class="ik ik-download"></i> {{ __('action.download') }}
                        </button>
                        <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>
                        <hr>

                        @if(in_array($journal->status, [1, 2, 3]))
                            <button type="button" class="btn btn-outline-danger btn-rounded mr-5 deleteItem"
                                    data-id="{{$document->id}}">
                                <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                            </button>

                            <a href="{{ route('office.doc.edit', ['type' => $type, 'j_id' => $journal->id, 'hash' => $document->doc_hash] ) }}"
                               class="btn btn-outline-warning btn-rounded m-1">
                                <i class="ik ik-edit"></i> {{ __('action.edit') }}
                            </a>
                        @endif

                        @if($agreement_users && $agreement_users->count())
                            @if($journal->status === (int)1)
                                <button type="button"
                                        class="btn btn-outline-success btn-rounded openConfirmForm">
                                    <i class="ik ik-send"></i> {{ __('action.send_to_ag') }}
                                </button>
                            @endif
                        @else
                            @if($journal->status === (int)1)
                                <button type="button"
                                        class="btn btn-outline-success btn-rounded openConfirmForm">
                                    <i class="ik ik-send"></i> {{ __('action.send_for_signature') }}
                                </button>
                            @endif
                        @endif

                        @if($journal->status == 10)
                            @include('qr-code-doc.qr-code-view')
                            <input id="doc_id" value="{{ $document->id }}" hidden>
                            <input id="doc_type" value="out_doc" hidden>
                        @endif

                        @if($journal->status === (int)1)
                            <div class="card-body confirmForm bg-light mt-5" style="display: none">
                                <div id="loading" class="loading-gif" style="display: none"></div>
                                <form name="testform" id="loginSignForm">
                                    <div class="form-group row">
                                        @if($signType->doc_sign_type === 1)
                                            <div class="col-sm-12">
                                                <label><i class="fa fa-key"></i> {{ __('action.select_ecp_key') }}
                                                </label><br>
                                                <label id="message"></label>
                                                <select name="key" class="form-control form-control-primary select2" style="width: 100%"
                                                        onchange="cbChanged(this)"></select>
                                            </div>
                                        @endif
                                        <div class="col-sm-12 mt-5">
                                            <label hidden><i class="fa fa-key"></i> {{ __('action.to_sign_by_key') }}
                                            </label><br>
                                            @if($signType->doc_sign_type === 2)
                                                <button type="submit" class="btn btn-outline-success modal-bg-class"
                                                        onclick="signLogin()">
                                                    <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-success modal-bg-class"
                                                        onclick="sign()">
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
                </div>

                @include('office.templates.doc-steps')
            </div>

        </div>
    </div>

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
                $('.modal-bg-class').removeClass().addClass(`btn modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val();
            });
        });
    </script>

@endsection
