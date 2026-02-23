<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head')
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

            @include('layouts.footer')
        </div>

    </div>

    <div class="container-fluid" hidden>
        <div class="row">
            <div class="col-sm-12 col-xl-4 mb-30">
                <h4 class="sub-title">Single Switche</h4>
                <input type="checkbox" class="js-single" checked />
            </div>
            <div class="col-sm-12 col-xl-4 mb-30">
                <h4 class="sub-title">Multiple Switches</h4>
                <input type="checkbox" class="js-switch" checked />
                <input type="checkbox" class="js-switch" checked />
                <input type="checkbox" class="js-switch" checked />
            </div>
            <div class="col-sm-12 col-xl-4 mb-30">
                <h4 class="sub-title">Enable Disable Switches</h4>
                <input type="checkbox" class="js-dynamic-state" checked />
                <button class="btn btn-primary js-dynamic-enable">Enable</button>
                <button class="btn btn-inverse js-dynamic-disable">Disable</button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8">
                <h4 class="sub-title">Color Switches</h4>
                <input type="checkbox" class="js-default" checked />
                <input type="checkbox" class="js-primary" checked />
                <input type="checkbox" class="js-success" checked />
                <input type="checkbox" class="js-info" checked />
                <input type="checkbox" class="js-warning" checked />
                <input type="checkbox" class="js-danger" checked />
                <input type="checkbox" class="js-inverse" checked />
            </div>
            <div class="col-sm-4">
                <h4 class="sub-title">Switch Sizes</h4>
                <input type="checkbox" class="js-large" checked />
                <input type="checkbox" class="js-medium" checked />
                <input type="checkbox" class="js-small" checked />
            </div>
        </div>
    </div>

@endguest
<script src="{{ asset('/assets/node_modules/popper.js/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('/bootstrap-datepicker/v1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/select2/dist/js/select2.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/jquery-toast-plugin/dist/jquery.toast.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('/assets/js/datatables.js') }}"></script>

<script src="{{ asset('/assets/node_modules/summernote/dist/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/jquery.repeater/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('/assets/node_modules/mohithg-switchery/dist/switchery.min.js') }}"></script>
<script src="{{ asset('/assets/js/form-advanced.js') }}"></script>
<script src="{{ asset('/assets/js/alerts.js') }}"></script>
<script src="{{ asset('/assets/dist/js/theme.min.js') }}"></script>
</body>
</html>
