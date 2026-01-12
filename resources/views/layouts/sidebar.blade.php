@php use App\Models\Admin\RequestForOrgs;use Illuminate\Support\Facades\Auth; @endphp
@php $user = App\Models\User::where('id', Auth::id())->first();@endphp
@php $isOrganizationDemo = $user->org_unit_id === 1;@endphp
@php $request = RequestForOrgs::where('user_id', Auth::id())->first();@endphp

    <!-- Lucide Icons CDN -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <nav class="h-full flex flex-col">
        <div class="flex-1">

            @foreach($sidebarMenus as $key => $item)
                @php
                    $menuList = $item->menuList;
                    $hasChildren = $item && $item->child && $item->child->count() > 0;
                    $isKolOrg = $menuList && in_array($menuList->code, ['KOL_ORG_S', 'KOL_ORG']);
                    $isPrtDocs = $menuList && $menuList->code === 'PRT_DOCS_S';
                    $icon = $menuList && $menuList->iconClass ? $menuList->iconClass->name : 'file-text';
                    $menuCount = $item->menuCount($item->id);
                    $isActive = $menuList && str_contains(url()->current(), $menuList->url_path);
                @endphp

                    <!-- Menu Item with Children or Special Types -->
                @if($hasChildren || $isKolOrg || $isPrtDocs)

                    <div class="menu-item {{ $isActive ? 'active' : '' }}" data-menu-id="{{ $item->id }}">
                        <div class="menu-header">
                            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                <i data-lucide="{{ $icon }}" class="{{ $icon }}" style="width: 22px; height: 22px;"></i>
                                <span class="menu-text">{{ $menuList->lang($menuList->id) }}</span>
                                @if($menuCount > 0)
                                    <span class="badge-count menu-count-{{ $item->id }}">{{ $menuCount }}</span>
                                @endif
                            </div>
                            <i data-lucide="chevron-right" class="chevron"></i>
                        </div>

                        <div class="submenu">
                            <!-- Regular Children -->
                            @if($hasChildren)
                                @foreach($item->child as $child)
                                    @php
                                        $childCount = method_exists($child, 'menuCount') ? $child->menuCount($child->id) : 0;
                                        $childIsActive = str_contains(url()->current(), $child->url_path);
                                    @endphp
                                    <div class="submenu-item">
                                        <a href="{{ $child->menuList->url_path }}"
                                           class="{{ $childIsActive ? 'active-link' : '' }}">
                                            <i data-lucide="{{ $child->menuList->iconClass ? $child->menuList->iconClass->name : 'circle' }}" class="{{ $child->menuList->iconClass ? $child->menuList->iconClass->name : 'circle' }}" style="width: 18px; height: 18px;"></i>
                                            <span>{{ \App\Models\Admin\MenuList::lang($child->menuList->id) }}</span>
                                            @if($childCount > 0)
                                                <span class="badge-count">{{ $childCount }}</span>
                                            @endif
                                        </a>
                                    </div>
                                @endforeach
                            @endif

                            <!-- KOL_ORG Submenu -->
                            @if($isKolOrg)
                                @php
                                    $col_org = $item->getKolOrg($item->menu_id);
                                    $route = $menuList->code === 'KOL_ORG' ? 'protocol-members' : 'prt-secretary';
                                @endphp
                                @if($col_org && $col_org->child)
                                    @foreach($col_org->child as $item2)
                                        <div class="submenu-item tree-item">
                                            <a href="{{ route($route, $item2->doc_hash) }}"
                                               class="{{ str_contains(url()->current(), $item2->doc_hash) ? 'active-link' : '' }}">
                                                <i data-lucide="folder" style="width: 18px; height: 18px;"></i>
                                                <span>{{ $item2->lang($item2->id) }}</span>
                                                @php
                                                    $itemCount = $route == 'protocol-members'
                                                        ? $item2->menuCountMem($item2->id)
                                                        : $item2->menuCount($item2->id);
                                                @endphp
                                                @if($itemCount > 0)
                                                    <span class="badge-count">{{ $itemCount }}</span>
                                                @endif
                                            </a>

                                            <!-- Nested Children -->
                                            @if(count($item2->child) > 0)
                                                <div class="nested-submenu">
                                                    @include('layouts.sidebarChild', ['childs' => $item2->child, 'route' => $route])
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            @endif

                            <!-- PRT_DOCS Submenu -->
                            @if($isPrtDocs)
                                @php($prt_docs = $item->getPrtDocs($item->menu_id))
                                    @if($prt_docs && $prt_docs->child)
                                        @foreach($prt_docs->child as $item3)
                                            <div class="submenu-item tree-item">
                                                <a href="{{ route('prt-secretary', $item3->doc_hash) }}"
                                                   class="{{ str_contains(url()->current(), $item3->doc_hash) ? 'active-link' : '' }}">
                                                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                                                    <span>{{ $item3->lang($item3->id) }}</span>
                                                </a>

                                                @if(count($item3->child) > 0)
                                                    <div class="nested-submenu">
                                                        @include('layouts.sidebarChildPrtDocs', ['childs' => $item3->child, 'route' => 'prt-secretary'])
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                    @endif
                        </div>
                    </div>

                @else
                    <!-- Simple Menu Item (No Children) -->
                    @if($menuList)
                        <div class="menu-item {{ $isActive ? 'active' : '' }}" data-menu-id="{{ $item->id }}">
                            <a href="{{ $menuList->url_path }}" class="menu-header">
                                <i data-lucide="{{ $icon }}" class="{{ $icon }}" style="width: 22px; height: 22px;"></i>
                                <span class="menu-text">{{ $item->menuList->lang($item->menuList->id) }}</span>
                                @if($menuCount > 0)
                                    <span class="badge-count menu-count-{{ $item->id }}">{{ $menuCount }}</span>
                                @endif
                            </a>
                        </div>
                    @endif
                @endif
            @endforeach

        </div>

        <!-- Logout -->
        <div class="logout">
            <div class="menu-item">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="menu-header">
                    <i data-lucide="log-out" style="width: 22px; height: 22px;"></i>
                    <span class="menu-text">{{ __('field.logout') }}</span>
                </a>
            </div>
        </div>

        <!-- Theme Toggle Tabs -->
        <div class="theme-tabs">
            <div class="theme-tab active" data-theme="light">
                <i data-lucide="sun" style="width: 20px; height: 20px;"></i>
            </div>
            <div class="theme-tab" data-theme="dark">
                <i data-lucide="moon" style="width: 20px; height: 20px;"></i>
            </div>
        </div>
    </nav>
</aside>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // Dropdown toggle
        document.querySelectorAll('.menu-header').forEach(header => {
            header.addEventListener('click', function(e) {
                // Don't toggle if it's a link
                if (this.tagName === 'A') return;

                e.preventDefault();
                const menuItem = this.parentElement;

                if (this.querySelector('.chevron')) {
                    // Close other open menus
                    document.querySelectorAll('.menu-item.open').forEach(item => {
                        if (item !== menuItem) {
                            item.classList.remove('open');
                        }
                    });

                    menuItem.classList.toggle('open');
                }

                // Set active
                document.querySelectorAll('.menu-item').forEach(item => {
                    if (!item.classList.contains('open')) {
                        item.classList.remove('active');
                    }
                });
                menuItem.classList.add('active');
            });
        });

        // Theme toggle
        const sidebar = document.getElementById('sidebar');
        const themeTabs = document.querySelectorAll('.theme-tab');

        themeTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                themeTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const theme = this.getAttribute('data-theme');
                if (theme === 'dark') {
                    sidebar.classList.add('dark');
                    localStorage.setItem('sidebar-theme', 'dark');
                } else {
                    sidebar.classList.remove('dark');
                    localStorage.setItem('sidebar-theme', 'light');
                }
            });
        });

        // Load saved theme
        const savedTheme = localStorage.getItem('sidebar-theme');
        if (savedTheme === 'dark') {
            sidebar.classList.add('dark');
            themeTabs.forEach(tab => {
                if (tab.getAttribute('data-theme') === 'dark') {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
        }

        // Auto-open active menu
        document.querySelectorAll('.menu-item.active').forEach(item => {
            if (item.querySelector('.submenu')) {
                item.classList.add('open');
            }
        });

        // Update menu counts periodically
        function updateMenuCounts() {
            document.querySelectorAll('.menu-item[data-menu-id]').forEach(menuItem => {
                const menuId = menuItem.getAttribute('data-menu-id');

                fetch('/get-menu-count', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ id: menuId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success === 200) {
                            const badge = menuItem.querySelector('.menu-count-' + menuId);
                            if (badge) {
                                badge.textContent = data.count;
                                if (data.count === 0) {
                                    badge.style.display = 'none';
                                } else {
                                    badge.style.display = 'inline-block';
                                }
                            } else if (data.count > 0) {
                                const countContainer = menuItem.querySelector('.menu-text');
                                if (countContainer) {
                                    const newBadge = document.createElement('span');
                                    newBadge.className = 'badge-count menu-count-' + menuId;
                                    newBadge.textContent = data.count;
                                    countContainer.parentNode.insertBefore(newBadge, countContainer.nextSibling);
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error updating menu count:', error));
            });
        }

        // Update counts every 30 seconds
        setInterval(updateMenuCounts, 30000);
    });
</script>

<style>
    :root {
        --sidebar-width: 256px;
        --navbar-height: 64px;
        --primary: #794DFD;
        --primary-hover: #5c36c1;
        --primary-light: #fdf2f8;
        --bg: #f8fafc;
        --card-bg: #ffffff;
        --text: #1e293b;
        --text-muted: #64748b;
        --border: #e2e8f0;
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 64px;
        left: 0;
        width: var(--sidebar-width);
        height: calc(100vh - 64px);
        background-color: #ffffff;
        border-right: 1px solid var(--border);
        padding: 1.5rem 0;
        overflow-y: auto;
        z-index: 30;
        display: flex;
        flex-direction: column;
        transition: background-color 0.3s;
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .sidebar.dark {
        background-color: #794dfd;
    }

    .sidebar.dark::-webkit-scrollbar-thumb {
        background: #5B39BE;
    }

    /* Menu Item */
    .menu-item {
        position: relative;
        margin: 0.25rem 1rem;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .menu-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 10px 1rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }

    .menu-header:hover {
        background-color: var(--primary-light);
        color: var(--primary);
        text-decoration: none;
    }

    .menu-header:hover i {
        color: var(--primary);
    }

    /* Active menu */
    .menu-item.active > .menu-header {
        background-color: var(--primary);
        color: white;
    }

    .menu-item.active > .menu-header i,
    .menu-item.active > .menu-header .menu-text,
    .menu-item.active .chevron {
        color: white !important;
    }

    /* Icons */
    .menu-header i:first-child {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        color: var(--text-muted);
        transition: color 0.3s;
    }

    .chevron {
        width: 14px;
        height: 14px;
        transition: transform 0.3s, color 0.3s;
        color: var(--text-muted);
    }

    .menu-item.open .chevron {
        transform: rotate(90deg);
    }

    .menu-text {
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
        flex: 1;
    }

    /* Badge Count */
    .badge-count {
        background-color: #dc2626;
        color: white;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 10px;
        line-height: 1;
        margin-left: auto;
    }

    .menu-item.active .badge-count {
        background-color: white;
        color: var(--primary);
    }

    /* Submenu */
    .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
        background-color: #f8f9fb;
    }

    .menu-item.open .submenu {
        max-height: 2000px;
    }

    .submenu-item {
        position: relative;
    }

    .submenu-item a {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.65rem 1.75rem 0.65rem 3.5rem;
        color: var(--text-muted);
        font-size: 13.5px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .submenu-item a:hover {
        background-color: var(--primary-light);
        color: var(--primary);
        padding-left: 3.8rem;
        text-decoration: none;
    }

    .submenu-item a.active-link {
        background-color: var(--primary);
        color: white;
    }

    .submenu-item a.active-link i {
        color: white;
    }

    .submenu-item i {
        width: 18px;
        height: 18px;
        color: var(--text-muted);
        transition: color 0.2s;
        flex-shrink: 0;
    }

    .submenu-item a:hover i {
        color: var(--primary);
    }

    /* Nested Submenu */
    .nested-submenu {
        padding-left: 1rem;
    }

    .nested-submenu .submenu-item a {
        padding-left: 4.5rem;
    }

    .nested-submenu .submenu-item a:hover {
        padding-left: 4.8rem;
    }

    /* Tree Items */
    .tree-item {
        position: relative;
    }

    /* Logout */
    .logout {
        margin-top: auto;
        border-top: 1px solid var(--border);
        padding-top: 1rem;
        margin-top: 2rem;
    }

    .logout .menu-item:hover .menu-header {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .logout .menu-item:hover .menu-header i {
        color: #dc2626;
    }

    /* Theme Tabs */
    .theme-tabs {
        display: flex;
        background-color: #f1f5f9;
        border-radius: 0.5rem;
        margin: 1rem 1rem 1rem 1rem;
        width: calc(100% - 2rem);
        padding: 4px;
    }

    .sidebar.dark .theme-tabs {
        background-color: #00000026;
    }

    .theme-tab {
        flex: 1;
        text-align: center;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.3s;
        padding: 6px 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .theme-tab.active {
        background-color: var(--primary);
        color: white;
    }

    .theme-tab i {
        width: 20px;
        height: 20px;
        color: var(--text-muted);
        transition: color 0.3s;
    }

    .theme-tab.active i {
        color: white;
    }

    .theme-tab:hover:not(.active) i {
        color: var(--primary);
    }

    /* Dark Mode */
    .sidebar.dark .menu-header {
        color: white;
    }

    .sidebar.dark .menu-header i:first-child,
    .sidebar.dark .chevron {
        color: white;
    }

    .sidebar.dark .menu-header:hover {
        background-color: #ffffff1f;
        color: #fff;
    }

    .sidebar.dark .menu-header:hover i {
        color: white;
    }

    .sidebar.dark .submenu {
        background-color: #5B39BE;
    }

    .sidebar.dark .submenu-item a {
        color: white;
    }

    .sidebar.dark .submenu-item a:hover {
        background-color: #4a2d9e;
        color: white;
    }

    .sidebar.dark .submenu-item a:hover i {
        color: white;
    }

    .sidebar.dark .submenu-item i {
        color: white;
    }

    .sidebar.dark .submenu-item a.active-link {
        background-color: white;
        color: var(--primary);
    }

    .sidebar.dark .submenu-item a.active-link i {
        color: var(--primary);
    }

    .sidebar.dark .logout {
        border-top-color: #5B39BE;
    }

    .sidebar.dark .menu-text {
        color: white;
    }
</style>
