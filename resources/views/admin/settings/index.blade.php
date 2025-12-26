@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.programm_settings') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-3">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('menu.programm_settings') }}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                @if(in_array('Super_Admin', $roles))
                    <div class="card">
                        <div class="card-body template-demo">
                            <div class="row">
                                <div class="col-lg-2 col-md-4 col-6">
                                    <div class="widget social-widget">
                                        <div class="widget-body">
                                            <div class="icon"><i class="ik ik-alert-triangle text-danger"></i></div>
                                            <div class="content">
                                                <div class="text">
                                                    <a href="{{ route('settings.log-file', ['key' => 'log']) }}" class="link-share"
                                                       target="_blank"
                                                       onclick="window.open('<?php echo ('/admin/settings-file/log'); ?>',
                                                           'modal',
                                                           'width=800,height=900,top=30,left=500');
                                                           return false;">
                                                        Log File</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <div class="widget social-widget">
                                        <div class="widget-body">
                                            <div class="icon"><i class="ik ik-refresh-ccw text-warning"></i></div>
                                            <div class="content">
                                                <div class="text"><a href="{{ route('settings.cache.clear', ['type' => 'route']) }}">Route Clear</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <div class="widget social-widget">
                                        <div class="widget-body">
                                            <div class="icon"><i class="ik ik-activity text-primary"></i></div>
                                            <div class="content">
                                                <div class="text"><a href="{{ route('settings.cache.clear', ['type' => 'config']) }}">Config Cache</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <div class="widget social-widget">
                                        <div class="widget-body">
                                            <div class="icon"><i class="ik ik-rotate-ccw text-info"></i></div>
                                            <div class="content">
                                                <div class="text"><a href="{{ route('settings.cache.clear', ['type' => 'clear']) }}">Clear Cache</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <div class="widget social-widget">
                                        <div class="widget-body">
                                            <div class="icon"><i class="ik ik-rotate-cw text-navy"></i></div>
                                            <div class="content">
                                                <div class="text"><a href="{{ route('settings.cache.clear', ['type' => 'view']) }}">View Cache</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <div class="widget social-widget">
                                        <div class="widget-body">
                                            <div class="icon"><i class="fab fa-behance text-behance"></i></div>
                                            <div class="content">
                                                <div class="text">Test</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-lg-2 col-md-4 col-6">
                                    <div class="widget social-widget">
                                        <div class="widget-body">
                                            <div class="icon"><i class="ik ik-help-circle text-navy"></i></div>
                                            <div class="content">
                                                <form action="{{ route('settings.set.demo') }}" method="post">
                                                    @csrf
                                                    <div class="col-sm-12 mt-30">
                                                        <p>
                                                            <input id="control" type="checkbox" name="is_demo" class="js-switch"
                                                                   @if($isDemo === true) checked @endif
                                                            />
                                                            <label for="control">Demo (ECP)</label>
                                                        </p>

                                                        <button type="submit" class="btn btn-primary mt-20">
                                                            <i class="ik ik-save"></i>{{ __('action.save') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="col-md-3">
                            <div class="d-inline">
                                <h5><i class="ik ik-settings"></i> {{ __('title.images') }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="card-body template-demo">
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <h6>{{ __('field.background_image') }}</h6>
                                <div>
                                    <img style="width: 200px; height: 120px" src="{{ asset($backgroundImage)  }}" alt="background_image">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <h6>{{ __('field.logo') }}</h6>
                                <div class="logo-centered">
                                    <img style="width: 100px; height: 100px" src="{{ $logoImage }}" alt="logo">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <h6>{{ __('field.favicon') }}</h6>
                                <div class="logo-centered">
                                    <img style="width: 100px; height: 100px" src="{{ $favicon }}" alt="logo">
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <h6>{{ __('field.out_doc_banner') }}</h6>
                                <div class="logo-centered">
                                    <img style="width: 100%; height: 80px" src="{{ $sentDocImage }}" alt="sent_doc_image">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="mb-3 col-md-3">
                                            <label for="background_image" class="form-label">{{ __('field.background_image') }}</label>
                                            <input type="file" class="form-control" id="background_image" name="background_image" accept="image/*">
                                        </div>
                                        <div class="mb-3 col-md-3">
                                            <label for="logo_image" class="form-label">{{ __('field.logo') }}</label>
                                            <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/*">
                                        </div>
                                        <div class="mb-3 col-md-3">
                                            <label for="favicon" class="form-label">{{ __('field.favicon') }}</label>
                                            <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                        </div>
                                        <div class="mb-3 col-md-3">
                                            <label for="sent_doc_image" class="form-label">{{ __('field.out_doc_banner') }}</label>
                                            <input type="file" class="form-control" id="sent_doc_image" name="sent_doc_image" accept="image/*">
                                        </div>
                                    </div>

                                    <div class="d-flex flex-row justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ik ik-save"></i>{{ __('action.save') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header">
                        <div class="col-md-3">
                            <div class="d-inline">
                                <h5><i class="ik ik-settings"></i> {{ __('menu.config') }} (.ENV)</h5>
                            </div>
                        </div>
                    </div>

                    <div class="card-body template-demo">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{ route('settings.set.env') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label for="bot_token" class="form-label">TELEGRAM_BOT_TOKEN</label>
                                            <input type="text" class="form-control" id="bot_token" name="bot_token" value="{{ $telegramBotToken??'' }}">
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label for="webhook_url" class="form-label">WEBHOOK_URL</label>
                                            <input type="text" class="form-control" id="webhook_url" name="webhook_url" value="{{ $telegramWebHookUrl??'' }}">
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label for="app_url" class="form-label">APP_URL</label>
                                            <input type="text" class="form-control" id="app_url" name="app_url" value="{{ $appUrl??'' }}">
                                        </div>
                                    </div>

                                    <div class="d-flex flex-row justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ik ik-save"></i>{{ __('action.save') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

