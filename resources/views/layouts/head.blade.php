<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EAI-EDO') }}</title>

    <!-- Scripts -->

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_new.svg') }}">
    <link href="{{ asset('/assets/fonts.css?family=Nunito+Sans:300,400,600,700,800') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/bootstrap-datepicker/v1.9.0/css/bootstrap-datepicker3.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/icon-kit/dist/css/iconkit.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/ionicons/dist/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/jquery-toast-plugin/dist/jquery.toast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/perfect-scrollbar/css/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/jquery-toast-plugin/dist/jquery.toast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/summernote/dist/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">

    <link rel="stylesheet" href="{{ asset('/assets/node_modules/mohithg-switchery/dist/switchery.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/boards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/free-sign-editor.css') }}">
    <style>
        /* Высота шапки твоего layout, которую надо вычесть, чтобы редактор
           занял область контента и не уезжал под меню. Нет шапки → 0px. */
        .fs-editor { --fs-header-h: 64px; }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css">'

    <script src="{{ asset('/assets/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('/assets/js/jquery.inputmask.bundle.js') }}"></script>
    <script src="{{ asset('/assets/src/js/vendor/jquery-ui.min.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>[x-cloak]{display:none !important}</style>
</head>
