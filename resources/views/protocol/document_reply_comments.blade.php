@if(count($documentReplyComments) > 0)
    <div class="card">
        <div class="card-header">
            <h5><i class="ik ik-check"></i> {{ __('field.document_signing_history') }}</h5>
        </div>
        <div class="card-body chat-box scrollable" style="max-height: 500px; overflow-x: auto">
            @foreach($documentReplyComments as $key => $comment)
                <div class="sl-item">
                    <div class="sl-left fw-600 f-16"><i class="ik ik-user"></i>
                        @php($user_fio = $comment->userWork->personal??0)
                        @if($user_fio)
                            {{ $user_fio->shortName() }}
                        @endif
                    </div>
                    <div class="sl-right">
                        <p class="text-muted">
                            @php($department = $comment->userWork->department??0)
                            @if($department)
                                {{ $comment->userWork->department->lang($comment->userWork->department->id) }}
                            @endif
-
                            @php($position = $comment->userWork->position??0)
                            @if($department)
                                {{ $comment->userWork->position->lang($comment->userWork->position->id) }}
                            @endif
                        </p>
                        <p class="mt-2">
                            {!! $comment->doc_text !!}
                        </p>
                        <div class="like-comm mt-10">
                            <span class="badge badge-pill {{ $comment->statusName->badgeClass->name??'' }}">
                                {{ $comment->statusDoc($comment->status) }}
                            </span>
                            @if($comment && $comment->status == 3 && $document->protocolEcpInfo($document->id, $comment->user_id)
                                                    && $document->protocolEcpInfo($document->id, $comment->user_id)->docInfo)
                                <i data-lucide="feather" style="font-size: 18px; cursor: pointer" onclick="showEspInfo({{ $comment->id }})"></i>

                                @include('templates.ecp.protocol-ecp-information')
                            @endif
                        </div>
                        <div class="like-comm mt-10">
                            <span>
                                <i data-lucide="clock"></i> {{ date('d.m.Y, H:i', strtotime($comment->created_at)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <hr>
            @endforeach
        </div>
    </div>
@endif
