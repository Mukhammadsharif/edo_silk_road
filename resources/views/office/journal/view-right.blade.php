<div class="view-right-modern">
    @if($journal->journal->doc_code !== 'INF_DOC')
        @if($directorResolution)
            <div class="execution-card-modern">
                <div class="card-header-modern">
                    <i data-lucide="users" class="text-white"></i>
                    <h3>{{ __('title.execution') }}</h3>
                </div>

                <div class="performers-list-modern">
                    @foreach($selectedPerformers as $key => $item)
                        <div class="performer-item-modern">
                            <div class="performer-avatar">
                                <i data-lucide="user" class="text-white"></i>
                            </div>
                            <div class="performer-info">
                                <div class="performer-name">{{ $item->toUser->personal->fullName()??'' }}</div>
                                <div class="performer-position">
                                    {{ $item->toDep->lang($item->toDep->id)??'' }},
                                    {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                                </div>
                            </div>
                            <div class="performer-role">
                                <span class="role-badge">
                                    {{ $item->resExecRole->lang($item->resExecRole->lang_id)??'' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="resolution-info-modern">
                    <div class="info-row-modern">
                        <span class="info-label-sm">{{ __('title.res_type') }}:</span>
                        <span class="badge-modern badge-aqua">
                            {{ $directorResolution->resType->lang($directorResolution->resType->lang_id)??'' }}
                        </span>
                    </div>
                    <div class="resolution-text-modern">
                        <strong>{{ __('title.res_text') }}:</strong>
                        <div class="text-content">{!! $directorResolution->res_text??'' !!}</div>
                    </div>
                </div>

                <div class="execution-status-modern">
                    <div class="status-item-modern">
                        <div class="status-label">{{ __('status.exec_status') }}</div>
                        <span class="status-badge-modern {{ $journal->statusWork->badgeClass??'' }}">
                            {{ $journal->statusWork->lang($journal->statusWork->id)??'unk' }}
                        </span>
                    </div>
                    <div class="status-item-modern">
                        <div class="status-label">{{ __('status.exec_date') }}</div>
                        <div class="status-value">
                            <i data-lucide="clock"></i>
                            {{ \Carbon\Carbon::parse($directorResolution->created_at)->format('d.m.y, H:i') }}
                        </div>
                    </div>
                    <div class="status-item-modern">
                        <div class="status-label">
                            @if(session()->get('locale') === 'ru')
                                {{ $directorResolution->fromUser->userPost->post_name_ru??'' }}
                            @else
                                {{ $directorResolution->fromUser->userPost->post_name??'' }}
                            @endif
                        </div>
                        <div class="status-value font-bold">{{ $directorResolution->fromUser->personal->shortName()??'' }}</div>
                    </div>
                </div>
            </div>
        @else
            <div class="execution-card-modern">
                <div class="card-header-modern">
                    <i data-lucide="users" class="text-white"></i>
                    <h3>{{ __('title.execution') }}</h3>
                </div>

                <div class="execution-status-modern">
                    <div class="status-item-modern">
                        <div class="status-label">{{ __('status.exec_status') }}</div>
                        <span class="status-badge-modern {{ $journal->statusWork->badgeClass??'' }}">
                            {{ $journal->statusWork->lang($journal->statusWork->id)??'unk' }}
                        </span>
                    </div>
                    <div class="status-item-modern">
                        <div class="status-label">
                            @if($journal->status === 1)
                                {{ __('field.created_at') }}
                            @else
                                {{ __('field.date_sent_for_execution') }}
                            @endif
                        </div>
                        <div class="status-value">
                            <i data-lucide="clock"></i>
                            {{ \Carbon\Carbon::parse($journal->created_at)->format('d.m.y, H:i') }}
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

        @if(isset($selectedAddPerformers) && $selectedAddPerformers && $selectedAddPerformers->count())
            <div class="execution-card-modern">
                <div class="card-header-modern">
                    <i data-lucide="users" class="text-white"></i>
                    <h3>{{ __('title.additional_performers') }}</h3>
                </div>

                <div class="performers-list-modern">
                    @foreach($selectedAddPerformers as $key => $item)
                        <div class="performer-item-modern">
                            <div class="performer-avatar">
                                <i data-lucide="user"></i>
                            </div>
                            <div class="performer-info">
                                <div class="performer-name">{{ $item->toUser->personal->fullName()??'' }}</div>
                                <div class="performer-position">
                                    {{ $item->toDep->lang($item->toDep->id)??'' }},
                                    {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                                </div>
                            </div>
                            <div class="performer-role">
                                <span class="role-badge">
                                    {{ $item->resExecRole->lang($item->resExecRole->lang_id)??'' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    @if(count($replyDocuments) > 0)
        <div class="execution-card-modern">
            <div class="card-header-modern">
                <i data-lucide="mail" class="text-white"></i>
                <h3>{{ __('title.answer_letters') }}</h3>
            </div>
            <div class="replies-list-modern">
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
                                        <i data-lucide="feather" style="font-size: 18px; cursor: pointer"
                                           onclick="showEspInfo({{ $doc->id }})"></i>

                                        @include('templates.ecp.ecp-information')
                                    @endif
                                </div>
                            @endif
                            <div class="like-comm mt-20">
                                <span class="mr-10">
                                    <i data-lucide="clock"></i> {{ date('d.m.y, H:i', strtotime($doc->updated_at)) }}
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

    <div class="execution-card-modern actions-card">
        <div class="card-header-modern">
            <i data-lucide="settings" class="text-white"></i>
            <h3>{{ __('action.action') }}</h3>
        </div>

        <div class="actions-body-modern-print">
            <button type="button" onclick="printContent()" class="btn-modern btn-secondary">
                <i data-lucide="printer"></i>
                <span>{{ __('action.print') }}</span>
            </button>

            <button type="button" onclick="downloadAsPDF()" class="btn-modern btn-secondary">
                <i data-lucide="download"></i>
                <span>{{ __('action.download') }}</span>
            </button>
            <input id="downloadName"
                   value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}"
                   hidden/>
        </div>

        <div class="actions-body-modern">
            @if(in_array($journal->status, [1, 2, 3]))
                <button type="button" class="btn-modern btn-danger deleteItem"
                        data-id="{{$document->id}}">
                    <i data-lucide="trash-2"></i>
                    <span>{{ __('action.delete') }}</span>
                </button>

                <a href="{{ route('office.doc.edit', ['type' => $type, 'j_id' => $journal->id, 'hash' => $document->doc_hash] ) }}"
                   class="btn-modern btn-warning">
                    <i data-lucide="edit"></i>
                    <span>{{ __('action.edit') }}</span>
                </a>
            @endif

            @if($signer)
                @if($journal->status === (int)1)
                    <button type="button" class="btn-modern btn-success openConfirmForm">
                        <i data-lucide="send"></i>
                        <span>{{ __('action.send_to_signer') }}</span>
                    </button>
                @endif
            @elseif($agreement_users && $agreement_users->count())
                @if($journal->status === (int)1)
                    <button type="button" class="btn-modern btn-success openConfirmForm">
                        <i data-lucide="send"></i>
                        <span>{{ __('action.send_to_ag') }}</span>
                    </button>
                @endif
            @else
                @if($journal->status === (int)1)
                    <button type="button" class="btn-modern btn-success openConfirmForm">
                        <i data-lucide="pen-tool"></i>
                        <span>{{ __('action.send_for_signature') }}</span>
                    </button>
                @endif
            @endif

            @if($journal->status === (int)1)
                <div class="card-body1 confirmForm mt-2" style="display: none">
                    <div id="loading" class="loading-gif" style="display: none"></div>
                    <form name="testform" id="loginSignForm">
                        @csrf
                        <div class="form-group row">
                            @if($signType->doc_sign_type === 1)
                                <div class="col-sm-12">
                                    <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}
                                    </label>
                                    <label id="message"></label>
                                    <select name="key" class="form-control form-control-primary select2"
                                            style="width: 100% !important;"
                                            onchange="cbChanged(this)"></select>
                                </div>
                            @endif
                            <div class="col-sm-12 mt-2">
                                <label hidden><i data-lucide="key-round"></i> {{ __('action.to_sign_by_key') }}
                                </label>
                                @if($signType->doc_sign_type === 2)
                                    <button type="submit" class="btn-modern btn-success modal-bg-class">
                                        <i data-lucide="key"></i>
                                        <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                                    </button>
                                @else
                                    <button type="button" class="btn-modern btn-success modal-bg-class"
                                            onclick="sign()">
                                        <i data-lucide="key"></i>
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

        @if(in_array($journal->status, [9,10,11]) && $journal->journal->doc_code !== 'BASE_INCOMING')
            @include('qr-code-doc.qr-code-view')
            <input id="doc_id" value="{{ $document->id }}" hidden>
            <input id="doc_type" value="work_doc" hidden>
        @endif
    </div>

    @include('office.templates.doc-steps')
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-right.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

@include('templates.ecp.login-sign')
@include('templates.download')

