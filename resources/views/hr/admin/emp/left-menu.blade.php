<div class="card">
    <div class="card-header"><h3><i class="ik ik-list"></i> {{ __('menu.menu') }}</h3></div>
    <div class="card-body template-demo">
        <a href="{{ route('hr.emp.view', [$user->id, $user->user_hash]) }}" class="btn btn-outline-info">
            <i class="ik ik-user"></i> {{ __('menu.emp_about') }}
        </a><br>
        <a href="{{ route('hr.emp.info.view', [$user->id, $user->user_hash]) }}" class="btn btn-outline-info">
            <i class="ik ik-user-check"></i> {{ __('menu.emp_info') }}
        </a><br>
        <a href="{{ route('hr.emp.order.view', [$user->id, $user->user_hash]) }}" class="btn btn-outline-info">
            <i class="ik ik-file-text"></i> {{ __('menu.emp_order') }}
        </a><br>
        <a href="{{ route('hr.emp.edu.view', [$user->id, $user->user_hash]) }}" class="btn btn-outline-info">
            <i class="ik ik-book-open"></i> {{ __('menu.emp_edu') }}
        </a><br>
        <a href="{{ route('hr.emp.fam.view', [$user->id, $user->user_hash]) }}" class="btn btn-outline-info">
            <i class="ik ik-heart-on"></i> {{ __('menu.emp_fam') }}
        </a><br>
        <a href="{{ route('hr.emp.exp.view', [$user->id, $user->user_hash]) }}" class="btn btn-outline-info">
            <i class="ik ik-list"></i> {{ __('menu.emp_exp') }}
        </a><br>
    </div>
</div>
