<div class="app-sidebar colored">
    <div class="sidebar-header">
        <a class="header-brand" href="{{ route('home') }}">
            <span class="text ml-5 small">
                {{ $userShortName }}
            </span>
        </a>
    </div>
    <div class="sidebar-content">
        <div class="nav-container">

            <nav id="main-menu-navigation" class="navigation-main">
                <div class="nav-item has-sub create-doc">
                    <a href="javascript:void(0)" class=""><i class="ik ik-plus"></i><span> Hujjat yaratish</span></a>
                    <div class="submenu-content">
                        <a href="{{ url('/office/document/create/incoming') }}" class="menu-item"><i class="ik ik-chevrons-down"></i> Kiruvchi xat</a>
                        <a href="{{ url('/office/document/create/outgoing') }}" class="menu-item"><i class="ik ik-chevrons-up"></i> Chiquvchi xat</a>
                        <a href="{{ url('/office/document/create/internal') }}" class="menu-item"><i class="ik ik-edit-2"></i> Ichki hujjatlar</a>
                    </div>
                </div>

                @if($sidebarMenus)
                    @foreach($sidebarMenus as $menu)
                        @if($menu->child && $menu->child->count() > 0)
                            <div class="nav-item has-sub {{ $isParentOpen($menu) }} parentMenu" data-menu-id="{{ $menu->id }}">
                                <a href="javascript:void(0)">
                                    <i class="{{ $menu->menuList->iconClass->name??'' }}"></i>
                                    <span>{{ $menu->getMenuName($menu->menu_id) }}</span>
                                    <span class="badge badge-danger count-parent-position1" data-id="{{ $menu->id }}"></span>
                                </a>
                                <div class="submenu-content" style="{{ $isParentOpen($menu) ? 'display:block;' : '' }}">
                                    @foreach($menu->child as $child)
                                        @php($url_path = $child->menuList->url_path??'#')
                                        <a href="{{ $url_path }}"
                                           class="menu-item {{ $isActiveMenu($url_path) }}">
                                            <i class="{{ $child->menuList->iconClass->name??'' }}"></i>
                                            {{ $child->getMenuName($child->menu_id) }}
                                            @if($child->menuList->count && $child->menuCount($child->id) > 0)
                                                <span class="badge badge-danger count-position">
                                                    {{ $child->menuCount($child->id) }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            @php($url_path2 = $menu->menuList->url_path??'#')
                            <div class="nav-item {{ $isActiveMenu($url_path2) }}">
                                <a href="{{ $url_path2 }}">
                                    <i class="{{ $menu->menuList->iconClass->name??'' }}"></i>
                                    <span>{{ $menu->getMenuName($menu->menu_id) }}</span>
                                    @if($menu->menuList->count && $menu->menuCount($menu->id) > 0)
                                        <span class="badge badge-danger count-position">
                                            {{ $menu->menuCount($menu->id) }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="nav-lavel">{{ __('field.empty') }}</div>
                @endif

                {{-- Protocols kategoriyasi --}}
                @if($protocolMenus->count() > 0)
                    <div class="nav-item has-sub {{ request()->is('protocol/*') ? 'open active' : '' }}" data-menu-id="1000">
                        <a href="javascript:void(0)">
                            <i class="ik ik-folder"></i>
                            <span>{{ __('menu.protocols') }}</span>
                            <span class="badge badge-danger count-parent-position1" data-id="1000"></span>
                        </a>
                        <div class="submenu-content" style="{{ request()->is('protocol/*') ? 'display:block;' : '' }}">
                            @foreach($protocolMenus as $menu)
                                @php($url_path2 = $menu->menuList->url_path??'#')
                                <a href="{{ $url_path2 }}"
                                   class="menu-item {{ $isActiveMenu($url_path2) }}">
                                    <i class="{{ $menu->menuList->iconClass->name??'' }}"></i>
                                    {{ $menu->getMenuName($menu->menu_id) }}
                                    @if($menu->menuList->count && $menu->menuCount($menu->id) > 0)
                                        <span class="badge badge-danger count-position">
                                            {{ $menu->menuCount($menu->id) }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </nav>
        </div>
    </div>
</div>

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const menuItems = document.querySelectorAll('.parentMenu');

        menuItems.forEach((item) => {
            let id = item.getAttribute('data-menu-id')
            let element = $(`span[data-id=${id}]`)
            $.ajax({
                url: "{{ route('get-menu-count') }}",
                method: 'POST',
                data: {id: id},
                beforeSend: function(){
                },
            }).done(function(response){
                console.log(response)
                if (response.success === 200) {
                   element.html(response?.count)
                   element.show()
                } else {
                    element.hide()
                }
            }).error(function(err){
                console.log(err)
            })
        })
    });
</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        getActiveMenu()

        function getActiveMenu() {
            const activeMenuId = localStorage.getItem('activeMenu');

            const menuItems = document.querySelectorAll('.parentMenu');

            menuItems.forEach((item) => {
                if(item.getAttribute('data-menu-id') !== activeMenuId) {
                    // item.classList.remove('open')
                }
            })

            if (activeMenuId) {
                const activeMenuElement = document.querySelector(`[data-menu-id="${activeMenuId}"]`);
                if (activeMenuElement) {
                    const countBadge = activeMenuElement.querySelector('.count-parent-position');
                    if(countBadge) countBadge.style.display = 'none';
                }
            }
        }

        const menuItems = document.querySelectorAll('.parentMenu');

        menuItems.forEach(function (menuItem) {
            menuItem.addEventListener('click', function (e) {

                const currentId = menuItem.getAttribute('data-menu-id')

                menuItems.forEach((item) => {
                    if(item.getAttribute('data-menu-id') !== currentId) {
                        // item.classList.remove('open')
                    }
                })

                localStorage.setItem('activeMenu', menuItem.getAttribute('data-menu-id'));
            });
        });
    });

</script>






