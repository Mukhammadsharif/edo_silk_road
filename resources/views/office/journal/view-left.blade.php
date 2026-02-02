<div class="view-left-modern">
    <div class="info-card-modern">
        <div class="info-card-header">
            <i data-lucide="info" class="text-white"></i>
            <h3>{{ __('document.document_info') }}</h3>
        </div>
        <div class="info-card-body">
            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="file-text" class="icon"></i>
                    <span>{{ __('document.document') }}</span>
                </div>
                <div class="info-value">
                    <span class="badge-modern badge-blue">
                        {{ $journal->journal->lang($journal->journal->lang_id)??'-' }}
                    </span>
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="shield" class="icon"></i>
                    <span>{{ __('document.confidentiality') }}</span>
                </div>
                <div class="info-value">
                    <span class="badge-modern badge-light-blue">
                        {{ $journal->docStatus->lang($journal->docStatus->lang_id)??'-' }}
                    </span>
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="file" class="icon"></i>
                    <span>{{ __('document.doc_name') }}</span>
                </div>
                <div class="info-value">
                    {{ $document->doc_name }}
                </div>
            </div>
            @if($document->doc_number)
                <div class="info-item-modern">
                    <div class="info-label">
                        <i data-lucide="hash" class="icon"></i>
                        <span>{{ __('document.doc_number') }}</span>
                    </div>
                    <div class="info-value">
                        <span class="doc-number-badge">{{ $document->doc_number }}</span>
                    </div>
                </div>
            @endif

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="calendar" class="icon"></i>
                    <span>{{ __('document.doc_date') }}</span>
                </div>
                <div class="info-value">
                    {{ \Carbon\Carbon::parse($document->doc_date)->format('d.m.Y') }}
                </div>
            </div>

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="timer" class="icon"></i>
                    <span>{{ __('document.signing_term') }}</span>
                </div>
                <div class="info-value">
                        @if($document->doc_term)
                            {{ \Carbon\Carbon::parse($document->doc_term)->format('d.m.Y') }}<br>
                            @if(!in_array($journal->status, [9,10,11]))
                                @php $date = \Carbon\Carbon::parse($document->doc_term)->startOfDay();
                                     $now = \Carbon\Carbon::parse($document->created_at)->startOfDay();
                                     if ($document->status > 1) {
                                         $now = \Carbon\Carbon::now()->startOfDay();
                                     }
                                     $diff = round($now->diffInDays($date)); @endphp
                                @if($document->doc_term < date('Y-m-d', strtotime(now())))
                                    <span class="badge-modern badge-warning">
                                        <i data-lucide="alert-circle" class="icon"></i>
                                        {{ __('field.overdue') }} {{ $diff }} {{ __('field.day') }}
                                    </span>
                                @else
                                    <span class="badge-modern badge-info {{ ($diff === 0 ? 'badge-danger':'') }}">
                                        <i data-lucide="clock" class="icon"></i>
                                        {{ $diff }} {{ __('field.day') }} {{ __('field.remainder') }}
                                    </span>
                                @endif
                            @endif
                        @else
                            {{ __('field.absent') }}
                        @endif
                </div>
            </div>

            @if($journal->journal->doc_code === 'BASE_INCOMING')
                <div class="info-item-modern">
                    <div class="info-label">
                        <i data-lucide="building" class="icon"></i>
                        <span>{{ __('field.from_org') }}</span>
                    </div>
                    <div class="info-value">
                        {{ $journal->docOrg ? $journal->docOrg->organization->lang($journal->docOrg->organization->id)??'' : '' }}
                    </div>
                </div>
            @elseif($journal->journal->doc_code === 'BASE_OUTGOING')
                <div class="info-item-modern">
                    <div class="info-label">
                        <i data-lucide="send" class="icon"></i>
                        <span>{{ __('field.to_org') }}</span>
                    </div>
                    <div class="info-value">
                        {{ $journal->docOrg ? $journal->docOrg->organization->lang($journal->docOrg->organization->id)??'' : '' }}
                    </div>
                </div>
            @endif

            <div class="info-item-modern">
                <div class="info-label">
                    <i data-lucide="user" class="icon"></i>
                    <span>{{ __('field.creator') }}</span>
                </div>
                <div class="info-value">
                    {{ $journal->fromUser->personal->shortName()??'' }}
                </div>
            </div>

            @if($journal->journal->doc_code !== 'INF_DOC')
                <div class="info-item-modern">
                    <div class="info-label">
                        <i data-lucide="user-check" class="icon"></i>
                        <span>{{ __('document.to_whom') }}</span>
                    </div>
                    <div class="info-value">
                        {{ $directorDocument->toUser->personal->shortName()??'' }}
                    </div>
                </div>
            @endif

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
                                        <a href="{{ route('file.pre-view',['id'=>$file->id]) }}"
                                           target="_blank" class="file-link"
                                           onclick="window.open('<?php echo('/file/pre-view/' . $file->id); ?>', 'modal', 'width=800,height=900,top=30,left=500'); return false;">
                                            {{ $file->file_name }}
                                        </a>
                                        <span class="file-size">({{ number_format($file->file_size/1024, 2) }} KB)</span>
                                    </div>
                                    <div class="file-actions">
                                        <a href="{{ route('file.download', ['id'=>$file->id]) }}" class="btn-file-action" title="{{ __('action.download') }}">
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
                                        <a href="{{ route('file.download', ['id'=>$file->id]) }}" class="btn-file-action" title="{{ __('action.download') }}">
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

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-left.css') }}">
