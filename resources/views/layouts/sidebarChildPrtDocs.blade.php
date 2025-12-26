<ul>
    @foreach($childs as $key => $child)
        <li class="dep_id dep{{ $child->id }}" data-id="{{$child->id}}">
            <a href="{{ route($route, $child->doc_hash) }}" class="menu-item">
                {{ $child->lang($child->id) }}
            </a>
            @if(count($child->child))
                @include('layouts.sidebarChildPrtDocs',['childs' => $child->child])
            @endif
        </li>
    @endforeach
</ul>
