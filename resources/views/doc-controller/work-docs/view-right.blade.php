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
                                    {{ $item->toDep?->lang($item->toDep?->id) ?? '' }},
                                    {{ $item->toUser?->userPost?->lang($item->toUser?->userPost?->id) ?? '' }}
                                </div>
                            </div>
                            <div class="performer-role">
                                <span class="role-badge">
                                    {{ $item->resExecRole?->lang($item->resExecRole?->lang_id) ?? '' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="resolution-info-modern">
                    <div class="info-row-modern">
                        <span class="info-label-sm">{{ __('title.res_type') }}:</span>
                        <span class="badge-modern badge-aqua">
                            {{ optional($directorResolution->resType)->lang(optional($directorResolution->resType)->lang_id) ?? '' }}
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
                            {{ $journal->statusWork?->lang($journal->statusWork?->id) ?? 'unk' }}
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
                                {{ optional(optional($directorResolution->fromUser)->userPost)->post_name_ru ?? '' }}
                            @else
                                {{ optional(optional($directorResolution->fromUser)->userPost)->post_name ?? '' }}
                            @endif
                        </div>
                        <div
                            class="status-value font-bold">{{ $directorResolution->fromUser->personal->shortName()??'' }}</div>
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
                            {{ $journal->statusWork?->lang($journal->statusWork?->id) ?? 'unk' }}
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
                    {{-- ИСПРАВЛЕНО: блок внутри @if($directorResolution), но лезет
                         в $directorDocument — РАЗНЫЕ переменные (резолюция vs шаг RESOL). --}}
                    @if(!empty($directorDocument) && $directorDocument->toUser)
                        <div class="status-item-modern">
                            <div class="status-label">
                                {{ optional(optional($directorDocument->toUser)->userPost)->lang(optional(optional($directorDocument->toUser)->userPost)->id) ?? '' }}
                            </div>
                            <div class="status-value font-bold">
                                {{ optional($directorDocument->toUser->personal)->shortName() ?? '' }}
                            </div>
                        </div>
                    @endif
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
                                    {{ $item->toDep?->lang($item->toDep?->id) ?? '' }},
                                    {{ $item->toUser?->userPost?->lang($item->toUser?->userPost?->id) ?? '' }}
                                </div>
                            </div>
                            <div class="performer-role">
                                <span class="role-badge">
                                    {{ $item->resExecRole?->lang($item->resExecRole?->lang_id) ?? '' }}
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
                                {{ $doc->fromUser?->department?->lang($doc->fromUser?->department?->id) ?? '' }}
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
                                        {{ $doc->statusReply?->lang($doc->statusReply?->id) ?? 'unk' }}
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

    @include('templates.comment_form')

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
            <input id="downloadName"
                   value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser?->personal?->shortName() ?? '' }}"
                   hidden/>

            @if(in_array('IT_Admin', $authrole) && $journal->status != 13)
                <div class="actions-body-modern">
                    <button type="button" class="btn-modern btn-danger" data-toggle="modal"
                            data-target="#cancelModel" data-id="{{ $journal->doc_id }}">
                        <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                    </button>
                </div>
            @endif

        </div>
    </div>

    @include('office.templates.doc-steps')

    <div class="modal fade" id="cancelModel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="cancelForm">
                @csrf
                @method('POST')
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

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-right.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
