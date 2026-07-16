{{--
    ═══════════════════════════════════════════════════════════════════════
    FREE-SIGN: PDF-viewer вместо HTML-текста документа.

    Подключается из всех execution-вьюшек (sign / agreement / confirm /
    executor / doc-control) первым условием — вместо *-page шаблонов.

    Грузит ПОСЛЕДНЮЮ версию PDF со штампами уже подписавших:
    каждая подпись создаёт новую запись document_files (*_signed_vN.pdf).

    Требует: $documentFile (последняя версия), $pdfVersion (номер, опц.)
    ═══════════════════════════════════════════════════════════════════════
--}}

<style>
    .fs-pdf-viewer {
        background: #f1f2f6;
        border-radius: 8px;
        padding: 16px;
        min-height: 480px;
        max-height: calc(100vh - 210px);
        overflow-y: auto;
    }
    body.theme-dark .fs-pdf-viewer,
    body.theme.dark .fs-pdf-viewer { background: #0f1117; }

    /* ВНИМАНИЕ: НЕ добавлять container-type: inline-size —
       он создаёт containment-контекст, canvas схлопывается,
       страница рендерится пустой (только бейдж «Стр. N»). */
    .fs-pdf-page {
        position: relative;
        margin: 0 auto 18px auto;
        background: #fff;
        box-shadow: 0 2px 14px rgba(15, 23, 42, .12);
        width: max-content;
        max-width: 100%;
    }
    .fs-pdf-page canvas {
        display: block;
        max-width: 100%;
        height: auto;
    }

    .fs-pdf-badge {
        position: absolute; top: -10px; left: 8px;
        background: #794DFD; color: #fff; font-size: 10px;
        padding: 2px 8px; border-radius: 6px; z-index: 2;
    }
    .fs-pdf-toolbar {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 12px; flex-wrap: wrap;
    }
    .fs-pdf-btn {
        border: 1px solid rgba(15,23,42,.10); background: #fff;
        border-radius: 6px; padding: 5px 10px; cursor: pointer;
        font-size: 12.5px; display: inline-flex; align-items: center; gap: 5px;
        text-decoration: none; color: inherit;
    }
    .fs-pdf-btn:hover { border-color: #794DFD; color: #794DFD; }
    .fs-pdf-zoom { font-size: 12.5px; color: #94a3b8; min-width: 46px; text-align: center; }
    .fs-pdf-loading { text-align: center; padding: 40px; color: #94a3b8; font-size: 14px; }

    /* ── Метки-призраки: где БУДЕТ подпись ── */
    .fs-ghost {
        position: absolute;
        border: 1px dashed;
        border-radius: 4px;
        opacity: .6;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        line-height: 1.1;
        text-align: center;
        color: #475569;
        pointer-events: none;
        overflow: hidden;
        padding: 1px;
    }
    .fs-ghost small { display: block; font-size: 8.5px; opacity: .85; }
    .fs-ghost-legend {
        display: flex; align-items: center; gap: 5px;
        font-size: 11.5px; color: #94a3b8;
    }
    .fs-ghost-dot {
        width: 9px; height: 9px; border-radius: 50%;
        border: 1px dashed #94a3b8; flex-shrink: 0;
    }
    .fs-pdf-ver { margin-left: auto; font-size: 11.5px; color: #94a3b8; }
</style>

<div class="work-doc-page" id="pdf2">
    <div class="work-doc-subpage" id="pdf3">

        @if(isset($journal->docStatus) && $journal->docStatus->doc_code !== 'OPEN')
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

            @if(!empty($fsGhosts) && $fsGhosts->count())
                <span class="fs-ghost-legend">
                    <span class="fs-ghost-dot"></span>
                    ожидается подписей: {{ $fsGhosts->count() }}
                </span>
            @endif

            @isset($pdfVersion)
                <span class="fs-pdf-ver">
                    @if($pdfVersion <= 0)
                        Оригинал (без подписей)
                    @else
                        Версия {{ $pdfVersion }} · подписей: {{ $pdfVersion }}
                    @endif
                </span>
            @endisset
        </div>

        <div class="fs-pdf-viewer" id="fsPdfViewer">
            <div class="fs-pdf-loading" id="fsPdfLoading">Загрузка документа…</div>
        </div>

    </div>
</div>

{{-- pdf.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script type="text/javascript">
    (function () {
        @isset($documentFile)

        // ── ДИАГНОСТИКА: смотри консоль (F12), если PDF не появился ──
        console.log('[free-sign] PDF-viewer init');

        // pdf.js мог ещё не загрузиться (скрипт выше грузится асинхронно
        // при медленной сети / кэше). Ждём, иначе pdfjsLib is not defined.
        function whenPdfJsReady(cb, tries) {
            tries = tries || 0;
            if (typeof pdfjsLib !== 'undefined') { cb(); return; }
            if (tries > 60) {   // ~6 секунд
                console.error('[free-sign] pdf.js не загрузился — проверь CDN/сеть');
                var l = document.getElementById('fsPdfLoading');
                if (l) l.textContent = 'Не удалось загрузить библиотеку просмотра PDF';
                return;
            }
            setTimeout(function () { whenPdfJsReady(cb, tries + 1); }, 100);
        }

        var PDF_URL = @json(route('file.pre-view', $documentFile->id));
        var viewer  = document.getElementById('fsPdfViewer');
        var loading = document.getElementById('fsPdfLoading');
        var pdfDoc  = null;
        var scale   = 1.0;

        // Метки-призраки: где БУДЕТ подпись. Только те, кто ещё не подписал —
        // у подписавших реальный штамп уже впечатан в PDF.
        // Координаты нормализованные (0..1) → умножаем на размер канваса.
        var GHOSTS = @json($fsGhosts ?? []);

        function drawGhosts(wrap, pageNo) {
            GHOSTS.filter(function (g) { return g.page === pageNo; })
                  .forEach(function (g) {
                var el = document.createElement('div');
                el.className = 'fs-ghost';
                // ВАЖНО: позиционируем в ПРОЦЕНТАХ, а не в px.
                // canvas сжимается через max-width:100%, поэтому px-координаты
                // от vp.width съезжают. Проценты масштабируются вместе с canvas.
                el.style.left        = (g.x * 100) + '%';
                el.style.top         = (g.y * 100) + '%';
                el.style.width       = (g.w * 100) + '%';
                el.style.height      = (g.h * 100) + '%';
                el.style.borderColor = g.color;
                el.style.background  = g.color + '18';
                el.innerHTML = '⏳ ' + g.label +
                               (g.signer ? '<small>' + g.signer + '</small>' : '');
                el.title = 'Здесь будет: ' + g.label + (g.signer ? ' — ' + g.signer : '');
                wrap.appendChild(el);
            });
        }

        async function render() {
            if (!pdfDoc) return;
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

                // Призраки поверх отрендеренной страницы.
                drawGhosts(wrap, p);
            }
        }

        async function load() {
            try {
                // cache-buster: после новой подписи PDF меняется, иначе
                // браузер отдаст старую версию из кэша.
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

        // Стартуем, когда pdf.js готов И DOM отрисован.
        whenPdfJsReady(function () {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            if (!viewer) {
                console.error('[free-sign] #fsPdfViewer не найден в DOM');
                return;
            }
            console.log('[free-sign] грузим PDF:', PDF_URL, '| призраков:', GHOSTS.length);
            load();
        });
        @else
        var l = document.getElementById('fsPdfLoading');
        if (l) l.textContent = 'К документу не приложен PDF-файл';
        @endisset
    })();
</script>
