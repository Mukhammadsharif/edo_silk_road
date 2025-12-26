<div class="col-md-2">
    <div class="card">
        <div class="card-header"><h3>{{ __('document.document_info') }}</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('document.document') }}:</strong>
                        <span class="badge badge-blue">
                            {{ $journal->journal->lang($journal->journal->lang_id)??'-' }}
                        </span>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('document.confidentiality') }}:</strong>
                        <span class="badge badge-light-blue">
                            {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                        </span>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('document.doc_date') }}:</strong>
                        {{ \Carbon\Carbon::parse($document->doc_date)->format('d.m.Y') }}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('document.signing_term') }}:</strong>
                        @if($document->signing_term)
                            @php $date = \Carbon\Carbon::parse($document->signing_term);
                                            $now = \Carbon\Carbon::parse($document->created_at);
                                            if ($document->status > 1) {
                                                $now = \Carbon\Carbon::now();
                                            }
                                            $diff = $date->diffInDays($now); @endphp
                            {{ \Carbon\Carbon::parse($document->signing_term)->format('d.m.Y') }}<br>
                            @if($document->signing_term < date('Y-m-d', strtotime(now())))
                                <span class="badge badge-pill badge-yellow">
                                                    <i class="ik ik-alert-circle"></i> {{ __('document.overdue') }}<br>
                                                        {{ $diff }} {{ __('document.day') }}
                                                </span>
                            @else
                                <span class="badge badge-pill badge-light-blue {{ ($diff === 0 ? "text-danger":"") }}">
                                                    <i class="ik ik-clock"></i>
                                                    @switch(session()->get('locale'))
                                        @case('ru')
                                            {{ __('document.remainder') }} {{ $diff }} {{ __('document.day') }}
                                            @break
                                        @case('en')
                                            {{ __('document.remainder') }} {{ $diff }} {{ __('document.day') }}
                                            @break
                                        @default
                                            {{ $diff }} {{ __('document.day') }} {{ __('document.remainder') }}
                                    @endswitch
                                                </span>
                            @endif
                        @endif
                    </div>
                </div>

                @if($journal->doc_type === 1 && $journal->j_type === 1)
                    <div class="col-md-12">
                        <div class="form-group">
                            <strong>{{ __('document.from_org') }}:</strong>
                            @switch(session()->get('locale'))
                                @case('ru')
                                    {{ $journal->docOrg->org_name_ru??'-' }}
                                    @break
                                @case('en')
                                    {{ $journal->docOrg->org_name_en??'-' }}
                                    @break
                                @default
                                    {{ $journal->docOrg->org_name??'-' }}
                            @endswitch
                        </div>
                    </div>
                @elseif($journal->doc_type === 1 && $journal->j_type === 2)
                    <div class="col-md-12">
                        <div class="form-group">
                            <strong>{{ __('document.to_org') }}:</strong>
                            @switch(session()->get('locale'))
                                @case('ru')
                                    {{ $journal->docOrg->org_name_ru??'-' }}
                                    @break
                                @case('en')
                                    {{ $journal->docOrg->org_name_en??'-' }}
                                    @break
                                @default
                                    {{ $journal->docOrg->org_name??'-' }}
                            @endswitch
                        </div>
                    </div>
                @endif

                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('field.creator') }}:</strong>
                        {{ $journal->fromUser->personal->shortName()??'' }}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('document.to_whom') }}:</strong>
                        {{ $directorDocument->toUser->personal->shortName()??'' }}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('document.doc_name') }}:</strong>
                        {{ $document->doc_name }}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <strong>{{ __('document.doc_files') }}:</strong>
                        @forelse($documentFiles as $key => $file)
                            @switch(strtolower($file->file_ext))
                                @case('jpg')
                                @case('jpeg')
                                @case('png')
                                    <div class="sl-item">
                                        <div class="sl-right">
                                            <div>
                                                <a href="{{ route('file.pre-view',['id'=>$file->id]) }}"
                                                   target="_blank" class="link"
                                                   onclick="window.open('<?php echo('/file/pre-view/' . $file->id); ?>',
                                                   'modal',
                                                   'width=800,height=900,top=30,left=500');
                                                   return false;"><i
                                                        class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                </a>
                                                <div class="like-comm float-right">
                                                    <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                       class="link mr-10 text-warning"><i
                                                            class="ik ik-download"></i>{{ __('download') }}
                                                    </a>
                                                    <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                        KB)</i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    @break

                                @case('pdf')
                                    <div class="sl-item">
                                        <div class="sl-right">
                                            <div>
                                                <a href="{{ route('file.pre-view',['id'=>$file->id]) }}"
                                                   target="_blank" class="link"
                                                   onclick="window.open('<?php echo('/file/pre-view/' . $file->id); ?>',
                                                   'modal',
                                                   'width=800,height=900,top=30,left=500');
                                                   return false;">
                                                    <i class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                </a>
                                                <div class="like-comm float-right">
                                                    <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                       class="link mr-10 text-warning"><i
                                                            class="ik ik-download"></i>{{ __('action.download') }}
                                                    </a>
                                                    <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                        KB)</i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    @break

                                @default
                                    <div class="sl-item">
                                        <div class="sl-right">
                                            <div>
                                                <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                   class="link"><i
                                                        class="fa fa-search-plus"></i> {{ $file->file_name }}
                                                </a>
                                                <div class="like-comm float-right">
                                                    <a href="{{ route('file.download', ['id'=>$file->id]) }}"
                                                       class="link mr-10 text-warning"><i
                                                            class="ik ik-download"></i>{{ __('action.download') }}
                                                    </a>
                                                    <i class="text-muted">({{ number_format($file->file_size/1024, 2) }}
                                                        KB)</i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    @break

                            @endswitch

                        @empty
                            <p class="text-left">{{ __('field.absent') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
