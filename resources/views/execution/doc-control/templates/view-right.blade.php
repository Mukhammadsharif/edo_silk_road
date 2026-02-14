<div class="view-right-modern">
    @if($directorDocument)
        @if($directorDocument->status === 1 && $journal->status == 7)
            <div class="execution-card-modern">
                <div class="card-header-modern">
                    <i data-lucide="copy-check" class="text-white"></i>
                    <h3>{{ __('title.execution') }}</h3>
                </div>
                <div class="card-body">
                    <div class="p-4 mt-15">
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

                    <div class="row" hidden>
                        <div class="col-md-12 mb-10">
                            <button type="button" id="letterChip" class="btn-modern btn-primary" disabled>
                                {{ __('action.create_resolution') }}
                            </button>
                        </div>
                    </div>

                    <div id="edoUsers">
                        <label>{{ __('action.select_performers') }} <span class="text-red">*</span></label>
                        <div class="input-group mb-2 mt-2">
                            <input type="text" class="form-control-modern mr-5" id="searchResPerformers" placeholder="{{ __('action.search') }}...">
                            <input type="text" id="doc_id" value="{{ $journal->doc_id }}" hidden>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 without-header">
                                <tbody id="unSelectedUsers">
                                </tbody>
                            </table>
                        </div>
                        <hr>
                    </div>

                    <form name=testform id="resForm" method="POST" action="{{ route('document.create-resolution', $journal->id) }}">
                        @csrf
                        @method('POST')
                        <div id="resDocForm" class="mt-10">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{ __('field.selected_performers') }}</label>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 without-header">
                                                <tbody id="selectedUsers" class="selected_users">
                                                @if($execution_users)
                                                    @foreach($execution_users as $key => $item)
                                                        <tr class="feed-item bg-light listItem{{$item->id??''}}">
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
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label>{{ __('field.resolution_type') }}</label>
                                            <select class="form-control-modern select2" style="width: 100%" name="r_type">
                                                @foreach($resolutionTypes as $key => $task)
                                                    <option value="{{ $task->id }}">
                                                        {{ $task->lang($task->lang_id) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label>{{ __('field.perf_term') }}</label>
                                            <input type="date" name="perf_term" class="form-control-modern"
                                                   value="{{ \Carbon\Carbon::now()->addMonth()->format('Y-m-d') }}"
                                                   required/>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>{{ __('field.resolution_text') }}</label>
                                        <textarea name="ecp_r_text" id="div_editor1"></textarea>
                                    </div>

                                    <div class="form-group" hidden>
                                        <button type="submit"
                                                class="btn btn-outline-primary confirmBtn">
                                            <i data-lucide="save"></i> {{ __('action.save') }}
                                        </button>
                                    </div>
                                    <div class="form-group" hidden>
                                        <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}
                                        </label><br>
                                        <label id="message"></label>
                                        <select name="key" class="form-control form-control-primary select2"
                                                style="width: 100% !important;"
                                                onchange="cbChanged(this)"></select>
                                    </div>
                                    <div class="actions-card">
                                        <label hidden><i data-lucide="key-round"></i> {{ __('action.to_sign_by_key') }}
                                        </label><br>
                                        <div class="actions-body-modern" style="padding: 0">
                                            <button type="submit" class="btn-modern btn-primary confirmBtn modal-bg-class"
                                                    onclick="signLogin()" id="btnConfLogin">
                                                <span class="confirm_type size10">
                                                    <i data-lucide="file-plus"></i>
                                                    {{ __('action.create_resolution') }}
                                                </span>
                                            </button>

                                            <button type="button" class="btn-modern btn-warning penCancelReturnModal"
                                                    data-id="3">
                                                <span class="size10">
                                                    <i data-lucide="arrow-big-left"></i>
                                                    {{ __('action.back_edit') }}
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mt-5">
                                        <img src="{{ url('loading.gif') }}" style="display: none" width="60px" class="loadingGif">
                                    </div>
                                    <div class="form-group">
                                        <input name="ecp_doc_type" value="work_dir_res" hidden/>
                                        <input name="ecp_doc_id" value="{{ $document->id }}" hidden/>
                                        <input name="ecp_confirm_id" id="ecp_confirm_id" value="5" hidden>
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

        @if($directorDocument->status > 1)
            <div class="execution-card-modern">
                <div class="card-header-modern">
                    <i data-lucide="copy-check" class="text-white"></i>
                    <h3>{{ __('title.execution') }}</h3>
                </div>
                <div class="card-body">
                    <div class="scrollable_div">
                        <table class="table mb-5 without-header border-bottom">
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

                    @if($directorResolution)
                        <div class="profile-pic">
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

                        <div class="p-4 border-top mt-15">
                            <div class="row text-center">
                                <div class="col-4 border-right">
                                    <p>{{ __('status.exec_status') }}:</p>
                                    <span class="badge {{ $journal->statusWork->badgeClass??'' }}">
                                {{ $journal->statusWork->lang($journal->statusWork->id)??'unk' }}
                            </span>
                                </div>
                                <div class="col-4 border-right">
                                    <p>{{ __('status.date_sent_for_execution') }}:</p>
                                    <i data-lucide="clock"></i>
                                    {{ date('d.m.y, H:i', strtotime($directorResolution->created_at)) }}
                                </div>
                                <div class="col-4">
                                    <p>
                                        {{ $directorResolution->fromUser->userPost->lang($directorResolution->fromUser->userPost->id)??'' }}:
                                    </p>
                                    <h6 class="fw-700">
                                        {{ $directorResolution->fromUser->personal->l_name??'' }}
                                        {{ $directorResolution->fromUser->personal->f_name??'' }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    @if(count($replyDocuments) > 0)
        <div class="execution-card-modern">
            <div class="card-header-modern">
                <i data-lucide="mail" class="text-white"></i>
                <h3>{{ __('title.answer_letters') }}</h3>
            </div>
            <div class="card-body chat-box scrollable_div">
                @foreach($replyDocuments as $key => $doc)
                    <div class="sl-item" id="doc_{{ $doc->id }}">
                        <div class="sl-left fw-600 f-16">{{ $doc->fromUser->personal->shortName()??'' }}</div>
                        <div class="sl-right">
                            <p class="text-muted">
                                @if(session()->get('locale') === 'ru')
                                    {{ $doc->fromUser->department->dep_name_ru??'' }}
                                @else
                                    {{ $doc->fromUser->department->dep_name??'' }}
                                @endif
                            </p>
                            <p class="mt-2">
                                {!! $doc->res_text??'' !!}
                            </p>

                            @if(is_array($doc->replyFiles))
                                <p><i class="ik ik-paperclip"></i> {{ __('document.doc_files') }}</p>
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
                                                        <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</i>
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
                                                        <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</i>
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
                                                        <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>@break
                                @endswitch
                            @endforeach
                            <div class="like-comm mt-20">
                                <div class="mt-10">
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
                                </div>
                                <div class="mt-10">
                                    <i data-lucide="clock"></i> {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
                                </div>
                                    @if($doc->from_user === $authUser->id && !in_array($journal->status, [10, 11]) && !in_array($doc->status, [3,4]))
                                        <div class="mt-10">
                                            <button type="button"
                                                    class="btn btn-icon btn-outline-danger deleteItem"
                                                    data-id="{{ $doc->id }}">
                                                <i class="ik ik-trash-2"></i>
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

    @if(!in_array($journal->journal->doc_code, ['BASE_OUTGOING']))
        <div class="execution-card-modern actions-card">
            <div class="card-header-modern">
                <i data-lucide="settings" class="text-white"></i>
                <h3>{{ __('action.action') }}</h3>
            </div>

            <div class="actions-body-modern-print">
                <button type="button" onclick="printContent()" class="btn-modern btn-secondary">
                    <i data-lucide="printer"></i> {{ __('action.print') }}
                </button>

                <button type="button" onclick="downloadAsPDF()" class="btn-modern btn-secondary">
                    <i data-lucide="download"></i> {{ __('action.download') }}
                </button>
                <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>

            </div>

            <div class="actions-body-modern">
                @if(count($replyDocuments) > 0 && $directorDocument->status == 6 && $journal->status != 10)
                    <button
                        class="btn-modern btn-success m-sm-1 openConfirmForm">
                        <i class="ik ik-archive"></i> {{ __('action.close_execution_document') }}
                    </button>
                @endif
            </div>

            @if(in_array($journal->status, [9, 10, 11]) && $journal->journal->doc_code !== 'BASE_INCOMING')
                @include('qr-code-doc.qr-code-view')
                <input id="doc_id" value="{{ $document->id }}" hidden>
                <input id="doc_type" value="work_doc" hidden>
            @endif
        </div>
    @else
        @if(count($replyDocuments) > 0 && $directorDocument->status == 5)
            <div class="card table-card">
                <div class="card-header">
                    <h3>{{ __('action.action') }}</h3>
                </div>
                <div class="card-body">
                    @if($journal->status != 10)
                        <button class="btn btn-success m-sm-1 openConfirmForm">
                            <i class="ik ik-archive"></i> {{ __('action.close_execution_document') }}
                        </button>
                    @endif

                    @if(in_array($journal->status, [9,10,11]) && $journal->journal->doc_code !== 'BASE_INCOMING')
                        <br><br>
                        @include('qr-code-doc.qr-code-view')
                        <input id="doc_id" value="{{ $document->id }}" hidden>
                        <input id="doc_type" value="work_doc" hidden>
                    @endif
                </div>
            </div>
        @endif
    @endif

    @endif
        <div class="card table-card confirmForm" style="display: none; overflow: hidden">
            <div class="card-body mt-5">
                <div id="loading" class="loading-gif" style="display: none"></div>
                <form name="testform" id="loginSignForm">
                    <div class="form-group row">
                        @if($signType->doc_sign_type === 1)
                            <div class="col-sm-12">
                                <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}
                                </label><br>
                                <label id="message"></label>
                                <select name="key" class="form-control form-control-primary select21" style="width: 100% !important;"
                                        onchange="cbChanged(this)"></select>
                            </div>
                        @endif
                        <div class="col-sm-8 mt-5">
                            <label hidden><i data-lucide="key-round"></i> {{ __('action.to_sign_by_key') }}
                            </label><br>
                            @if($signType->doc_sign_type === 2)
                                <button type="submit"
                                        class="btn btn-success m-sm-1 confirmBtn modal-bg-class"
                                        onclick="signLogin()">
                                    <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                                </button>
                            @else
                                <button type="button"
                                        class="btn btn-success confirmBtn modal-bg-class"
                                        onclick="sign()">
                                    <span class="confirm_type">{{ __('action.to_sign') }}</span> (ERI)
                                </button>
                            @endif
                        </div>
                        <div class="col-sm-4 mt-5">
                            <img src="{{ url('loading.gif') }}" style="display: none" width="60px" class="loadingGif">
                        </div>
                    </div>
                    <div class="form-group">
                        <input name="ecp_doc_type" value="close_exec_doc" hidden/>
                        <input name="ecp_doc_id" value="{{ $document->id }}" hidden/>
                        <input name="ecp_confirm_id" id="ecp_confirm_id" value="" hidden>
                        <input name="ecp_r_text" id="ecp_r_text" hidden>
                        <input name="r_type" id="r_type" hidden>
                    </div>
                    <div class="form-group">
                        <label id="keyId" hidden></label>
                        <textarea name="pkcs7" hidden></textarea>
                    </div>
                </form>
            </div>
        </div>

        @include('office.templates.doc-steps')
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-right.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

@include('e-imzo.e-imzo')

@include('qr-code-doc.qr-code-view-js')
@include('templates.ecp.login-sign')
@include('templates.download')
