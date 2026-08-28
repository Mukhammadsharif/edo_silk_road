<!DOCTYPE html>
<html>
@include('qr-code-doc.qr-head')

<body class="hold-transition skin-blue layout-top-nav" onselectstart="return false">

@include('qr-code-doc.qr-header')

<div class="wrapper">
    <div class="content-wrapper">
        <div class="container" style="margin-top: 7em">
            <div class="row">

                @include('qr-code-doc.qr-doc-left')

                <div class="col-md-8 offset-0">
                    <div class="card table-card" style="max-width: 794px;margin: 0 auto">

                        <div class="card-header">
                            <h3 class="text-center"></h3>
                        </div>

                        <div class="card-body">
                            @if(in_array($journal->status, [9, 10, 11]))
                                <input id="id" value="{{ $document->id }}" hidden>
                                <input id="type" value="work_doc" hidden>

                                @include('qr-code-doc.key-form')

                                {{-- v69: free-sign — PDF viewer со штампами вместо HTML-шаблона.
                                     v69.2: обёртка + адаптив под мобилку. --}}
                                @if(!empty($isFreeSignDoc) && $isFreeSignDoc)
                                    {{-- v69.3: id="doc_content" — ТА ЖЕ обёртка, что у штатного
                                         work-doc: key-js прячет её до ввода верного кода и
                                         показывает после (content.hide()/show()). PDF при этом
                                         успевает отрендериться в фоне — canvas не зависит от
                                         layout скрытого контейнера. --}}
                                    <div id="doc_content" class="fs-qr-wrap work-doc-page">
                                        @include('execution.confirm.templates.free-sign-page')
                                    </div>
                                    <style>
                                        /* ═══ v70: полный адаптив публичной QR-страницы (fs) ═══ */

                                        /* Базовое: fs-блок как штатная А4-страница */
                                        .fs-qr-wrap.work-doc-page { width: 17cm; max-width: 100%;
                                            margin: 0 auto; padding: 0; box-shadow: none; }
                                        .fs-qr-wrap .fs-pdf-viewer { width: 100%; max-width: 100%;
                                            overflow-x: auto; -webkit-overflow-scrolling: touch; }
                                        .fs-qr-wrap .fs-pdf-page { max-width: none; margin: 0 auto; }
                                        .fs-qr-wrap .fs-pdf-toolbar { flex-wrap: wrap; gap: 6px; }
                                        .card.table-card { width: 100%; max-width: 100% !important;
                                            margin: 0 !important; overflow: visible; }

                                        /* ═══ Планшет и телефон ═══ */
                                        @media (max-width: 768px) {
                                            body { overflow-x: hidden; }   /* страховка от любых вылетов */

                                            /* Отступ под фикс-шапку меньше — экран дорог */
                                            .container[style*="margin-top: 7em"] { margin-top: 4em !important;
                                                padding-left: 10px; padding-right: 10px; }

                                            /* ДОКУМЕНТ ПЕРВЫМ, инфо-карточка под ним:
                                               ради документа QR и сканируют */
                                            .content-wrapper .row > .col-md-8 { order: -1; }
                                            .content-wrapper .row > .col-md-4 { margin-top: 14px; }

                                            /* Верхняя тёмная полоса: компактнее */
                                            .accessible .socials { margin-right: 0 !important;
                                                display: flex; align-items: center; justify-content: center;
                                                gap: 6px; flex-wrap: wrap; padding: 4px 8px; }
                                            .accessible .eri-top-class { font-size: 12px; }
                                            .accessible svg { width: 18px; height: 18px; }

                                            /* Карточки: компактные поля.
                                               Пустой header прячем ТОЛЬКО у правой (контентной)
                                               карточки — заголовок левой «Hujjat haqida
                                               ma'lumot» остаётся. */
                                            .col-md-8 .card.table-card > .card-header { display: none; }
                                            .col-md-4 .card.table-card > .card-header { padding: 10px 12px; }
                                            .col-md-4 .card.table-card > .card-header h3 { font-size: 15px; margin: 0; }
                                            .card.table-card .card-body { padding: 10px; }
                                            .card.table-card .feed-item { padding: 6px 0; }
                                            .card.table-card .feeds-body p { margin-bottom: 4px;
                                                font-size: 13px; word-break: break-word; }
                                            .card.table-card .feeds-body h6.title { font-size: 12.5px; }

                                            /* Форма кода: инпут и кнопка во всю ширину, крупные для пальца */
                                            #key_form h4 { font-size: 16px; }
                                            #key_form .doc_number, #key_form .col-sm-6 { max-width: 100%;
                                                flex: 0 0 100%; }
                                            #key_form input#key { text-align: center; font-size: 18px;
                                                letter-spacing: 6px; height: 46px !important; }
                                            #key_form #submitBtn { height: 46px !important; }

                                            /* Viewer: тулбар по центру, выше рабочая область */
                                            .fs-qr-wrap .fs-pdf-toolbar { justify-content: center; }
                                            .fs-qr-wrap .fs-pdf-ver { width: 100%; text-align: center; }
                                            .fs-qr-wrap .fs-pdf-viewer { max-height: calc(100vh - 130px);
                                                min-height: 300px; padding: 8px; }
                                        }

                                        /* ═══ Совсем узкие (телефон вертикально) ═══ */
                                        @media (max-width: 420px) {
                                            .container[style*="margin-top: 7em"] { margin-top: 3.4em !important;
                                                padding-left: 6px; padding-right: 6px; }
                                            .accessible .eri-top-class { font-size: 10.5px; }
                                            #header-section img { height: 40px !important; width: 40px !important; }
                                            .fs-qr-wrap .fs-pdf-btn { padding: 6px 10px; font-size: 13px; }
                                            .fs-qr-wrap .fs-pdf-viewer { padding: 4px; border-radius: 6px; }
                                        }
                                    </style>
                                @else
                                    @include('qr-code-doc.doc-contents.work-doc')
                                @endif

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
