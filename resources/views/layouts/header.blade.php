@php use App\Models\Admin\RequestForOrgs;use Illuminate\Support\Facades\Auth; @endphp
@php $user = App\Models\User::where('id', Auth::id())->first();@endphp
@php $isOrganizationDemo = $user->org_unit_id === 1;@endphp
@php $request = RequestForOrgs::where('user_id', Auth::id())->first();@endphp

<header class="header-top" header-theme="light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            @if($isOrganizationDemo && !$request)
                <div
                    class="alert bg-success alert-success text-white d-flex flex-row justify-content-between align-items-center"
                    role="alert"
                    style="width: 35%; margin: 0 0 0 20px;">
                    <span>Tashkilotga birikish uchun so'rov qoldiring</span>
                    <button class="btn btn-light" id="requestButton" data-toggle="modal" data-target="#RequestModal">
                        So'rov qoldirish
                    </button>
                </div>
            @endif

            @if($isOrganizationDemo && $request && $request->status == 1)
                <div
                    class="alert bg-secondary alert-success text-white d-flex flex-row justify-content-between align-items-center"
                    role="alert"
                    style="width: 35%; margin: 0 0 0 20px;">
                    <span>Tashkilotga birikish uchun so'rovingiz ko'rib chiqilmoqda</span>
                    <button class="btn btn-danger" id="cancelButton" data-toggle="modal" data-target="#CancelModal">
                        Bekor qilish
                    </button>
                </div>
            @endif

            @if($isOrganizationDemo && $request && $request->status == 3)
                <div
                    class="alert bg-danger alert-success text-white d-flex flex-row justify-content-between align-items-center"
                    role="alert"
                    style="width: 35%; margin: 0 0 0 20px;">
                    <span>Tashkilotga birikish uchun qoldirgan so'rovingiz rad etildi</span>
                    <button class="btn btn-light" id="requestButton" data-toggle="modal" data-target="#RequestModal">
                        So'rov qoldirish
                    </button>
                </div>
            @endif

            @can('super_admin')
                <div class="nav-item">
                    <a href="{{ route('admin.subscription-requests') }}" class="nav-link">
                        <i class="ik ik-clock"></i>
                        <span>Obuna so'rovlari</span>
                        @php
                            $pendingCount = \App\Models\Admin\Subscriptions::where('status', 2)->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="badge badge-warning ml-2">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </div>
            @endcan

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
                <button type="button" class="nav-link ml-10 hidden" id="apps_modal_btn" data-toggle="modal"
                        data-target="#appsModal"><i class="ik ik-grid"></i></button>
                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        @if(file_exists(public_path().'/profile_img/'.'img_'.Auth::user()->id.'.png'))
                            <img src="{{ url('/profile_img/'.'img_'.Auth::user()->id.'.png') }}" width="32" height="32"
                                 class="rounded-circle" style="object-fit: cover;">
                        @else
                            <img src="{{ url('/images/profile_image1_32.png') }}" width="32" height="32"
                                 class="rounded-circle" style="object-fit: cover;">
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

<div class="modal fade" id="RequestModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-plus-circle"></i> {{ __('action.create') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul id="save_msgList"></ul>
                <div class="form-group mb-3">
                    <label for="">{{ __('field.tax_number') }}</label>
                    <input type="number" required class="tax_number form-control">
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-light" data-dismiss="modal"><i
                        class="ik ik-x"></i>{{ __('action.cancel') }}</button>
                <button class="btn btn-outline-info modal_btn"><i class="ik ik-save"></i>{{ __('action.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="CancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="ik ik-alert-octagon text-danger"></i> So'rovni bekor qilish</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Rostdan ham tashkilotga birikish uchun yuborilgan so'rovni bekor qilmoqchimisiz?</p>
                <p>Bu amalni ortga qaytarib bo'lmaydi.</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-light" data-dismiss="modal">
                    <i class="ik ik-x"></i> Yopish
                </button>
                <button type="button" class="btn btn-danger cancel-request-btn">
                    <i class="ik ik-trash-2"></i> Bekor qilish
                </button>
            </div>
        </div>
    </div>
</div>

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
            let save_msgList = $('#save_msgList'); // можно переиспользовать, но лучше очистить

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
                        // Показать успех можно через toast или просто перезагрузить
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
