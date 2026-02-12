<nav class="breadcrumb-modern">
    <a href="{{ route('home') }}" class="breadcrumb-item">
        <i data-lucide="home"></i>
        <span>{{ __('menu.home') ?? 'Главная' }}</span>
    </a>
    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
    <a href="{{ route('hr.emp') }}" class="breadcrumb-item">
        <span>{{ __('menu.hr_emp') }}</span>
    </a>
    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
    <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}" class="breadcrumb-item">
        <span>{{ $user->userPersonal->l_name??'-' }} {{ $user->userPersonal->f_name??'-' }}</span>
    </a>
    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
    <span class="breadcrumb-item active">{{ __('menu.view') }}</span>
</nav>
