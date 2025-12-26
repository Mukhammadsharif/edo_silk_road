@extends('layouts.app')

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
                    <div class="logo-centered">
                        <a href="/login"><img src="{{ $logoImage }}" alt=""></a>
                    </div>
                    <h3>{{ __('field.log-in-to-eai') }}</h3>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <input id="username" type="text" name="username" class="form-control @error('username') is-invalid @else border-primary @enderror" placeholder="Login" value="{{ old('username') }}" autocomplete="username" autofocus>
                            <i class="ik ik-user"></i>
                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @else border-primary @enderror" placeholder="Parol" autocomplete="current-password">
                            <i class="ik ik-lock"></i>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="sign-btn text-center">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ik ik-log-in"></i> {{ __('field.log-in') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
