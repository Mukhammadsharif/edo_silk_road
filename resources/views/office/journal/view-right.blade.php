<div class="col-md-4">
    @if($journal->journal->doc_code !== 'INF_DOC')
        @if($directorResolution)
            <div class="card table-card">
                <div class="card-header">
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

                <div class="profile-pic ml-20">
                    <p>
                        <strong>{{ __('title.res_type') }}:</strong>
                        <span class="badge badge-aqua">
                         {{ $directorResolution->resType->lang($directorResolution->resType->lang_id)??'' }}
                    </span>
                    </p>
                    <strong>{{ __('title.res_text') }}:</strong> {!! $directorResolution->res_text??'' !!}
                </div>

                <div class="p-4 border-top mt-15">
                    <div class="row text-center">
                        <div class="col-4 border-right">
                            <p>{{ __('status.exec_status') }}</p>
                            <span class="badge {{ $journal->statusWork->badgeClass??'' }}">
                                {{ $journal->statusWork->lang($journal->statusWork->id)??'unk' }}
                        </span>
                        </div>
                        <div class="col-4 border-right">
                            <p>{{ __('status.exec_date') }}</p>
                            <i class="ik ik-clock"></i>
                            {{ \Carbon\Carbon::parse($directorResolution->created_at)->format('d.m.y, H:i') }}
                        </div>
                        <div class="col-4">
                            <p>
                                @if(session()->get('locale') === 'ru')
                                    {{ $directorResolution->fromUser->userPost->post_name_ru??'' }}
                                @else
                                    {{ $directorResolution->fromUser->userPost->post_name??'' }}
                                @endif
                            </p>
                            <h6 class="fw-700">{{ $directorResolution->fromUser->personal->shortName()??'' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card table-card">
                <div class="card-header">
                    <h3>{{ __('title.execution') }}</h3>
                </div>

                <div class="p-4 border-top mt-15">
                    <div class="row text-center">
                        <div class="col-4 border-right">
                            <p>{{ __('status.exec_status') }}</p>
                            <span class="ml-10 badge {{ $journal->statusWork->badgeClass??'' }}">
                            {{ $journal->statusWork->lang($journal->statusWork->id)??'unk' }}
                        </span>
                        </div>
                        <div class="col-4 border-right">
                            <p>
                                @if($journal->status === 1)
                                    {{ __('field.created_at') }}
                                @else
                                    {{ __('field.date_sent_for_execution') }}
                                @endif
                            </p>
                            {{ \Carbon\Carbon::parse($journal->created_at)->format('d.m.y, H:i') }}
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

        @if(isset($selectedAddPerformers) && $selectedAddPerformers && $selectedAddPerformers->count())
            <div class="card table-card">
                <div class="card-header">
                    <h3><i class="ik ik-users"></i> {{ __('title.additional_performers') }}</h3>
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
            </div>
        @endif
    @endif

    @if(count($replyDocuments) > 0)
        <div class="card">
            <div class="card-header">
                <h3>{{ __('title.answer_letters') }}</h3>
            </div>
            <div class="card-body chat-box scrollable_div">
                @foreach($replyDocuments as $key => $doc)
                    <div class="sl-item">
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
                            @if(isset($execution_step_ids) && !in_array($doc->doc_exec_step_id, $execution_step_ids))
                                <div class="mt-10">
                                    <span class="badge badge-pill {{ $doc->statusReply->badgeClass??'' }}">
                                        {{ $doc->statusReply->lang($doc->statusReply->id)??'unk' }}
                                    </span>
                                    @if($doc && $doc->status == 5 && $journal->workEcpInfo($document->id, $doc->from_user)
                                            && $journal->workEcpInfo($document->id, $doc->from_user)->docInfo)
                                        <i class="ik ik-feather ml-10" style="font-size: 18px; cursor: pointer"
                                           onclick="showEspInfo({{ $doc->id }})"></i>

                                        @include('templates.ecp.ecp-information')
                                    @endif
                                </div>
                            @endif
                            <div class="like-comm mt-20">
                                <span class="mr-10">
                                    <i class="ik ik-clock"></i> {{ date('d.m.y, H:i', strtotime($doc->updated_at)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <hr>
                @endforeach
            </div>
        </div>
    @endif

    @include('templates.mini_editor_js')

    @if($journal->status != 1)
        @include('templates.comment_form')
    @endif

    <div class="card table-card">
        <div class="card-header">
            <h3>{{ __('action.action') }}</h3>
        </div>
        <div class="card-body">
            <button type="button" onclick="printContent()" class="btn btn-outline-secondary m-1 btn-rounded">
                <i class="ik ik-printer"></i> {{ __('action.print') }}
            </button>

            <button type="button" onclick="downloadAsPDF()" class="btn btn-outline-dark m-1 btn-rounded">
                <i class="ik ik-download"></i> {{ __('action.download') }}
            </button>
            <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>
            <hr>
            @if(in_array($journal->status, [1, 2, 3]))
                <button type="button" class="btn btn-outline-danger btn-rounded m-1 deleteItem"
                        data-id="{{$document->id}}">
                    <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                </button>

                <a href="{{ route('office.doc.edit', ['type' => $type, 'j_id' => $journal->id, 'hash' => $document->doc_hash] ) }}"
                   class="btn btn-outline-warning btn-rounded m-1">
                    <i class="ik ik-edit"></i> {{ __('action.edit') }}
                </a>
            @endif

            @if($signer)
                @if($journal->status === (int)1)
                    <button type="button"
                            class="btn btn-outline-success btn-rounded m-1 openConfirmForm">
                        <i class="ik ik-send"></i> {{ __('action.send_to_signer') }}
                    </button>
                @endif
            @elseif($agreement_users && $agreement_users->count())
                @if($journal->status === (int)1)
                    <button type="button"
                            class="btn btn-outline-success btn-rounded m-1 openConfirmForm">
                        <i class="ik ik-send"></i> {{ __('action.send_to_ag') }}
                    </button>
                @endif
            @else
                @if($journal->status === (int)1)
                    <button type="button"
                            class="btn btn-outline-success btn-rounded m-1 openConfirmForm">
                        <i class="ik ik-feather"></i> {{ __('action.send_for_signature') }}
                    </button>
                @endif
            @endif

            @if(in_array($journal->status, [9,10,11]) && $journal->journal->doc_code !== 'BASE_INCOMING')
                @include('qr-code-doc.qr-code-view')
                <input id="doc_id" value="{{ $document->id }}" hidden>
                <input id="doc_type" value="work_doc" hidden>
            @endif
            @if($journal->status === (int)1)
                <div class="card-body1 confirmForm mt-2" style="display: none">
                    <div id="loading" class="loading-gif" style="display: none"></div>
                    <form name="testform" id="loginSignForm">
                        @csrf
                        <div class="form-group row">
                            @if($signType->doc_sign_type === 1)
                                <div class="col-sm-12">
                                    <label><i class="fa fa-key"></i> {{ __('action.select_ecp_key') }}
                                    </label>
                                    <label id="message"></label>
                                    <select name="key" class="form-control form-control-primary select21"
                                            style="width: 100%"
                                            onchange="cbChanged(this)"></select>
                                </div>
                            @endif
                            <div class="col-sm-12 mt-2">
                                <label hidden><i class="fa fa-key"></i> {{ __('action.to_sign_by_key') }}
                                </label>
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
                                <img src="{{ url('loading.gif') }}" style="display: none" width="60px"
                                     class="loadingGif">
                            </div>
                        </div>
                        <div class="form-group">
                            @if($signer)
                                <input name="ecp_doc_type" value="send_to_sign" hidden/>
                            @elseif($agreement_users && $agreement_users->count())
                                <input name="ecp_doc_type" value="send_to_agreement" hidden/>
                            @else
                                <input name="ecp_doc_type" value="send_to_create_res" hidden/>
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

@include('templates.ecp.login-sign')
@include('templates.download')

