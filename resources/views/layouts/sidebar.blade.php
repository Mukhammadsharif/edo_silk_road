@php
    use App\Models\Admin\OrganizationUnits;
    use App\Models\Admin\RequestForOrgs;
    use Illuminate\Support\Facades\Auth;
@endphp

@php $user = App\Models\User::where('id', Auth::id())->first(); @endphp
@php $isOrganizationDemo = $user->org_unit_id === 1; @endphp
@php $request = RequestForOrgs::where('user_id', Auth::id())->first(); @endphp
@php $isOwner = in_array(Auth::id(), OrganizationUnits::where('status', 1)->pluck('owner_user_id')->toArray()); @endphp

<link rel="stylesheet" href="{{ asset('css/templates/layouts/sidebar.css') }}">

<aside class="sidebar" id="sidebar">
    <nav class="h-full flex flex-col">
        <div class="flex-1">
            <div class="main-one">
                <a href="{{ url('/edo') }}" class="menu-header">
                    <div class="main-gap">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.main') }}</span>
                    </div>
                </a>
            </div>

            <div class="menu-item ">
                <div class="menu-header">
                    <div style="display: flex; align-items: center; gap:15px; flex: 1;">
                        <i data-lucide="file-plus-2" style="width: 22px; height: 22px;"></i>
                        <span class="menu-text">{{ __('menu.create_document') }}</span>
                    </div>
                    <i data-lucide="chevron-right" class="chevron"></i>
                </div>

                <div class="submenu">
                    <div class="submenu-item">
                        <a href="{{ url('/office/document/create/incoming') }}">
                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">
                            <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                            <span>{{ __('document.incoming_message') }}</span>
                        </a>
                    </div>
                    <div class="submenu-item">
                        <a href="{{ url('/office/document/create/outgoing') }}">
                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">
                            <i data-lucide="upload" style="width: 18px; height: 18px;"></i>
                            <span>{{ __('document.outgoing_message') }}</span>
                        </a>
                    </div>
                    <div class="submenu-item">
                        <a href="{{ url('/office/document/create/internal') }}">
                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">
                            <i data-lucide="file-edit" style="width: 18px; height: 18px;"></i>
                            <span>{{ __('document.internal_docs') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            @foreach($sidebarMenus as $key => $item)
                @php
                    $menuList     = $item->menuList;
                    $hasChildren  = $item && $item->child && $item->child->count() > 0;
                    $isKolOrg     = $menuList && in_array($menuList->code, ['KOL_ORG_S', 'KOL_ORG']);
                    $isPrtDocs    = $menuList && str_contains($menuList->code, 'PRT');
                    $icon         = $menuList && $menuList->iconClass ? $menuList->iconClass->name : 'file-text';
                    $menuCount    = $item->menuCount($item->id);
                    $isActive     = $menuList && str_contains(url()->current(), $menuList->url_path);
                @endphp

                @if($hasChildren || $isKolOrg || $isPrtDocs)
                    <div class="menu-item {{ $isActive ? 'active' : '' }} menu-item-{{ $item->id }}" data-menu-id="{{ $item->id }}">
                        <div class="menu-header">
                            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                <i data-lucide="{{ $icon }}" style="width: 22px; height: 22px;"></i>
                                <span class="menu-text">{{ $menuList->lang($menuList->id) }}</span>
                                <span class="badge-count menu-count-{{ $item->id }}" style="display: none">{{ $menuCount }}</span>
                            </div>
                            <i data-lucide="chevron-right" class="chevron"></i>
                        </div>

                        <div class="submenu">
                            @if($hasChildren)
                                @foreach($item->child as $child)
                                    @php
                                        $childCount   = method_exists($child, 'menuCount') ? $child->menuCount($child->id) : 0;
                                        $childIsActive = str_contains(url()->current(), $child->url_path);
                                    @endphp
                                    <div class="submenu-item">
                                        <a href="{{ $child->menuList->url_path }}"
                                           class="{{ $childIsActive ? 'active-link' : '' }}">
                                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">
                                            <i data-lucide="{{ $child->menuList->iconClass ? $child->menuList->iconClass->name : 'circle' }}"
                                               style="width: 18px; height: 18px;"></i>
                                            <span>{{ \App\Models\Admin\MenuList::lang($child->menuList->id) }}</span>
                                            @if($childCount > 0)
                                                <span class="badge-count">{{ $childCount }}</span>
                                            @endif
                                        </a>
                                    </div>
                                @endforeach
                            @endif

                            @if($isKolOrg)
                                @php
                                    $col_org = $item->getKolOrg($item->menu_id);
                                    $route   = $menuList->code === 'KOL_ORG' ? 'protocol-members' : 'prt-secretary';
                                @endphp
                                @if($col_org && $col_org->child)
                                    @foreach($col_org->child as $item2)
                                        <div class="submenu-item tree-item">
                                            <a href="{{ route($route, $item2->doc_hash) }}"
                                               class="{{ str_contains(url()->current(), $item2->doc_hash) ? 'active-link' : '' }}">
                                                <img src="{{ asset('images/leftdown.svg') }}" alt=""
                                                     style="opacity:0.4">
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

                                            @if(count($item2->child) > 0)
                                                <div class="nested-submenu">
                                                    @include('layouts.sidebarChild', ['childs' => $item2->child, 'route' => $route])
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            @endif

                            @if($isPrtDocs)
                                @php $prt_docs = $item->getPrtDocs($item->menu_id) @endphp
                                @if($prt_docs && $prt_docs->child)
                                    @foreach($prt_docs->child as $item3)
                                        <div class="submenu-item tree-item">
                                            <a href="{{ route('prt-secretary', $item3->doc_hash) }}"
                                               class="{{ str_contains(url()->current(), $item3->doc_hash) ? 'active-link' : '' }}">
                                                <img src="{{ asset('images/leftdown.svg') }}" alt=""
                                                     style="opacity:0.4">
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
                    @if($menuList)
                        <div class="menu-item {{ $isActive ? 'active' : '' }}" data-menu-id="{{ $item->id }}">
                            <a href="{{ $menuList->url_path }}" class="menu-header">
                                <i data-lucide="{{ $icon }}" style="width: 22px; height: 22px;"></i>
                                <span class="menu-text">{{ $item->menuList->lang($item->menuList->id) }}</span>
                                @if($menuCount > 0)
                                    <span class="badge-count menu-count-{{ $item->id }}">{{ $menuCount }}</span>
                                @endif
                            </a>
                        </div>
                    @endif
                @endif
            @endforeach

            @if($protocolMenus && $protocolMenus->count())
                <div class="menu-item {{ request()->is('protocol/*') ? 'active' : '' }}" data-menu-id="1000">
                    <div class="menu-header">
                        <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                            <i data-lucide="files" style="width: 22px; height: 22px;"></i>
                            <span class="menu-text">{{ __('field.protocols') }}</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron"></i>
                    </div>

                    <div class="submenu">
                        @foreach($protocolMenus as $prtMenu)
                            @php
                                $prtChildCount    = method_exists($prtMenu, 'menuCount') ? $prtMenu->menuCount($prtMenu->id) : 0;
                                $prtChildIsActive = str_contains(url()->current(), $prtMenu->url_path);
                            @endphp
                            <div class="submenu-item">
                                <a href="{{ $prtMenu->menuList->url_path }}"
                                   class="{{ $prtChildIsActive ? 'active-link' : '' }}">
                                    <i data-lucide="{{ $prtMenu->menuList->iconClass ? $prtMenu->menuList->iconClass->name : 'file' }}"
                                       style="width: 18px; height: 18px;"></i>
                                    <span>{{ \App\Models\Admin\MenuList::lang($prtMenu->menuList->id) }}</span>
                                    @if($prtChildCount > 0)
                                        <span class="badge-count">{{ $prtChildCount }}</span>
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

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

        @if(!$isOwner && $isOrganizationDemo && !$request)
            <div class="request">
                <div class="rocket-icon">
                    <i data-lucide="rocket" style="width: 30px; height: 30px;"></i>
                </div>
                <div class="dots">
                    <i data-lucide="ellipsis-vertical" style="width: 18px; height: 18px;"></i>
                </div>
                <p>Tashkilot xodimi <br> bo'lish uchun so'rov <br> qoldiring</p>

                <button class="btn btn-success-custom open-request-modal"
                        data-toggle="modal"
                        data-target="#RequestModal">
                    {{ __('field.leave_request') }}
                </button>
            </div>
        @endif

        <div class="theme-tabs">
            <div class="theme-tab active" data-theme="light">
                <i class="dark" data-lucide="sun" style="width: 20px; height: 20px;"></i>
            </div>
            <div class="theme-tab" data-theme="dark">
                <i class="light" data-lucide="moon" style="width: 20px; height: 20px;"></i>
            </div>
        </div>

        <div class="sidebar-user-profile-card">
            <div class="sidebar-user-avatar">
                <div class="sidebar-avatar-circle">
                    <i data-lucide="user" class="text-white"></i>
                </div>
                <div class="status-indicator"></div>
            </div>
            <div class="sidebar-user-info">
                <p class="sidebar-user-name">{{ $user->userPersonal->shortName()??'' }}</p>
                <p class="sidebar-user-role">{{ $user->currentWork->position->lang($user->currentWork->position->id)??'User' }}</p>
            </div>
        </div>
    </nav>
</aside>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<div class="modal fade" id="RequestModal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header"
                 style="background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%); color: white; border-radius: 16px 16px 0 0;">
                <h4 class="modal-title">
                    <i data-lucide="plus-circle"
                       style="width: 20px; height: 20px; display: inline-block; vertical-align: middle;"></i>
                    {{ __('field.leave_request') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">
                    <span>×</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div class="modern-form">
                    <ul id="request_error_list"></ul>
                    <div class="form-group-modern">
                        <label><i data-lucide="hash"></i> {{ __('action.fill_tax_number') }}</label>
                        <input type="number" class="form-control-modern tax_number" required>
                    </div>
                </div>
            </div>
            <div class="form-actions" style="border-top: 2px solid #f1f5f9; padding: 1rem 2rem;">
                <button class="btn-modern btn-light" data-dismiss="modal">
                    <i data-lucide="circle-x"></i> {{ __('action.cancel') }}
                </button>
                <button class="btn-modern btn-primary send-request-btn">
                    <i data-lucide="save"></i> {{ __('action.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.send-request-btn', function (e) {
        e.preventDefault();

        let taxNumber = $('.tax_number').val();
        let errorBox = $('#request_error_list');

        errorBox.html('').removeClass('alert alert-danger');

        $.ajax({
            url: '/admin/organizations/send-request',
            method: 'POST',
            data: {
                tax_number: taxNumber,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend() {
                $('.send-request-btn').prop('disabled', true);
            },
            success(response) {
                if (response.status === 400) {
                    errorBox.addClass('alert alert-danger');
                    $.each(response.errors, function (_, msg) {
                        errorBox.append(`<li>${msg}</li>`);
                    });
                } else {
                    $('#RequestModal').modal('hide');
                    $('.tax_number').val('');
                    setTimeout(() => location.reload(), 500);
                }
            },
            error() {
                errorBox.addClass('alert alert-danger').html('<li>Xatolik yuz berdi</li>');
            },
            complete() {
                $('.send-request-btn').prop('disabled', false);
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.menu-header').forEach(header => {
            header.addEventListener('click', function (e) {
                if (this.tagName === 'A') return;
                e.preventDefault();

                const menuItem = this.parentElement;

                if (this.querySelector('.chevron')) {
                    document.querySelectorAll('.menu-item.open').forEach(item => {
                        if (item !== menuItem) item.classList.remove('open');
                    });
                    menuItem.classList.toggle('open');
                }

                document.querySelectorAll('.menu-item').forEach(item => {
                    if (!item.classList.contains('open')) item.classList.remove('active');
                });
                menuItem.classList.add('active');
            });
        });

        const sidebar = document.getElementById('sidebar');
        const themeTabs = document.querySelectorAll('.theme-tab');

        themeTabs.forEach(tab => {
            tab.addEventListener('click', function () {
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

        const savedTheme = localStorage.getItem('sidebar-theme');
        if (savedTheme === 'dark') {
            sidebar.classList.add('dark');
            themeTabs.forEach(tab => {
                if (tab.getAttribute('data-theme') === 'dark') tab.classList.add('active');
                else tab.classList.remove('active');
            });
        }

        document.querySelectorAll('.menu-item.active').forEach(item => {
            if (item.querySelector('.submenu')) item.classList.add('open');
        });

        function updateMenuCounts() {
            document.querySelectorAll('.menu-item[data-menu-id]').forEach(menuItem => {
                const menuId = menuItem.getAttribute('data-menu-id');
                fetch('/get-menu-count', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({id: menuId})
                })
                    .then(response => response.json())
                    .then(data => {
                        const badge = menuItem.querySelector('.menu-count-' + menuId);
                        if (data.success === 200) {
                            if (badge) {
                                badge.textContent = data.count;
                                badge.style.display = data.count > 0 ? 'inline-block' : 'none';
                            }
                        } else {
                            badge.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Menu count error:', error));
            });
        }
        updateMenuCounts()
        setInterval(updateMenuCounts, 30000);
    });
</script>

<script src="{{ asset('js/vanilla-tilt.min.js') }}"></script>

<style>
    .sidebar-user-profile-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        border: 2px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(121, 77, 253, 0.08);
        transition: all 0.3s ease;
        margin: 0 16px 16px 16px; /* Добавляем отступы как у других элементов */
    }

    .sidebar-user-profile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(121, 77, 253, 0.15);
        border-color: #794DFD;
    }

    .sidebar-user-avatar {
        position: relative;
    }

    .sidebar-avatar-circle {
        width: 48px; /* Уменьшил размер для компактности */
        height: 48px;
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(121, 77, 253, 0.3);
    }

    .sidebar-avatar-circle i {
        width: 24px;
        height: 24px;
        color: white;
    }

    .status-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        background: #10b981;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sidebar-user-info {
        flex: 1;
        min-width: 0; /* Для правильного text-overflow */
    }

    .sidebar-user-name {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.3px;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-user-role {
        margin: 0.25rem 0 0 0;
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Для collapsed состояния */
    #sidebar.collapsed .sidebar-user-profile-card {
        flex-direction: column;
        gap: 0.5rem;
        padding: 1rem;
    }

    #sidebar.collapsed .sidebar-user-info {
        display: none;
    }

    #sidebar.collapsed .sidebar-avatar-circle {
        width: 40px;
        height: 40px;
    }

    #sidebar.collapsed .sidebar-avatar-circle i {
        width: 20px;
        height: 20px;
    }
</style>

<style>
    .request {
        background: linear-gradient(120deg, #7c3aed 0%, #a855f7 50%, #c239d7 100%);
        margin: 100px 20px 30px 20px !important;
        padding: 10px 10px 25px 10px;
        border-radius: 12px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        height: 190px;
        align-items: center;
        position: relative;
        color: white;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3);
        transform-style: preserve-3d;
        transition: all 0.4s ease;
    }

    .request:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: 0 25px 60px rgba(124, 58, 237, 0.5);
        background: linear-gradient(120deg, #7c3aed 0%, #b065ff 45%, #d14be8 100%);
    }

    .request p {
        color: white;
        font-weight: bold;
        margin-bottom: 20px;
        margin-top: 50px;
        font-size: 14px;
        line-height: 1.4;
    }

    .btn-success-custom {
        background: rgba(255, 255, 255, 0.25);
        color: #fff !important;
        padding: 6px 24px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .btn-success-custom:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: scale(1.08);
    }

    .rocket-icon {
        position: absolute;
        top: 10px;
        left: 10px;
    }

    .dots {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.2);
        padding: 5px;
        border-radius: 50%;

    }

    .rocket-icon {
        transition: transform 0.4s ease;
        cursor: pointer;
    }

    .request:hover .rocket-icon {
        transform: translateY(-8px) rotate(15deg);
    }


    .main-one a {
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        color: white;
        margin: 0 16px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: medium;
    }

    .main-gap {
        display: flex;
        gap: 14px;
    }

    #sidebar.collapsed .menu-header,
    #sidebar.collapsed .menu-header a,
    #sidebar.collapsed .submenu-item a,
    #sidebar.collapsed .logout a,
    #sidebar.collapsed .theme-tabs {
        justify-content: center !important;
        text-align: center !important;
    }

    #sidebar.collapsed .main-gap,
    #sidebar.collapsed .menu-header > div[style*="flex"],
    #sidebar.collapsed .submenu-item a,
    #sidebar.collapsed .menu-header i:first-child,
    #sidebar.collapsed .submenu-item a i {
        justify-content: center !important;
        margin: 0 auto !important;
        padding: 0 !important;
    }

    #sidebar.collapsed i[data-lucide] {
        margin: 0 !important;
    }

    #sidebar.collapsed i[data-lucide] {
        width: 26px !important;
        height: 26px !important;
    }

    #sidebar.collapsed .menu-text,
    #sidebar.collapsed .chevron,
    #sidebar.collapsed .badge-count,
    #sidebar.collapsed span:not(.icon) {
        display: none !important;
    }

    #sidebar.collapsed .theme-tabs {
        flex-direction: column-reverse !important;
        transform: rotate(180deg) !important;
        gap: 8px !important;
        margin: 20px auto !important;
        width: fit-content !important;
        padding: 10px !important;
    }

    #sidebar.collapsed .theme-tab {
        transform: rotate(180deg) !important;
    }


    #sidebar.collapsed i svg,
    #sidebar.collapsed .lucide svg {
        width: 35px !important;
        height: 35px !important;
    }

    #sidebar.collapsed .theme-tab {
        width: 34px;
        transform: rotate(180deg) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof VanillaTilt !== 'undefined') {
            VanillaTilt.init(document.querySelectorAll("#sidebar .request"), {
                max: 12,
                speed: 700,
                glare: true,
                "max-glare": 0.35,
                scale: 1.03,
                perspective: 1000,
                transition: true,
                gyroscope: true
            });
        } else {
            console.warn("VanillaTilt kutubxonasi yuklanmagan!");
        }
    });
</script>
