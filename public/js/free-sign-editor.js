document.addEventListener('DOMContentLoaded', function () {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    /* v50: в ПАЛИТРЕ остаётся только QR-код (palette: true). Остальные
       типы НЕ удалены из списка — они нужны для отрисовки и штамповки
       полей СТАРЫХ документов, где такие метки уже расставлены. */
    const ELEMENT_TYPES = [
        {key: 'signature', label: 'Подпись ЭЦП', icon: '✒️', kind: 'box', def: {w: 0.20, h: 0.06}},
        {key: 'qr', label: 'QR-код', icon: '▦', kind: 'box', def: {w: 0.09, h: 0.09}, palette: true},
        {key: 'full_name', label: 'ФИО', icon: '👤', kind: 'text', def: {w: 0.24, h: 0.035}},
        {key: 'position', label: 'Должность', icon: '🏷️', kind: 'text', def: {w: 0.24, h: 0.03}},
        {key: 'sign_date', label: 'Дата', icon: '📅', kind: 'text', def: {w: 0.16, h: 0.03}},
        {key: 'sign_time', label: 'Время', icon: '🕒', kind: 'text', def: {w: 0.12, h: 0.03}},
        {key: 'cert_number', label: 'Номер серт.', icon: '#', kind: 'text', def: {w: 0.24, h: 0.03}},
        {key: 'cert_info', label: 'Инфо серт.', icon: 'ℹ️', kind: 'text', def: {w: 0.28, h: 0.05}},
        {key: 'stamp_approved', label: '«Согласовано»', icon: '✅', kind: 'box', def: {w: 0.16, h: 0.06}},
        {key: 'stamp_confirmed', label: '«Утверждено»', icon: '☑️', kind: 'box', def: {w: 0.16, h: 0.06}},
        {key: 'free_text', label: 'Текст', icon: '📝', kind: 'text', def: {w: 0.24, h: 0.04}},
    ];
    const typeByKey = Object.fromEntries(ELEMENT_TYPES.map(t => [t.key, t]));

    const COLORS = ['#794DFD', '#e0679a', '#3a9bdc', '#20a37b', '#e0872f', '#8b5cf6', '#0ea5a3', '#d4547e'];

    // Данные-заглушки приходят из Blade (window.FS_DEMO).
    const DEMO = window.FS_DEMO || {};
    let pollTimer = null;
    // Подписанты могут быть пустыми на старте — добавляются из списка кандидатов.
    const demoSigners = (DEMO.signers && DEMO.signers.length) ? DEMO.signers : [];

    const state = {
        pdfDoc: null, scale: 1.0, pages: [], elements: [], selectedId: null, seq: 1, tool: 'select', pendingArea: null,
        sequential: (DEMO.sequential !== undefined ? !!DEMO.sequential : true),  // true=по очереди, false=параллельно
        resolutionRequired: (DEMO.resolutionRequired !== undefined ? !!DEMO.resolutionRequired : true),  // v49: обязательная резолюция
        mode: DEMO.mode || 'edit',   // 'edit' — расстановка, 'view' — ожидание подписей
        // Mock-статусы подписи для режима view. Позже придут с backend по polling.
        // status: 'pending' | 'signed', signedAt — когда подписал.
        signStatus: DEMO.signStatus || {},
        signers: demoSigners.map((sg, i) => ({
            id: sg.id, name: sg.name, info: sg.info || '',
            role: sg.role || 'Подписант', color: sg.color || COLORS[i % COLORS.length]
        })),
        activeSignerId: demoSigners.length ? demoSigners[0].id : null,
        signerSeq: (demoSigners.reduce((m, sg) => Math.max(m, sg.id), 0) || 0) + 1,
    };

    const stageArea = document.getElementById('stageArea');
    const jsonOut = document.getElementById('jsonOut');

    /* ── Подписант helpers ── */
    function signerById(id) {
        return state.signers.find(s => s.id === id);
    }

    function activeSigner() {
        return signerById(state.activeSignerId);
    }

    function initials(name) {
        return name.split(/\s+/).filter(Boolean).slice(0, 2).map(w => w[0].toUpperCase()).join('');
    }

    function fieldCount(id) {
        return state.elements.filter(e => e.signerId === id).length;
    }

    /* ── Рендер карточек подписантов ── */
    const signerList = document.getElementById('signerList');

    function renderSigners() {
        document.getElementById('signerCnt').textContent = state.signers.length;
        signerList.innerHTML = '';

        const isView = state.mode === 'view';

        state.signers.forEach((s, idx) => {
            // Статус приходит с сервера: signed (5) | active (2, его очередь) | pending (1)
            const st = state.signStatus[s.id] || 'pending';
            const isTurn = isView && st === 'active';

            const card = document.createElement('div');
            card.className = 'fse-signer-card'
                + (!isView && s.id === state.activeSignerId ? ' active' : '')
                + (isView && st === 'signed' ? ' fse-signed' : '')
                + (isTurn ? ' fse-turn' : '');

            // Footer различается: edit — счётчик+удаление; view — статус.
            let foot;
            if (isView) {
                const statusHtml = st === 'signed'
                    ? '<span class="fse-status fse-status-signed">✓ Подписал</span>'
                    : (st === 'active'
                        ? '<span class="fse-status fse-status-turn">● Ожидает подписи</span>'
                        : '<span class="fse-status fse-status-wait">○ В очереди</span>');
                foot = `<div class="fse-sc-foot"><div class="fse-sc-fields">${statusHtml}</div></div>`;
            } else {
                foot = `<div class="fse-sc-foot">
                    <div class="fse-sc-fields"><b>${fieldCount(s.id)}</b> ${plural(fieldCount(s.id))}${s.id === state.activeSignerId ? ' · <span class="fse-sc-active-tag">активен</span>' : ''}</div>
                    <button class="fse-sc-remove" data-remove="${s.id}" title="Удалить">✕</button>
                </div>`;
            }

            card.innerHTML = `
      <div class="fse-accent-bar" style="background:${s.color}"></div>
      <div class="fse-sc-seq">${idx + 1}</div>
      <div class="fse-sc-head">
        <div class="fse-sc-avatar" style="background:${s.color}">${initials(s.name)}</div>
        <div class="fse-sc-meta">
          <div class="fse-sc-name">${s.name}</div>
          <div class="fse-sc-role">${s.role} · ${s.info || ''}</div>
        </div>
      </div>
      ${foot}`;

            if (!isView) {
                card.addEventListener('click', e => {
                    if (e.target.closest('[data-remove]')) return;
                    state.activeSignerId = s.id;
                    renderSigners();
                    updateActiveHint();
                });
                card.querySelector('[data-remove]').addEventListener('click', () => removeSigner(s.id));
            }
            signerList.appendChild(card);
        });

        updateResolToggle();   // v49: тумблер «обязательная резолюция»
    }

    /* ═══ v49: тумблер «обязательное заполнение резолюции» ═══
     | Показывается, только если среди подписантов есть роль RESOL
     | (DEMO.resolRoleId). При включённом контроле исполнения
     | (DEMO.controlEnabled) заблокирован: проект резолюции готовит
     | контролёр, тумблер не имеет смысла. Значение уходит в payload
     | сохранения (resolution_required) -> saveFromEditor.
     */
    function ensureResolToggle() {
        let box = document.getElementById('fsResolReqBox');
        if (box) return box;

        box = document.createElement('div');
        box.id = 'fsResolReqBox';
        box.style.cssText = 'display:none;margin:10px 0;padding:10px 12px;'
            + 'border:1px solid rgba(15,23,42,.08);border-radius:10px;'
            + 'background:#fff;font-size:12.5px;line-height:1.45;';
        box.innerHTML =
            '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin:0;font-weight:600;color:#1e293b;">'
            + '<input type="checkbox" id="fsResolReq" style="accent-color:#794DFD;width:16px;height:16px;flex:0 0 auto;">'
            + '<span>Обязательное заполнение резолюции</span>'
            + '</label>'
            + '<div id="fsResolReqHint" style="color:#94a3b8;margin-top:4px;"></div>';
        signerList.parentNode.insertBefore(box, signerList.nextSibling);

        box.querySelector('#fsResolReq').addEventListener('change', function () {
            state.resolutionRequired = this.checked;
            updateResolToggle();
        });
        return box;
    }

    function updateResolToggle() {
        const box  = ensureResolToggle();
        const rid  = DEMO.resolRoleId != null ? String(DEMO.resolRoleId) : null;
        const hasResol = !!rid && state.signers.some(s => String(s.roleId) === rid);

        box.style.display = hasResol ? '' : 'none';
        if (!hasResol) return;

        const cb   = box.querySelector('#fsResolReq');
        const hint = box.querySelector('#fsResolReqHint');
        const isView = state.mode === 'view';

        cb.checked = !!state.resolutionRequired;

        if (DEMO.controlEnabled) {
            cb.disabled = true;
            hint.textContent = 'Недоступно: включён контроль исполнения — проект резолюции готовит контролёр';
        } else if (isView) {
            cb.disabled = true;
            hint.textContent = cb.checked
                ? 'Резолюционер подписывает только после создания резолюции'
                : 'Резолюционер может подписать без резолюции';
        } else {
            cb.disabled = false;
            hint.textContent = cb.checked
                ? 'Резолюционер не сможет подписать, пока не создаст резолюцию (исполнители, срок, текст)'
                : 'Резолюционер сможет подписать без резолюции — документ завершится без исполнителей';
        }
    }

    function plural(n) {
        const a = Math.abs(n) % 100, b = a % 10;
        if (a > 10 && a < 20) return 'элементов';
        if (b > 1 && b < 5) return 'элемента';
        if (b === 1) return 'элемент';
        return 'элементов';
    }

    function removeSigner(id) {
        const cnt = fieldCount(id);
        if (cnt > 0 && !confirm(`У подписанта ${cnt} элемент(ов). Удалить вместе с ними?`)) return;
        state.elements = state.elements.filter(e => e.signerId !== id);
        state.signers = state.signers.filter(s => s.id !== id);
        if (state.activeSignerId === id) {
            state.activeSignerId = state.signers.length ? state.signers[0].id : null;
        }
        renderSigners();
        updateActiveHint();
        redrawAll();
    }

    /* Добавление подписанта */
    // Кандидаты в подписанты — люди компании из Blade (FS_DEMO.candidates).
    const CANDIDATES = (DEMO.candidates && DEMO.candidates.length) ? DEMO.candidates : [];

    document.getElementById('addSignerBtn').addEventListener('click', () => {
        document.getElementById('addForm').classList.add('fse-open');
        document.getElementById('addSignerBtn').style.display = 'none';
        renderCandidates('');
        document.getElementById('afSearch').focus();
    });
    document.getElementById('afCancel').addEventListener('click', closeAddForm);

    function closeAddForm() {
        document.getElementById('addForm').classList.remove('fse-open');
        document.getElementById('addSignerBtn').style.display = 'block';
        document.getElementById('afSearch').value = '';
        document.getElementById('afCandidates').innerHTML = '';
    }

    // Поиск по списку (по мере ввода).
    document.getElementById('afSearch').addEventListener('input', e => {
        renderCandidates(e.target.value.trim().toLowerCase());
    });

    function renderCandidates(query) {
        const box = document.getElementById('afCandidates');
        box.innerHTML = '';

        // Уже добавленных не показываем повторно.
        const usedIds = new Set(state.signers.map(s => s.id));

        let list = CANDIDATES.filter(c => !usedIds.has(c.id));
        if (query) list = list.filter(c => (c.name || '').toLowerCase().includes(query));

        if (!CANDIDATES.length) {
            box.innerHTML = '<div class="fse-cand-empty">Список сотрудников пуст (FS_DEMO.candidates)</div>';
            return;
        }
        if (!list.length) {
            box.innerHTML = '<div class="fse-cand-empty">Никого не найдено</div>';
            return;
        }

        list.slice(0, 50).forEach(c => {
            const item = document.createElement('div');
            item.className = 'fse-cand-item';
            item.innerHTML = `<span class="fse-cand-ava">${initials(c.name || '?')}</span><span>${c.name || 'Без имени'}</span>`;
            item.addEventListener('click', () => addSignerFromCandidate(c));
            box.appendChild(item);
        });
    }

    // Добавление подписанта из выбранного кандидата.
    function addSignerFromCandidate(c) {
        const roleSel = document.getElementById('afRole');
        const roleId = roleSel.value;                          // id роли → в БД
        const roleLabel = roleSel.options[roleSel.selectedIndex]?.text || 'Подписант';
        const s = {
            id: c.id,                                   // реальный users.id
            name: c.name,
            info: c.position || '',                     // должность, если есть
            roleId: roleId,                             // document_execution_roles.id
            role: roleLabel,                            // локализованное имя для UI
            color: COLORS[state.signers.length % COLORS.length],
        };
        state.signers.push(s);
        state.activeSignerId = s.id;
        // синхронизируем seq, чтобы не пересечься с ручными id
        state.signerSeq = Math.max(state.signerSeq, c.id + 1);
        closeAddForm();
        renderSigners();
        updateActiveHint();
    }

    function updateActiveHint() {
        const s = activeSigner();
        const dot = document.querySelector('#activeHint .fse-dot');
        const txt = document.getElementById('activeHintText');
        if (!s) {
            dot.style.background = 'var(--muted)';
            txt.innerHTML = 'Добавьте подписанта, чтобы расставлять элементы';
            return;
        }
        dot.style.background = s.color;
        txt.innerHTML = `Ставим для: <b>${s.name}</b> (${s.role})`;
    }

    /* ── Палитра ── */
    const paletteEl = document.getElementById('palette');
    ELEMENT_TYPES.filter(t => t.palette).forEach(t => {   // v50: только QR
        const chip = document.createElement('div');
        chip.className = 'fse-chip';
        chip.innerHTML = `<span class="fse-ic">${t.icon}</span><span>${t.label}</span>`;
        chip.addEventListener('click', () => addElementCentered(t.key));
        paletteEl.appendChild(chip);
    });

    /* ── Поповер типов ── */
    const tpList = document.getElementById('tpList');
    const typePopover = document.getElementById('typePopover');
    ELEMENT_TYPES.filter(t => t.palette).forEach(t => {   // v50: только QR (и в поповере рисования рамки)
        const item = document.createElement('div');
        item.className = 'fse-tp-item';
        item.innerHTML = `<span class="fse-ic">${t.icon}</span><span>${t.label}</span>`;
        item.addEventListener('click', () => {
            if (state.pendingArea) {
                const a = state.pendingArea;
                const el = makeEl(t.key, a.page, a.nx, a.ny, a.nw, a.nh);
                state.elements.push(el);
                redrawAll();
                select(el.id);
                renderSigners();
            }
            hidePopover();
        });
        tpList.appendChild(item);
    });

    function hidePopover() {
        typePopover.classList.remove('fse-active');
        state.pendingArea = null;
        removePreview();
    }

    document.addEventListener('mousedown', e => {
        if (typePopover.classList.contains('fse-active') && !typePopover.contains(e.target) && !e.target.closest('.konvajs-content')) hidePopover();
    });

    // Переключатель порядка подписания (по очереди / параллельно).
    // Применение режима edit/view: блокировка редактирования + баннер статуса.
    function applyMode() {
        const isView = state.mode === 'view';
        const root = document.querySelector('.fs-editor');
        if (root) root.classList.toggle('fse-viewmode', isView);

        // Прячем инструменты редактирования во view-режиме.
        const hide = (id, on) => { const el = document.getElementById(id); if (el) el.style.display = on ? 'none' : ''; };
        hide('addSignerBtn', isView);
        hide('orderSwitch', isView);
        hide('palette', isView);
        hide('toolGroup', isView);
        hide('clearBtn', isView);

        // Кнопка Сохранить → в view становится «Отправлено» (неактивна).
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            if (isView) {
                saveBtn.textContent = 'Отправлено на подпись';
                saveBtn.classList.add('fse-btn-sent');
                saveBtn.style.pointerEvents = 'none';
                saveBtn.style.opacity = '.7';
            } else {
                saveBtn.textContent = 'Сохранить и отправить';
                saveBtn.classList.remove('fse-btn-sent');
                saveBtn.style.pointerEvents = '';
                saveBtn.style.opacity = '';
            }
        }

        // Заголовок подсказки под палитрой.
        const hint = document.getElementById('activeHintText');
        if (hint && isView) {
            hint.innerHTML = 'Документ отправлен. Ожидаем подписи участников.';
            const dot = document.querySelector('#activeHint .fse-dot');
            if (dot) dot.style.background = 'var(--accent)';
        }

        // Метки на PDF: во view — не таскаются/не выделяются.
        state.pages.forEach(pg => {
            pg.mainLayer.getChildren(n => n.getClassName() === 'Group').forEach(g => g.draggable(!isView && state.tool === 'select'));
            pg.tr.nodes([]);
            pg.mainLayer.draw();
            pg.uiLayer.draw();
        });

        // Баннер статуса вверху сайдбара.
        renderStatusBanner();
    }

    // Баннер общего статуса документа (сколько подписали).
    function renderStatusBanner() {
        let banner = document.getElementById('fseStatusBanner');
        if (state.mode !== 'view') { if (banner) banner.remove(); return; }

        const total = state.signers.length;
        const signed = state.signers.filter(s => (state.signStatus[s.id] || 'pending') === 'signed').length;
        const done = total > 0 && signed === total;

        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'fseStatusBanner';
            banner.className = 'fse-status-banner';
            const sidebar = document.querySelector('.fse-sidebar');
            if (sidebar) sidebar.insertBefore(banner, sidebar.firstChild);
        }
        banner.className = 'fse-status-banner' + (done ? ' fse-done' : '');
        banner.innerHTML = done
            ? `<div class="fse-sb-title">✓ Документ подписан</div>
               <div class="fse-sb-sub">Все участники (${total}) поставили подпись</div>`
            : `<div class="fse-sb-title">⏳ Ожидание подписей</div>
               <div class="fse-sb-sub">Подписали ${signed} из ${total}</div>
               <div class="fse-sb-bar"><div class="fse-sb-fill" style="width:${total ? Math.round(signed/total*100) : 0}%"></div></div>`;
    }

    function applyOrderUI() {
        document.querySelectorAll('#orderSwitch .fse-order-opt').forEach(opt => {
            const isSeq = opt.dataset.order === 'sequential';
            opt.classList.toggle('fse-active', isSeq === state.sequential);
        });
        // Класс на корне редактора управляет показом номеров очерёдности.
        const root = document.querySelector('.fs-editor');
        if (root) root.classList.toggle('fse-parallel', !state.sequential);
    }
    document.getElementById('orderSwitch').addEventListener('click', e => {
        const opt = e.target.closest('.fse-order-opt');
        if (!opt) return;
        state.sequential = (opt.dataset.order === 'sequential');
        applyOrderUI();
        emitJson();
    });

    document.getElementById('toolGroup').addEventListener('click', e => {
        const t = e.target.closest('.fse-tool');
        if (!t) return;
        state.tool = t.dataset.tool;
        document.querySelectorAll('.fse-toolgroup .fse-tool').forEach(x => x.classList.toggle('fse-active', x === t));
        state.pages.forEach(pg => {
            pg.wrap.classList.toggle('fse-draw-mode', state.tool === 'draw');
            pg.mainLayer.getChildren(n => n.getClassName() === 'Group').forEach(g => g.draggable(state.tool === 'select'));
            pg.mainLayer.draw();
        });
        if (state.tool === 'draw') select(null);
    });

    async function loadPdf(data) {
        document.getElementById('loading').style.display = 'grid';
        // сохраняем копию байтов для генерации итогового PDF (pdf.js «съедает» ArrayBuffer)
        state.pdfBytes = (data instanceof Uint8Array) ? data.slice() : new Uint8Array(data).slice();
        state.pdfDoc = await pdfjsLib.getDocument({data: state.pdfBytes.slice()}).promise;
        await renderAllPages();
        document.getElementById('loading').style.display = 'none';
    }

    async function renderAllPages() {
        stageArea.querySelectorAll('.fse-page-wrap').forEach(n => n.remove());
        state.pages = [];
        for (let p = 1; p <= state.pdfDoc.numPages; p++) {
            const page = await state.pdfDoc.getPage(p);
            const viewport = page.getViewport({scale: state.scale * 1.5});
            const wrap = document.createElement('div');
            wrap.className = 'fse-page-wrap';
            if (state.tool === 'draw') wrap.classList.add('fse-draw-mode');
            wrap.style.width = viewport.width + 'px';
            wrap.style.height = viewport.height + 'px';
            const badge = document.createElement('div');
            badge.className = 'fse-page-badge';
            badge.textContent = 'Стр. ' + p;
            wrap.appendChild(badge);
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf';
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            wrap.appendChild(canvas);
            await page.render({canvasContext: canvas.getContext('2d'), viewport}).promise;
            const holder = document.createElement('div');
            holder.className = 'konva-holder';
            wrap.appendChild(holder);
            stageArea.appendChild(wrap);
            const stage = new Konva.Stage({container: holder, width: viewport.width, height: viewport.height});
            const mainLayer = new Konva.Layer();
            const uiLayer = new Konva.Layer();
            stage.add(mainLayer);
            stage.add(uiLayer);
            const tr = new Konva.Transformer({
                rotateEnabled: false, borderStroke: '#794DFD',
                anchorStroke: '#794DFD', anchorFill: '#fff', anchorSize: 9,
                boundBoxFunc: (o, n) => (n.width < 8 || n.height < 6) ? o : n
            });
            uiLayer.add(tr);
            const pageObj = {
                pageNumber: p,
                pxW: viewport.width,
                pxH: viewport.height,
                wrap,
                stage,
                mainLayer,
                uiLayer,
                tr
            };
            state.pages.push(pageObj);
            attachStageEvents(pageObj);
        }
        redrawAll();
    }

    function attachStageEvents(pg) {
        const stage = pg.stage;
        let draw = null;
        stage.on('mousedown touchstart', ev => {
            const onEmpty = (ev.target === stage);
            if (state.tool === 'draw' && onEmpty) {
                const drawer = activeSigner();
                if (!drawer) { alert('Сначала добавьте подписанта'); return; }
                const pos = stage.getPointerPosition();
                const rect = new Konva.Rect({
                    x: pos.x, y: pos.y, width: 0, height: 0, name: 'preview-rect',
                    fill: drawer.color + '22', stroke: drawer.color, strokeWidth: 1.5, dash: [5, 4]
                });
                pg.uiLayer.add(rect);
                pg.uiLayer.draw();
                draw = {rect, sx: pos.x, sy: pos.y};
            } else if (state.tool === 'select' && onEmpty) {
                select(null);
            }
        });
        stage.on('mousemove touchmove', () => {
            if (!draw) return;
            const pos = stage.getPointerPosition();
            if (!pos) return;
            draw.rect.setAttrs({
                x: Math.min(pos.x, draw.sx), y: Math.min(pos.y, draw.sy),
                width: Math.abs(pos.x - draw.sx), height: Math.abs(pos.y - draw.sy)
            });
            pg.uiLayer.batchDraw();
        });
        stage.on('mouseup touchend', () => {
            if (!draw) return;
            const r = draw.rect;
            draw = null;
            const nw = r.width() / pg.pxW, nh = r.height() / pg.pxH, nx = r.x() / pg.pxW, ny = r.y() / pg.pxH;
            if (nw < 0.01 || nh < 0.008) {
                r.destroy();
                pg.uiLayer.draw();
                return;
            }
            state.pendingArea = {page: pg.pageNumber, nx, ny, nw, nh};
            showPopoverAt(pg, r);
        });
    }

    function removePreview() {
        state.pages.forEach(pg => {
            pg.uiLayer.find('.preview-rect').forEach(n => n.destroy());
            pg.uiLayer.draw();
        });
    }

    function showPopoverAt(pg, rect) {
        const box = pg.stage.container().getBoundingClientRect();
        let left = box.left + rect.x() + rect.width() + 8, top = box.top + rect.y();
        if (left + 240 > window.innerWidth) left = box.left + rect.x() - 240;
        if (top + 380 > window.innerHeight) top = window.innerHeight - 390;
        const s = activeSigner();
        document.querySelector('#tpSub .fse-dot').style.background = s.color;
        document.getElementById('tpSubName').textContent = s.name;
        typePopover.style.left = Math.max(8, left) + 'px';
        typePopover.style.top = Math.max(8, top) + 'px';
        typePopover.classList.add('fse-active');
    }

    function makeEl(typeKey, page, nx, ny, nw, nh) {
        const t = typeByKey[typeKey];
        const el = {
            id: state.seq++,
            page,
            type: typeKey,
            signerId: state.activeSignerId,
            nx,
            ny,
            nw: nw ?? t.def.w,
            nh: nh ?? t.def.h,
            params: t.kind === 'text' ? {font_size: 10, align: 'left'} : {}
        };
        if (typeKey === 'free_text') el.params.text = 'Текст';
        clamp(el);
        return el;
    }

    function addElementCentered(typeKey) {
        if (state.activeSignerId == null) {
            alert('Сначала добавьте подписанта');
            return;
        }
        const first = state.pages[0];
        if (!first) return;
        const t = typeByKey[typeKey];
        const el = makeEl(typeKey, first.pageNumber, 0.4 - t.def.w / 2, 0.4 - t.def.h / 2);
        state.elements.push(el);
        redrawAll();
        select(el.id);
        renderSigners();
    }

    function redrawAll() {
        state.pages.forEach(pg => {
            pg.mainLayer.destroyChildren();
            pg.tr.nodes([]);
        });
        const isView = state.mode === 'view';

        state.elements.forEach(el => {
            const pg = state.pages.find(p => p.pageNumber === el.page);
            if (!pg) return;
            const t = typeByKey[el.type];
            const s = signerById(el.signerId);
            const color = s ? s.color : '#794DFD';

            // ═══ ЛОГИКА МЕТОК-ПРИЗРАКОВ ═══
            // В режиме view метка = подсказка «здесь БУДЕТ подпись».
            // Как только подписант подписал — на PDF уже есть реальный
            // QR/подпись (проштампованы сервером), поэтому призрак УБИРАЕМ,
            // чтобы не дублировать поверх настоящей метки.
            const signerStatus = s ? (state.signStatus[s.id] || 'pending') : 'pending';
            if (isView && signerStatus === 'signed') {
                return;   // подписал → метка уже на PDF → призрак не рисуем
            }

            const w = el.nw * pg.pxW, h = el.nh * pg.pxH;
            const group = new Konva.Group({
                x: el.nx * pg.pxW, y: el.ny * pg.pxH,
                draggable: (!isView && state.tool === 'select'), id: 'el-' + el.id,
                opacity: isView ? 0.55 : 1,   // в view — приглушённый призрак
            });
            group.add(new Konva.Rect({
                width: w, height: h, cornerRadius: 4,
                fill: color + (isView ? '14' : '22'),
                stroke: color,
                strokeWidth: isView ? 1 : 1.5,
                dash: isView ? [6, 4] : (t.kind === 'text' ? [4, 3] : []),
            }));
            // Подпись метки: в view поясняем, что здесь будет.
            group.add(new Konva.Text({
                text: isView ? ('⏳ ' + t.label) : (t.icon + ' ' + t.label),
                width: w, height: h, padding: 4,
                fontSize: Math.max(9, Math.min(12, h * 0.32)),
                fill: isView ? '#64748b' : '#334155',
                align: 'center', verticalAlign: 'middle', listening: false
            }));
            if (s) {
                const tag = new Konva.Label({x: 2, y: 2, listening: false});
                tag.add(new Konva.Tag({fill: color, cornerRadius: 3}));
                tag.add(new Konva.Text({
                    text: initials(s.name), fontSize: 9, fontStyle: 'bold',
                    fill: '#fff', padding: 2
                }));
                group.add(tag);
            }
            group.on('click tap', e => {
                e.cancelBubble = true;
                if (state.tool === 'select') select(el.id);
            });
            group.on('dragstart', e => {
                e.cancelBubble = true;
                select(el.id);
            });
            group.on('dragmove', () => {
                el.nx = group.x() / pg.pxW;
                el.ny = group.y() / pg.pxH;
                syncProps();
            });
            group.on('dragend', () => {
                el.nx = group.x() / pg.pxW;
                el.ny = group.y() / pg.pxH;
                clamp(el);
                group.position({x: el.nx * pg.pxW, y: el.ny * pg.pxH});
                pg.mainLayer.draw();
                syncProps();
                emitJson();
            });
            group.on('transformend', () => {
                const sx = group.scaleX(), sy = group.scaleY();
                el.nw = (w * sx) / pg.pxW;
                el.nh = (h * sy) / pg.pxH;
                el.nx = group.x() / pg.pxW;
                el.ny = group.y() / pg.pxH;
                group.scale({x: 1, y: 1});
                clamp(el);
                redrawAll();
                select(el.id);
            });
            pg.mainLayer.add(group);
        });
        state.pages.forEach(pg => pg.mainLayer.draw());
        if (state.selectedId != null) applyTransformer(state.selectedId);
        emitJson();
    }

    function clamp(el) {
        el.nw = Math.max(0.02, Math.min(el.nw, 1));
        el.nh = Math.max(0.01, Math.min(el.nh, 1));
        el.nx = Math.max(0, Math.min(el.nx, 1 - el.nw));
        el.ny = Math.max(0, Math.min(el.ny, 1 - el.nh));
    }

    function applyTransformer(id) {
        state.pages.forEach(pg => {
            pg.tr.nodes([]);
            pg.uiLayer.draw();
        });
        if (id == null || state.tool !== 'select') return;
        const el = state.elements.find(e => e.id === id);
        if (!el) return;
        const pg = state.pages.find(p => p.pageNumber === el.page);
        const g = pg.mainLayer.findOne('#el-' + id);
        if (g) {
            pg.tr.nodes([g]);
            pg.uiLayer.draw();
        }
    }

    function select(id) {
        state.selectedId = id;
        applyTransformer(id);
        syncProps();
    }

    const props = document.getElementById('props');

    function syncProps() {
        const el = state.elements.find(e => e.id === state.selectedId);
        if (!el) {
            props.classList.remove('fse-active');
            return;
        }
        props.classList.add('fse-active');
        const t = typeByKey[el.type];
        const s = signerById(el.signerId);
        document.getElementById('pType').textContent = t.label;
        document.getElementById('pSigner').textContent = s ? s.name : '—';
        document.getElementById('pPage').textContent = el.page;
        document.getElementById('pXY').textContent = `${el.nx.toFixed(3)} / ${el.ny.toFixed(3)}`;
        document.getElementById('pWH').textContent = `${el.nw.toFixed(3)} × ${el.nh.toFixed(3)}`;
        const isText = t.kind === 'text';
        document.getElementById('fsRow').style.display = isText ? 'flex' : 'none';
        document.getElementById('textRow').style.display = (el.type === 'free_text') ? 'flex' : 'none';
        if (isText) document.getElementById('pFont').value = el.params.font_size || 10;
        if (el.type === 'free_text') document.getElementById('pText').value = el.params.text || '';
    }

    document.getElementById('pFont').addEventListener('input', e => {
        const el = sel();
        if (el) {
            el.params.font_size = +e.target.value;
            emitJson();
        }
    });
    document.getElementById('pText').addEventListener('input', e => {
        const el = sel();
        if (el) {
            el.params.text = e.target.value;
            redrawAll();
            select(el.id);
        }
    });
    document.getElementById('delBtn').addEventListener('click', () => {
        const el = sel();
        if (!el) return;
        state.elements = state.elements.filter(e => e.id !== el.id);
        select(null);
        redrawAll();
        renderSigners();
    });

    function sel() {
        return state.elements.find(e => e.id === state.selectedId);
    }

    // Сборка payload для сохранения (используется и в emitJson, и при отправке).
    function buildPayload() {
        return {
            sequential: state.sequential,
            resolution_required: state.resolutionRequired,   // v49
            signers: state.signers.map((s, i) => ({
                user_id: s.id,
                role_id: s.roleId ?? null,
                order: i + 1,
                color: s.color,
            })),
            fields: state.elements.map(el => ({
                page: el.page, type: el.type, signer_id: el.signerId,
                x: +el.nx.toFixed(4), y: +el.ny.toFixed(4),
                width: +el.nw.toFixed(4), height: +el.nh.toFixed(4),
                params: el.params
            })),
        };
    }

    function emitJson() {
        jsonOut.textContent = JSON.stringify(buildPayload(), null, 2);
    }

    function setZoom(s) {
        state.scale = Math.max(0.5, Math.min(2.5, s));
        document.getElementById('zoomVal').textContent = Math.round(state.scale * 100) + '%';
        renderAllPages();
    }

    document.getElementById('zoomIn').onclick = () => setZoom(state.scale + 0.15);
    document.getElementById('zoomOut').onclick = () => setZoom(state.scale - 0.15);
    document.getElementById('clearBtn').onclick = () => {
        state.elements = [];
        select(null);
        redrawAll();
        renderSigners();
    };
    document.getElementById('saveBtn').onclick = async () => {
        const btn = document.getElementById('saveBtn');

        // Проверки перед отправкой.
        if (!state.signers.length) { alert('Добавьте хотя бы одного подписанта'); return; }
        if (!DEMO.saveUrl) { alert('Не задан URL сохранения (FS_DEMO.saveUrl)'); return; }

        const old = btn.textContent;
        btn.textContent = 'Сохранение…';
        btn.style.pointerEvents = 'none';
        try {
            const payload = buildPayload();
            const resp = await fetch(DEMO.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': DEMO.csrf || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });
            if (!resp.ok) {
                const t = await resp.text();
                throw new Error('HTTP ' + resp.status + ' — ' + t.slice(0, 300));
            }
            const data = await resp.json().catch(() => ({}));
            btn.textContent = '✓ Сохранено';

            // Перезагружаем страницу: кнопка «Подписать и отправить» рендерится
            // сервером ($canSign), поэтому без перезагрузки её в DOM нет.
            // Заодно сервер отдаст актуальные метки, статусы и режим.
            setTimeout(() => {
                window.location.href = DEMO.redirectAfterSave || window.location.href;
            }, 600);
        } catch (err) {
            console.error(err);
            alert('Ошибка сохранения: ' + err.message);
            btn.textContent = old;
        } finally {
            btn.style.pointerEvents = 'auto';
        }
    };

    /* ─────────────────────────────────────────────────────────────
   ГЕНЕРАЦИЯ ИТОГОВОГО PDF С MOCK-ДАННЫМИ (имитация штампа при подписании)
   Координаты редактора: y растёт ВНИЗ (0 сверху).
   Координаты PDF:        y растёт ВВЕРХ (0 снизу).
   Перевод: pdfX = nx*W ; pdfY = H - (ny+nh)*H
   ───────────────────────────────────────────────────────────── */
    let _cyrFont = null;

    async function loadCyrFont() {
        if (_cyrFont) return _cyrFont;
        // DejaVuSans поддерживает кириллицу; тянем с jsDelivr (dejavu-fonts)
        const urls = [
            'https://cdn.jsdelivr.net/npm/dejavu-fonts-ttf@2.37.3/ttf/DejaVuSans.ttf',
            'https://cdn.jsdelivr.net/gh/dejavu-fonts/dejavu-fonts@version_2_37/ttf/DejaVuSans.ttf'
        ];
        for (const u of urls) {
            try {
                const r = await fetch(u);
                if (r.ok) {
                    _cyrFont = await r.arrayBuffer();
                    return _cyrFont;
                }
            } catch (e) {
            }
        }
        return null; // фолбэк на стандартный шрифт (латиница)
    }

    function hexToRgb01(hex) {
        const h = hex.replace('#', '');
        return {
            r: parseInt(h.substr(0, 2), 16) / 255,
            g: parseInt(h.substr(2, 2), 16) / 255,
            b: parseInt(h.substr(4, 2), 16) / 255
        };
    }

    // mock-текст по типу элемента
    function mockContent(el, signer) {
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const date = `${pad(now.getDate())}.${pad(now.getMonth() + 1)}.${now.getFullYear()}`;
        const time = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
        const certSn = '5E:A1:0C:' + Math.random().toString(16).slice(2, 6).toUpperCase();
        switch (el.type) {
            case 'full_name':
                return signer.name;
            case 'position':
                return signer.info || signer.role;
            case 'sign_date':
                return 'Дата: ' + date;
            case 'sign_time':
                return 'Время: ' + time;
            case 'cert_number':
                return '№ ' + certSn;
            case 'cert_info':
                return signer.name + '\nСертификат: ' + certSn + '\nдо 01.01.2027';
            case 'free_text':
                return el.params?.text || 'Текст';
            case 'stamp_approved':
                return 'СОГЛАСОВАНО';
            case 'stamp_confirmed':
                return 'УТВЕРЖДЕНО';
            case 'signature':
                return signer.name; // подпись рисуем как рамку + имя
            default:
                return '';
        }
    }

    // Рисуем QR прямо в PDF как сетку модулей (надёжнее embedPng — генератор отдаёт GIF)
    function drawQrOnPage(page, text, x, y, size, rgb) {
        const qr = qrcode(0, 'M');
        qr.addData(text);
        qr.make();
        const n = qr.getModuleCount();
        const m = size / n;
        page.drawRectangle({x, y, width: size, height: size, color: rgb(1, 1, 1)});
        for (let r = 0; r < n; r++) for (let c = 0; c < n; c++) {
            if (qr.isDark(r, c))
                page.drawRectangle({x: x + c * m, y: y + size - (r + 1) * m, width: m, height: m, color: rgb(0, 0, 0)});
        }
    }

    async function generateSignedPdf() {
        if (!state.pdfBytes) {
            alert('Сначала откройте PDF');
            return;
        }
        if (state.elements.length === 0) {
            alert('Добавьте хотя бы один элемент');
            return;
        }
        const {PDFDocument, rgb, StandardFonts, degrees} = PDFLib;

        const pdfDoc = await PDFDocument.load(state.pdfBytes);
        let font, bold;
        const fontBytes = await loadCyrFont();
        if (fontBytes && PDFLib.PDFDocument) {
            pdfDoc.registerFontkit(window.fontkit);
            font = await pdfDoc.embedFont(fontBytes, {subset: true});
            bold = font; // один вес достаточно для mock
        } else {
            font = await pdfDoc.embedFont(StandardFonts.Helvetica);
            bold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
        }

        const pages = pdfDoc.getPages();

        for (const el of state.elements) {
            const page = pages[el.page - 1];
            if (!page) continue;
            const {width: W, height: H} = page.getSize();
            const signer = signerById(el.signerId) || {name: '—', info: '', role: '', color: '#794DFD'};
            const col = hexToRgb01(signer.color);

            const x = el.nx * W;
            const w = el.nw * W;
            const h = el.nh * H;
            const y = H - (el.ny * H) - h;   // низ элемента в координатах PDF

            const t = typeByKey[el.type];

            // ── QR ──
            if (el.type === 'qr') {
                const url = 'https://edoline.uz/verify/' + (signer.id) + '-' + Math.random().toString(36).slice(2, 8);
                const side = Math.min(w, h);
                drawQrOnPage(page, url, x, y + (h - side), side, rgb);
                continue;
            }

            // ── штампы: рамка + цветной текст под углом-нет, прямой ──
            if (el.type === 'stamp_approved' || el.type === 'stamp_confirmed') {
                page.drawRectangle({
                    x, y, width: w, height: h, borderColor: rgb(col.r, col.g, col.b),
                    borderWidth: 1.5, color: rgb(col.r, col.g, col.b), opacity: 0.08, borderOpacity: 1
                });
                const label = mockContent(el, signer);
                const fs = Math.min(h * 0.42, 14);
                const tw = font.widthOfTextAtSize(label, fs);
                page.drawText(label, {
                    x: x + (w - tw) / 2,
                    y: y + h / 2 - fs * 0.35,
                    size: fs,
                    font,
                    color: rgb(col.r, col.g, col.b)
                });
                const sub = signer.name;
                const fs2 = Math.min(h * 0.22, 8);
                const tw2 = font.widthOfTextAtSize(sub, fs2);
                page.drawText(sub, {
                    x: x + (w - tw2) / 2,
                    y: y + h * 0.12,
                    size: fs2,
                    font,
                    color: rgb(col.r, col.g, col.b)
                });
                continue;
            }

            // ── подпись ЭЦП: пунктирная рамка + имя рукописным наклоном (mock) ──
            if (el.type === 'signature') {
                page.drawRectangle({
                    x, y, width: w, height: h, borderColor: rgb(col.r, col.g, col.b),
                    borderWidth: 1, color: rgb(col.r, col.g, col.b), opacity: 0.06, borderOpacity: 0.9
                });
                const name = signer.name;
                const fs = Math.min(h * 0.45, 16);
                page.drawText(name, {x: x + 6, y: y + h * 0.45, size: fs, font, color: rgb(col.r, col.g, col.b)});
                const meta = 'Подписано ЭЦП · E-IMZO';
                const fs2 = Math.min(h * 0.22, 7);
                page.drawText(meta, {x: x + 6, y: y + h * 0.12, size: fs2, font, color: rgb(0.4, 0.4, 0.45)});
                continue;
            }

            // ── текстовые типы ──
            const content = mockContent(el, signer);
            const lines = content.split('\n');
            const fs = Math.min((el.params?.font_size || 10), h / Math.max(1, lines.length) * 1.1, h * 0.8);
            let ty = y + h - fs;   // сверху вниз
            for (const line of lines) {
                page.drawText(line, {
                    x: x + 2, y: Math.max(y, ty), size: Math.max(6, fs), font,
                    color: rgb(0.12, 0.15, 0.2)
                });
                ty -= fs * 1.25;
            }
            // тонкая подложка цвета подписанта слева (метка)
            page.drawRectangle({x: x - 0.5, y, width: 2, height: h, color: rgb(col.r, col.g, col.b)});
        }

        const bytes = await pdfDoc.save();
        const blob = new Blob([bytes], {type: 'application/pdf'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'signed-document-mock.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(a.href), 4000);
    }

    document.getElementById('file').addEventListener('change', async e => {
        const f = e.target.files[0];
        if (!f) return;
        const buf = await f.arrayBuffer();
        state.elements = [];
        select(null);
        await loadPdf(buf);
        renderSigners();
    });

    async function makeDemoPdf() {
        const {PDFDocument, rgb, StandardFonts} = PDFLib;
        const doc = await PDFDocument.create();
        const font = await doc.embedFont(StandardFonts.Helvetica);
        const bold = await doc.embedFont(StandardFonts.HelveticaBold);
        for (let i = 1; i <= 2; i++) {
            const page = doc.addPage([595, 842]);
            page.drawText('ACWA operations', {x: 60, y: 770, size: 20, font: bold, color: rgb(.35, .2, .7)});
            page.drawText('Employee Transfer Letter — page ' + i, {x: 60, y: 740, size: 12, font});
            ['To: Jaloliddin Yunusov', 'From: P&C Department', 'Date: July 01, 2026', '',
                'We are writing to formally acknowledge you that, in line with', 'business requirements, you will be transferred to SK01 project.', '',
                'All agreed terms and conditions of your employment remain unchanged.']
                .forEach((l, idx) => page.drawText(l, {x: 60, y: 690 - idx * 22, size: 11, font}));
            page.drawText('Signatures:', {x: 60, y: 420, size: 12, font: bold});
            page.drawRectangle({x: 60, y: 300, width: 150, height: 90, borderColor: rgb(.8, .8, .85), borderWidth: 1});
            page.drawRectangle({x: 230, y: 300, width: 150, height: 90, borderColor: rgb(.8, .8, .85), borderWidth: 1});
        }
        return await doc.save();
    }

    /**
     * Восстановление сохранённых меток из конфига (DEMO.fields) на канвас.
     * Без этого зоны подписи не видны при открытии существующего документа:
     * данные приходят с сервера, но никто не превращает их в элементы.
     */
    function hydrateFields() {
        if (!DEMO.fields || !DEMO.fields.length) return;

        DEMO.fields.forEach(f => {
            const t = typeByKey[f.type];
            if (!t) return;   // неизвестный тип — пропускаем

            state.elements.push({
                id: state.seq++,
                page: Number(f.page),
                type: f.type,
                signerId: f.signer_id,
                nx: parseFloat(f.x),
                ny: parseFloat(f.y),
                nw: parseFloat(f.width),
                nh: parseFloat(f.height),
                params: f.params || (t.kind === 'text' ? {font_size: 10, align: 'left'} : {}),
            });
        });

        redrawAll();
        renderSigners();   // обновит счётчики меток на карточках
    }

    (async () => {
        renderSigners();
        updateActiveHint();
        applyOrderUI();

        // Реальный PDF документа (из document_files через ftp_13), если задан URL.
        if (DEMO.pdfUrl) {
            try {
                const resp = await fetch(DEMO.pdfUrl, {
                    headers: DEMO.csrf ? {'X-CSRF-TOKEN': DEMO.csrf} : {},
                    credentials: 'same-origin'
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const buf = await resp.arrayBuffer();
                await loadPdf(buf);
            } catch (err) {
                console.error('Не удалось загрузить PDF документа:', err);
                document.getElementById('loading').textContent = 'Ошибка загрузки PDF документа';
                return;
            }
        } else {
            // Фолбэк: демо-документ (локальная отладка без backend).
            await loadPdf(await makeDemoPdf());
        }

        // Восстанавливаем сохранённые метки из БД (DEMO.fields) на канвас.
        hydrateFields();

        // Применяем режим ПОСЛЕ загрузки PDF (метки уже на канвасе).
        applyMode();

        // ── Реальный polling статуса (если задан URL) ──
        if (DEMO.statusUrl) {
            startStatusPolling();
        } else {
            // ── DEMO-кнопки (работают только без statusUrl) ──
            const dToggle = document.getElementById('demoToggleMode');
            if (dToggle) dToggle.onclick = () => {
                state.mode = state.mode === 'edit' ? 'view' : 'edit';
                renderSigners();
                applyMode();
            };
            const dSign = document.getElementById('demoSignNext');
            if (dSign) dSign.onclick = () => {
                if (state.mode !== 'view') { alert('Сначала переключись в режим просмотра (🔄 Режим)'); return; }
                const next = state.signers.find(s => (state.signStatus[s.id] || 'pending') !== 'signed');
                if (!next) { alert('Все уже подписали'); return; }
                state.signStatus[next.id] = 'signed';
                renderSigners();
                renderStatusBanner();
            };
        }
    })();

    // Опрос статуса документа каждые N секунд (режим view).
    /**
     * Перезагрузка PDF после того, как кто-то подписал.
     * Сервер штампует метки и сохраняет НОВУЮ версию document_files —
     * preView отдаёт последнюю, поэтому просто перезапрашиваем.
     */
    async function reloadPdfWithStamps() {
        try {
            // cache-buster: иначе браузер отдаст старую версию из кэша.
            const url = DEMO.pdfUrl + (DEMO.pdfUrl.includes('?') ? '&' : '?') + '_ts=' + Date.now();
            const resp = await fetch(url, {
                headers: DEMO.csrf ? {'X-CSRF-TOKEN': DEMO.csrf} : {},
                credentials: 'same-origin'
            });
            if (!resp.ok) return;
            const buf = await resp.arrayBuffer();
            await loadPdf(buf);      // перерисует страницы
            redrawAll();             // призраки: подписавшие исчезнут
        } catch (e) {
            console.error('Не удалось обновить PDF со штампами:', e);
        }
    }

    function startStatusPolling() {
        // Прячем демо-кнопки, если есть.
        ['demoToggleMode', 'demoSignNext'].forEach(id => {
            const el = document.getElementById(id); if (el) el.style.display = 'none';
        });

        const poll = async () => {
            try {
                const resp = await fetch(DEMO.statusUrl, {
                    headers: DEMO.csrf ? {'X-CSRF-TOKEN': DEMO.csrf} : {},
                    credentials: 'same-origin'
                });
                if (!resp.ok) return;
                const data = await resp.json();

                // Обновляем режим.
                if (data.mode && data.mode !== state.mode) {
                    state.mode = data.mode;
                }
                // Обновляем статусы подписантов (user_id → signed/pending).
                if (Array.isArray(data.signers)) {
                    const map = {};
                    data.signers.forEach(s => { map[s.user_id] = s.status; });
                    // Кто-то подписал с прошлого опроса? → сервер создал НОВУЮ
                    // версию PDF со штампом → перезагружаем документ, иначе
                    // увидим старый PDF без реальных подписей.
                    const wasSigned = Object.values(state.signStatus).filter(v => v === 'signed').length;
                    const nowSigned = Object.values(map).filter(v => v === 'signed').length;

                    state.signStatus = map;

                    if (nowSigned > wasSigned && DEMO.pdfUrl) {
                        reloadPdfWithStamps();
                    }
                }

                renderSigners();
                applyMode();

                // Если документ завершён — можно остановить опрос.
                if (data.is_done) {
                    clearInterval(pollTimer);
                }
            } catch (e) {
                console.error('Ошибка опроса статуса:', e);
            }
        };

        poll();                              // сразу при старте
        pollTimer = setInterval(poll, 12000); // затем каждые 12 сек
    }
});
