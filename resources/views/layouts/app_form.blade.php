<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
{{--@include('layouts.head')--}}
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Form Advance | ThemeKit - Admin Template</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_new.svg') }}">

    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800" rel="stylesheet">

    <link rel="stylesheet" href="/assets/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/assets/node_modules/ionicons/dist/css/ionicons.min.css">
    <link rel="stylesheet" href="/assets/node_modules/icon-kit/dist/css/iconkit.min.css">
    <link rel="stylesheet" href="/assets/node_modules/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="/assets/node_modules/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="/assets/node_modules/summernote/dist/summernote-bs4.css">
    <link rel="stylesheet" href="/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">
    <link rel="stylesheet" href="/assets/node_modules/mohithg-switchery/dist/switchery.min.css">
    <link rel="stylesheet" href="/assets/dist/css/theme.min.css">
    <script src="/assets/src/js/vendor/modernizr-2.8.3.min.js"></script>
</head>
<body>

@guest
    <div class="auth-wrapper">
        @yield('content')
    </div>
@else
    <div class="wrapper">

        @include('layouts.header')

        <div class="page-wrap">

            @include('layouts.sidebar')

            <div class="main-content">
                @yield('content')
            </div>

            @include('templates.chat-list')

            @include('layouts.footer')
        </div>

    </div>

@endguest


</body>
</html>
