<div class="view-right-modern">
    @if($directorDocument->status > 1)
        <div class="execution-card-modern">
            <div class="card-header-modern">
                <i data-lucide="clipboard-check" class="text-white"></i>
                <h3>{{ __('title.execution') }}</h3>
            </div>
            <div class="performers-list-modern">
                @if($directorDocument->replyComments && $directorDocument->replyComments->count())
                    <div class="ml-10">
                        @foreach($directorDocument->replyComments as $edoCancelMessage)
                            <div class="profile-pic">
                                <p>
                                    <strong>{{ __('status.exec_status') }}:</strong>
                                    <span class="badge {{ $edoCancelMessage->statusReply->badgeClass??'' }}">
                                    {{ $edoCancelMessage->statusReply->lang($edoCancelMessage->statusReply->id)??'unk'}}
                                </span>
                                </p>
                                <p>
                                    <strong>{{ __('status.date_sent_for_execution') }}:</strong>
                                    {{ date('d.m.y, H:i', strtotime($edoCancelMessage->created_at)) }}
                                </p>
                                <p>
                                    <strong>{{ __('comment.comment') }}:</strong>
                                    {!! $edoCancelMessage->res_text??'' !!}
                                </p>
                                <hr>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="p-4 mt-15">
                    <div class="row text-center">
                        <div class="col-4 border-right">
                            <p>{{ __('status.exec_status') }}:</p>
                            <span class="badge {{ $directorDocument->statusSign->badgeClass??'' }}">
                                        {{ $directorDocument->statusSign->lang($directorDocument->statusSign->id)??'unk' }}
                                    </span>
                        </div>
                        <div class="col-4 border-right">
                            <p>{{ __('field.created_date') }}:</p>
                            <i data-lucide="clock"></i>
                            {{ date('d.m.y, H:i', strtotime($directorDocument->created_at)) }}
                        </div>
                        <div class="col-4">
                            <p>
                                {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                                :
                            </p>
                            <h6 class="fw-700">
                                {{ $directorDocument->toUser->personal->l_name??'' }}
                                {{ $directorDocument->toUser->personal->f_name??'' }}
                            </h6>
                        </div>
                    </div>
                </div>
                @if($directorDocument->status == 2)
                    <div class="actions-body-modern">
                        <button type="button" class="btn-modern btn-danger m-sm-1 openCancelEditModal"
                                data-id="2">
                            <i data-lucide="ban"></i> {{ __('action.cancel') }}
                        </button>
                        <button type="button" class="btn-modern btn-warning m-sm-1 openCancelEditModal"
                                data-id="3">
                            <i data-lucide="edit"></i> {{ __('action.back_edit') }}
                        </button>
                        <button type="button" class="btn-modern btn-success m-sm-1 sign-doc-btn">
                            <i data-lucide="signature"></i> {{ __('action.to_sign') }}
                        </button>
                    </div>
                @endif
                <form name="testform" id="loginSignForm" class="ecp-form" style="display: none">
                    <div id="resDocForm" class="mt-10">
                        <div class="row">
                            <div class="col-md-12">
                                @if($signType->doc_sign_type === 1)
                                    <div class="form-group">
                                        <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}
                                        </label><br>
                                        <label id="message"></label>
                                        <select name="key" class="form-control form-control-primary select2"
                                                style="width: 100%"
                                                onchange="cbChanged(this)">
                                        </select>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label hidden><i data-lucide="key-round"></i> {{ __('action.to_sign_by_key') }}
                                    </label><br>
                                    @if($signType->doc_sign_type === 2)
                                        <button type="submit"
                                                class="btn-modern btn-outline-success m-sm-1 confirmBtn modal-bg-class"
                                                onclick="signLogin()">
                                            <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn-modern btn-outline-success confirmBtn modal-bg-class"
                                                onclick="sign()">
                                            <span class="confirm_type">{{ __('action.to_sign') }}</span> (ERI)
                                        </button>
                                    @endif
                                </div>
                                <div class="col-sm-6 mt-5">
                                    <img src="{{ url('loading.gif') }}" style="display: none" width="60px"
                                         class="loadingGif">
                                </div>
                                <div class="form-group">
                                    <input name="ecp_doc_type" value="sign_out_doc" hidden/>
                                    <input name="ecp_r_text" value="" hidden/>
                                    <input name="ecp_doc_id" value="{{ $document->id }}" hidden/>
                                    <input name="ecp_confirm_id" value="5" hidden>
                                </div>
                                <div class="form-group">
                                    <label id="keyId" hidden></label>
                                    <textarea name="pkcs7" hidden></textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($replyDocuments && count($replyDocuments) > 0)
        <div class="execution-card-modern">
            <div class="card-header-modern">
                <i data-lucide="mail" class="text-white"></i>
                <h3>{{ __('title.answer_letters') }}</h3>
            </div>
            <div class="performers-list-modern scrollable_div">
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
                                <p><i data-lucide="paperclip"></i> {{ __('title.doc_files') }}</p>
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

    @if($journal->status != 1)
        @include('templates.comment_form')
    @endif

    <div class="execution-card-modern actions-card">
        <div class="card-header-modern">
            <i data-lucide="settings" class="text-white"></i>
            <h3>{{ __('action.action') }}</h3>
        </div>
        <div class="actions-body-modern">
            <button type="button" onclick="printContent()"
                    class="btn-modern btn-secondary">
                <i data-lucide="printer"></i> {{ __('action.print') }}
            </button>

{{--            <a href="{{ route('pdf.sent-doc', [$document->id, $document->doc_hash]) }}"--}}
{{--               class="btn btn-outline-secondary btn-rounded">--}}
{{--                <i data-lucide="download"></i> {{ __('action.download') }}--}}
{{--            </a> --}}
            <br><br>

            @if($journal->status === 10)
                @include('qr-code-doc.qr-code-view')
                <input id="doc_id" value="{{ $document->id }}" hidden>
                <input id="doc_type" value="out_doc" hidden>
            @endif
        </div>
    </div>

    @include('office.templates.doc-steps')
        <!-- Cancel/Return Modal -->
        <div class="modal fade" id="openCancelReturnModal">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-yellow">
                        <h5 class="modal_title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="confirmForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>{{ __('action.leave_comment') }} <span class="text-red">*</span></label>
                                            <input type="text" name="confirm_id" id="confirm_id" hidden>
                                            <textarea name="r_text" id="div_editor2" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn-modern btn-outline-warning float-right modal_btn"></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>



        <!-- Loading Modal -->
        <div class="loadModal modal fade" tabindex="-1" role="dialog" id="spinnerModal">
            <div class="modal-dialog modal-dialog-centered text-center" role="document">
                <span class="fa fa-spinner fa-spin fa-3x w-100"></span>
            </div>
        </div>

        <!-- Response (success, error) Modal -->
        <div class="modal fade" id="responseModal">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header modal-content-class">
                        <h5 class="modal-title" id="resModalTitle"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5 id="resModalMessage"></h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modern btn-light" data-dismiss="modal">
                            {{ __('action.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
</div>

<script>
    let editor2cfg = {}
    editor2cfg.toolbar = "mytoolbar";
    editor2cfg.toolbar_mytoolbar = "{bold,italic,justifyleft,justifycenter,justifyright,fontsize,forecolor,backcolor}";
    let second = document.querySelector('#div_editor2');
    if(second) new RichTextEditor("#div_editor2", editor2cfg);
</script>
<style>
    .keyboard-selected {
        background: #f8f9fa !important;
    }
</style>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-right.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>
@include('templates.ecp.login-sign')

<script type="text/javascript">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.openCancelEditModal').on('click', function () {
            let btn_id = $(this).data('id');
            let modal_title = "{{ __('action.action.cancel_doc') }}";
            let modal_btn = "{{ __('action.action.cancel') }}";
            if (btn_id === 3) {
                modal_title = "{{ __('action.action.back_edit_doc') }}";
                modal_btn = "{{ __('action.send') }}";
            }
            $('.modal_title').html(modal_title);
            $('.modal_btn').html(modal_btn);
            $('#confirm_id').val(btn_id);
            $('#openCancelReturnModal').modal('show');
        });

        $('.sign-doc-btn').on('click', function () {
            $('.ecp-form').toggle();
        });

        $('#confirmForm').on('submit', function (e) {

            e.preventDefault();

            $.ajax({
                url: "{{ route('confirm-reject-doc', $journal->id) }}",
                method: 'GET',
                data: $(this).serialize(),
                beforeSend: function () {
                },
            }).done(function (response) {
                console.log(response)
                $('#openCancelReturnModal').modal('hide');
                $('#resModalTitle').text(response.title);
                $('#resModalMessage').text(response.message);
                $('#responseModal').modal('toggle');
                if (response.success === 200) {
                    $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else if (response.success === 201) {
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                } else {
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                }

            }).error(function (err) {
                console.log(err)
            })
        });
    });
</script>
