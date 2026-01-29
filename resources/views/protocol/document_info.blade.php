<div class="view-sidebar view-sidebar-left">
    <div class="info-card-modern">
        <div class="info-card-header">
            <i data-lucide="info" class="text-white"></i>
            <h3>{{ __('document.document_info') }}</h3>
        </div>
        <div class="info-card-body">
            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="file-text" class="icon"></i>
                    <span>{{ __('field.doc_type') }}</span>
                </div>
                <div class="info-value">
                    <span class="badge-modern badge-blue">
                        @php($doc_type = $document->docType->id??'')
                        @if($doc_type)
                            {{ $document->docType->lang($doc_type) }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="file" class="icon"></i>
                    <span>{{ __('field.doc_name') }}</span>
                </div>
                <div class="info-value">
                    {{ $document->doc_name }}
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="hash" class="icon"></i>
                    <span>{{ __('field.doc_number') }}</span>
                </div>
                <div class="info-value">
                    <span class="doc-number-badge">{{ $document->doc_number }}</span>
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="globe" class="icon"></i>
                    <span>{{ __('field.doc_lang') }}</span>
                </div>
                <div class="info-value">
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
                            {{ __('field.absent') }}
                    @endswitch
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="calendar" class="icon"></i>
                    <span>{{ __('field.doc_date') }}</span>
                </div>
                <div class="info-value">
                    {{ date('d.m.Y', strtotime($document->doc_date)) }}
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="shield" class="icon"></i>
                    <span>{{ __('field.visibility') }}</span>
                </div>
                <div class="info-value">
                    <span class="badge-modern badge-light-blue">
                       @if($document->visibility == 1) {{ __('field.secret') }}@else {{ __('field.public') }} @endif
                    </span>
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="chart-line" class="icon"></i>
                    <span>{{ __('field.status') }}</span>
                </div>
                <div class="info-value">
                    <span class="badge badge-pill {{ $document->statusName->badgeClass->name??'' }}">
                        {{ $document->statusDoc($document->status) }}
                    </span>
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="calendar" class="icon"></i>
                    <span>{{ __('field.doc_term') }}</span>
                </div>
                <div class="info-value">
                    @php($doc_term = $document->doc_term??'')
                    @if($doc_term)
                        {{ date('d.m.Y', strtotime($document->doc_term)) }}
                    @else
                        {{ __('field.absent') }}
                    @endif
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="user" class="icon"></i>
                    <span>{{ __('field.doc_secretary') }}</span>
                </div>
                <div class="info-value">
                    @php($secretaryShortName = $document->secretary->personal??0)
                    @if($secretaryShortName)
                        {{ $secretaryShortName->shortName() }}
                    @endif
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="calendar" class="icon"></i>
                    <span>{{ __('field.created_date') }}</span>
                </div>
                <div class="info-value">
                    {{ date('d.m.Y, H:i', strtotime($document->created_at)) }}
                </div>
            </div>

            <div class="info-item-modern files-section">
                <div class="info-label">
                    <i data-lucide="paperclip" class="icon"></i>
                    <span>{{ __('document.doc_files') }}</span>
                </div>
                <div class="files-list">
                    @forelse($documentFiles as $key => $file)
                        @switch(strtolower($file->file_ext))
                            @case('jpg')
                            @case('jpeg')
                            @case('png')
                            @case('pdf')
                                <div class="file-item-modern">
                                    <div class="file-icon">
                                        <i data-lucide="file-image" class="icon text-white"></i>
                                    </div>
                                    <div class="file-info">
                                        <a href="#"
                                           target="_blank" class="file-link"
                                           onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>', 'modal', 'width=800,height=900,top=30,left=500'); return false;">
                                            {{ $file->file_name }}
                                        </a>
                                        <span class="file-size">({{ number_format($file->file_size/1024, 2) }} KB)</span>
                                    </div>
                                    <div class="file-actions">
                                        <a href="{{ route('prt-document-file.download', ['id' =>$file->id]) }}" class="btn-file-action" title="{{ __('action.download') }}">
                                            <i data-lucide="download" class="icon"></i>
                                        </a>
                                    </div>
                                </div>
                                @break

                            @default
                                <div class="file-item-modern">
                                    <div class="file-icon">
                                        <i data-lucide="file" class="icon text-white"></i>
                                    </div>
                                    <div class="file-info">
                                        <a href="{{ route('file.download', ['id'=>$file->id]) }}" class="file-link">
                                            {{ $file->file_name }}
                                        </a>
                                        <span class="file-size">({{ number_format($file->file_size/1024, 2) }} KB)</span>
                                    </div>
                                    <div class="file-actions">
                                        <a href="{{ route('prt-document-file.download', ['id' =>$file->id]) }}" class="btn-file-action" title="{{ __('action.download') }}">
                                            <i data-lucide="download" class="icon"></i>
                                        </a>
                                    </div>
                                </div>
                                @break
                        @endswitch
                    @empty
                        <p class="empty-files">{{ __('field.absent') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
