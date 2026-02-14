<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<body style="font-family: Cambria">

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

            @include('layouts.r_sidebar')

            @include('layouts.footer')
        </div>

    </div>

@endguest
</body>
</html>
