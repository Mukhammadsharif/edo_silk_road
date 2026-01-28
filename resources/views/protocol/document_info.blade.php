<div class="view-sidebar view-sidebar-left">
    <div class="info-card-modern">
        <div class="info-card-header">
            <i data-lucide="info" class="text-white"></i>
            <h3>{{ __('document.document_info') }}</h3>
        </div>
        <div class="info-card-body">
            <div class="form-group">
                <span class="text-muted">{{ __('field.doc_type') }}</span>
                <h6>
                    @php($doc_type = $document->docType->id??'')
                    @if($doc_type)
                        {{ $document->docType->lang($doc_type) }}
                    @endif
                </h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.doc_name') }}</span>
                <h6>{{ $document->doc_name }}</h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.doc_number') }}</span>
                <h6>{{ $document->doc_number }}</h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.doc_lang') }}</span>
                <h6><i class="ik ik-globe"></i>
                    @switch($document->doc_lang)
                        @case('uz')
                            {{ __('lang.uzbek') }}
                            @break
                        @case('kr')
                            {{ __('lang.cyrillic') }}
                            @break
                        @case('ru')
                            {{ __('lang.russian') }}
                            @break
                        @case('en')
                            {{ __('lang.english') }}
                            @break
                        @default
                            {{ __('message.error_message') }}
                    @endswitch
                </h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.doc_date') }}</span>
                <h6><i class="ik ik-calendar"></i> {{ date('d.m.Y', strtotime($document->doc_date)) }}</h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.visibility') }}</span>
                <h6>@if($document->visibility == 1) {{ __('field.secret') }}@else {{ __('field.public') }} @endif</h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.status') }}</span>
                <h6>
                    <span class="badge badge-pill {{ $document->statusName->badgeClass->name??'' }}">
                        {{ $document->statusDoc($document->status) }}
                    </span>
                </h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.doc_term') }}</span>
                <h6>
                    @php($doc_term = $document->doc_term??'')
                    @if($doc_term)
                        {{ date('d.m.Y', strtotime($document->doc_term)) }}
                    @else
                        {{ __('field.absent') }}
                    @endif
                </h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.doc_secretary') }}</span>
                <h6>
                    @php($secretaryShortName = $document->secretary->personal??0)
                    @if($secretaryShortName)
                        {{ $secretaryShortName->shortName() }}
                    @endif
                </h6>
            </div>

            <div class="form-group">
                <span class="text-muted">{{ __('field.created_date') }}</span>
                <h6>{{ date('d.m.Y, H:i', strtotime($document->created_at)) }}</h6>
            </div>

            <div class="form-group">
                <span class="text-muted mb-10">{{ __('field.doc_files') }}</span>
                @forelse($documentFiles as $key => $file)
                    <div class="sl-item"><hr>
                        <div class="sl-right pb-10">
                            @switch(strtolower($file->file_ext))
                                @case('jpg')
                                @case('jpeg')
                                @case('png')
                                    <a href="#" target="_blank" class="link"
                                       onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>',
                                                           'modal',
                                                           'width=800,height=900,top=30,left=500');
                                                           return false;">
                                        {{ $file->file_name }}
                                    </a>
                                    <div class="like-comm float-right">
                                        <a href="{{ route('prt-document-file.download', ['id' =>$file->id]) }}"
                                           class="link text-secondary"><i data-lucide="download"></i>{{ __('action.download') }}
                                        </a>
                                        <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                    </div>
                                    @break
                                @case('pdf')
                                    <a href="#" onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>',
                                                           'modal',
                                                           'width=800,height=900,top=30,left=500');
                                                           return false;">
                                        {{ $file->file_name }}
                                    </a>
                                    <div class="like-comm float-right">
                                        <a href="{{ route('prt-document-file.download', ['id' =>$file->id]) }}"
                                           class="link text-secondary"><i data-lucide="download"></i>{{ __('action.download') }}
                                        </a>
                                        <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                    </div>
                                    @break
                                @default
                                    <div class="like-comm float-right">
                                        <a href="{{ route('prt-document-file.download', ['id' =>$file->id]) }}"
                                           class="link text-secondary">{{ $file->file_name }}
                                            <i data-lucide="download"></i>{{ __('field.download') }}
                                            <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                        </a>
                                    </div>
                            @endswitch
                        </div>
                    </div>
                @empty
                    <p class="text-left">{{ __('field.absent') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
