<ul>
    @foreach($childs as $key => $child)
        <li class="dep_id dep{{ $child->id }}" data-id="{{ $child->id }}">
            {{ $child->lang($child->id) }}
            @if(count($child->child))
                @include('document-settings.protocol.manageChild', ['childs' => $child->child])
            @endif
        </li>
    @endforeach
</ul>
