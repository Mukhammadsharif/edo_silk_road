<div class="page-header">
    <div class="row align-items-end">
        <div class="col-md-2">
            <div class="page-header-title">
                <i class="ik ik-file-text bg-blue"></i>
                <div class="d-inline">
                    <h5>{{ __('title.working_documents') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="w-80">
                @include('templates.alert')
            </div>
        </div>
        <div class="col-md-3">
            <nav class="breadcrumb-container" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('office.work-docs') }}">{{ __('title.working_documents') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('action.view') }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
