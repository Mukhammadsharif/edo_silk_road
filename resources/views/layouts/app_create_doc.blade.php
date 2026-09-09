<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EAI-EDO') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_new.svg') }}">
    <link href="{{ asset('/assets/fonts.css?family=Nunito+Sans:300,400,600,700,800') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/icon-kit/dist/css/iconkit.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/ionicons/dist/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/jquery-toast-plugin/dist/jquery.toast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/perfect-scrollbar/css/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/jquery-toast-plugin/dist/jquery.toast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/summernote/dist/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark-theme.css') }}">

    <link rel="stylesheet" href="{{ asset('fontawesome-web/css/all.min.css') }}">

    <script src="{{ asset('/assets/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('/assets/js/jquery.inputmask.bundle.js') }}"></script>
    <script src="{{ asset('/assets/src/js/vendor/jquery-ui.min.js') }}"></script>
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
            <div class="main-content" style="padding-top: 3px">
                @yield('content')
            </div>
        </div>
    </div>


    <div class="modal fade apps-modal" id="appsModal" tabindex="-1" role="dialog" aria-labelledby="appsModalLabel" aria-hidden="true" data-backdrop="false">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i data-lucide="ban"></i></button>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="quick-search">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-4 ml-auto mr-auto">
                                <div class="input-wrap">
                                    <input type="text" id="quick-search" class="form-control" placeholder="Search..." />
                                    <i data-lucide="search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body d-flex align-items-center">
                    <div class="container">
                        <div class="apps-wrap">
                            <div class="app-item">
                                <a href="#"><i class="ik ik-bar-chart-2"></i><span>Dashboard</span></a>
                            </div>
                            <div class="app-item dropdown">
                                <a href="#" class="dropdown-toggle" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ik ik-command"></i><span>Ui</span></a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </div>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-mail"></i><span>Message</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-users"></i><span>Accounts</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-shopping-cart"></i><span>Sales</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-briefcase"></i><span>Purchase</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-server"></i><span>Menus</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-clipboard"></i><span>Pages</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-message-square"></i><span>Chats</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-map-pin"></i><span>Contacts</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-box"></i><span>Blocks</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-calendar"></i><span>Events</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-bell"></i><span>Notifications</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-pie-chart"></i><span>Reports</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-layers"></i><span>Tasks</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-edit"></i><span>Blogs</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i data-lucide="settings"></i><span>Settings</span></a>
                            </div>
                            <div class="app-item">
                                <a href="#"><i class="ik ik-more-horizontal"></i><span>More</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    <script src="{{ asset('/assets/node_modules/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('/assets/node_modules/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('/assets/node_modules/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('/assets/node_modules/jquery.repeater/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('/assets/dist/js/theme.min.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="{{ asset('js/upload-size-guard.js') }}"></script>

    {{--<script>
        document.getElementById('createForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById("submitBtn").innerHTML = "{{ __('action.saving') }}...";
        });

        // SELECT FILE
        $(".custom-file-input").on("change", function() {
            let files = Array.from(this.files)
            let fileName = files.map(f =>{return f.name}).join(", ")
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    </script>--}}
</body>
</html>
