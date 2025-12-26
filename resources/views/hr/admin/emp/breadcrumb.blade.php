<nav class="breadcrumb-container" aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('hr.emp') }}">{{ __('menu.hr_emp') }}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}">{{ $user->userPersonal->l_name??'-' }} {{ $user->userPersonal->f_name??'-' }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('menu.view') }}</li>
    </ol>
</nav>
