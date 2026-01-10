@extends('layouts.app')

<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/login.css') }}">

@section('content')
    <div class="container-fluid h-100">
        <div class="row flex-row h-100 bg-white">
            <div class="col-xl-8 col-lg-6 col-md-5 p-0 d-md-block d-lg-block d-sm-none d-none">
                <div class="lavalite-bg" style="background-image: url('{{ $backgroundImage }}')">
                    <div class="lavalite-overlay"></div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-7 my-auto p-0">
                <div class="authentication-form mx-auto">
                    <div class="main-content">
                        <div class="login-container">
                            <div class="logo-centered">
                                <a href="/login"><img src="{{ $logoImage }}" alt=""></a>
                            </div>
                            <h3 class="login-subtitle">{{ __('field.log-in-to-eai') }}</h3>

                            <div class="auth-tabs">
                                <button class="auth-tab" onclick="showTab('login')">Login</button>
                                <button class="auth-tab active" onclick="showTab('eri')">ERI</button>
                            </div>

                            <div id="login-tab" class="tab-content">
                                <form action="{{ route('login') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Login</label>
                                        <input type="text" name="username" class="form-control" placeholder="Loginni kiriting" required>
                                    </div>
                                    <div class="mb-3 position-relative">
                                        <label class="form-label">Parol</label>
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Parolni kiriting" required>
                                        <i class="ik ik-eye password-toggle" onclick="togglePassword()"></i>
                                    </div>
{{--                                    <a href="{{ route('password.request') }}" class="forgot-password">Login yoki parol yodingizdan chiqidimi?</a>--}}
                                    <button type="submit" class="btn-login">Kirish</button>
                                </form>
                            </div>

                            <!-- ERI Tab -->
                            <div id="eri-tab" class="tab-content active">
                                    <input type="hidden" name="eri" id="selectedEri" value="">

                                    <div class="mb-3">
                                        <select class="form-select" id="eriSelect" disabled>
                                            <option value="">Tanlanmagan</option>
                                        </select>
                                    </div>

                                    <button type="button" class="btn-usb" onclick="openUsbToken()">
                                        <img src="https://esi.uz/oauth2/assets/images/icons/pfx.ico" alt="pfx" style="height: 20px">
{{--                                        <i class="ik ik-credit-card"></i>--}}
                                        ERI ni tanlang
                                    </button>

                                    <button onclick="signin()" class="btn-login" id="eriLoginBtn" disabled>Kirish</button>
                            </div>

{{--                            <hr class="divider">--}}

{{--                            <!-- Register Link -->--}}
{{--                            <div class="register-link">--}}
{{--                                <p class="mb-0 text-muted">Tizimda akkauntingiz yo'qmi?</p>--}}
{{--                                <a href="{{ route('register') }}">Ro'yxatdan o'ting</a>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ERI Modal -->
    <div class="eri-modal" id="eriModal">
        <div class="eri-modal-content">
            <div class="eri-modal-header">
                <h5>ERI ni tanlang</h5>
                <button class="eri-modal-close" onclick="closeEriModal()">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="eri-modal-body" id="eriModalBody">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">ERI kalit tekshirilmoqda...</span>
                    </div>
                    <p class="mt-3">E-IMZO yuklanmoqda...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11') }}"></script>
    <script src="{{ asset('js/e_imzo.js') }}"></script>
    <script src="{{ asset('js/e_imzo_client.js') }}"></script>
    <script src="{{ asset('js/login_e_imzo.js') }}"></script>

    <script>
        localStorage.clear();
    </script>
@endsection
