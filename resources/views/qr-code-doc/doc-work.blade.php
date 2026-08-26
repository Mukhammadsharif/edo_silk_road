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
                                    <div class="fs-qr-wrap">
                                        @include('execution.confirm.templates.free-sign-page')
                                    </div>
                                    <style>
                                        /* Публичная QR-страница: viewer во всю карточку, опрятно */
                                        .fs-qr-wrap { margin: 0 -5px; }
                                        .fs-qr-wrap .fs-pdf-viewer { width: 100%; overflow-x: auto;
                                            -webkit-overflow-scrolling: touch; }
                                        .fs-qr-wrap .fs-pdf-viewer canvas { max-width: 100%; height: auto; }
                                        .fs-qr-wrap .fs-pdf-toolbar { flex-wrap: wrap; gap: 6px; }

                                        /* Мобилка: колонки в столбик уже складывает bootstrap (col-md-*),
                                           подтягиваем то, что он не решает */
                                        @media (max-width: 768px) {
                                            .container[style*="margin-top: 7em"] { margin-top: 4.5em !important; }
                                            .fs-qr-wrap { margin: 0 -12px; }
                                            .fs-qr-wrap .fs-pdf-toolbar { justify-content: center; }
                                            .fs-qr-wrap .fs-pdf-ver { width: 100%; text-align: center; }
                                            .card.table-card .card-header { display: none; } /* пустая шапка */
                                            .card.table-card .card-body { padding: 10px; }
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
