@php
    use App\Models\Admin\RequestForOrgs;
    use Illuminate\Support\Facades\Auth;

    $user = App\Models\User::where('id', Auth::id())->first();
    $isOrganizationDemo = $user?->org_unit_id === 1;
    $request = RequestForOrgs::where('user_id', Auth::id())->first();
@endphp

<header class="header-top d-flex justify-content-between align-items-center" header-theme="light">


             <div class="logo">
                <a href="{{ url('/edo') }}">
                <img src="{{ asset('images/edofy.svg') }}" style="width: 100px" alt=""></a>

            </div>

                    <div class="top-menu d-flex align-items-center ms-auto">


                        @php $locale = session()->get('locale', 'uz'); @endphp
                        <div class="dropdown language-dropdown">
                            <a class="language-toggle d-flex align-items-center" href="#" id="menuDropdown"
                               role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="https://flagcdn.com/24x18/{{ $locale === 'ru' ? 'ru' : ($locale === 'en' ? 'gb' : 'uz') }}.png"
                                     class="flag-straight mr-2" width="24" height="18" alt="{{ strtoupper($locale) }}">
                                <span class="lang-code">{{ strtoupper($locale) }}</span>
                                <i class="ik ik-chevron-down ml-2" style="font-size:12px;"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right language-menu">
                                @foreach (['uz' => 'uz', 'ru' => 'ru', 'en' => 'gb'] as $code => $flag)
                                    <a href="{{ url('/lang/'.$code) }}"
                                       class="dropdown-item d-flex align-items-center {{ $locale === $code ? 'active' : '' }}">
                                        <img src="https://flagcdn.com/24x18/{{ $flag }}.png"
                                             class="flag-straight mr-3" width="24" height="18" alt="{{ strtoupper($code) }}">
                                        <span class="flex-grow-1">{{ strtoupper($code) }}</span>
                                        @if($locale === $code)
                                            <i class="ik ik-check ml-auto"></i>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>



                        <div class="dropdown ms-3">
                            <a class="dropdown-toggle d-flex align-items-center justify-content-center text-white"
                               href="#" id="userDropdown" role="button" data-toggle="dropdown"
                               style="background: linear-gradient(to right, #ea4c89, #d6336c); width:32px; height:32px; border-radius:50%; font-weight:bold;">
                                SK
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('profile.my') }}"><i class="ik ik-user dropdown-icon"></i> {{ __('field.profile') }}</a>
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


<div class="modal fade" id="RequestModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-plus-circle"></i> {{ __('field.leave_request') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul id="save_msgList"></ul>
                <div class="form-group mb-3">
                    <label for="">{{ __('action.fill_tax_number') }}</label>
                    <input type="number" required class="tax_number form-control">
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-light" data-dismiss="modal"><i
                        class="ik ik-x"></i>{{ __('action.cancel') }}</button>
                <button class="btn btn-outline-info modal_btn"><i data-lucide="save"></i>{{ __('action.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="CancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="ik ik-alert-octagon text-danger"></i> {{ __('action.reject_request') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('field.are_you_sure_reject_joining_request') }}</p>
                <p>{{ __('field.it_cannot_undone') }}</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-light" data-dismiss="modal">
                    <i class="ik ik-x"></i> {{ __('action.close') }}
                </button>
                <button type="button" class="btn btn-danger cancel-request-btn">
                    <i class="ik ik-trash-2"></i> {{ __('action.cancel') }}
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



.container-fluid{
 padding: 10px 20px !important;
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

.logo-text {
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
    transition: background-color 0.2s ease, color 0.2s ease;
    margin: 0 25px 0 10px;
}

.language-dropdown .dropdown-toggle:hover {
    background-color: #f8f9fa;
    color: #794DFD;
    text-decoration: none;
}

.language-dropdown .dropdown-menu {
    min-width: 134px;
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
    background-color: #f0f0ff;
    color: #794DFD;
}

.language-dropdown .dropdown-item.active {
    background-color: #794DFD;
    color: white;
}

.language-dropdown .dropdown-item.active:hover {
    background-color: #5f3de0;
    color: white;
}

.dropdown-toggle[aria-haspopup="true"] {
    background: linear-gradient(to right, #794DFD, #5f3de0);
    color: white;
}

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

    .header-top{
        padding:5px 20px !important;
        box-shadow: rgba(172, 172, 172, 0.2) 0px 2px px 0px !important;
        z-index: 10000;
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
    min-width: 134px;
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


    .flag-rounded {
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        object-fit: cover;
    }



.d-flex{
    align-items:center;
}

.alert{
    opacity: 0;
}
</style>
