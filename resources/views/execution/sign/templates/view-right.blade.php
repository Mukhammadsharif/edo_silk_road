<div class="col-md-4">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-pen-nib"></i> {{ __('title.sign_employee') }}</h3>
        </div>
        <div class="card-block">
            <div class="sl-item">
                <div class="sl-left fw-600 f-16">{{ $signer->toSign->personal->fullName()??'' }}</div>
                <div class="sl-right">
                    <p class="text-muted">
                        {{ $signer->toSign->department->lang($signer->toSign->department->id)??'' }}
                        - {{ $signer->toSign->userPost->lang($signer->toSign->userPost->id)??'' }}
                    </p>
                    @if($signer->replyComment && $signer->replyComment->res_text)
                        <p class="mt-2">
                            {!! $signer->replyComment->res_text !!}
                        </p>
                    @endif
                    <div>
                        <span class="badge badge-pill {{ $signer->statusSign->badgeClass??'' }}">
                            {{ $signer->statusSign->lang($signer->statusSign->id)??'unk' }}
                        </span>
                        @if($signer && $signer->status == 5 && $journal->workEcpInfo($document->id, $signer->to_user)
                                        && $journal->workEcpInfo($document->id, $signer->to_user)->docInfo)
                            <i class="ik ik-feather ml-10" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $signer->id }})"></i>

                            @include('templates.ecp.signer-ecp-information')
                        @endif
                    </div>
                    <div class="mt-10">
                        @if($signer->status != (int)1)
                            <span class="sl-date pull-right"><i class="ik ik-clock"></i>
                                {{ date('d.m.y, H:i', strtotime($signer->updated_at)) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <hr>
        </div>
    </div>

    <div class="card">
        @if($agreement_users && $agreement_users->count())
            <div class="card-header">
                <h3><i class="fa fa-handshake"></i> {{ __('title.agreement_employees') }}</h3>
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
                            @if(count($item->replyDocs($item->j_id, $item->to_user)) > 0)
                                @foreach($item->replyDocs($item->j_id, $item->to_user) as $k => $doc)
                                    @if($doc->res_text)
                                        <p class="mt-2">
                                            {!! $doc->res_text !!}
                                        </p>
                                    @endif
                                <div>
                                    <span class="badge badge-pill {{ $doc->statusReply->badgeClass??'' }}">
                                        {{ $doc->statusReply->lang($doc->statusReply->id)??'unk' }}
                                    </span>

                                    @if($doc && $doc->status == 5 && $journal->workEcpInfo($document->id, $doc->from_user)
                                        && $journal->workEcpInfo($document->id, $doc->from_user)->docInfo)
                                        <i class="ik ik-feather ml-10" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $doc->id }})"></i>

                                        @include('templates.ecp.ecp-information')
                                    @endif
                                </div>
                                <div class="mt-10">
                                    <span class="sl-date pull-right mt-5"><i class="ik ik-clock"></i>
                                        {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
                                    </span>
                                </div><hr>
                                @endforeach
                            @else
                                <div>
                                    <span class="badge badge-pill {{ $item->statusSign->badgeClass??'' }}">
                                        {{ $item->statusSign->lang($item->statusSign->id)??'unk' }}
                                    </span>
                                </div>
                                <div class="mt-10">
                                    @if($item->status != (int)2)
                                        <span class="sl-date pull-right"><i class="ik ik-clock"></i>
                                        {{ date('d.m.y, H:i', strtotime($item->updated_at)) }}
                                    </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        @if($journal->status == (int)5)
            <div class="card-header">
                <h3><i class="ik ik-check-circle"></i> {{ __('title.confirm_document') }}</h3>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-outline-danger m-sm-1 openCancelReturnModal" data-id="2">
                    {{ __('action.cancel') }}
                </button>

                <button type="button" class="btn btn-outline-warning m-sm-1 openCancelReturnModal" data-id="3">
                    {{ __('action.re-editing') }}
                </button>

                <button type="button" class="btn btn-outline-success m-sm-1 openConfirmForm" data-id="5">
                    {{ __('action.to_sign') }}
                </button>
            </div>

            <div class="card-body confirmForm bg-light" style="display: none">
                <div id="loading" class="loading-gif" style="display: none"></div>
                @csrf
                @method("POST")
                <form name="testform" id="loginSignForm">
                    <div class="form-group row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('action.leave_comment') }}..</label>
                                <textarea name="ecp_r_text" id="div_editor1"></textarea>
                            </div>
                        </div>
                        @if($signType->doc_sign_type === 1)
                            <div class="col-sm-12">
                                <label><i class="fa fa-key"></i> {{ __('action.select_ecp_key') }}
                                </label><br>
                                <label id="message"></label>
                                <select name="key" class="form-control form-control-primary select2" style="width: 100%"
                                        onchange="cbChanged(this)"></select>
                            </div>
                        @endif
                        <div class="col-sm-6 mt-5">
                            <label hidden><i class="fa fa-key"></i> {{ __('action.to_sign_by_key') }}
                            </label><br>
                            @if($signType->doc_sign_type === 2)
                                <button type="submit" class="btn btn-outline-success modal-bg-class">
                                    <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-success modal-bg-class"
                                        onclick="sign()">
                                    <span class="confirm_type">{{ __('action.to_sign') }}</span> (ERI)
                                </button>
                            @endif
                        </div>
                        <div class="col-sm-6 mt-5">
                            <img src="{{ url('loading.gif') }}" style="display: none" width="60px" class="loadingGif">
                        </div>
                    </div>
                    <div class="form-group">
                        @if($agreement_users && $agreement_users->count())
                            <input name="ecp_doc_type" value="from_signer_to_ag" hidden/>
                        @else
                            <input name="ecp_doc_type" value="from_signer_to_c_res" hidden/>
                        @endif
                        <input name="ecp_doc_id" value="{{ $document->id }}" hidden/>
                        <input name="ecp_confirm_id" id="ecp_confirm_id" hidden>
                    </div>
                    <div class="form-group">
                        <label id="keyId" hidden></label>
                        <textarea name="pkcs7" hidden></textarea>
                    </div>
                </form>
            </div>
        @endif
    </div>

    @include('templates.comment_form')

    <div class="card table-card">
        <div class="card-header">
            <h3>{{ __('title.action') }}</h3>
        </div>
        <div class="card-body">
            <button type="button" onclick="printContent()"
                    class="btn btn-outline-secondary btn-rounded mr-5">
                <i class="ik ik-printer"></i> {{ __('action.print') }}
            </button>

            <button type="button" onclick="downloadAsPDF()" class="btn btn-outline-dark m-1 btn-rounded">
                <i class="ik ik-download"></i> {{ __('action.download') }}
            </button>
            <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>

            @if(in_array($journal->status, [9,10,11]) && $journal->journal->doc_code !== 'BASE_INCOMING')
                <br><br>
                @include('qr-code-doc.qr-code-view')
                <input id="doc_id" value="{{ $document->id }}" hidden>
                <input id="doc_type" value="work_doc" hidden>
            @endif
        </div>
    </div>

    @include('office.templates.doc-steps')

{{--    <!-- Cancel/Return Modal -->--}}
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
                                        <textarea name="r_text" id="div_editor2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-warning float-right modal_btn"></button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

@include('qr-code-doc.qr-code-view-js')
@include('templates.ecp.login-sign')
@include('templates.download')

{{--<link rel="stylesheet" href="{{ asset('/richeditor//richtexteditor/rte_theme_default.css') }}" />
<script type="text/javascript" src="{{ asset('/richeditor//richtexteditor/rte.js') }}"></script>
<script type="text/javascript" src='{{ asset('/richeditor//richtexteditor/plugins/all_plugins.js') }}'></script>--}}

<script>
    let editor1cfg = {}
    editor1cfg.toolbar = "mytoolbar";
    editor1cfg.toolbar_mytoolbar = "{bold,italic,justifyleft,justifycenter,justifyright,fontsize,forecolor,backcolor}";
    let editor1 = new RichTextEditor("#div_editor1", editor1cfg);

    let editor2cfg = {}
    editor2cfg.toolbar = "mytoolbar";
    editor2cfg.toolbar_mytoolbar = "{bold,italic,justifyleft,justifycenter,justifyright,fontsize,forecolor,backcolor}";
    let editor2 = new RichTextEditor("#div_editor2", editor2cfg);
</script>

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // For Users
        $('#depHeadSignDocForm').hide();

        $('#depHeadSignDocBtn').on('click', function () {
            $('#depHeadSignDocForm').toggle();
        });

        $('.openCancelReturnModal').on('click', function () {
            let btn_id = $(this).data('id');
            let modal_title = "{{ __('action.cancel_doc') }}";
            let modal_btn = "{{ __('action.cancel') }}";
            if (btn_id === 3) {
                modal_title = "{{ __('action.back_edit_doc') }}";
                modal_btn = "{{ __('action.send') }}";
            }
            $('.modal_title').html(modal_title);
            $('.modal_btn').html(modal_btn);
            $('#confirm_id').val(btn_id);
            $('#openCancelReturnModal').modal('show');
        });

        $('#confirmForm').on('submit', function(e){

            e.preventDefault();

            $.ajax({
                url: "{{ route('confirm-reject-doc', $journal->id) }}",
                method: 'PUT',
                data: $(this).serialize(),
                beforeSend: function(){
                    $('#openCancelReturnModal').modal('toggle');
                },
            }).done(function(response){
                if (response.success === 200) {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else if (response.success === 201) {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                } else {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }

            }).error(function(err){
                console.log(err)
            })

        });
    });
</script>
