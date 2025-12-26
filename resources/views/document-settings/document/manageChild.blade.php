<ul>
    @foreach($childs as $key => $child)
        <li>
            <i>{{ $child->order_number }}. {{ $child->lang($child->lang_id) }}</i> - ({{ $child->doc_code }})
            <a href="{{ route('document-type.edit', $child->id) }}">
                <i class="ik ik-edit-1 f-16 ml-5 text-info"></i>
            </a>
            @if(count($child->childs))
                @include('document-settings.document.manageChild',['childs' => $child->childs])
            @endif
        </li>
    @endforeach
</ul>
