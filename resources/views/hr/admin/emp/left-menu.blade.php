<div class="hr-left-menu-modern">
    <div class="hr-menu-header">
        <h3>
            <i data-lucide="menu"></i>
            {{ __('menu.menu') }}
        </h3>
    </div>
    <div class="hr-menu-items">
        <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}"
           class="hr-menu-link {{ request()->routeIs('hr.emp.view') ? 'active' : '' }}">
            <i data-lucide="user"></i>
            <span>{{ __('menu.emp_about') }}</span>
        </a>
        <a href="{{ route('hr.emp.info.view', [$user->id, $user->user_hash]) }}"
           class="hr-menu-link {{ request()->routeIs('hr.emp.info.view') ? 'active' : '' }}">
            <i data-lucide="user-check"></i>
            <span>{{ __('menu.emp_info') }}</span>
        </a>
        <a href="{{ route('hr.emp.order.view', [$user->id, $user->user_hash]) }}"
           class="hr-menu-link {{ request()->routeIs('hr.emp.order.view') ? 'active' : '' }}">
            <i data-lucide="file-text"></i>
            <span>{{ __('menu.emp_order') }}</span>
        </a>
        <a href="{{ route('hr.emp.edu.view', [$user->id, $user->user_hash]) }}"
           class="hr-menu-link {{ request()->routeIs('hr.emp.edu.view') ? 'active' : '' }}">
            <i data-lucide="book-open"></i>
            <span>{{ __('menu.emp_edu') }}</span>
        </a>
        <a href="{{ route('hr.emp.fam.view', [$user->id, $user->user_hash]) }}"
           class="hr-menu-link {{ request()->routeIs('hr.emp.fam.view') ? 'active' : '' }}">
            <i data-lucide="heart"></i>
            <span>{{ __('menu.emp_fam') }}</span>
        </a>
        <a href="{{ route('hr.emp.exp.view', [$user->id, $user->user_hash]) }}"
           class="hr-menu-link {{ request()->routeIs('hr.emp.exp.view') ? 'active' : '' }}">
            <i data-lucide="briefcase"></i>
            <span>{{ __('menu.emp_exp') }}</span>
        </a>
    </div>
</div>
