<header class="header-top" header-theme="light">
   
    <div class="nav-container">
        <div class="d-flex justify-content-between ">
            <div class="logo">
        <img src="{{ asset('images/logo.svg') }}" alt="" />
        <div class="logo-text">
          <p>O’ZBEKISTON TEMIR YO’LLARI</p>
          <span>AKSIYADORLIK JAMIYATI</span>
        </div>
      </div>
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
<!-- 
                <button type="button" class="nav-link ml-10 right-sidebar-toggle">
                    <i class="ik ik-message-square"></i><span class="badge bg-danger chatListCount"></span>
                </button> -->

           @php $locale = session()->get('locale', 'uz'); @endphp

<div class="dropdown language-dropdown">
    <a class="language-toggle d-flex align-items-center" href="#" id="menuDropdown"
       role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

        <img src="https://flagcdn.com/24x18/{{ $locale === 'ru' ? 'ru' : ($locale === 'en' ? 'gb' : 'uz') }}.png"
             class="flag-straight mr-2"
             width="24" height="18"
             alt="{{ strtoupper($locale) }}">

        <span class="lang-code">{{ strtoupper($locale) }}</span>

        <i class="ik ik-chevron-down ml-2" style="font-size:12px;"></i>
    </a>

    <div class="dropdown-menu dropdown-menu-right language-menu">
        @foreach (['uz' => 'uz', 'ru' => 'ru', 'en' => 'gb'] as $code => $flag)
            <a href="{{ url('/lang/'.$code) }}"
               class="dropdown-item d-flex align-items-center {{ $locale === $code ? 'active' : '' }}">
                
                <img src="https://flagcdn.com/24x18/{{ $flag }}.png"
                     class="flag-straight mr-3"
                     width="24" height="18"
                     alt="{{ strtoupper($code) }}">

                <span class="flex-grow-1">{{ strtoupper($code) }}</span>

                @if($locale === $code)
                    <i class="ik ik-check ml-auto"></i>
                @endif
            </a>
        @endforeach
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
                <div class="dropdown notification-dropdown">
    <a class="nav-link d-flex align-items-center" href="#" id="notiDropdown" role="button" data-toggle="dropdown"
       aria-haspopup="true" aria-expanded="false" style="position: relative; margin-left:10px;">
        <i class="ik ik-bell" style="font-size:18px; color:#495057;"></i>
        <span class="notification-count">3</span> <!-- dynamic count -->
    </a>
    <div class="dropdown-menu dropdown-menu-right notification-menu" aria-labelledby="notiDropdown">
        <h6 class="dropdown-header"><i class="ik ik-bell"></i> Notifications</h6>
        <div class="notifications-wrap" style="max-height: 250px; overflow-y: auto;">
            <a href="#" class="dropdown-item">New document uploaded</a>
            <a href="#" class="dropdown-item">Your request was approved</a>
            <a href="#" class="dropdown-item">Message from Admin</a>
        </div>
        <div class="dropdown-footer text-center">
            <a href="#">View All</a>
        </div>
    </div>
</div>
                <button type="button " class="nav-link ml-10 hidden" id="apps_modal_btn" data-toggle="modal"
                        data-target="#appsModal"><i class="ik ik-grid"></i></button>
                <div class="dropdown" style="display:flex; align-items:center;">
                    <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" style="display:flex; align-items:center;     background: linear-gradient(to right, #ea4c89, #d6336c); width:32px; height:32px; justify-content:center; border-radius:50%; font-weight:bold; color:#fff;   "
                       aria-haspopup="true" aria-expanded="false">
                       SK
                        <!-- @if(file_exists(public_path().'/profile_img/'.'img_'.Auth::user()->id.'.png'))
                            <img src="{{ url('/profile_img/'.'img_'.Auth::user()->id.'.png') }}" width="32" height="32" class="rounded-circle" style="object-fit: cover;">
                        @else
                            <img src="{{ url('/images/profile_image1_32.png') }}" width="32" height="32" class="rounded-circle" style="object-fit: cover;">
                        @endif -->
                    </a>
                  
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" ><i
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

<style>
    /* Header Top */
.header-top {
    padding: 4px 20px !important;
}

/* Logo */
.logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo p {
    font-size: 12px;
    font-weight: 600;
    color: #515151;
}

.logo span {
    font-size: 10px;
    color: #959595;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-text p {
    margin: 0;
    color: #707070;
}

/* Language dropdown */
.language-dropdown .dropdown-toggle {
    color: #495057;
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    transition: background-color 0.2s ease, color 0.2s ease;
    margin: 0 25px 0 10px;
}

.language-dropdown .dropdown-toggle:hover {
    background-color: #f8f9fa;
    color: #794DFD;
    text-decoration: none;
}

.language-dropdown .dropdown-menu {
    min-width: 180px;
    border-radius: 0.75rem;
    overflow: hidden;
    margin-top: 0.5rem;
}

.language-dropdown .dropdown-item {
    font-size: 0.95rem;
    color: #495057;
    transition: all 0.2s ease;
}

.language-dropdown .dropdown-item:hover {
    background-color: #f0f0ff; /* subtle light hover */
    color: #794DFD;
}

.language-dropdown .dropdown-item.active {
    background-color: #794DFD;
    color: white;
}

.language-dropdown .dropdown-item.active:hover {
    background-color: #5f3de0; /* darker variant of primary for hover */
    color: white;
}

/* Profile dropdown */
.dropdown-toggle[aria-haspopup="true"] {
    background: linear-gradient(to right, #794DFD, #5f3de0);
    color: white;
}

/* Profile dropdown menu icons */
.dropdown-menu .dropdown-icon {
    color: #794DFD;
    transition: color 0.2s ease;
}

.dropdown-menu .dropdown-item:hover .dropdown-icon {
    color: #5f3de0;
}

.dropdown-menu .dropdown-item.active .dropdown-icon {
    color: white;
}

/* Flags */
.flag-straight {
    border-radius: 2px;
    object-fit: cover;
    display: block;
    box-shadow: 0 1px 2px rgba(0,0,0,0.15);
}

.flag-straight,
.flag-straight img {
    border-radius: 2px !important;
}

/* Optional hover scale for flags */
.language-dropdown .dropdown-item:hover .flag-rounded {
    transform: scale(1.05);
}

    .flag-straight {
    border-radius: 2px;         /* NOT rounded-circle */
    object-fit: cover;
    display: block;
    box-shadow: 0 1px 2px rgba(0,0,0,0.15);
}

/* Remove any bootstrap rounding */
.flag-straight,
.flag-straight img {
    border-radius: 2px !important;
}

    .header-top{
        padding:4px 20px !important;
    }
    .header-top-left{
        display:flex;
        align-items:center;
    }
    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
      }

      .logo p {
        font-size: 12px;
        font-weight: 600;
        color: #515151;
      }

      .logo span {
        font-size: 10px;
        color: #959595;
      }
      .logo-text{
        display: flex;
        flex-direction: column;
      }
      .logo-text p {
        margin: 0;
        color: #707070;
      }

     .language-dropdown .dropdown-toggle {
        color: #495057;
        font-size: 0.95rem;
        padding: 0.5rem 1rem;
        transition: background-color 0.2s ease;
        margin:0 25px 0 10px;
    }

    .language-dropdown .dropdown-toggle:hover {
        background-color: #f8f9fa;
        text-decoration: none;
    }

    .language-dropdown .dropdown-menu {
        min-width: 180px;
        border-radius: 0.75rem;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .language-dropdown .dropdown-item {
        font-size: 0.95rem;
        color: #495057;
        transition: all 0.2s ease;
    }

    .language-dropdown .dropdown-item:hover {
        background-color: #fdf2f8;
        color: #794dfd;
    }

    .language-dropdown .dropdown-item.active {
        background-color: #794dfd;
        color: white;
    }

    .language-dropdown .dropdown-item.active:hover {
        background-color: #d9467a;
        color: white;
    }

    /* Flags: straight proportions (4:3 ratio) with rounded corners */
    .flag-rounded {
        border-radius: 4px;           /* Soft rounded corners */
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        object-fit: cover;            /* Ensures flag fills without distortion */
    }

    /* Optional: slight scale on hover */
    .language-dropdown .dropdown-item:hover .flag-rounded {
        transform: scale(1.05);
    }

    /* Notification icon container */
.notification-dropdown .nav-link {
    position: relative;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    color: #495057;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: background 0.2s ease, color 0.2s ease;
}

.notification-dropdown .nav-link:hover {
    background-color: #f8f9fa;
    color: #794DFD;
    text-decoration: none;
}

/* Notification counter */
.notification-count {
    position: absolute;
    top: 0px;
    right: 0px;
    transform: translate(50%, -50%);
    background-color: #dc2626; /* red badge */
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 50%;
    line-height: 1;
}

/* Notification dropdown menu */
.notification-menu {
    min-width: 220px;
    border-radius: 0.75rem;
    overflow: hidden;
    margin-top: 0.5rem;
    padding: 0.25rem 0;
}

.notification-menu .dropdown-header {
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #e5e5e5;
}

.notification-menu .dropdown-item {
    font-size: 0.85rem;
    color: #495057;
    padding: 0.5rem 1rem;
    transition: background 0.2s ease;
}

.notification-menu .dropdown-item:hover {
    background-color: #f1f1ff;
    color: #794DFD;
}

.notification-menu .dropdown-footer {
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
    border-top: 1px solid #e5e5e5;
}

.notification-menu .dropdown-footer a {
    text-decoration: none;
    color: #794DFD;
    font-weight: 500;
}

.notification-menu .dropdown-footer a:hover {
    text-decoration: underline;
}

.d-flex{
    align-items:center;
}

</style>