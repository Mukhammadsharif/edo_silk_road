<div class="card printPage" id="printPage">
    <div class="card-header"><h3>{{ __('field.doc_text') }}</h3></div>
    <div class="card-body">
        @if($html_doc)
            {!! $html_doc !!}
        @endif

            @forelse($documentFiles as $file)
                @include('templates.file-viewer', ['file' => $file])
            @empty
                <div class="empty-document-state">
                    <i data-lucide="file-x"></i>
                    <p>{{ __('message.no_document_files') ?? 'Файлы документа не найдены' }}</p>
                </div>
            @endforelse
    </div>
</div>
