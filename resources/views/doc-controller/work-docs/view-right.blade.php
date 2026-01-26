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
                    @if($directorResolution->res_term)
                        @include('templates.director-resolution-res-term')
                    @endif
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
                            <i data-lucide="clock"></i>
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

    <div class="card table-card">
        <div class="card-header">
            <h3>{{ __('action.action') }}</h3>
        </div>
        <div class="card-body">
            <button type="button" onclick="printContent()" class="btn btn-outline-secondary btn-rounded mr-5">
                <i data-lucide="printer"></i> {{ __('action.print') }}
            </button>

            <button type="button" onclick="downloadAsPDF()" class="btn btn-outline-dark m-1 btn-rounded">
                <i data-lucide="download"></i> {{ __('action.download') }}
            </button>
            <input id="downloadName" value="{{ $journal->journal->lang($journal->journal->lang_id)??'-' }} - {{ $journal->fromUser->personal->shortName()??'' }}" hidden/>

            @if(in_array('IT admin', $authrole) && $journal->status != 13)
                <button type="button" class="btn btn-outline-danger btn-rounded mr-5" data-toggle="modal" data-target="#cancelModel" data-id="{{ $journal->doc_id }}">
                    <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                </button>
            @endif
            <br><br>
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
