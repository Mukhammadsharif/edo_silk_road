@php
    use App\Models\Admin\OrganizationUnits;
    use App\Models\Admin\RequestForOrgs;
    use Illuminate\Support\Facades\Auth;
@endphp

@php $user = App\Models\User::where('id', Auth::id())->first(); @endphp
@php $isOrganizationDemo = $user->org_unit_id === 1; @endphp
@php $request = RequestForOrgs::where('user_id', Auth::id())->where('status', 1)->first(); @endphp
@php $isOwner = in_array(Auth::id(), OrganizationUnits::where('status', 1)->pluck('owner_user_id')->toArray()); @endphp
@php $userOrganizations = $user->accessibleOrganizations(); @endphp
@php $activeOrganization = $userOrganizations->firstWhere('id', $user->org_unit_id); @endphp

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
            <a href="{{ route('boards.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('boards.*') ? 'bg-[#7C3AED] text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                <i class="ti ti-layout-kanban text-xl"></i>
                <span>Доски</span>
            </a>
            {{-- ═══════════════════════════════════════════════════════════════
                 ORG SWITCHER — над «Создать документ»
                 Показывается только для юзеров не в demo (id=1) и
                 только если у них есть хотя бы одна активная организация
                 ═══════════════════════════════════════════════════════════════ --}}
            @if(!$isOrganizationDemo && $userOrganizations->count() > 0)
                <div class="org-switcher" id="orgSwitcher">
                    <button type="button" class="org-switcher-trigger" id="orgSwitcherTrigger">
                        <div class="org-switcher-left">
                            <div class="org-switcher-icon">
                                <i data-lucide="building-2" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div class="org-switcher-info">
                                <span class="org-switcher-label">{{ __('field.organization') ?? 'Tashkilot' }}</span>
                                <span class="org-switcher-name">{{ $activeOrganization->name_uz ?? '—' }}</span>
                            </div>
                        </div>
                        <i data-lucide="chevron-down" class="org-switcher-chevron" style="width: 16px; height: 16px;"></i>
                    </button>

                    <div class="org-switcher-menu" id="orgSwitcherMenu">
                        <div class="org-switcher-menu-header">
                            {{ __('field.choose_organization') ?? 'Tashkilotni tanlang' }}
                        </div>
                        @foreach($userOrganizations as $org)
                            <button type="button"
                                    class="org-switcher-item {{ $org->id === $user->org_unit_id ? 'active' : '' }}"
                                    data-org-id="{{ $org->id }}"
                                {{ $org->id === $user->org_unit_id ? 'disabled' : '' }}>
                                <div class="org-switcher-item-icon">
                                    <i data-lucide="{{ $org->id === $user->org_unit_id ? 'check' : 'building' }}"
                                       style="width: 16px; height: 16px;"></i>
                                </div>
                                <span class="org-switcher-item-name">{{ $org->name_uz }}</span>
                                @if($org->tax_number)
                                    <span class="org-switcher-item-tin">{{ $org->tax_number }}</span>
                                @endif
                            </button>
                        @endforeach

                        <div class="org-switcher-divider"></div>

                        <button type="button" class="org-switcher-item org-switcher-add"
                                data-toggle="modal" data-target="#RequestModal">
                            <div class="org-switcher-item-icon">
                                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                            </div>
                            <span class="org-switcher-item-name">
                                {{ __('field.add_organization') ?? 'Yana tashkilot qo\'shish' }}
                            </span>
                        </button>
                    </div>
                </div>
            @endif


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
                            {{--                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">--}}
                            <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                            <span>{{ __('document.incoming_message') }}</span>
                        </a>
                    </div>
                    <div class="submenu-item">
                        <a href="{{ url('/office/document/create/outgoing') }}">
                            {{--                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">--}}
                            <i data-lucide="upload" style="width: 18px; height: 18px;"></i>
                            <span>{{ __('document.outgoing_message') }}</span>
                        </a>
                    </div>
                    <div class="submenu-item">
                        <a href="{{ url('/office/document/create/internal') }}">
                            {{--                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">--}}
                            <i data-lucide="file-edit" style="width: 18px; height: 18px;"></i>
                            <span>{{ __('document.internal_docs') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════
                 ХРАНИЛИЩЕ ОРГАНИЗАЦИИ
                 Read для всех активных сотрудников org, write для управленцев
                 ═══════════════════════════════════════════════════════════════ --}}
            @php
                // Виден всем сотрудникам которые числятся в текущей org или Super_Admin
                $userRoleIds   = \App\Models\Admin\UserRole::where('model_id', Auth::id())->pluck('role_id')->toArray();
                $userRoleNames = \App\Models\Admin\Role::whereIn('id', $userRoleIds)->pluck('name')->toArray();

                $isSuperAdmin     = in_array('Super_Admin', $userRoleNames, true);
                $canSeeOnec    = $isSuperAdmin
                    || in_array('Director', $userRoleNames, true)
                    || in_array('IT_Admin', $userRoleNames, true);
                $hasActiveWorkInOrg = !$isSuperAdmin && \App\Models\Admin\UserWorkHistory::where('user_id', Auth::id())
                    ->where('org_id', Auth::user()->org_unit_id)
                    ->where('status', 1)
                    ->exists();

                $canSeeStorage = $isSuperAdmin || $hasActiveWorkInOrg;
            @endphp

            @if($canSeeStorage)
                <div class="menu-item {{ str_contains(url()->current(), '/org-storage') ? 'active' : '' }}">
                    <a href="{{ route('storage.index') }}" class="menu-header">
                        <div style="display: flex; align-items: center; gap:15px; flex: 1;">
                            <i data-lucide="folder-archive" style="width: 22px; height: 22px;"></i>
                            <span class="menu-text">{{ __('menu.org_storage') ?? 'Хранилище организации' }}</span>
                        </div>
                    </a>
                </div>
            @endif

            {{-- ═══════════════ ИНТЕГРАЦИЯ С 1С ═══════════════
                 Виден директору, IT-админу и Super_Admin --}}
            @php
                $canSeeOnec = $isSuperAdmin
                    || in_array('Director', $userRoleNames, true)
                    || in_array('IT_Admin', $userRoleNames, true);
            @endphp
            @if($canSeeOnec)
                <div class="menu-item {{ str_contains(url()->current(), '/admin/onec') ? 'active' : '' }}">
                    <a href="{{ route('admin.onec.index') }}" class="menu-header">
                        <div style="display: flex; align-items: center; gap:15px; flex: 1;">
                            <i data-lucide="plug-zap" style="width: 22px; height: 22px;"></i>
                            <span class="menu-text">{{ __('Интеграция с 1С') }}</span>
                        </div>
                    </a>
                </div>
            @endif

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
                            <i data-lucide="{{ $icon }}" style="width: 22px; height: 22px; flex-shrink:0;"></i>
                            <span class="menu-text">{{ $menuList->lang($menuList->id) }}</span>
                            <span class="badge-count menu-count-{{ $item->id }}" style="display:none!important">0</span>
                            <i data-lucide="chevron-right" class="chevron" style="flex-shrink:0; margin-left:4px;"></i>
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
                                            {{--                                            <img src="{{ asset('images/leftdown.svg') }}" alt="" style="opacity:0.4">--}}
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
                                                {{--                                                <img src="{{ asset('images/leftdown.svg') }}" alt=""--}}
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
                                                {{--                                                <img src="{{ asset('images/leftdown.svg') }}" alt=""--}}
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

        {{-- Litsenziya: superadmin uchun yaratish, mijoz (owner) uchun faollashtirish --}}
        @if($isSuperAdmin)
            <div class="menu-item">
                <a href="{{ route('admin.licenses.index') }}" class="menu-header">
                    <i data-lucide="key-round" style="width: 22px; height: 22px;"></i>
                    <span class="menu-text">Litsenziyalar</span>
                </a>
            </div>
        @elseif($isOwner)
            <div class="menu-item">
                <a href="{{ route('license.activate') }}" class="menu-header">
                    <i data-lucide="shield-check" style="width: 22px; height: 22px;"></i>
                    <span class="menu-text">Litsenziya</span>
                </a>
            </div>
        @endif

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

        {{--        @if(!$isOwner && $isOrganizationDemo && !$request)--}}
        @if(!$isOwner && $isOrganizationDemo)
            <div class="request">
                <div class="rocket-icon">
                    <i data-lucide="rocket" style="width: 30px; height: 30px;"></i>
                </div>
                <div class="dots">
                    <i data-lucide="ellipsis-vertical" style="width: 18px; height: 18px;"></i>
                </div>
                @php $locale = session()->get('locale', 'uz'); @endphp
                @if($locale == 'uz')
                    <p>Tashkilot xodimi <br> bo'lish uchun so'rov <br> qoldiring</p>
                @elseif($locale == 'kr')
                    <p>Ташкилот ходими <br> бўлиш учун сўров <br> қолдиринг</p>
                @elseif($locale == 'en')
                    <p>Request to become <br> an employee of <br> an organization</p>
                @elseif($locale == 'ru')
                    <p>Подайте заявку, <br> чтобы стать сотрудником <br> организации</p>
                @endif

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
                    document.body.classList.add('theme-dark');
                    localStorage.setItem('sidebar-theme', 'dark');
                } else {
                    sidebar.classList.remove('dark');
                    document.body.classList.remove('theme-dark');
                    localStorage.setItem('sidebar-theme', 'light');
                }
            });
        });

        const savedTheme = localStorage.getItem('sidebar-theme');
        if (savedTheme === 'dark') {
            sidebar.classList.add('dark');
            document.body.classList.add('theme-dark');
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
                            if(badge) {
                                badge.style.display = 'none !important';
                            }
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

{{-- ═══════════════════════════════════════════════════════════════
     ORG SWITCHER — стили
     ═══════════════════════════════════════════════════════════════ --}}
<style>
    .org-switcher {
        position: relative;
        margin: 12px 16px 0;
    }

    .org-switcher-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        border: 1px solid rgba(121, 77, 253, 0.15);
        background: linear-gradient(135deg, rgba(121, 77, 253, 0.04), rgba(157, 111, 255, 0.06));
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.18s ease;
        font-family: inherit;
    }

    .org-switcher-trigger:hover {
        background: linear-gradient(135deg, rgba(121, 77, 253, 0.08), rgba(157, 111, 255, 0.12));
        border-color: rgba(121, 77, 253, 0.30);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(121, 77, 253, 0.18);
    }

    .org-switcher-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }

    .org-switcher-icon {
        width: 32px; height: 32px;
        flex-shrink: 0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        color: #fff;
        box-shadow: 0 4px 12px -3px rgba(121, 77, 253, 0.45);
    }

    .org-switcher-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        flex: 1;
        min-width: 0;
        text-align: left;
    }

    .org-switcher-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted, #94a3b8);
        font-weight: 600;
    }

    .org-switcher-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text, #1e293b);
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .org-switcher-chevron {
        flex-shrink: 0;
        color: var(--text-muted, #94a3b8);
        transition: transform 0.2s ease;
    }

    .org-switcher.open .org-switcher-chevron {
        transform: rotate(180deg);
    }

    .org-switcher-menu {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0; right: 0;
        z-index: 1500;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 12px;
        box-shadow: 0 12px 32px -8px rgba(15, 23, 42, 0.2),
        0 4px 8px -2px rgba(15, 23, 42, 0.06);
        padding: 6px;
        max-height: 360px;
        overflow-y: auto;
        animation: orgMenuSlide 0.18s ease;
    }

    .org-switcher.open .org-switcher-menu {
        display: block;
    }

    @keyframes orgMenuSlide {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .org-switcher-menu-header {
        padding: 8px 10px 6px;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted, #94a3b8);
        font-weight: 700;
    }

    .org-switcher-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        background: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        font-size: 13px;
        color: var(--text, #1e293b);
        transition: background 0.15s ease;
    }

    .org-switcher-item:hover:not(:disabled) {
        background: rgba(121, 77, 253, 0.06);
    }

    .org-switcher-item:disabled,
    .org-switcher-item.active {
        background: rgba(121, 77, 253, 0.10);
        cursor: default;
    }

    .org-switcher-item-icon {
        width: 28px; height: 28px;
        flex-shrink: 0;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(121, 77, 253, 0.08);
        color: #794DFD;
    }

    .org-switcher-item.active .org-switcher-item-icon {
        background: linear-gradient(135deg, #794DFD, #9d6fff);
        color: #fff;
    }

    .org-switcher-item-name {
        flex: 1;
        font-weight: 500;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .org-switcher-item-tin {
        font-size: 11px;
        color: var(--text-muted, #94a3b8);
        flex-shrink: 0;
    }

    .org-switcher-divider {
        height: 1px;
        background: rgba(15, 23, 42, 0.08);
        margin: 6px 4px;
    }

    .org-switcher-add {
        color: #794DFD;
        font-weight: 600;
    }

    .org-switcher-add .org-switcher-item-icon {
        background: linear-gradient(135deg, rgba(121, 77, 253, 0.10), rgba(157, 111, 255, 0.15));
    }

    /* Collapsed sidebar — hide org switcher labels, only icon visible */
    #sidebar.collapsed .org-switcher {
        margin: 12px 8px 0;
    }

    #sidebar.collapsed .org-switcher-info,
    #sidebar.collapsed .org-switcher-chevron {
        display: none !important;
    }

    #sidebar.collapsed .org-switcher-trigger {
        justify-content: center;
        padding: 10px;
    }

    #sidebar.collapsed .org-switcher-menu {
        left: calc(100% + 8px);
        right: auto;
        top: 0;
        min-width: 240px;
    }

    /* Dark theme adjustments */
    [data-theme="dark"] .org-switcher-menu {
        background: #1e293b;
        border-color: rgba(255, 255, 255, 0.08);
    }
    [data-theme="dark"] .org-switcher-name {
        color: #f1f5f9;
    }
    [data-theme="dark"] .org-switcher-item {
        color: #cbd5e1;
    }
    [data-theme="dark"] .org-switcher-item:hover:not(:disabled) {
        background: rgba(121, 77, 253, 0.18);
    }
    [data-theme="dark"] .org-switcher-divider {
        background: rgba(255, 255, 255, 0.08);
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

        // ═══════════════════════════════════════════════════════════════
        // ORG SWITCHER — открытие/закрытие меню + AJAX переключение
        // ═══════════════════════════════════════════════════════════════
        const orgSwitcher = document.getElementById('orgSwitcher');
        if (orgSwitcher) {
            const trigger = document.getElementById('orgSwitcherTrigger');
            const menu = document.getElementById('orgSwitcherMenu');

            // Toggle меню
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                orgSwitcher.classList.toggle('open');
            });

            // Закрыть при клике вне
            document.addEventListener('click', function (e) {
                if (!orgSwitcher.contains(e.target)) {
                    orgSwitcher.classList.remove('open');
                }
            });

            // Esc — закрыть
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    orgSwitcher.classList.remove('open');
                }
            });

            // Клик по организации — переключение
            orgSwitcher.querySelectorAll('.org-switcher-item[data-org-id]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (btn.disabled || btn.classList.contains('active')) return;

                    const orgId = btn.dataset.orgId;
                    if (!orgId) return;

                    // Дизейблим все пункты на время запроса
                    orgSwitcher.querySelectorAll('.org-switcher-item').forEach(b => b.disabled = true);
                    btn.classList.add('switching');

                    fetch('/switch-organization/' + encodeURIComponent(orgId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                        .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
                        .then(({ ok, status, data }) => {
                            if (ok && data.status === 200) {
                                // Перезагружаем — все данные на странице зависят от org_unit_id
                                window.location.href = '/edo';
                            } else {
                                alert(data.message || 'Xatolik');
                                orgSwitcher.querySelectorAll('.org-switcher-item').forEach(b => {
                                    if (!b.classList.contains('active')) b.disabled = false;
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Switch org error:', err);
                            alert('Tarmoq xatosi');
                            orgSwitcher.querySelectorAll('.org-switcher-item').forEach(b => {
                                if (!b.classList.contains('active')) b.disabled = false;
                            });
                        });
                });
            });
        }
    });
</script>
