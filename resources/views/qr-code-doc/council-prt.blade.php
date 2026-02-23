<!DOCTYPE html>
<html>
@include('qr-code-doc.qr-head')

<body class="hold-transition skin-blue layout-top-nav" onselectstart="return false">

@include('qr-code-doc.qr-header')

<div class="wrapper">
    <div class="content-wrapper">
        <div class="container" style="margin-top: 7em">
            <div class="row">

                <div class="col-md-4">
                    <div class="card table-card">

                        <div class="card-header">
                            <h3>Hujjat haqida ma`lumot</h3>
                        </div>
                        <div class="card-body feeds-widget">
                            <div class="feeds-body">
                                <h6 class="title"></h6>
                            </div>
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left"><i class="ik ik-flag"></i></div>
                                    <div class="feeds-body">
                                        <h6 class="title text-blue">Hujjat turi:</h6>
                                        <p>{{ $document->cnType->p_name??'' }}</p>
                                    </div>
                                </a>
                            </div>
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left"><i class="ik ik-file-text"></i></div>
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
                                        <p>№ {{ $document->doc_number }}<br> {{ date('d.m.Y', strtotime($document->doc_date)) }} yil.</p>
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
                            @if($document->status == 5)
                                <input id="id" value="{{ $document->id }}" hidden>
                                <input id="type" value="cn_protocol" hidden>

                                @include('qr-code-doc.key-form')

                                @include('qr-code-doc.doc-contents.council')

                            @else
                                <h6 class="text-center text-danger">{{ __('field.doc_is_not_full_signed') }}</h6>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('qr-code-doc.key-js')
</body>
</html>
