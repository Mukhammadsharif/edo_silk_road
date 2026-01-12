@foreach($childs as $key => $child)
    <div class="submenu-item nested-item dep_id dep{{ $child->id }}" data-id="{{$child->id}}">
        <a href="{{ route($route, $child->doc_hash) }}"
           class="{{ str_contains(url()->current(), $child->doc_hash) ? 'active-link' : '' }}">
            @if(!count($child->child))
                <i data-lucide="minus" style="width: 16px; height: 16px;"></i>
            @else
                <i data-lucide="folder" style="width: 16px; height: 16px;"></i>
            @endif
            <span>{{ $child->lang($child->id) }}</span>
        </a>

        @if(count($child->child) > 0)
            <div class="nested-submenu">
                @include('layouts.sidebarChildPrtDocs', ['childs' => $child->child, 'route' => $route])
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
</style>
