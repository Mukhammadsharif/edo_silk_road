<div class="page-header-modern">
    <div class="header-content">
        <div class="header-left">
            <div class="header-icon">
                <i data-lucide="briefcase"></i>
            </div>
            <div class="header-text">
                <h1>{{ __('title.working_documents') }}</h1>
                <p>{{ __('message.view_work_document') ?? 'Просмотр рабочего документа' }}</p>
            </div>
        </div>
        <div class="header-center">
            @include('templates.alert')
        </div>
        <div class="header-right">
            <nav class="breadcrumb-modern">
                <a href="{{ route('home') }}" class="breadcrumb-item">
                    <i data-lucide="home"></i>
                    <span>{{ __('menu.home') ?? 'Главная' }}</span>
                </a>
                <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                <a href="{{ route('office.work-docs') }}" class="breadcrumb-item">
                    <span>{{ __('title.working_documents') }}</span>
                </a>
                <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                <span class="breadcrumb-item active">{{ __('action.view') }}</span>
            </nav>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header.css') }}">
