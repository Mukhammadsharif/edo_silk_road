@foreach($childs as $key => $child)
    <div class="submenu-item nested-item dep_id dep{{ $child->id }}" data-id="{{$child->id}}">
        <a href="{{ route($route, $child->doc_hash) }}"
           class="{{ str_contains(url()->current(), $child->doc_hash) ? 'active-link' : '' }}">
            <i data-lucide="minus" style="width: 16px; height: 16px;"></i>
            <span>{{ $child->lang($child->id) }}</span>

            @if($route == 'protocol-members')
                @if($child->menuCountMem($child->id) > 0)
                    <span class="badge-count">{{ $child->menuCountMem($child->id) }}</span>
                @endif
            @else
                @if($child->menuCount($child->id) > 0)
                    <span class="badge-count">{{ $child->menuCount($child->id) }}</span>
                @endif
            @endif
        </a>

        @if(count($child->child) > 0)
            <div class="nested-submenu">
                @include('layouts.sidebarChild', ['childs' => $child->child, 'route' => $route])
            </div>
        @endif
    </div>
@endforeach

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

<style>
    .nested-item a {
        padding-left: 5rem !important;
    }

    .nested-item a:hover {
        padding-left: 5.3rem !important;
    }

    .nested-submenu .nested-item a {
        padding-left: 6rem !important;
    }

    .nested-submenu .nested-item a:hover {
        padding-left: 6.3rem !important;
    }

    .nested-item .badge-count {
        margin-left: auto;
    }
</style>
