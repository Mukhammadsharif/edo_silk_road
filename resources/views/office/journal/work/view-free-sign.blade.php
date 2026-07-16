@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">

{{--
    ═══════════════════════════════════════════════════════════════════════
    FREE-SIGN: штатная вьюшка документа, но вместо HTML-текста — PDF-viewer.

    Отличие от work/view.blade.php ровно одно: блок #printPage заменён на
    pdf.js-вьювер, который грузит ПОСЛЕДНЮЮ версию PDF (со всеми штампами
    подписей). Вся остальная логика — шапка, левая панель, правая панель
    (подписи, резолюция, исполнение), e-imzo, печать, QR — как в оригинале.

    Требует из контроллера: $documentFile (последняя версия PDF).
    ═══════════════════════════════════════════════════════════════════════
--}}

<style>
    /* PDF-viewer вместо текста документа */
    .fs-pdf-viewer {
        background: #f1f2f6;
        border-radius: 8px;
        padding: 16px;
        min-height: 500px;
        max-height: calc(100vh - 220px);
        overflow-y: auto;
    }

    body.theme-dark .fs-pdf-viewer,
    body.theme.dark .fs-pdf-viewer {
        background: #0f1117;
    }

    .fs-pdf-page {
        position: relative;
        margin: 0 auto 18px auto;
        background: #fff;
        box-shadow: 0 2px 14px rgba(15, 23, 42, 0.12);
        width: max-content;
        max-width: 100%;
    }

    .fs-pdf-page canvas {
        display: block;
        max-width: 100%;
        height: auto;
    }

    .fs-pdf-badge {
        position: absolute;
        top: -10px;
        left: 8px;
        background: #794DFD;
        color: #fff;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 6px;
        z-index: 2;
    }

    .fs-pdf-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .fs-pdf-btn {
        border: 1px solid rgba(15, 23, 42, 0.10);
        background: #fff;
        border-radius: 6px;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 12.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .fs-pdf-btn:hover {
        border-color: #794DFD;
        color: #794DFD;
    }

    .fs-pdf-zoom {
        font-size: 12.5px;
        color: #94a3b8;
        min-width: 46px;
        text-align: center;
    }

    .fs-pdf-loading {
        text-align: center;
        padding: 40px;
        color: #94a3b8;
        font-size: 14px;
    }

    .fs-pdf-ver {
        margin-left: auto;
        font-size: 11.5px;
        color: #94a3b8;
    }

    @media screen and (max-width: 1030px) {
        .work-doc-page { margin-left: 0 !important; }
    }
    @media screen and (max-width: 1024px) {
        .work-doc-page { margin-left: 0 !important; }
    }
</style>

@section('content')
    <div class="work-view-page-modern">
        @include('office.journal.work.templates.header')

        <div class="document-view-layout">
            <!-- Left Sidebar -->
            <div class="view-sidebar view-sidebar-left">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-left')
                </div>
            </div>

            <!-- Document Content: PDF вместо HTML -->
            <div class="document-content-section" id="pdf1">
                <div class="work-doc-page" id="pdf2">
                    <div class="work-doc-subpage" id="pdf3">

                        @if($journal->docStatus->doc_code !== 'OPEN')
                            <div class="doc-confidentiality">
                                {{ $journal->docStatus->lang($journal->docStatus->lang_id) ?? '-' }}
                            </div>
                        @endif

                        <div class="fs-pdf-toolbar">
                            <button type="button" class="fs-pdf-btn" id="fsZoomOut">−</button>
                            <span class="fs-pdf-zoom" id="fsZoomVal">100%</span>
                            <button type="button" class="fs-pdf-btn" id="fsZoomIn">+</button>

                            @isset($documentFile)
                                <a href="{{ route('file.pre-view', $documentFile->id) }}" target="_blank" class="fs-pdf-btn">
                                    <i data-lucide="external-link" style="width:14px;height:14px;"></i>
                                    {{ __('action.view') }}
                                </a>
                            @endisset

                            {{-- Версия: исходник или N-я подпись --}}
                            @isset($pdfVersion)
                                <span class="fs-pdf-ver">
                                    @if($pdfVersion === 0)
                                        Оригинал (без подписей)
                                    @else
                                        Версия {{ $pdfVersion }} · подписей: {{ $pdfVersion }}
                                    @endif
                                </span>
                            @endisset
                        </div>

                        <div class="fs-pdf-viewer" id="fsPdfViewer">
                            <div class="fs-pdf-loading" id="fsPdfLoading">{{ __('message.loading') ?? 'Загрузка документа…' }}</div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="view-sidebar view-sidebar-right">
                <div class="sidebar-card-modern">
                    @include('office.journal.view-right')
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    @include('modals.delete-document')

    @include('office.journal.view-js')

    @include('print.print')

    @include('qr-code-doc.qr-code-view-js')

    @include('print.iframe-print')

    @include('e-imzo.e-imzo')

    {{-- pdf.js для рендера документа --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.openConfirmForm').on('click', function () {
                $('.confirmForm').show();
                let btn_id = $(this).data('id');
                let confirm_type = $(this).html();
                let modal_bg_class = "btn-success";
                if (btn_id === -5) {
                    confirm_type = "{{ __('cancel') }}";
                    modal_bg_class = "btn-outline-warning";
                } else if (btn_id === -6) {
                    confirm_type = "{{ __('re-editing') }}";
                    modal_bg_class = "btn-outline-warning";
                }
                $('.modal-bg-class').removeClass().addClass(`btn-modern modal-bg-class ${modal_bg_class}`);
                $('.confirm_type').html(confirm_type);
                $('#ecp_confirm_id').val();
            });
        });

        /* ═══════════ PDF-VIEWER: последняя версия документа ═══════════
           Грузит PDF через preView (диск ftp_13). Контроллер отдаёт
           ПОСЛЕДНЮЮ запись document_files — то есть со всеми штампами
           уже подписавших участников. */
        (function () {
            @isset($documentFile)
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            var PDF_URL = @json(route('file.pre-view', $documentFile->id));
            var viewer  = document.getElementById('fsPdfViewer');
            var loading = document.getElementById('fsPdfLoading');
            var pdfDoc  = null;
            var scale   = 1.0;

            async function render() {
                if (!pdfDoc) return;

                // Чистим страницы, оставляя индикатор.
                viewer.querySelectorAll('.fs-pdf-page').forEach(function (n) { n.remove(); });

                for (var p = 1; p <= pdfDoc.numPages; p++) {
                    var page = await pdfDoc.getPage(p);
                    var vp = page.getViewport({scale: scale * 1.4});

                    var wrap = document.createElement('div');
                    wrap.className = 'fs-pdf-page';

                    var badge = document.createElement('div');
                    badge.className = 'fs-pdf-badge';
                    badge.textContent = 'Стр. ' + p;
                    wrap.appendChild(badge);

                    var canvas = document.createElement('canvas');
                    canvas.width = vp.width;
                    canvas.height = vp.height;
                    wrap.appendChild(canvas);
                    viewer.appendChild(wrap);

                    await page.render({canvasContext: canvas.getContext('2d'), viewport: vp}).promise;
                }
            }

            async function load() {
                try {
                    // cache-buster: после новой подписи PDF меняется,
                    // без этого браузер отдаст старую версию из кэша.
                    var url = PDF_URL + (PDF_URL.indexOf('?') > -1 ? '&' : '?') + '_ts=' + Date.now();
                    var resp = await fetch(url, {credentials: 'same-origin'});
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);

                    var buf = await resp.arrayBuffer();
                    pdfDoc = await pdfjsLib.getDocument({data: buf}).promise;

                    if (loading) loading.style.display = 'none';
                    await render();
                } catch (e) {
                    console.error('PDF load error:', e);
                    if (loading) loading.textContent = 'Не удалось загрузить документ';
                }
            }

            function setZoom(v) {
                scale = Math.max(0.5, Math.min(2.5, v));
                document.getElementById('fsZoomVal').textContent = Math.round(scale * 100) + '%';
                render();
            }

            var zi = document.getElementById('fsZoomIn');
            var zo = document.getElementById('fsZoomOut');
            if (zi) zi.onclick = function () { setZoom(scale + 0.15); };
            if (zo) zo.onclick = function () { setZoom(scale - 0.15); };

            load();
            @else
            var l = document.getElementById('fsPdfLoading');
            if (l) l.textContent = 'К документу не приложен PDF-файл';
            @endisset
        })();
    </script>

@endsection
