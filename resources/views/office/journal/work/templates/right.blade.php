<div class="col-md-4">
    @if($directorResolution)
        <div class="card table-card">
            <div class="card-header">
                <h3>{{ __('execution') }}</h3>
            </div>

            <div class="card-block">
                <div class="table-responsive">
                    <table class="table mb-0 without-header">
                        <tbody>
                        @foreach($selectedPerformers as $key => $item)
                            <tr>
                                <td>
                                    <div class="d-inline-block align-middle">
                                        <div class="d-inline-block">
                                            <h6 class="mb-0">{{ $item->toUser->personal->fullName()??'' }}</h6>
                                            <p class="text-muted mb-0">
                                                {{ $item->toUser->userPost->lang($item->toUser->usePost->id)??'' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <h6 class="fw-500">
                                        <span class="badge badge-primary">
{{--                                            @if(session()->get('locale') === 'ru')--}}
                                            {{--                                                {{ $item->depHeadRole->role_name_ru??'-' }}--}}
                                            {{--                                            @else--}}
                                            {{--                                                {{ $item->depHeadRole->role_name??'-' }}--}}
                                            {{--                                            @endif--}}
                                        </span>
                                    </h6>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if(count($edoCancelMessages))
                <div class="ml-20">
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

            <div class="profile-pic ml-20">
                <p>
                    <strong>{{ __('res.res_type') }}:</strong>
                    <span class="badge badge-aqua">
                                @if(session()->get('locale') === 'ru')
                            {{ $directorResolution->resType->task_name_ru??'' }}
                        @else
                            {{ $directorResolution->resType->task_name??'' }}
                        @endif
                            </span>
                </p>
                <strong>{{ __('res.res_text') }}:</strong> {!! $directorResolution->res_text??'' !!}
            </div>

            <div class="p-4 border-top mt-15">
                <div class="row text-center">
                    <div class="col-4 border-right">
                        <p>{{ __('status.exec_status') }}</p>
                        <span class="badge badge-pill {{ $journal->statusWork->bg_class??'' }}">
                                    {{ __($journal->statusWork->lang_code??'unk') }}
                                </span>
                    </div>
                    <div class="col-4 border-right">
                        <p>{{ __('status.date_sent_for_execution') }}</p>
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
                <h3>{{ __('document.execution') }}</h3>
            </div>

            @if(count($edoCancelMessages))
                <div class="ml-20 mt-10">
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

            <div class="p-4 border-top mt-15">
                <div class="row text-center">
                    <div class="col-4 border-right">
                        <p>{{ __('status.exec_status') }}</p>
                        @if($journal->statusWork)
                            <span class="ml-10 badge {{ $journal->statusWork->badgeClass??'' }}">
                                        {{ $journal->statusWork->lang($journal->statusWork->id)??'' }}
                                    </span>
                        @else
                            <span class="ml-10 badge {{ $status->bg_class??'' }}">
                                        {{ __($status->lang_code??'unk') }}
                                    </span>
                        @endif
                    </div>
                    <div class="col-4 border-right">
                        <p>
                            @if($journal->status === 1)
                                {{ __('time.created_at') }}
                            @else
                                {{ __('status.date_sent_for_execution') }}
                            @endif
                        </p>
                        {{ \Carbon\Carbon::parse($journal->created_at)->format('d.m.y, H:i') }}
                    </div>
                    <div class="col-4">
                        <p>
                            @if(session()->get('locale') === 'ru')
                                {{ $directorDocument->toUser->userPost->post_name_ru??'' }}
                            @else
                                {{ $directorDocument->toUser->userPost->post_name??'' }}
                            @endif
                        </p>
                        <h6 class="fw-700">{{ $directorDocument->toUser->personal->shortName()??'' }}</h6>
                    </div>
                </div>
            </div>
        </div>
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
                                                                class="ik ik-download"></i>{{ __('download') }}
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

                            @if($doc->user_role === 4)
                                <div class="mt-10">
                                            <span class="badge badge-pill {{ $doc->statusReply->bg_class??'' }}">
                                                {{ __($doc->statusReply->lang_code??'unk') }}
                                            </span>
                                </div>
                            @endif

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

    @if($isAddResolution && $journal->user_role === 1)
        <div class="card table-card">
            <div class="card-header">
                <h3><i class="ik ik-users"></i> {{ __('document.additional_performers') }}</h3>
            </div>

            <div class="card-block">
                <div class="table-responsive">
                    <table class="table mb-0 without-header">
                        <tbody>
                        @foreach($selectedAddDepHeadPerformers as $key => $item)
                            <tr>
                                <td>
                                    <div class="d-inline-block align-middle">
                                        <div class="d-inline-block">
                                            <h6 class="mb-0">{{ $item->toUser->personal->fullName()??'' }}</h6>
                                            <p class="text-muted mb-0">
                                                @if(session()->get('locale') === 'ru')
                                                    {{ $item->toUser->userPost->post_name_ru??'' }}
                                                @else
                                                    {{ $item->toUser->userPost->post_name??'' }}
                                                @endif {{ $item->created_at }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <h6 class="fw-500">
                                                 <span class="badge badge-primary">
                                                    @if(session()->get('locale') === 'ru')
                                                         {{ $item->depHeadRole->role_name_ru??'' }}
                                                     @else
                                                         {{ $item->depHeadRole->role_name??'' }}
                                                     @endif
                                                </span>
                                    </h6>
                                </td>
                            </tr>
                        @endforeach

                        @foreach($selectedAddDepEmpPerformers as $key => $item)
                            <tr>
                                <td>
                                    <div class="d-inline-block align-middle">
                                        <div class="d-inline-block">
                                            <h6 class="mb-0">{{ $item->toUser->personal->fullName()??'' }}</h6>
                                            <p class="text-muted mb-0">
                                                @if(session()->get('locale') === 'ru')
                                                    {{ $item->toUser->userPost->post_name_ru??'' }}
                                                @else
                                                    {{ $item->toUser->userPost->post_name??'' }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <h6 class="fw-500">
                                                <span class="badge badge-primary">
                                                    @if(session()->get('locale') === 'ru')
                                                        {{ $item->depEmpRole->role_name_ru??'' }}
                                                    @else
                                                        {{ $item->depEmpRole->role_name??'' }}
                                                    @endif
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

    <div class="card table-card">
        <div class="card-header">
            <h3>{{ __('title.action') }}</h3>
        </div>
        <div class="card-body">
            <button type="button" onclick="" class="btn btn-outline-secondary btn-rounded mr-5" id="printButton">
                <i data-lucide="printer"></i> {{ __('action.print') }}
            </button>

{{--            <a href="{{ route('pdf.work-doc', [$document->id, $document->doc_hash]) }}"--}}
{{--               class="btn btn-outline-secondary btn-rounded">--}}
{{--                <i data-lucide="download"></i> {{ __('action.download') }}--}}
{{--            </a>--}}
            <br><br>
            @if($journal->status === (int)1)
                <button type="button" class="btn btn-outline-danger btn-rounded mr-5 deleteItem"
                        data-id="{{$document->id}}">
                    <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                </button>
                <a href="{{ route('office.doc.edit', [$journal->id, $document->doc_hash] ) }}"
                   class="btn btn-outline-info btn-rounded mr-5">
                    <i class="ik ik-edit"></i> {{ __('action.edit') }}
                </a>
            @endif

            @if($isAgUserCount > 0)
                @if($journal->status === (int) 1 && $signer->count())
                    <button type="button"
                            class="btn btn-success btn-rounded openConfirmForm">
                        <i class="ik ik-send"></i> {{ __('action.send_for_signature') }}
                    </button>
                @else
                    <button type="button"
                            class="btn btn-success btn-rounded openConfirmForm">
                        <i class="ik ik-send"></i> {{ __('action.send_to_ag') }}
                    </button>
                @endif
            @else
                @if($journal->status === (int)1)
                    <button type="button"
                            class="btn btn-success btn-rounded openConfirmForm">
                        <i class="ik ik-send"></i> {{ __('action.send_for_signature') }}
                    </button>
                @endif
            @endif

            @if(in_array($journal->status, [9,10]))
                @include('qr-code-doc.qr-code-view')
                <input id="doc_id" value="{{ $document->id }}" hidden>
                <input id="doc_type" value="work_doc" hidden>
            @endif

            @if($journal->status === (int)1)
                <div class="card-body confirmForm bg-light mt-5" style="display: none">
                    <div id="loading" class="loading-gif" style="display: none"></div>
                    <form name=testform>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}
                                </label><br>
                                <label id="message"></label>
                                <select name="key" class="form-control form-control-primary select21" style="width: 100% !important;"
                                        onchange="cbChanged(this)"></select>
                            </div>
                            <div class="col-sm-8 mt-5">
                                <label hidden><i data-lucide="key-round"></i> {{ __('type.to_sign_by_key') }}
                                </label><br>
                                <button type="button" class="btn btn-success modal-bg-class"
                                        onclick="signLogin()" hidden>
                                    <span class="confirm_type">{{ __('type.to_sign') }}</span> (LOGIN)
                                </button>
                                <button type="button" class="btn btn-success modal-bg-class"
                                        onclick="sign()">
                                    <span class="confirm_type">{{ __('type.to_sign') }}</span> (ERI)
                                </button>
                            </div>
                            <div class="col-sm-4 mt-5">
                                <img src="{{ url('loading.gif') }}" style="display: none" width="60px" class="loadingGif">
                            </div>
                        </div>
                        <div class="form-group">
                            @if(in_array($journal->doc_type, [3, 14, 21, 23]) && $signer->count())
                                <input name="ecp_doc_type" value="send_to_signer" hidden/>

                            @else
                                @if($isAgUserCount > 0)
                                    <input name="ecp_doc_type" value="send_to_ag" hidden/>
                                @else
                                    <input name="ecp_doc_type" value="send_for_sign" hidden/>
                                @endif
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

</div>
