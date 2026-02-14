@if($item->menuList->code === 'KOL_ORG_S' || $item->menuList->code === 'KOL_ORG')
    @php($col_org = $item->getKolOrg($item->menu_id))
    @php($route = 'prt-secretary')
    @if($item->menuList->code === 'KOL_ORG')
        @php($route = 'protocol-members')
    @endif
    @if($col_org)
        <ul id="tree3">
            @foreach($col_org->child as $key2 => $item2)
                <li>
                    <a href="{{ route($route, $item2->doc_hash) }}"
                       class="menu-item {{ str_contains(url()->current(), $item2->doc_hash) ? 'active': '' }}">
                        @if(!count($item2->child))
                            <i class="ik ik-minus"></i>
                        @endif
                        {{ $item2->lang($item2->id) }}
                        @if($route == 'protocol-members')
                            @if($item2->menuCountMem($item2->id))
                                <span class="badge badge-danger count-position">
                                    {{ $item2->menuCountMem($item2->id) }}
                                </span>
                            @endif
                        @else
                            @if($item2->menuCount($item2->id))
                                <span class="badge badge-danger count-position tree-count-position">
                                    {{ $item2->menuCount($item2->id) }}
                                </span>
                            @endif
                        @endif

{{--                            @if($item->menuList->code === 'KOL_ORG')--}}
{{--                                @php($col_count = 0)--}}
{{--                                @foreach($item2->child as $key3 => $child)--}}
{{--                                    @php($col_count = $col_count + $child->menuCountMem($child->id))--}}
{{--                                @endforeach--}}
{{--                                @if($col_count > 0)--}}
{{--                                    <span class="badge badge-danger count-position">--}}
{{--                                    {{ $col_count }}--}}
{{--                            </span>--}}
{{--                                @endif--}}
{{--                            @endif--}}
                    </a>
                    @if(count($item2->child))
                        @include('layouts.sidebarChild',['childs' => $item2->child])
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
@endif
