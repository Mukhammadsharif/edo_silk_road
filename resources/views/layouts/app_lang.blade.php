<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'UzMRC-EDO') }}</title>

    <link rel="icon" href="{{ $favicon }}" type="image/x-icon" />
    <link href="{{ asset('/assets/fonts.css?family=Nunito+Sans:300,400,600,700,800') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/icon-kit/dist/css/iconkit.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/ionicons/dist/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/main.css') }}">


    <link href="{{ asset('/lang/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('/lang/bootstrap-editable.css') }}" rel="stylesheet">

    <script src="{{ asset('/lang/jquery.min.js') }}"></script>
    <script src="{{ asset('/lang/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/lang/bootstrap-editable.min.js') }}"></script>

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

    <script src="{{ asset('/assets/node_modules/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('/assets/js/datatables.js') }}"></script>


@endguest

</body>
</html>
