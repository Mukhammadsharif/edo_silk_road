@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/settings/settings-modern.css') }}" rel="stylesheet">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="settings"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.programm_settings') }}</h1>
                        <p>{{ __('menu.programm_settings') }}</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.programm_settings') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            @if(in_array('Super_Admin', $roles))
                <!-- System Management Card -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="server"></i>
                            <span>{{ __('field.system_management') }}</span>
                        </div>
                    </div>

                    <div class="settings-grid-container">
                        <!-- Log File -->
                        <a href="{{ route('settings.log-file', ['key' => 'log']) }}"
                           target="_blank"
                           onclick="window.open('<?php echo ('/admin/settings-file/log'); ?>', 'modal', 'width=800,height=900,top=30,left=500'); return false;"
                           class="settings-card-modern danger">
                            <div class="settings-icon">
                                <i data-lucide="alert-triangle"></i>
                            </div>
                            <div class="settings-content">
                                <h4>Log File</h4>
                                <p>View system logs</p>
                            </div>
                            <div class="settings-arrow">
                                <i data-lucide="external-link"></i>
                            </div>
                        </a>

                        <!-- Route Clear -->
                        <a href="{{ route('settings.cache.clear', ['type' => 'route']) }}" class="settings-card-modern warning">
                            <div class="settings-icon">
                                <i data-lucide="refresh-ccw"></i>
                            </div>
                            <div class="settings-content">
                                <h4>Route Clear</h4>
                                <p>Clear route cache</p>
                            </div>
                            <div class="settings-arrow">
                                <i data-lucide="chevron-right"></i>
                            </div>
                        </a>

                        <!-- Config Cache -->
                        <a href="{{ route('settings.cache.clear', ['type' => 'config']) }}" class="settings-card-modern primary">
                            <div class="settings-icon">
                                <i data-lucide="activity"></i>
                            </div>
                            <div class="settings-content">
                                <h4>Config Cache</h4>
                                <p>Clear config cache</p>
                            </div>
                            <div class="settings-arrow">
                                <i data-lucide="chevron-right"></i>
                            </div>
                        </a>

                        <!-- Clear Cache -->
                        <a href="{{ route('settings.cache.clear', ['type' => 'clear']) }}" class="settings-card-modern info">
                            <div class="settings-icon">
                                <i data-lucide="rotate-ccw"></i>
                            </div>
                            <div class="settings-content">
                                <h4>Clear Cache</h4>
                                <p>Clear all cache</p>
                            </div>
                            <div class="settings-arrow">
                                <i data-lucide="chevron-right"></i>
                            </div>
                        </a>

                        <!-- View Cache -->
                        <a href="{{ route('settings.cache.clear', ['type' => 'view']) }}" class="settings-card-modern navy">
                            <div class="settings-icon">
                                <i data-lucide="rotate-cw"></i>
                            </div>
                            <div class="settings-content">
                                <h4>View Cache</h4>
                                <p>Clear view cache</p>
                            </div>
                            <div class="settings-arrow">
                                <i data-lucide="chevron-right"></i>
                            </div>
                        </a>

                        <!-- Demo Mode Setting -->
                        <div class="settings-card-modern demo">
                            <div class="settings-icon">
                                <i data-lucide="help-circle"></i>
                            </div>
                            <div class="settings-content">
                                <h4>Demo Mode (ECP)</h4>
                                <form action="{{ route('settings.set.demo') }}" method="post" class="demo-form">
                                    @csrf
                                    <div class="demo-toggle">
                                        <input id="control"
                                               type="checkbox"
                                               name="is_demo"
                                               class="js-switch"
                                               @if($isDemo === true) checked @endif>
                                        <label for="control">Enable Demo Mode</label>
                                    </div>
                                    <button type="submit" class="btn-modern btn-primary" style="width: auto; margin-top: 1rem;">
                                        <i data-lucide="save"></i>
                                        {{ __('action.save') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Images Settings Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="image"></i>
                        <span>{{ __('title.images') }}</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <!-- Current Images Preview -->
                    <div class="images-preview-grid">
                        <div class="image-preview-card">
                            <h6>{{ __('field.background_image') }}</h6>
                            <div class="image-wrapper">
                                <img src="{{ asset($backgroundImage) }}" alt="background_image">
                            </div>
                        </div>

                        <div class="image-preview-card">
                            <h6>{{ __('field.logo') }}</h6>
                            <div class="image-wrapper logo">
                                <img src="{{ $logoImage }}" alt="logo">
                            </div>
                        </div>

                        <div class="image-preview-card">
                            <h6>{{ __('field.favicon') }}</h6>
                            <div class="image-wrapper logo">
                                <img src="{{ $favicon }}" alt="favicon">
                            </div>
                        </div>

                        <div class="image-preview-card">
                            <h6>{{ __('field.out_doc_banner') }}</h6>
                            <div class="image-wrapper banner">
                                <img src="{{ $sentDocImage }}" alt="sent_doc_image">
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data" class="modern-form" style="margin-top: 2rem;">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="image"></i>
                                    {{ __('field.background_image') }}
                                </label>
                                <input type="file"
                                       class="form-control-modern file-input"
                                       id="background_image"
                                       name="background_image"
                                       accept="image/*">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="image"></i>
                                    {{ __('field.logo') }}
                                </label>
                                <input type="file"
                                       class="form-control-modern file-input"
                                       id="logo_image"
                                       name="logo_image"
                                       accept="image/*">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="image"></i>
                                    {{ __('field.favicon') }}
                                </label>
                                <input type="file"
                                       class="form-control-modern file-input"
                                       id="favicon"
                                       name="favicon"
                                       accept="image/*">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="image"></i>
                                    {{ __('field.out_doc_banner') }}
                                </label>
                                <input type="file"
                                       class="form-control-modern file-input"
                                       id="sent_doc_image"
                                       name="sent_doc_image"
                                       accept="image/*">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Environment Configuration Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="terminal"></i>
                        <span>{{ __('menu.config') }} (.ENV)</span>
                    </div>
                </div>

                <div style="padding: 2rem;">
                    <form action="{{ route('settings.set.env') }}" method="POST" class="modern-form">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="message-circle"></i>
                                    TELEGRAM_BOT_TOKEN
                                </label>
                                <input type="text"
                                       class="form-control-modern"
                                       id="bot_token"
                                       name="bot_token"
                                       value="{{ $telegramBotToken ?? '' }}"
                                       placeholder="Enter telegram bot token">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="link"></i>
                                    WEBHOOK_URL
                                </label>
                                <input type="text"
                                       class="form-control-modern"
                                       id="webhook_url"
                                       name="webhook_url"
                                       value="{{ $telegramWebHookUrl ?? '' }}"
                                       placeholder="Enter webhook URL">
                            </div>

                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="globe"></i>
                                    APP_URL
                                </label>
                                <input type="text"
                                       class="form-control-modern"
                                       id="app_url"
                                       name="app_url"
                                       value="{{ $appUrl ?? '' }}"
                                       placeholder="Enter application URL">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
@endsection
