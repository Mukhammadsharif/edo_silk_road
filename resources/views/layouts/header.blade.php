
@php
use App\Models\Admin\RequestForOrgs;
use Illuminate\Support\Facades\Auth;

$user = App\Models\User::where('id', Auth::id())->first();
$isOrganizationDemo = $user?->org_unit_id === 1;
$request = RequestForOrgs::where('user_id', Auth::id())->first();
@endphp

<header class="header-top d-flex justify-content-between align-items-center px-4 py-2 shadow-sm bg-white"
    header-theme="light">

    <div class="d-flex align-items-center gap-3">
        <a href="{{ url('/edo') }}" class="d-flex align-items-center text-decoration-none gap-2">
            <img src="{{ asset('images/edoline.svg') }}" alt="EDOLINE" style="width:34px; height:34px;">
            <span class="fw-bold fs-5" style="color: #794DFD; letter-spacing: -0.5px;">EDOLINE</span>
        </a>

        <button id="sidebar-toggle" class=" btn-light rounded-circle  shadow-sm border"
            style="width: 42px; height: 42px; font-size: 1.3rem; transition: all 0.25s;">
            <i data-lucide="panel-left-close" class="icon-closed text-muted " style="width:30px; color:#794DFD"></i>
            <i data-lucide="panel-left-open" class="icon-open text-muted d-none" style="width:30px; color:#794DFD"></i>
        </button>

    </div>

    <div class="top-menu d-flex align-items-center ms-auto">

        @php $locale = session()->get('locale', 'uz'); @endphp
        <div class="dropdown language-dropdown">
            <a class="language-toggle d-flex align-items-center" href="#" id="menuDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="https://flagcdn.com/24x18/{{ $locale === 'ru' ? 'ru' : ($locale === 'en' ? 'gb' : 'uz') }}.png"
                    class="flag-straight mr-2" width="24" height="18" alt="{{ strtoupper($locale) }}">
                <span class="lang-code">{{ strtoupper($locale) }}</span>
                <i class="ik ik-chevron-down ml-2" style="font-size:12px;"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right language-menu">
                @foreach (['uz' => 'uz', 'ru' => 'ru', 'en' => 'gb'] as $code => $flag)
                <a href="{{ url('/lang/'.$code) }}"
                    class="dropdown-item d-flex align-items-center {{ $locale === $code ? 'active' : '' }}">
                    <img src="https://flagcdn.com/24x18/{{ $flag }}.png" class="flag-straight mr-3" width="24"
                        height="18" alt="{{ strtoupper($code) }}">
                    <span class="flex-grow-1">{{ strtoupper($code) }}</span>
                    @if($locale === $code)
                    <i class="ik ik-check ml-auto"></i>
                    @endif
                </a>
                @endforeach
            </div>
        </div>


        <div class="dropdown ms-3">
            <a class="dropdown-toggle d-flex align-items-center justify-content-center text-white" href="#"
                id="userDropdown" role="button" data-toggle="dropdown"
                style="width:32px; height:32px; border-radius:50%; font-weight:bold;">
                <img src="{{ $user->image() }}" alt="user-img" style="width:32px; height:32px; border-radius:50%;"/>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile.my') }}"><i class="ik ik-user dropdown-icon"></i> {{
                    __('field.profile') }}</a>
                <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="ik ik-power dropdown-icon"></i> {{ __('field.logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</header>


<script>
    document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    if (!toggle || !sidebar) {
        console.warn('Toggle or sidebar not found');
        return;
    }


    let collapsed = localStorage.getItem('sidebar-collapsed') === 'true';


    if (collapsed) {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
    }

    updateIcon();

    function updateIcon() {
        const closed = toggle.querySelector('.icon-closed');
        const open   = toggle.querySelector('.icon-open');
        if (closed && open) {
            closed.classList.toggle('d-none', collapsed);
            open.classList.toggle('d-none', !collapsed);
        }
        lucide.createIcons();
    }


    toggle.addEventListener('click', () => {
        collapsed = !collapsed;

        sidebar.classList.toggle('collapsed', collapsed);
        document.body.classList.toggle('sidebar-collapsed', collapsed);

        localStorage.setItem('sidebar-collapsed', collapsed);
        updateIcon();
        window.dispatchEvent(new Event('resize'));
    });
});
</script>


<script src="{{ asset('/ajax/jquery.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).on('click', '.modal_btn', function (e) {

            e.preventDefault();

            var data = {
                'tax_number': $('.tax_number').val(),
            }

            let modal = $('#RequestModal')
            let save_msgList = $('#save_msgList')
            let success_message = $('#success_message')

            $.ajax({
                url: '/admin/organizations/send-request',
                method: 'POST',
                data: data,
                beforeSend: function () {

                },
                success: function (response) {
                    if (response.status === 400) {
                        save_msgList.html("");
                        save_msgList.addClass('alert alert-danger');
                        $.each(response.errors, function (key, err_value) {
                            $('#save_msgList').append('<li>' + err_value + '</li>');
                        });
                        $('.create_btn').text('Save');
                    } else {
                        save_msgList.html("");
                        success_message.addClass('alert alert-success');
                        success_message.text(response.message);
                        modal.find('input').val('');
                        $('.modal_btn').text('Save');
                        modal.modal('hide');
                        setTimeout(() => window.location.reload(), 500)
                    }
                },
            }).error(function (err) {
                console.log(err)
            })
        })

        $(document).on('click', '.cancel-request-btn', function (e) {
            e.preventDefault();

            let modal = $('#CancelModal');
            let save_msgList = $('#save_msgList');

            $.ajax({
                url: '/admin/organizations/cancel-request',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('.cancel-request-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Bekor qilinmoqda...');
                },
                success: function (response) {
                    if (response.status === 200) {
                        modal.modal('hide');

                        setTimeout(() => window.location.reload(), 500);
                    }
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON?.errors || {error: ['Xatolik yuz berdi. Qayta urinib ko\'ring.']};
                    save_msgList.html("");
                    save_msgList.addClass('alert alert-danger').show();
                    $.each(errors, function (key, err_value) {
                        if (Array.isArray(err_value)) {
                            err_value.forEach(msg => $('#save_msgList').append('<li>' + msg + '</li>'));
                        } else {
                            $('#save_msgList').append('<li>' + err_value + '</li>');
                        }
                    });
                },
                complete: function () {
                    $('.cancel-request-btn').prop('disabled', false).html('<i class="ik ik-trash-2"></i> Bekor qilish');
                }
            });
        });
    })
</script>


<style>
    :root {
        --sidebar-width: 260px;
        --sidebar-collapsed: 78px;
    }

    #sidebar {
        width: var(--sidebar-width);
        transition: width 0.35s ease;
    }

    #sidebar.collapsed {
        width: var(--sidebar-collapsed);
    }

    #sidebar.collapsed .menu-text,
    #sidebar.collapsed .chevron,
    #sidebar.collapsed .badge-count,
    #sidebar.collapsed .request,
    #sidebar.collapsed span:not(.icon) {
        display: none !important;
    }

    #sidebar.collapsed .menu-header,
    #sidebar.collapsed a.d-flex {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    #sidebar-toggle {
        margin-left: 130px;
    }

    #sidebar.collapsed .submenu {
        position: absolute;
        left: var(--sidebar-collapsed);
        top: 0;
        width: 260px;
        background: inherit;
        border-left: 1px solid #dee2e6;
        box-shadow: 6px 0 20px rgba(0, 0, 0, 0.12);
        z-index: 10;
        display: none;
    }

    #sidebar.collapsed .menu-item:hover .submenu,
    #sidebar.collapsed .menu-item.open .submenu {
        display: block;
    }


    .wrapper .page-wrap .main-content {
        padding: 30px 15px 30px 255px;
        background-color: #f6f7fb;
        min-height: calc(100vh - 120px);
        margin-top: 60px;
        -moz-transition: .3s;
        -o-transition: .3s;
        -webkit-transition: .3s;
        transition: .3s;
    }

    body.sidebar-collapsed .wrapper .page-wrap .main-content {
        padding-left: 80px !important;
    }

    #sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .lucide-panel-left-close,
    .lucide-panel-left-open {
        color: #794DFD !important;
    }

    .flex-grow-1 {
        color: #212529;
    }

    .fw-bold {
        margin: 0 0 0 10px;
        font-size: 18px;
        color: #007bff;
        font-weight: 700;
    }
</style>
