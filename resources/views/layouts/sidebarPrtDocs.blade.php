@if($item->menuList->code === 'PRT_DOCS_S')
    @php($prt_docs = $item->getPrtDocs($item->menu_id))
    @if($prt_docs)
        <ul id="tree4">
            @foreach($prt_docs->child as $key2 => $item3)
                <li>
                    <a href="{{ route('prt-secretary', $item3->doc_hash) }}"
                       class="menu-item">
                        @if(!count($item3->child))
                            <i class="ik ik-minus"></i>
                        @endif
                        {{ $item3->lang($item3->id) }}
                    </a>
                    @if(count($item3->child))
                        @include('layouts.sidebarChildPrtDocs',['childs' => $item3->child])
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
@endif
