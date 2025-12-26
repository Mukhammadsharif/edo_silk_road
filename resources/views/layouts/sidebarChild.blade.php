<ul>
    @foreach($childs as $key => $child)
        <li class="dep_id dep{{ $child->id }}" data-id="{{$child->id}}">
            <a href="{{ route($route, $child->doc_hash) }}" class="menu-item {{ str_contains(url()->current(), $child->doc_hash) ? 'active': '' }}">
                {{ $child->lang($child->id) }}
                @if($route == 'protocol-members')
                    @if($child->menuCountMem($child->id))
                        <span class="badge badge-danger count-position">
                        {{ $child->menuCountMem($child->id) }}
                    </span>
                    @endif
                @else
                    @if($child->menuCount($child->id))
                        <span class="badge badge-danger count-position">
                        {{ $child->menuCount($child->id) }}
                    </span>
                    @endif
                @endif
            </a>
            @if(count($child->child))
                @include('layouts.sidebarChild',['childs' => $child->child])
            @endif
        </li>
    @endforeach
</ul>
