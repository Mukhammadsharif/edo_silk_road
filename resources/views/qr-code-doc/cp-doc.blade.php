<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UzMRC - edo.uzmrc.uz</title>
    <link rel="icon" href="{{ $favicon }}" type="image/x-icon"/>
    <link href="{{ asset('/assets/fonts.css?family=Nunito+Sans:300,400,600,700,800') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/dist/css/main.css') }}">

    <link rel="stylesheet" href="{{ asset('/assets/node_modules/icon-kit/dist/css/iconkit.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/node_modules/ionicons/dist/css/ionicons.min.css') }}">
</head>

<body class="hold-transition skin-blue layout-top-nav" onmousedown="return false" onselectstart="return false">
<div class="wrapper">
    <header class="main-header bg-green">
        <nav class="navbar navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <a href="#" class="navbar-brand"><b>UzMRC</b></a>
                </div>
            </div>
        </nav>
    </header>

    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="card table-card">

                        <div class="card-header">
                            <h3>Hujjat haqida ma`lumot</h3>
                        </div>
                        <div class="card-body feeds-widget">
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left"><i class="ik ik-mail"></i></div>
                                    <div class="feeds-body">
                                        <h6 class="title text-blue">Hujjat nomi:</h6>
                                        <p>{{ $document->doc_name }}</p>
                                    </div>
                                </a>
                            </div>
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left"><i data-lucide="clock"></i></div>
                                    <div class="feeds-body">
                                        <h6 class="title text-blue">Hujjat raqami/sanasi:</h6>
                                        <p>№ {{ $document->doc_number }}<br> {{ date('d.m.Y', strtotime($document->doc_date)) }}</p>
                                    </div>
                                </a>
                            </div>
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left"><i class="ik ik-user"></i></div>
                                    <div class="feeds-body">
                                        <h6 class="title text-blue">Kim tomonidan imzolangan:</h6>
                                        <p>
                                            {{ mb_substr($user->personal->f_name??'', 0,1,) }}.
                                            {{ mb_substr($user->personal->m_name??'', 0,1,) }}.
                                            {{ $user->personal->l_name??'' }}
                                        </p>
                                    </div>
                                </a>
                            </div>
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left"><i class="ik ik-feather"></i></div>
                                    <div class="feeds-body">
                                        <h6 class="title text-blue">Imzolangan sana:</h6>
                                        <p>
                                            @if($confirmData)
                                                {{ date('d.m.Y H:i', strtotime($confirmData->created_at)) }}
                                            @else
                                                {{ date('d.m.Y H:i', strtotime($document->created_at)) }}
                                            @endif
                                        </p>
                                    </div>
                                </a>
                            </div>
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left"><i class="ik ik-user"></i></div>
                                    <div class="feeds-body">
                                        <h6 class="title text-blue">Hujjat ijrochisi:</h6>
                                        <p>
                                            {{ mb_substr($document->createdId->personal->f_name??'', 0,1,) }}.
                                            {{ mb_substr($document->createdId->personal->m_name??'', 0,1,) }}.
                                            {{ $document->createdId->personal->l_name??'' }}
                                        </p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 offset-0">
                    <div class="card table-card" style="max-width: 794px;margin: 0 auto">

                        <div class="card-header">
                            <h3 class="text-center"></h3>
                        </div>

                        <div class="card-body">
                            @if(1 === 2)
                            {!! $document->docText->doc_text??'*' !!}
                            <div class="p-4 mt-15">
                                <div class="row text-center">
                                    <div class="table-responsive">
                                        <table class="mb-10 without-header">

                                            @forelse($memberTypes as $type)
                                                <h6 class="text-left fw-700"> {{ __($type->memberRoleName($type->role_id)) }}:</h6>
                                                @foreach($members as $member)
                                                    @if($member->role_id === $type->role_id)
                                                        <table class="mb-10 without-header" style="width: 100%">
                                                            <tbody>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <small
                                                                        class="text-muted d-block">{{ $member->userWork->userPost->post_name_ru??'*' }}</small>
                                                                </td>
                                                                <td style="width: 30%">
                                                                    {!! $member->qrCode($document->id, $member->user_id) !!}
                                                                </td>
                                                                <td style="width: 30%">
                                                                    <b>
                                                                        {{ mb_substr($member->userWork->personal->f_name??'', 0,1,) }}
                                                                        .{{ $member->userWork->personal->l_name??'' }}
                                                                    </b><br>
                                                                    <span class="badge {{ $member->cpMemStatus->bg_class??'' }}">
                                                                    <i class="{{ $member->cpMemStatus->icon_code??'' }}"></i>
                                                                    @php $sts = $member->cpMemStatus->lang_code??'*'; @endphp
                                                                        {{ __($sts) }}
                                                                </span>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                @endforeach
                                            @empty
                                                {{ __('not_found') }}
                                            @endforelse
                                        </table>
                                    </div>

                                    <div class="col-4 mt-35">
                                        <h6 class="fw-600 mb-0">{{ __('performer') }}:<br>
                                            {{ mb_substr($document->createdId->personal->f_name??'', 0,1,) }}
                                            .{{ $document->createdId->personal->l_name??'' }}
                                        </h6><br>
                                        <small>{{ __('tel_number') }}</small>
                                    </div>
                                </div>
                            </div>
                            @else
                                <p>-</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
