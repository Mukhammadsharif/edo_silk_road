<header class="header-top" header-theme="light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <div class="top-menu d-flex align-items-center">
                <button type="button" class="btn-icon mobile-nav-toggle d-lg-none"><span></span></button>
                <div class="header-search" hidden>
                    <div class="input-group">
                        <span class="input-group-addon search-close"><i class="ik ik-x"></i></span>
                        <input type="text" class="form-control">
                        <span class="input-group-addon search-btn"><i class="ik ik-search"></i></span>
                    </div>
                </div>
                <button type="button" id="navbar-fullscreen" class="nav-link" hidden><i class="ik ik-maximize"></i>
                </button>
            </div>
            <div class="top-menu d-flex align-items-center">

                {{--<div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notiDropdown" role="button" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <i class="ik ik-message-square"></i><span class="badge bg-danger count"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notiDropdown">
                        <h4 class="header"><i class="ik ik-bell"></i> {{ __('message.notifications') }}</h4>
                        <div class="notifications-wrap" style="max-height: 400px; overflow-x: auto">
                        </div>
                    </div>
                </div>--}}

                <button type="button" class="nav-link ml-10 right-sidebar-toggle">
                    <i class="ik ik-message-square"></i><span class="badge bg-danger chatListCount"></span>
                </button>

                <div class="dropdown">
                    @php $locale = session()->get('locale'); @endphp
                    <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        @switch($locale)
                            @case('ru')
                                RU
                                @break
                            @case('en')
                                EN
                                @break
                            @default
                                UZ
                        @endswitch
                    </a>
                    <div class="dropdown-menu dropdown-menu-right menu-grid" aria-labelledby="menuDropdown">
                        <a class="dropdown-item" href="{{ url('/lang/uz') }}" data-toggle="tooltip" data-placement="top"
                           title="O`zbekcha">UZ</a>
                        <a class="dropdown-item" href="{{ url('/lang/ru') }}" data-toggle="tooltip" data-placement="top"
                           title="Русский">RU</a>
                        <a class="dropdown-item" href="{{ url('/lang/en') }}" data-toggle="tooltip" data-placement="top"
                           title="English">EN</a>
                    </div>
                </div>

                <div class="dropdown hidden">
                    <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false"><i class="ik ik-plus"></i></a>
                    <div class="dropdown-menu dropdown-menu-right menu-grid" aria-labelledby="menuDropdown">
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top"
                           title="Dashboard"><i class="ik ik-bar-chart-2"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Message"><i
                                class="ik ik-mail"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Accounts"><i
                                class="ik ik-users"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Sales"><i
                                class="ik ik-shopping-cart"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Purchase"><i
                                class="ik ik-briefcase"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Pages"><i
                                class="ik ik-clipboard"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Chats"><i
                                class="ik ik-message-square"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Contacts"><i
                                class="ik ik-map-pin"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Blocks"><i
                                class="ik ik-inbox"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Events"><i
                                class="ik ik-calendar"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top"
                           title="Notifications"><i class="ik ik-bell"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="More"><i
                                class="ik ik-more-horizontal"></i></a>
                    </div>
                </div>
                <button type="button " class="nav-link ml-10 hidden" id="apps_modal_btn" data-toggle="modal"
                        data-target="#appsModal"><i class="ik ik-grid"></i></button>
                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        @if(file_exists(public_path().'/profile_img/'.'img_'.Auth::user()->id.'.png'))
                            <img src="{{ url('/profile_img/'.'img_'.Auth::user()->id.'.png') }}" width="32" height="32" class="rounded-circle" style="object-fit: cover;">
                        @else
                            <img src="{{ url('/images/profile_image1_32.png') }}" width="32" height="32" class="rounded-circle" style="object-fit: cover;">
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="{{ url('/profile/my') }}"><i
                                class="ik ik-user dropdown-icon"></i> {{ __('field.profile') }}</a>
                        <a class="dropdown-item" href="#" hidden><i
                                class="ik ik-settings dropdown-icon"></i> @lang('menu.administrator')</a>
                        <a class="dropdown-item" href="#" hidden><i
                                class="ik ik-mail dropdown-icon"></i> @lang('menu.edo_inbox_docs')</a>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            <i class="ik ik-power dropdown-icon"></i> {{ __('field.logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>
