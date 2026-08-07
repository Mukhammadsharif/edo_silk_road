<div class="view-right-modern">
    @if($directorDocument)
        <div class="execution-card-modern">
            <div class="card-header-modern">
                <i data-lucide="clipboard-check" class="text-white"></i>
                <h3>{{ __('title.execution') }}</h3>
            </div>

            <div class="card-block">
                <div class="table-responsive scrollable_div">
                    <table class="table mb-0 without-header">
                        <tbody>
                        @foreach($selectedPerformers as $key => $item)
                            <tr>
                                <td>
                                    <div class="d-inline-block align-middle">
                                        <div class="d-inline-block">
                                            <h6 class="mb-0">{{ $item->toUser->personal->fullName()??'' }}</h6>
                                            <p class="text-muted mb-0">
                                                {{ $item->toDep->lang($item->toDep->id)??'' }},
                                                {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <h6 class="fw-500">
                                        <span class="badge badge-primary">
                                            {{ $item->resExecRole->lang($item->resExecRole->lang_id)??'' }}
                                        </span>
                                    </h6>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <hr>
            <div class="profile-pic card-body">
                <p>
                    <strong>{{ __('title.res_type') }}:</strong>
                    <span class="badge badge-aqua">
                                {{ $directorResolution->resType->lang($directorResolution->resType->lang_id)??'' }}
                    </span>
                </p>
                @if($directorResolution->res_term)
                    @include('templates.director-resolution-res-term')
                @endif
                <strong>{{ __('title.res_text') }}:</strong> {!! $directorResolution->res_text??'' !!}
            </div>
            <hr>

            <div class="p-2">
                <div class="row text-center">
                    <div class="col-4 border-right">
                        <p>{{ __('status.exec_status') }}:</p>
                        <span class="badge {{ $journal->statusWork->badgeClass??'' }}">
                                {{ $journal->statusWork->lang($journal->statusWork->id)??'unk' }}
                            </span>
                    </div>
                    <div class="col-4 border-right">
                        <p>{{ __('field.created_date') }}:</p>
                        <i data-lucide="clock"></i>
                        {{ date('d.m.y, H:i', strtotime($directorDocument->updated_at)) }}
                    </div>
                    <div class="col-4">
                        <p>
                            {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}:
                        </p>
                        <h6 class="fw-700">
                            {{ $directorDocument->toUser->personal->l_name??'' }}
                            {{ $directorDocument->toUser->personal->f_name??'' }}
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(count($replyDocuments) > 0)
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
                                <p><i data-lucide="paperclip"></i> {{ __('doc_files') }}</p>
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
                                                            data-lucide="download"></i>{{ __('download') }}
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
                                                            data-lucide="download"></i>{{ __('download') }}
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
                                                            data-lucide="download"></i>{{ __('download') }}
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
                            <div class="like-comm mt-20">
                                @if(isset($execution_step_ids) && !in_array($doc->doc_exec_step_id, $execution_step_ids))
                                    <div class="mt-10">
                                        <span class="badge badge-pill {{ $doc->statusReply->badgeClass??'' }}">
                                            {{ __($doc->statusReply->lang($doc->statusReply->id)??'unk') }}
                                        </span>

                                        @if($doc && $doc->status == 5 && $journal->workEcpInfo($document->id, $doc->from_user)
                                        && $journal->workEcpInfo($document->id, $doc->from_user)->docInfo)
                                            <i data-lucide="feather" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $doc->id }})"></i>

                                            @include('templates.ecp.ecp-information')
                                        @endif
                                    </div>
                                @endif
                                <div class="mt-10">
                                    <i data-lucide="clock"></i> {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
                                </div>
                                @if($doc->from_user === $authUser->id && !in_array($journal->status, [10, 11]) && $current_execution->id === $doc->doc_exec_step_id)
                                    <div class="mt-10">
                                        <button type="button"
                                                class="btn-modern btn-danger deleteItem"
                                                data-id="{{ $doc->id }}">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr>
                @endforeach
            </div>
        </div>
    @endif

    @if(in_array($current_execution->status, [2,4]) && in_array($journal->status, [9, 11]))
        <div class="execution-card-modern">
            <div class="card-header-modern">
                <i data-lucide="file-text" class="text-white"></i>
                <h3>{{ __('title.exec_document') }}</h3>
            </div>
            <div class="performers-list-modern">
                <form method="POST" action="{{ route('document.reply-document', $current_execution->id) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('document.doc_text') }} <span class="text-red">*</span></label>
                                <textarea name="r_text" id="div_editor1"></textarea>
                                {!! $errors->first('r_text', '<p class="text-danger">:message</p>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label>{{ __('document.doc_files') }}</label>
                                <div class="custom-file">
                                    <input type="file" name="doc_files[]" class="custom-file-input form-control-modern"
                                           id="customFile" multiple>
                                    <label class="custom-file-label"
                                           for="customFile">{{ __('action.upload_files') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i> {{ __('action.save') }}
                            </button>
                        </div>
                    </div>
                </form>

                <div class="row mt-10">
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="button" id="letterChip" class="btn-modern btn-primary mt-10">
                                <i data-lucide="plus-circle"></i> {{ __('title.add_additional_performers') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- v65: получателям «Для информации» (FOR_INF) кнопка
                     «Подтвердить исполнение» НЕ показывается — они не
                     исполнители, подтверждать им нечего. --}}
                @if($current_execution->status == 4 /*&& $current_execution->from_user == $directorDocument->to_user*/
                    && ($current_execution->resExecRole?->doc_code ?? '') !== 'FOR_INF'
                    && $current_execution->replyDocs($journal->id, $authUser->id)
                    && $current_execution->replyDocs($journal->id, $authUser->id)->count())
                    <div class="row mt-10">
                        <div class="form-group">
                            <div class="col-md-12">
                                <a href="{{ route('execute.work.document', $current_execution->id) }}" class="btn-modern btn-success">
                                    <i data-lucide="check-circle"></i> {{ __('action.execute') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="edoUsers">
                    <h5>{{ __('field.employees') }} <span class="text-red">*</span></h5>
                    <div class="table-responsive" style="overflow-y: scroll; height: 400px">
                        <table class="table table-hover mb-0 without-header">
                            <div class="input-group mb-2 mt-2">
                                <input type="text" class="form-control-modern mr-5" id="userSearch"
                                       onkeyup="searchUserFunction()" placeholder="{{ __('action.search') }}...">
                            </div>
                            <tbody id="unSelectedUsers">
                            @foreach($execution_users as $key => $item)
                                <tr class="feed-item select-user listItem{{$item->id??''}}">
                                    <td>
                                        <button value="{{$item->id??''}}" type="button" class="close removeItem">
                                            <span class="text-danger" aria-hidden="true">×</span>
                                        </button>

                                        <div class="d-inline-block align-middle">
                                            <div class="d-inline-block">
                                                <h6 class="mb-0">{{ $item->personal->fullName()??'' }}</h6>
                                                <p class="mb-0">
                                                    {{ $item->userPost->lang($item->userPost->id)??'' }}
                                                </p>
                                            </div>
                                            <br><br>

                                            <small class="float-left removeItem">
                                                <select class="form-control-modern select2" name="user_role[]">
                                                    @foreach($resolutionExecRoles as $r_key => $role)
                                                        <option
                                                            value="{{ $role->id }}" {{ ($r_key == 1 ? "selected":"") }}>
                                                            {{ $role->lang($role->lang_id) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </small>

                                            <input value="{{ $item->id??'' }}" name="user_id[]" hidden/>
                                            <input value="{{ $item->dep_id }}" name="dep_id[]" hidden/>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr>
                </div>

            </div>
        </div>

        <div class="execution-card-modern selected-additional-performers">
            <div class="card-header-modern">
                <i data-lucide="users" class="text-white"></i>
                <h3>{{ __('title.additional_performers') }}</h3>
            </div>
            <div class="performers-list-modern">
                <form method="POST"
                      action="{{ route('document.add-performers', $journal->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-block" id="resDocForm">
                        <div class="table-responsive">
                            <table class="table table-hover without-header">
                                <tbody id="selectedUsers"></tbody>
                            </table>
                        </div>
                    </div>
                    <button type="submit" class="btn-modern btn-primary m5 btnSave">
                        <i data-lucide="save"></i> {{ __('action.save') }}
                    </button>
                </form>
            </div>
        </div>
    @endif


    @if($selectedAddPerformers && $selectedAddPerformers->count())
        <div class="execution-card-modern">
            <div class="card-header-modern">
                <i data-lucide="users" class="text-white"></i>
                <h3>{{ __('title.additional_performers') }}</h3>
            </div>

            <div class="card-block">
                <div class="table-responsive scrollable_div">
                    <table class="table mb-0 without-header">
                        <tbody>
                        @foreach($selectedAddPerformers as $key => $item)
                            <tr>
                                <td>
                                    <div class="d-inline-block align-middle">
                                        <div class="d-inline-block">
                                            <h6 class="mb-0">{{ $item->toUser->personal->fullName()??'' }}</h6>
                                            <p class="text-muted mb-0">
                                                {{ $item->toDep->lang($item->toDep->id)??'' }},
                                                {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <h6 class="fw-500">
                                        <span class="badge badge-primary">
                                             {{ $item->resExecRole->lang($item->resExecRole->lang_id)??'' }}
                                        </span>
                                    </h6>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 border-top mt-15" hidden>
                <div class="row text-center">
                    <div class="col-4 border-right">
                        <p>{{ __('status.exec_status') }}</p>
                        <span class="badge badge-success">
                            <i data-lucide="check-circle"></i> {{ __('status.confirmed') }}
                        </span>
                    </div>
                    <div class="col-4 border-right">
                        <p>{{ __('status.date_sent_for_execution') }}</p>
                        <i data-lucide="clock"></i> {{ \Carbon\Carbon::parse($selectedAddPerformers[0]['created_at']??now())->format('d.m.y H:i')  }}
                    </div>
                    <div class="col-4">
                        <p>
                            {{ $current_execution->toUser->userPost->lang($current_execution->toUser->userPost->id)??'' }}
                        </p>
                        <h6 class="fw-700">{{ $current_execution->toUser->personal->shortName()??'' }}</h6>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('templates.comment_form')

    @if($journal->journal->doc_code != 'BASE_OUTGOING')
        <div class="execution-card-modern actions-card">
            <div class="card-header-modern">
                <i data-lucide="settings" class="text-white"></i>
                <h3>{{ __('action.action') }}</h3>
            </div>
            <div class="actions-body-modern">
                <button type="button" onclick="printContent()" class="btn-modern btn-secondary">
                    <i data-lucide="printer"></i> {{ __('action.print') }}
                </button>

                <button type="button" onclick="downloadAsPDF()" class="btn-modern btn-secondary">
                    <i data-lucide="download"></i> {{ __('action.download') }}
                </button>
                <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>
            </div>

            @if(in_array($journal->status, [9,10,11]) && $journal->journal->doc_code !== 'BASE_INCOMING')
                @include('qr-code-doc.qr-code-view')
                <input id="doc_id" value="{{ $document->id }}" hidden>
                <input id="doc_type" value="work_doc" hidden>
            @endif
        </div>
    @endif

    @include('office.templates.doc-steps')
</div>

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
                                    <textarea name="r_text" id="div_editor2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-modern btn-warning float-right modal_btn"></button>
                </div>
            </form>

        </div>
    </div>
</div>

@include('qr-code-doc.qr-code-view-js')
@include('templates.download')

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-right.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

<script>
    let editor1cfg = {}
    editor1cfg.toolbar = "mytoolbar";
    editor1cfg.toolbar_mytoolbar = "{bold,italic,justifyleft,justifycenter,justifyright,fontsize,forecolor,backcolor}";
    let editor1 = new RichTextEditor("#div_editor1", editor1cfg);
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
            //$('.modal-bg-class').removeClass(['bg-success', 'bg-warning', 'bg-yellow']).addClass(modal_bg_class);
            $('.modal_title').html(modal_title);
            $('.modal_btn').html(modal_btn);
            $('#confirm_id').val(btn_id);
            $('#openCancelReturnModal').modal('show');
        });
    });
</script>

{{--EMIZO Error Modal--}}
<div class="modal fade" id="imzo-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-yellow">
                <h4 class="modal-title"><i data-lucide="key-round"></i> e-imzo</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 id="imzo-msg"></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-light pull-right"
                        data-dismiss="modal">{{ __('action.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

