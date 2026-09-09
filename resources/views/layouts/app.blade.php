<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head')
<meta name="csrf-token" content="{{ csrf_token() }}">
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

    <script src="{{ asset('/bootstrap-datepicker/v1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
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
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="{{ asset('js/upload-size-guard.js') }}"></script>

<script>

    $(function () {
        $('.my-bootstrap-datepicker').datepicker({
            language: "en",
            autoclose: true,
            format: "dd.mm.yyyy",
            todayHighlight: true
        });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // SELECT FILE
    $(".custom-file-input").on("change", function() {
        let files = Array.from(this.files)
        let fileName = files.map(f =>{return f.name}).join(", ")
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    function printDoc()
    {
        let prtContent = document.getElementById("printDoc");
        let WinPrint = window.open();
        WinPrint.document.write(prtContent.innerHTML);
        WinPrint.document.head.insertAdjacentHTML('beforeend', '<link rel="stylesheet" type="text/css" href="/assets/node_modules/bootstrap/dist/css/bootstrap.min.css"/>' );
        WinPrint.document.head.insertAdjacentHTML('beforeend', '<link rel="stylesheet" type="text/css" href="/assets/dist/css/theme.min.css"/>' );
        WinPrint.document.close();
        WinPrint.focus();
        WinPrint.print();
        WinPrint.close();
    }

    setTimeout(function () {
        $('.hideAlert').hide();
    }, 5000);


    function searchIconsCreate() {
        var input = document.getElementById("searchIconCreate");
        var filter = input.value.toLowerCase();
        var nodes = document.getElementsByClassName('icon_div_list_create');

        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function searchIconsEdit() {
        var input = document.getElementById("searchIconEdit");
        var filter = input.value.toLowerCase();
        var nodes = document.getElementsByClassName('icon_div_list_edit');

        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }
</script>

<style>
    .icon-list-wrap {
        max-height: 400px;
        overflow-y: auto;
        margin-bottom: 50px;
    }
    .icon-list-wrap .icon-list-item  {
        margin-bottom: 0;
        border-radius: 6px;
        background: #2196f32b;
    }
    .icon-list-wrap .icon-list-item i {
        float: unset;
        width: 60px;
        height: 60px;
        text-align: center;
        border-radius: .1875rem;
        margin-right: 1rem;
        margin-bottom: 0;
        font-size: 1.8rem;
        -webkit-transition: all .2s ease-in-out;
        -o-transition: all .2s ease-in-out;
        -moz-transition: all .2s ease-in-out;
        transition: all .2s ease-in-out;
    }
</style>
@stack('scripts')
</body>
</html>
