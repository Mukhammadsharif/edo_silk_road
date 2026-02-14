<div class="card printPage" id="printPage">
    <div class="card-header"><h3>{{ __('field.doc_text') }}</h3></div>
    <div class="card-body">
        @if($html_doc)
            {!! $html_doc !!}
        @endif

        @forelse($documentFiles as $key => $file)
            @switch(strtolower($file->file_ext))
                @case('jpg')
                @case('jpeg')
                @case('png')
                @case('pdf')
                    {{ $file->file_name }}
                    <iframe src="{{ route('file.pre-view',['id'=>$file->id]) }}"
                            style="width: 100%; height: 800px;"></iframe>
                    <hr>
                    @break
                    @break
            @endswitch
        @empty

        @endforelse
    </div>
</div>
