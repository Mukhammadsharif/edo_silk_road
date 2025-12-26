{{--@forelse($documentFiles as $key => $file)--}}
{{--    @switch(strtolower($file->file_ext))--}}
{{--        @case('jpg')--}}
{{--        @case('jpeg')--}}
{{--        @case('png')--}}
{{--        @case('pdf')--}}
{{--            <span class="iframe-text">{{ $file->file_name }}</span>--}}
{{--            <iframe src="{{ route('file.pre-view',['id'=>$file->id]) }}"--}}
{{--                    style="width: 100%; height: 800px" class="iframe-content"></iframe>--}}
{{--            <span class="iframe-text"><hr></span>--}}
{{--            @break--}}
{{--            @break--}}
{{--    @endswitch--}}
{{--@empty--}}
{{--@endforelse--}}
