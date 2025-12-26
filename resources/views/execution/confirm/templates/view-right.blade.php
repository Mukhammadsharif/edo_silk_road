<div class="col-md-4">
    @if(!$directorResolution)
        @if($directorDocument->status > 0)
            <div class="card">
                <div class="card-header">
                    <h3>{{ __('title.execution') }}</h3>
                </div>
                <div class="card-body">
                    @if(count($edoCancelMessages))
                        <div class="ml-10 scrollable_div">
                            @foreach($edoCancelMessages as $edoCancelMessage)
                                <div class="profile-pic">
                                    <p>
                                        <strong>{{ __('status.exec_status') }}:</strong>
                                        <span class="badge {{ $edoCancelMessage->statusCancel->bg_class??'' }}">
                                    {{ __($edoCancelMessage->statusCancel->lang_code??'unk') }}
                                </span>
                                    </p>
                                    <p>
                                        <strong>{{ __('status.date_sent_for_execution') }}:</strong>
                                        {{ date('d.m.y, H:i', strtotime($edoCancelMessage->created_at)) }}
                                    </p>
                                    <p>
                                        <strong>{{ __('comment.comment') }}:</strong>
                                        {!! $edoCancelMessage->description??'' !!}
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
                                <span class="badge {{ $journal->statusWork->badgeClass??'' }}">
                                {{ $journal->statusWork->lang($journal->statusWork->id)??'unk' }}
                            </span>
                            </div>
                            <div class="col-4 border-right">
                                <p>{{ __('field.created_date') }}:</p>
                                <i class="ik ik-clock"></i>
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

                        @if($journal->status == 8)
                            <div class="row" hidden>
                                <div class="col-md-12 mb-10">
                                    <button type="button" id="letterChip" class="btn btn-outline-primary" disabled>
                                        {{ __('action.create_resolution') }}
                                    </button>
                                </div>
                            </div>

                            <div id="edoUsers">
                                <label>{{ __('action.select_performers') }} <span class="text-red">*</span></label>
                                <div class="input-group mb-2 mt-2">
                                    <input type="text" class="form-control mr-5" id="searchResPerformers" placeholder="{{ __('action.search') }}...">
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
                                                                            <select class="form-control" name="user_role[]">
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
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-6">
                                                    <label>{{ __('field.resolution_type') }}</label>
                                                    <select class="form-control" name="r_type">
                                                        @foreach($resolutionTypes as $key => $task)
                                                            <option value="{{ $task->id }}">
                                                                {{ $task->lang($task->lang_id) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label>{{ __('field.perf_term') }} <span class="text-red">*</span></label>
                                                    <input type="date" name="perf_term" class="form-control" value="" required/>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>{{ __('field.resolution_text') }}</label>
                                                <textarea name="ecp_r_text" id="div_editor1"></textarea>
                                            </div>

                                            <div class="col-sm-12 mt-5">
                                                <label hidden><i class="fa fa-key"></i> {{ __('action.to_sign_by_key') }}
                                                </label><br>
                                                <div class="row">
                                                    <div class="col-md-5 mb-10">
                                                        <button type="submit" class="btn btn-outline-primary confirmBtn modal-bg-class"
                                                                onclick="signLogin()" id="btnConfLogin">
                                                            <span class="confirm_type">{{ __('action.create_resolution') }}</span>
                                                        </button>
                                                    </div>

                                                    <div class="col-md-6 mb-10">
                                                        <button type="button" class="btn btn-outline-warning mr-2 openCancelReturnModal"
                                                                data-id="3">
                                                            {{ __('action.back_edit') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        @endif
                </div>
            </div>
        @endif
    @else
        @if($directorDocument)
            <div class="card">
                <div class="card-header">
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
                                <i class="ik ik-clock"></i>
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

{{--                    @if($journal->status == 8)--}}
{{--                        <div class="row">--}}
{{--                            <div class="col-md-12 mb-10">--}}
{{--                                <button type="button" class="btn btn-outline-danger mr-2 openCancelReturnModal"--}}
{{--                                        data-id="2">--}}
{{--                                    {{ __('action.cancel') }}--}}
{{--                                </button>--}}
{{--                                <button type="button" class="btn btn-outline-warning mr-2 openCancelReturnModal"--}}
{{--                                        data-id="3">--}}
{{--                                    {{ __('action.back_edit') }}--}}
{{--                                </button>--}}

{{--                                <br><br>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endif--}}

                    <div class="scrollable_div">
                        <table class="table mb-5 without-header border-bottom currentResolution">
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

                        <div class="profile-pic currentResolution">
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


                    <div id="edoUsers">
                        <label>{{ __('action.select_performers') }} <span class="text-red">*</span></label>
                        <div class="input-group mb-2 mt-2">
                            <input type="text" class="form-control mr-5" id="searchResPerformers" placeholder="{{ __('action.search') }}...">
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


                    @if($journal->status == 8)
                    <form name=testform class="loginSignForm" id="resForm" method="POST">
                        @csrf
                        @method('POST')
                        <div id="resDocForm" class="mt-10">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label>{{ __('title.resolution_text') }}</label>
                                            <textarea name="ecp_r_text" id="div_editor1"></textarea>
                                        </div>
                                    </div>
                                    @if($signType->doc_sign_type === 1)
                                        <div class="form-group">
                                            <label><i class="fa fa-key"></i> {{ __('action.select_ecp_key') }}
                                            </label><br>
                                            <label id="message"></label>
                                            <select name="key" class="form-control form-control-primary select2"
                                                    style="width: 100%"
                                                    onchange="cbChanged(this)"></select>
                                        </div>
                                    @endif
                                    <div class="col-md-12">
                                        <label hidden><i class="fa fa-key"></i> {{ __('action.to_sign_by_key') }}
                                        </label><br>
                                        @if($signType->doc_sign_type === 2)
                                            <button type="submit" class="btn btn-outline-success confirmBtn mt-5 modal-bg-class"
                                                    id="btnConfLogin">
                                                <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-success confirmBtn mt-5 modal-bg-class"
                                                    onclick="sign()" id="btnConfEri">
                                                <span class="confirm_type">{{ __('action.to_sign') }}</span> (ERI)
                                            </button>
                                        @endif

                                        @if(isset($directorResolution) && $directorResolution)
                                            <button type="button" class="btn btn-outline-info ml-10 mt-5 editResolution">
                                                {{ __('action.edit_resolution') }}
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-outline-danger ml-10 mt-5 openCancelReturnModal"
                                                data-id="2">
                                            {{ __('action.cancel') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-warning mr-2 mt-5 openCancelReturnModal"
                                                data-id="3">
                                            {{ __('action.back_edit') }}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 mt-5">
                                        <img src="{{ url('loading.gif') }}" style="display: none" width="60px" class="loadingGif">
                                    </div>
                                    <div class="form-group">
                                        <select class="form-control" name="r_type" hidden></select>
                                        <input name="ecp_doc_type" value="sign_resolution" hidden/>
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

                        <div id="newResDocForm" class="mt-10">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{ __('title.selected_performers') }}</label>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 without-header">
                                                <tbody id="selectedUsers" class="selected_users">
                                                @foreach($selectedPerformers as $key => $item)
                                                    <tr class="feed-item bg-light listItem{{$item->toUser->id??''}}">
                                                        <td>
                                                            <button value="{{$item->toUser->id??''}}" type="button" class="close removeItem">
                                                                <span class="text-danger" aria-hidden="true">×</span>
                                                            </button>

                                                            <div class="d-inline-block align-middle">
                                                                <div class="d-inline-block">
                                                                    <h6 class="mb-0">{{ $item->toUser->personal->fullName()??'' }}</h6>
                                                                    <p class="mb-0">
                                                                        {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                                                                    </p>
                                                                </div>
                                                                <br><br>

                                                                <small class="float-left removeItem">
                                                                    <select class="form-control" name="user_role[]">
                                                                        @foreach($resolutionExecRoles as $r_key => $role)
                                                                            <option
                                                                                value="{{ $role->id }}" {{ ($r_key == 1 ? "selected":"") }}>
                                                                                {{ $role->lang($role->lang_id) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </small>

                                                                <input value="{{ $item->toUser->id??'' }}" name="user_id[]" hidden/>
                                                                <input value="{{ $item->toUser->dep_id }}" name="dep_id[]" hidden/>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label>{{ __('field.resolution_type') }}</label>
                                                <select class="form-control" name="r_type">
                                                    @foreach($resolutionTypes as $key => $task)
                                                        <option value="{{ $task->id }}">
                                                            {{ $task->lang($task->lang_id) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-6">
                                                <label>{{ __('field.perf_term') }}</label>
                                                <input type="date" name="perf_term" class="form-control" value="{{ $directorResolution->res_term??'' }}" required/>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>{{ __('field.resolution_text') }}</label>
                                            <textarea name="ecp_r_text" id="div_editor3">{{ $directorResolution->res_text??'' }}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <button type="button" class="btn btn-outline-warning mr-2" id="closeEditRes">
                                                <i class="ik ik-rotate-ccw"></i> {{ __('action.cancel') }}
                                            </button>

                                            <button type="submit"
                                                    class="btn btn-outline-primary confirmBtn">
                                                <i class="ik ik-save"></i> {{ __('action.save') }}
                                            </button>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            @if(count($replyDocuments) > 0)
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
                                    <div class="like-comm mt-20">
                                        @if(isset($execution_step_ids) && !in_array($doc->doc_exec_step_id, $execution_step_ids))
                                            <div class="mt-10">
                                                <span class="badge badge-pill {{ $doc->statusReply->badgeClass??'' }}">
                                                    {{ __($doc->statusReply->lang($doc->statusReply->id)??'unk') }}
                                                </span>

                                                @if($doc && $doc->status == 5 && $journal->workEcpInfo($document->id, $doc->from_user)
                                                    && $journal->workEcpInfo($document->id, $doc->from_user)->docInfo)
                                                    <i class="ik ik-feather ml-10" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $doc->id }})"></i>

                                                    @include('templates.ecp.ecp-information')
                                                @endif
                                            </div>
                                        @endif
                                        <div class="mt-10">
                                            <i class="ik ik-clock"></i> {{ date('d.m.y, H:i', strtotime($doc->created_at)) }}
                                        </div>
                                        @if($doc->from_user === $authUser->id && $journal->status !== (int)10 && !in_array($doc->status, [3,4]))
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

            @if($selectedAddPerformers && $selectedAddPerformers->count())
                <div class="card table-card">
                    <div class="card-header">
                        <h3><i class="ik ik-users"></i> {{ __('title.additional_performers') }}</h3>
                    </div>

                    <div class="card-block">
                        <div class="table-responsive">
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
                </div>
            @endif

            @if(!in_array($journal->journal->doc_code, ['BASE_INCOMING', 'BASE_OUTGOING']))
                <div class="card table-card">
                    <div class="card-header">
                        <h3>{{ __('action.action') }}</h3>
                    </div>
                    <div class="card-body">
                        @if(count($replyDocuments) > 0 && $directorDocument->status == 6 && $journal->status != 10)
                            <button
                                class="btn btn-outline-success m-1 openConfirmForm">
                                <i class="ik ik-archive"></i> {{ __('action.close_execution_document') }}
                            </button>
                            <button type="button" class="btn btn-outline-warning m-1 openCancelReturnModal"
                                    data-id="4">
                                {{ __('action.back_edit') }}
                            </button>
                        @endif
                        <button type="button" onclick="printContent()" class="btn btn-outline-secondary btn-rounded m-1">
                            <i class="ik ik-printer"></i> {{ __('action.print') }}
                        </button>

                        <button type="button" onclick="downloadAsPDF()" class="btn btn-outline-dark m-1 btn-rounded">
                            <i class="ik ik-download"></i> {{ __('action.download') }}
                        </button>
                        <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>

                        @if(in_array($journal->status, [9,10,11]))
                            <br><br>
                            @include('qr-code-doc.qr-code-view')
                            <input id="doc_id" value="{{ $document->id }}" hidden>
                            <input id="doc_type" value="work_doc" hidden>
                        @endif
                    </div>
                </div>
            @else
                <div class="card table-card">
                    <div class="card-header">
                        <h3>{{ __('action.action') }}</h3>
                    </div>
                    <div class="card-body">
                        @if(count($replyDocuments) > 0 && $directorDocument->status == 6 && $journal->status != 10)
                            <button class="btn btn-outline-success m-sm-1 openConfirmForm">
                                <i class="ik ik-archive"></i> {{ __('action.close_execution_document') }}
                            </button>
                            <button type="button" class="btn btn-outline-warning m-1 openCancelReturnModal"
                                    data-id="4">
                                {{ __('action.back_edit') }}
                            </button>
                        @endif

                        <button type="button" onclick="printContent()" class="btn btn-outline-secondary btn-rounded ml-1 mr-5">
                            <i class="ik ik-printer"></i> {{ __('action.print') }}
                        </button>

                        @if(in_array($journal->status, [9,10,11]) && $journal->journal->doc_code !== 'BASE_INCOMING')
                            <br><br>
                            @include('qr-code-doc.qr-code-view')
                            <input id="doc_id" value="{{ $document->id }}" hidden>
                            <input id="doc_type" value="work_doc" hidden>
                        @endif
                    </div>
                </div>
            @endif

            @if($journal->status === 11)
                <div class="card table-card confirmForm" style="display: none">
                    <div class="card-body mt-5">
                        <div id="loading" class="loading-gif" style="display: none"></div>
                        <form name="testform" id="loginSignForm">
                            <div class="form-group row">
                                @if($signType->doc_sign_type === 1)
                                    <div class="col-sm-12">
                                        <label><i class="fa fa-key"></i> {{ __('action.select_ecp_key') }}
                                        </label><br>
                                        <label id="message"></label>
                                        <select name="key" class="form-control form-control-primary select21" style="width: 100%"
                                                onchange="cbChanged(this)"></select>
                                    </div>
                                @endif
                                <div class="col-sm-8 mt-5">
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
            @endif
        @endif
    @endif

    @include('templates.comment_form')

    @include('office.templates.doc-steps')
</div>

@include('e-imzo.e-imzo')

@include('qr-code-doc.qr-code-view-js')
@include('templates.ecp.login-sign')
@include('templates.download')

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#newResDocForm').hide();
        let resolution = '{{ $directorResolution }}'
        if(resolution) {
            $('#edoUsers').hide();
        }

        $('#div_editor3').attr('name', 'r_text')

        $('#changeResForm').hide();

        $('.editResolution').on('click', function () {
            $('#resDocForm').hide();
            $('#readyResolution').hide();
            $('.editResolution').hide();
            $('.loginSignForm').off('submit').removeClass('loginSignForm').addClass('changeForm')
            $('.changeForm').attr('action', '{{ route('document.change-resolution', $journal->id) }}');

            $('#newResDocForm').show();
            $('#edoUsers').show();
            $('.currentResolution').hide();
            $('#div_editor3').attr('name', 'ecp_r_text')
            $('#div_editor1').attr('name', 'r_text')
        })

        $('#closeEditRes').on('click', function () {
            $('#newResDocForm').hide();
            $('#edoUsers').hide();

            $('#resDocForm').show();
            $('#readyResolution').show();
            $('.editResolution').show();
            $('.changeForm').off('submit').removeClass('changeForm').addClass('loginSignForm')
            $('.loginSignForm').attr('action', '');
            $('.currentResolution').show();
            $('#div_editor3').attr('name', 'r_text')
            $('#div_editor1').attr('name', 'ecp_r_text')
        })

        $('.loginSignForm').on('submit', function(e){

            e.preventDefault();

            $.ajax({
                url: "{{ route('login.sign.doc') }}",
                method: 'POST',
                data: $(this).serialize(),
                beforeSend: function(){},
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
