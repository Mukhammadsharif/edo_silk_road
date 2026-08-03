@extends('layouts.app')

{{-- Карточка входящего документа Didox — дизайн-система modern.
     Действия из бизнес-процесса: Подписать в Didox / Отправить на согласование --}}

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/working-documents-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <style>
        .didox-status { display:inline-flex; align-items:center; gap:6px; padding:4px 12px;
            border-radius:20px; font-size:12px; font-weight:600; white-space:nowrap; }
        .didox-status i, .didox-status svg { width:14px; height:14px; }
        .didox-status.st-signed    { background:#dcfce7; color:#15803d; }
        .didox-status.st-review    { background:#fef9c3; color:#a16207; }
        .didox-status.st-notsigned { background:#fee2e2; color:#b91c1c; }
        .didox-status.st-approved  { background:#dcfce7; color:#15803d; }
        .didox-status.st-pending   { background:#f1f5f9; color:#64748b; }

        .didox-card-grid { display:grid; grid-template-columns: minmax(0, 1fr) 380px; gap:1.5rem; align-items:start; }
        @media (max-width: 1100px) { .didox-card-grid { grid-template-columns: minmax(0, 1fr); } }

        .didox-panel { background:#fff; border:1px solid var(--border, #e2e8f0); border-radius:16px; overflow:hidden; min-width:0; max-width:100%; }
        .didox-panel .panel-head { display:flex; justify-content:space-between; align-items:center;
            padding:14px 18px; border-bottom:1px solid var(--border, #e2e8f0); font-weight:600; }
        .didox-panel .panel-head .title { display:inline-flex; align-items:center; gap:8px; }
        .didox-panel .panel-body { padding:18px; }

        .approver-row { display:flex; justify-content:space-between; align-items:center;
            padding:8px 0; border-bottom:1px dashed var(--border, #e2e8f0); font-size:14px; }
        .approver-row:last-child { border-bottom:none; }

        .spin { animation: didox-spin 1s linear infinite; }
        @keyframes didox-spin { from { transform:rotate(0); } to { transform:rotate(360deg); } }
        #print-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center;
            min-height:380px; color:#94a3b8; gap:10px; }

        /* ── Единый просмотрщик документа: печатная форма + вложения одним потоком ──
           Никаких iframe: страницы PDF рисует pdf.js в <canvas>, HTML-фолбэк
           печатной формы кладётся в shadow root (изоляция стилей Didox). */
        #doc-flow { background:#eef2f7; padding:16px 12px; min-height:420px;
            display:flex; flex-direction:column; align-items:center; gap:14px; }
        /* Листы не сжимаются: иначе при фиксированной высоте контейнера
           флекс-колонка ужимает их вместо прокрутки. */
        #doc-flow > * { flex:0 0 auto; }
        /* Режим «в области одной страницы»: высота контейнера равна листу,
           прокрутка внутри, со снапом — лист встаёт ровно в окно. */
        #doc-flow.mode-paged { height:var(--doc-page-h, 78vh); min-height:0;
            overflow-y:auto; overscroll-behavior:contain;
            scroll-snap-type:y mandatory; scroll-padding-top:16px; }
        #doc-flow.mode-paged .doc-sheet { scroll-snap-align:start; scroll-margin-top:16px; }
        .doc-sheet { width:100%; max-width:940px; background:#fff; border-radius:4px;
            box-shadow:0 1px 5px rgba(15,23,42,.16); overflow:hidden; }
        .doc-sheet canvas { display:block; width:100%; height:auto; }
        .doc-part-sep { width:100%; max-width:940px; display:flex; align-items:center; gap:10px;
            font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;
            letter-spacing:.04em; margin-top:6px; }
        .doc-part-sep::before, .doc-part-sep::after { content:''; flex:1; height:1px; background:#cbd5e1; }
        .doc-part-error { width:100%; max-width:940px; background:#fff7ed; border:1px solid #fed7aa;
            color:#9a3412; border-radius:8px; padding:12px 14px; font-size:13px;
            display:flex; align-items:center; gap:8px; }
        .doc-html-host { display:block; }

        /* Панель инструментов документа: одна компактная строка.
           Свои классы, а не .btn-modern — у него глобальные правила ширины,
           из-за которых кнопки растягивались на всю строку. */
        .doc-head { flex-wrap:nowrap; gap:12px; padding:10px 14px; }
        .doc-head .title { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .doc-toolbar { display:flex; align-items:center; gap:6px; flex:0 0 auto; }
        .doc-btn { display:inline-flex; align-items:center; gap:6px; flex:0 0 auto;
            width:auto !important; white-space:nowrap; cursor:pointer;
            font-size:12.5px; font-weight:600; line-height:1; padding:7px 11px;
            border:1px solid var(--border, #e2e8f0); border-radius:8px;
            background:#fff; color:#475569; transition:border-color .15s, color .15s, background .15s; }
        .doc-btn:hover { border-color:#c4b5fd; color:#6d28d9; }
        .doc-btn.is-primary { background:#7c3aed; border-color:#7c3aed; color:#fff; }
        .doc-btn.is-primary:hover { background:#6d28d9; border-color:#6d28d9; color:#fff; }
        .doc-btn[disabled] { opacity:.55; cursor:default; }
        .doc-btn i, .doc-btn svg { width:15px; height:15px; flex-shrink:0; }
        .doc-btn .cnt { font-size:11px; background:#ede9fe; color:#6d28d9;
            border-radius:999px; padding:1px 6px; }
        /* На узком экране остаются только иконки — подпись прячется */
        @media (max-width: 640px) { .doc-btn .lbl { display:none; } }

        .doc-parts-strip { display:none; flex-wrap:wrap; gap:8px; padding:10px 14px;
            border-bottom:1px solid var(--border, #e2e8f0); background:#fbfcfe; }
        .doc-parts-strip.open { display:flex; }
        .doc-part-chip { display:inline-flex; align-items:center; gap:6px; font-size:12px;
            color:#475569; background:#fff; border:1px solid var(--border, #e2e8f0);
            border-radius:999px; padding:4px 10px; text-decoration:none; max-width:100%; }
        .doc-part-chip:hover { border-color:#c4b5fd; color:#6d28d9; }
        .doc-part-chip .nm { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:220px; }
        .doc-part-chip i, .doc-part-chip svg { width:13px; height:13px; flex-shrink:0; }
        #doc-progress { font-size:12px; color:#64748b; padding:0 14px; min-height:0; }
        #doc-progress:not(:empty) { padding:6px 14px 0; min-height:16px; }

        /* Ctrl+P со страницы печатает ТОЛЬКО сам документ, без вложений —
           так же, как кнопка «Печать документа». */
        @media print {
            body * { visibility:hidden !important; }
            #doc-flow .doc-sheet[data-part="print-form"],
            #doc-flow .doc-sheet[data-part="print-form"] * { visibility:visible !important; }
            #doc-flow .doc-sheet[data-part="print-form"] {
                position:absolute; left:0; top:0; width:100%;
                max-width:none; box-shadow:none; border-radius:0; }
        }

        .didox-actions-stack .btn-modern { width:100%; justify-content:center; }
        #sign-progress { font-size:13px; color:#64748b; margin-top:10px; min-height:18px; }
        .decide-btns { display:flex; gap:10px; margin-top:10px; }
        .decide-btns .btn-modern { flex:1; justify-content:center; }
        /* Защита от горизонтального скролла (широкая печатная форма Didox) */
        #external-doc-card, #external-doc-card * { max-width: 100%; }
        #external-doc-card { overflow-x: hidden; box-sizing: border-box; }
        .content-wrapper-modern { max-width: 100%; overflow-x: hidden; }
        .didox-card-grid { min-width: 0; }
        #print-form-holder { max-width: 100%; overflow: hidden; }
        #print-form-holder iframe { max-width: 100%; width: 100%; }
        .select2-container { max-width: 100%; }

        /* ── Карточки выбора согласующих (самодостаточные стили) ── */
        .didox-search-box { position:relative; margin-bottom:10px; }
        .didox-search-box i, .didox-search-box svg { position:absolute; left:12px; top:50%;
            transform:translateY(-50%); width:16px; height:16px; color:#94a3b8; pointer-events:none; }
        .didox-search-box input { width:100%; padding:9px 12px 9px 38px; font-size:14px;
            border:1px solid var(--border, #e2e8f0); border-radius:10px; outline:none;
            transition:border-color .15s; background:#fff; }
        .didox-search-box input:focus { border-color:#8b5cf6; }

        .didox-approver-card { display:block; cursor:pointer; margin-bottom:8px; }
        .didox-approver-card input { display:none; }
        .didox-approver-card .card-inner { display:flex; align-items:center; gap:12px;
            padding:10px 12px; border:1.5px solid var(--border, #e2e8f0); border-radius:12px;
            background:#fff; transition:border-color .15s, background .15s; }
        .didox-approver-card:hover .card-inner { border-color:#c4b5fd; }
        .didox-approver-card input:checked + .card-inner { border-color:#8b5cf6; background:#f5f3ff; }

        .didox-approver-card .avatar { width:38px; height:38px; border-radius:50%; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            background:#ede9fe; color:#7c3aed; }
        .didox-approver-card .avatar i, .didox-approver-card .avatar svg { width:18px; height:18px; }
        .didox-approver-card input:checked + .card-inner .avatar { background:#8b5cf6; color:#fff; }

        .didox-approver-card .info { flex:1; min-width:0; }
        .didox-approver-card .info .name { display:block; font-size:14px; font-weight:600;
            color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .didox-approver-card .info .position { display:block; font-size:12px; color:#64748b;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        .didox-approver-card .tick { width:22px; height:22px; border-radius:50%; flex-shrink:0;
            border:1.5px solid var(--border, #e2e8f0); display:flex; align-items:center;
            justify-content:center; color:transparent; transition:all .15s; }
        .didox-approver-card .tick i, .didox-approver-card .tick svg { width:14px; height:14px; }
        .didox-approver-card input:checked + .card-inner .tick {
            background:#8b5cf6; border-color:#8b5cf6; color:#fff; }

    </style>

    <div class="document-page-modern" style="max-width:100%; overflow-x:hidden;"
         id="external-doc-card"
         data-doc-id="{{ $doc->id }}"
         data-didox-id="{{ $doc->didox_id }}">

        <!-- Заголовок -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-text"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ $typeLabels[$doc->doc_type] ?? $doc->doc_type }} № {{ $doc->doc_number ?? '—' }}</h1>
                        <p>{{ $org->name_ru ?? $org->name_uz }}
                            · от {{ optional($doc->doc_date)->format('d.m.Y') ?? '—' }}
                            · контрагент {{ $doc->partner_tin ?? $doc->seller_tin ?? '—' }}</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="{{ route('external-docs.index') }}" class="breadcrumb-item">
                        <span>Внешний документооборот</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">№ {{ $doc->doc_number ?? $doc->id }}</span>
                </nav>
            </div>
        </div>

        <div id="alertContainer" class="alert-container-modern"></div>

        <div class="content-wrapper-modern">

            <!-- Статусная строка -->
            <div class="filters-card-modern" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding:16px 20px;">
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <span id="list-status-badge">
                        @if($doc->isRejected())
                            <span class="didox-status st-notsigned"><i data-lucide="ban"></i> Отказано в Didox</span>
                        @elseif($doc->list_status === 'signed')
                            <span class="didox-status st-signed"><i data-lucide="check-circle"></i> Подписан</span>
                        @elseif($doc->list_status === 'on_internal_review')
                            <span class="didox-status st-review"><i data-lucide="clock"></i> На внутреннем согласовании</span>
                        @else
                            <span class="didox-status st-notsigned"><i data-lucide="circle-alert"></i> Не подписан</span>
                        @endif
                    </span>
                    <span id="approval-status-badge">
                        @if($doc->approval_status === 'approved')
                            <span class="didox-status st-approved"><i data-lucide="check-check"></i> Прошёл внутреннее согласование</span>
                        @elseif($doc->approval_status === 'on_review')
                            <span class="didox-status st-review"><i data-lucide="users"></i> На согласовании</span>
                        @elseif($doc->list_status === 'signed')
                            {{-- Подписан без запуска внутреннего согласования — оно и не требовалось --}}
                            <span class="didox-status st-approved"><i data-lucide="check-circle"></i> Согласование не требовалось</span>
                        @else
                            <span class="didox-status st-pending"><i data-lucide="minus-circle"></i> Не отправлен на согласование</span>
                        @endif
                    </span>
                </div>

                @if(!$authorized && $canSign)
                    {{-- Предупреждение видят только ответственные лица (они подписывают) --}}
                    <span style="display:inline-flex; align-items:center; gap:8px; color:#a16207;">
                        <i data-lucide="alert-triangle"></i>
                        Нет авторизации в Didox —
                        <a href="{{ route('external-docs.index') }}" style="color:#7c3aed; font-weight:600;">войти →</a>
                    </span>
                @endif
            </div>

            <div class="didox-card-grid">

                <div class="didox-doc-col" style="min-width:0; display:flex; flex-direction:column; gap:1rem;">
                    @php
                        /*
                         | Состав документа в порядке печати: сначала печатная форма Didox,
                         | затем файл документа и вложения. Все части грузятся байтами
                         | с нашего домена — иначе pdf.js упирается в CORS storage-а Didox.
                         */
                        $docParts = [[
                            'key'  => 'print-form',
                            'name' => 'Печатная форма',
                            'url'  => route('external-docs.print-pdf', $doc->id),
                            'open' => route('external-docs.print-pdf', $doc->id),
                            'main' => true,
                        ]];
                        foreach ($attachments as $att) {
                            $docParts[] = [
                                'key'  => 'att-' . $att['id'],
                                'name' => $att['name'],
                                'url'  => route('external-docs.attachment-raw', [$doc->id, $att['id']]),
                                'open' => route('external-docs.file', [$doc->id, $att['id']]),
                                'main' => false,
                            ];
                        }
                    @endphp

                    <div class="didox-panel">
                        <div class="panel-head doc-head">
                            <span class="title"><i data-lucide="file-text"></i> Документ</span>
                            <div class="doc-toolbar">
                                @if(count($docParts) > 1)
                                    <button type="button" class="doc-btn" id="btn-toggle-parts"
                                            title="Состав документа">
                                        <i data-lucide="paperclip"></i>
                                        <span class="cnt">{{ count($docParts) }}</span>
                                    </button>
                                @endif
                                <button type="button" class="doc-btn" id="btn-view-mode"
                                        title="Вид: одна страница в области">
                                    <i data-lucide="rows-3"></i>
                                </button>
                                <button type="button" class="doc-btn" id="btn-load-print" title="Обновить">
                                    <i data-lucide="refresh-cw"></i>
                                </button>
                                <button type="button" class="doc-btn" id="btn-print-main"
                                        title="Печать: только сам документ, без вложений">
                                    <i data-lucide="printer"></i><span class="lbl">Документ</span>
                                </button>
                                @if(count($docParts) > 1)
                                    <button type="button" class="doc-btn is-primary" id="btn-print-all"
                                            title="Печать: документ вместе с вложениями, одним файлом">
                                        <i data-lucide="printer"></i><span class="lbl">С вложениями</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Состав документа: свёрнут по умолчанию, разворачивается скрепкой.
                             Сами файлы уже отрисованы ниже одним потоком. --}}
                        @if(count($docParts) > 1)
                            <div class="doc-parts-strip" id="doc-parts-strip">
                                @foreach($docParts as $part)
                                    <a class="doc-part-chip" href="{{ $part['open'] }}" target="_blank" rel="noopener">
                                        <i data-lucide="file-text"></i>
                                        <span class="nm">{{ $part['name'] }}</span>
                                        <i data-lucide="external-link"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div id="doc-progress"></div>

                        <div id="doc-flow">
                            <div id="print-placeholder">
                                <i data-lucide="loader-2" class="spin" style="width:48px;height:48px;"></i>
                                <p>Загрузка документа...</p>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- /левая колонка --}}

                <!-- Действия -->
                <div class="didox-actions-stack" style="display:flex; flex-direction:column; gap:1.5rem;">

                    @if($doc->isRejected())
                        <div class="didox-panel">
                            <div class="panel-body" style="display:flex; align-items:center; gap:8px; color:#b91c1c; font-size:14px;">
                                <i data-lucide="ban"></i> Документ отклонён в Didox — действия недоступны.
                            </div>
                        </div>
                    @endif

                    @php $isOutgoing = (int)($doc->owner ?? 0) === 1; @endphp
                        <!-- Подписать в Didox: ответственные лица.
                         Входящий — подпись с объединением; исходящий — подпись = отправка контрагенту. -->
                    @if($canSign && !$doc->isRejected())
                        <div class="didox-panel">
                            <div class="panel-head">
                                <span class="title"><i data-lucide="pen-tool"></i>
                                    {{ $isOutgoing ? 'Подписать и отправить контрагенту' : 'Подписать документ в Didox' }}
                                </span>
                            </div>
                            <div class="panel-body">
                                @if($doc->list_status === 'signed')
                                    <div style="display:flex; align-items:center; gap:8px; color:#15803d; font-size:14px;">
                                        <i data-lucide="check-circle"></i>
                                        {{ $isOutgoing ? 'Документ подписан и отправлен контрагенту.' : 'Документ подписан в Didox.' }}
                                    </div>
                                @elseif(!$authorized)
                                    <div style="display:flex; align-items:center; gap:8px; color:#a16207; font-size:13px;">
                                        <i data-lucide="alert-triangle"></i>
                                        <span>Нет авторизации в Didox —
                                        <a href="{{ route('external-docs.index') }}" style="color:#7c3aed; font-weight:600;">войти на странице списка →</a>
                                    </span>
                                    </div>
                                @else
                                    <p style="font-size:13px; color:#64748b; margin-bottom:12px;">
                                        @if($isOutgoing)
                                            Подписание Юр. ЭЦП через E-IMZO: выберите ключ — документ будет
                                            подписан и <b>отправлен контрагенту</b> через Didox.
                                        @else
                                            Подписание Юр. ЭЦП через E-IMZO: выберите ключ — документ будет
                                            подписан и отправлен в Didox.
                                        @endif
                                    </p>

                                    {{-- Форма по образцу проекта: партиал e-imzo сам грузит ключи в select[name=key] --}}
                                    <form name="testform" onsubmit="return false;">
                                        <div class="filter-item" style="margin-bottom:12px;">
                                            <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') ?? 'Ключ ЭЦП' }}</label>
                                            <label id="message" style="display:block; font-size:12px; color:#64748b;"></label>
                                            <select name="key" class="filter-select-modern select2-ecp"
                                                    style="width:100% !important;" onchange="cbChanged(this)"></select>
                                        </div>

                                        <button type="button" class="btn-modern btn-primary" id="btn-sign" style="width:100%;"
                                                onclick="{{ $isOutgoing ? 'signDidoxOutgoing()' : 'signDidox()' }}">
                                            <i data-lucide="pen-line"></i>
                                            {{ $isOutgoing ? 'Подписать и отправить' : 'Подписать в Didox' }}
                                        </button>

                                        {{-- Скрытые поля, которые читает e-imzo/наш signDidox --}}
                                        <input name="ecp_doc_type" value="didox_document" hidden/>
                                        <input name="ecp_doc_id" value="{{ $doc->id }}" hidden/>
                                        <input name="ecp_confirm_id" id="ecp_confirm_id" hidden/>
                                        <input name="ecp_r_text" id="ecp_r_text" hidden/>
                                        <label id="keyId" hidden></label>
                                        <textarea name="pkcs7" hidden></textarea>
                                    </form>

                                    <div id="sign-progress"></div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Внутреннее согласование -->
                    @if(($doc->list_status === 'signed' || $doc->isRejected()) && $doc->approvers->isEmpty())
                        {{-- Документ уже подписан/отклонён и согласование не запускалось — панель не нужна --}}
                    @else
                        <div class="didox-panel">
                            <div class="panel-head">
                                <span class="title"><i data-lucide="users"></i> Внутреннее согласование</span>
                            </div>
                            <div class="panel-body">
                                @if($doc->list_status === 'signed')
                                    {{-- Подписан, но согласующие были назначены ранее — только история, без выбора/действий --}}
                                    @foreach($doc->approvers as $a)
                                        <div class="approver-row">
                                        <span style="display:inline-flex; align-items:center; gap:8px;">
                                            <i data-lucide="user"></i> {{ $a->user->name ?? ('#'.$a->user_id) }}
                                        </span>
                                            <span>
                                            @if($a->status === 'approved')
                                                    <span class="didox-status st-approved"><i data-lucide="check"></i> согласовал</span>
                                                @elseif($a->status === 'rejected')
                                                    <span class="didox-status st-notsigned" title="{{ $a->comment }}"><i data-lucide="x"></i> отклонил</span>
                                                @else
                                                    <span class="didox-status st-pending"><i data-lucide="clock"></i> не завершил</span>
                                                @endif
                                        </span>
                                        </div>
                                    @endforeach
                                @elseif($doc->approvers->isEmpty() && !$doc->isRejected())
                                    @if($candidates->isEmpty())
                                        <div style="display:flex; align-items:center; gap:8px; color:#a16207; font-size:13px;">
                                            <i data-lucide="alert-triangle"></i>
                                            В вашей организации нет других сотрудников для согласования.
                                        </div>
                                    @else
                                        <label style="font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px; margin-bottom:8px;">
                                            <i data-lucide="user-plus"></i> Выбор согласующих ({{ $candidates->count() }})
                                        </label>

                                        <div class="didox-search-box">
                                            <i data-lucide="search"></i>
                                            <input type="text" id="approverSearch"
                                                   onkeyup="searchApproverFunction()" placeholder="{{ __('action.search') }}...">
                                        </div>

                                        <div id="approvers-list" style="max-height:340px; overflow-y:auto; padding-right:4px;">
                                            @foreach($candidates as $key => $c)
                                                <div class="select-user" @if($key >= 5) data-overflow="1" style="display:none;" @endif>
                                                    <label class="didox-approver-card">
                                                        <input type="checkbox" name="didox_agree[]" value="{{ $c->user_id }}">
                                                        <div class="card-inner">
                                                            <div class="avatar"><i data-lucide="user"></i></div>
                                                            <div class="info">
                                                                <span class="name">{{ $c->name }}</span>
                                                                <span class="position">{{ trim($c->department . ($c->department && $c->position ? ' — ' : '') . $c->position) ?: '—' }}</span>
                                                            </div>
                                                            <div class="tick"><i data-lucide="check"></i></div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if($candidates->count() > 5)
                                            <div style="font-size:12px; color:#94a3b8; margin:6px 0 10px;">
                                                Показаны первые 5 — остальных ищите через поиск.
                                            </div>
                                        @endif

                                        <button type="button" class="btn-modern btn-primary" id="btn-send-review" style="width:100%;">
                                            <i data-lucide="send"></i> Отправить на согласование
                                        </button>
                                    @endif
                                @else
                                    @foreach($doc->approvers as $a)
                                        <div class="approver-row">
                                        <span style="display:inline-flex; align-items:center; gap:8px;">
                                            <i data-lucide="user"></i> {{ $a->user->name ?? ('#'.$a->user_id) }}
                                        </span>
                                            <span>
                                            @if($a->status === 'approved')
                                                    <span class="didox-status st-approved" title="{{ $a->signed_at ? 'Подписано ЭЦП ' . $a->signed_at->format('d.m.Y H:i') : '' }}">
                                                    <i data-lucide="{{ $a->pkcs7 ? 'pen-line' : 'check' }}"></i> согласовал
                                                </span>
                                                @elseif($a->status === 'rejected')
                                                    <span class="didox-status st-notsigned" title="{{ $a->comment }}"><i data-lucide="x"></i> отклонил</span>
                                                @else
                                                    <span class="didox-status st-pending"><i data-lucide="clock"></i> ожидает</span>
                                                @endif
                                        </span>
                                        </div>
                                    @endforeach

                                    @if($myApproval && $myApproval->status === 'pending')
                                        <hr style="margin:14px 0;">

                                        {{-- Решение подписывается ЭЦП согласующего.
                                             Если на странице нет формы подписанта — рендерим свою testform
                                             (партиал e-imzo наполнит select ключами). --}}
                                        @if(!($canSign && $authorized && $doc->list_status !== 'signed'))
                                            <form name="testform" onsubmit="return false;">
                                                <div class="filter-item" style="margin-bottom:10px;">
                                                    <label><i data-lucide="key-round"></i> Ключ ЭЦП для подписи решения</label>
                                                    <label id="message" style="display:block; font-size:12px; color:#64748b;"></label>
                                                    <select name="key" class="filter-select-modern"
                                                            style="width:100% !important;" onchange="cbChanged(this)"></select>
                                                </div>
                                                <label id="keyId" hidden></label>
                                                <textarea name="pkcs7" hidden></textarea>
                                            </form>
                                        @endif

                                        <div class="filter-item">
                                            <label><i data-lucide="message-square"></i> Ваше решение</label>
                                            <textarea id="decide-comment" class="filter-input-modern" rows="2"
                                                      placeholder="Комментарий (обязателен при отклонении)"></textarea>
                                        </div>
                                        <div class="decide-btns">
                                            <button type="button" class="btn-modern btn-primary" data-decide="1">
                                                <i data-lucide="pen-line"></i> Подписать и согласовать
                                            </button>
                                            <button type="button" class="btn-modern btn-secondary" data-decide="0">
                                                <i data-lucide="x"></i> Подписать и отклонить
                                            </button>
                                        </div>
                                        <div id="decide-progress" style="font-size:13px; color:#64748b; margin-top:8px; min-height:16px;"></div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

    {{-- jQuery уже подключён глобально в layouts/head. Повторно НЕ грузим —
         иначе сбросятся плагины select2 и toast. --}}

    {{-- Скрипты E-IMZO проекта (e_imzo.js → CAPIWS, e_imzo_client.js → EIMZOClient).
         Партиал сам грузит ключи в форму testform и вызывает AppLoad по window.onload. --}}
    @php
        $needSignerForm  = $canSign && $authorized && $doc->list_status !== 'signed';
        $needApproverKey = $myApproval && $myApproval->status === 'pending';
    @endphp
    @if($needSignerForm || $needApproverKey)
        @include('e-imzo.e-imzo')
    @endif

    {{-- pdf.js — рендер страниц вложений в canvas (та же версия, что в free-sign). --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    {{-- Скрипт инлайн (паттерн work-docs): исполняется сразу при парсинге,
         не зависит от stack('scripts'). jQuery — из head; select2 ждётся
         внутри initDidoxSelect2 с ретраями. --}}
    <script>
        // Инициализация select2 с ожиданием загрузки плагина (он подключается
        // в layout ПОСЛЕ контента, поэтому в $(document).ready может быть ещё не готов).
        function initDidoxSelect2(attempt) {
            attempt = attempt || 0;
            if (typeof $.fn === 'undefined' || typeof $.fn.select2 === 'undefined') {
                if (attempt < 40) { setTimeout(function () { initDidoxSelect2(attempt + 1); }, 150); }
                // select2 не загрузился — оставляем нативный select
                return;
            }

            // Согласующие
            var $ap = $('.select2-approvers');
            if ($ap.length && !$ap.hasClass('select2-hidden-accessible')) {
                $ap.select2({
                    width: '100%',
                    placeholder: "{{ __('action.choose') }}",
                    allowClear: true,
                    minimumResultsForSearch: 5
                });
            }

            // ЭЦП-комбобокс (партиал e-imzo наполняет его ключами асинхронно)
            var $ecp = $('.select2-ecp');
            if ($ecp.length && $ecp.find('option').length && !$ecp.hasClass('select2-hidden-accessible')) {
                $ecp.select2({ width: '100%' });
            } else if ($ecp.length && !$ecp.find('option').length && attempt < 40) {
                // ключи ещё не пришли — повторим для ЭЦП
                setTimeout(function () { initDidoxSelect2(attempt + 1); }, 300);
            }
        }

        $(document).ready(function () {
            initDidoxSelect2(0);
        });

        function searchApproverFunction() {
            const filter = document.getElementById('approverSearch').value.toLowerCase().trim();
            const nodes = document.querySelectorAll('#approvers-list .select-user');
            nodes.forEach(function (node, i) {
                if (filter === '') {
                    // пустой поиск: первые 5 + все отмеченные
                    const checked = node.querySelector('input:checked');
                    node.style.display = (i < 5 || checked) ? 'block' : 'none';
                } else {
                    node.style.display = node.innerText.toLowerCase().includes(filter) ? 'block' : 'none';
                }
            });
        }

        function toastOrAlert(text, type) {
            try {
                if (window.$ && $.toast) {
                    $.toast({ text: text, icon: type || 'info', position: 'top-right', hideAfter: 6000 });
                    return;
                }
            } catch (e) {}
            var colors = { warning: '#f59e0b', error: '#ef4444', success: '#22c55e', info: '#8b5cf6' };
            var b = document.createElement('div');
            b.style.cssText = 'position:fixed; top:18px; right:18px; z-index:99999; max-width:360px;'
                + 'background:#fff; border-left:4px solid ' + (colors[type] || colors.info) + ';'
                + 'box-shadow:0 8px 24px rgba(0,0,0,.14); border-radius:10px; padding:12px 16px;'
                + 'font-size:14px; color:#1e293b;';
            b.textContent = text;
            b.onclick = function () { b.remove(); };
            document.body.appendChild(b);
            setTimeout(function () { b.remove(); }, 6000);
        }

        /* Уведомления при открытии карточки — автономно, без jQuery-ready */
        document.addEventListener('DOMContentLoaded', function () {
            @if($myApproval && $myApproval->status === 'pending')
            toastOrAlert('Документ ожидает вашего согласования — решение подписывается ЭЦП.', 'info');
            @elseif(!$authorized && $canSign && $doc->list_status !== 'signed')
            toastOrAlert('Сессия Didox истекла или отсутствует — для подписания войдите на странице списка.', 'warning');
            @elseif($doc->list_status === 'signed' && $doc->approval_status === 'approved')
            toastOrAlert('Документ подписан в Didox и прошёл внутреннее согласование.', 'success');
            @endif
        });

        (function () {
            const csrf = '{{ csrf_token() }}';
            // ИНН/организация определяются на сервере по активной организации пользователя

            const jsonPost = (url, body) => fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(body || {}),
            }).then(r => r.json());

            const progress = (msg) => { const el = document.getElementById('sign-progress'); if (el) el.textContent = msg; };

            const LOGIN_URL = '{{ route('external-docs.index') }}';
            const isDidoxAuthError = (msg) => /re-authentication|token expired|token.*missing|авторизац/i.test(String(msg || ''));

            /* ══════════════════ Просмотрщик документа ══════════════════
               Печатная форма и вложения идут одним непрерывным потоком листов,
               без единого iframe. Печатная форма — HTML из Didox в shadow root
               (это ровно сам документ, без вложений), вложения — pdf.js в canvas.
               Байты вложений берём с нашего домена (attachment-raw): у storage
               Didox нет CORS-заголовков. */
            const DOC_PARTS      = @json($docParts);
            const ATT_PARTS      = DOC_PARTS.slice(1);
            const PRINT_PDF_URL  = '{{ route('external-docs.print-pdf', $doc->id) }}';
            const PRINT_HTML_URL = '{{ route('external-docs.print', $doc->id) }}';
            const DOC_TITLE      = @json(($typeLabels[$doc->doc_type] ?? $doc->doc_type) . ' № ' . ($doc->doc_number ?? $doc->id));
            const PRINT_CONCURRENCY = 3;  // сколько страниц растеризуем одновременно

            const flow     = document.getElementById('doc-flow');
            const docProg  = document.getElementById('doc-progress');
            const pdfDocs  = new Map();   // part.key → PDFDocumentProxy (переиспользуем при печати)
            const printImages = new Map();// part.key → [blob-URL страниц] для печати
            let   printFormHtml   = null; // сырой HTML печатной формы Didox
            let   printFormCached = null;
            let   loadingDoc      = false;

            const setDocProgress = (msg) => { if (docProg) docProg.textContent = msg || ''; };

            /** Ждём загрузки внешней библиотеки (скрипты идут после разметки). */
            function waitFor(check, tries) {
                tries = tries || 60;
                return new Promise((resolve, reject) => {
                    (function tick(n) {
                        if (check()) return resolve();
                        if (n <= 0) return reject(new Error('Библиотека не загрузилась'));
                        setTimeout(() => tick(n - 1), 150);
                    })(tries);
                });
            }

            function partSeparator(text) {
                const sep = document.createElement('div');
                sep.className = 'doc-part-sep';
                sep.textContent = text;
                return sep;
            }

            function partError(text) {
                const box = document.createElement('div');
                box.className = 'doc-part-error';
                box.textContent = text;
                return box;
            }

            /* ── Печатная форма (сам документ) ───────────────────────────── */

            /** HTML печатной формы из Didox; кэшируется на время жизни страницы. */
            async function fetchPrintHtml() {
                if (printFormHtml !== null) return printFormHtml;

                const data = await fetch(PRINT_HTML_URL, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json());

                if (!data.success) {
                    throw new Error(isDidoxAuthError(data.message)
                        ? 'сессия Didox истекла, сохранённой копии формы ещё нет'
                        : (data.message || 'нет данных'));
                }

                printFormHtml   = typeof data.data === 'string' ? data.data : (data.data?.data ?? '');
                printFormCached = data.cached ? (data.cached_at || '') : null;
                return printFormHtml;
            }

            /**
             * Рисует печатную форму. Shadow root даёт изоляцию стилей Didox
             * в обе стороны — при этом это НЕ iframe и содержимое печатается.
             */
            async function renderPrintFormPart() {
                try {
                    const html = await fetchPrintHtml();

                    if (printFormCached) {
                        flow.appendChild(partSeparator('Сохранённая копия от ' + printFormCached));
                    }

                    const sheet = document.createElement('div');
                    sheet.className = 'doc-sheet';
                    sheet.dataset.part = 'print-form';
                    const host = document.createElement('div');
                    host.className = 'doc-html-host';
                    sheet.appendChild(host);
                    flow.appendChild(sheet);

                    host.attachShadow({ mode: 'open' }).innerHTML =
                        '<style>:host{display:block;background:#fff;color:#0f172a;padding:24px;'
                        + 'font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.45;}'
                        + 'table{border-collapse:collapse;max-width:100%;}'
                        + 'img{max-width:100%;height:auto;}</style>'
                        + html;

                    return true;
                } catch (e) {
                    // Фолбэк — PDF печатной формы через pdf.js
                    try {
                        await renderPdfPart({ key: 'print-form', name: 'Печатная форма', url: PRINT_PDF_URL }, 'print-form');
                        return true;
                    } catch (e2) {
                        flow.appendChild(partError('Печатная форма недоступна: ' + e.message));
                        return false;
                    }
                }
            }

            /* ── Вложения ────────────────────────────────────────────────── */

            /** PDF части: из кэша или скачиваем и разбираем. */
            async function getPdf(part) {
                if (pdfDocs.has(part.key)) return pdfDocs.get(part.key);

                const resp = await fetch(part.url, { credentials: 'same-origin' });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);

                const pdf = await pdfjsLib.getDocument({ data: await resp.arrayBuffer() }).promise;
                pdfDocs.set(part.key, pdf);
                return pdf;
            }

            /** Рисует все страницы одного PDF в поток документа. */
            async function renderPdfPart(part, partAttr) {
                const pdf = await getPdf(part);

                for (let p = 1; p <= pdf.numPages; p++) {
                    const page = await pdf.getPage(p);
                    const base = page.getViewport({ scale: 1 });

                    // Масштаб под ширину колонки. Верхняя граница 2× — выше растёт
                    // только память под canvas (≈8 МБ на страницу A4), не читаемость.
                    // Для печати страницы перерисовываются отдельно, в высоком качестве.
                    const boxWidth = Math.min(flow.clientWidth - 24, 940) || 800;
                    const ratio    = Math.min(window.devicePixelRatio || 1, 1.5);
                    const scale    = Math.max(1, Math.min(2, (boxWidth / base.width) * ratio));
                    const vp       = page.getViewport({ scale: scale });

                    const sheet  = document.createElement('div');
                    sheet.className = 'doc-sheet';
                    sheet.dataset.part = partAttr || part.key;
                    const canvas = document.createElement('canvas');
                    canvas.width  = Math.floor(vp.width);
                    canvas.height = Math.floor(vp.height);
                    sheet.appendChild(canvas);
                    flow.appendChild(sheet);

                    await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
                    setDocProgress(part.name + ': стр. ' + p + ' из ' + pdf.numPages);
                }
            }

            /* ── Сборка потока ───────────────────────────────────────────── */

            window.loadDocumentFlow = loadDocumentFlow;
            async function loadDocumentFlow() {
                if (loadingDoc) return;
                loadingDoc = true;

                pdfDocs.clear();
                printImages.forEach(urls => urls.forEach(u => { try { URL.revokeObjectURL(u); } catch (e) {} }));
                printImages.clear();
                printFormHtml = null;
                flow.innerHTML = '<div id="print-placeholder"><i data-lucide="loader-2" class="spin"'
                    + ' style="width:48px;height:48px;"></i><p>Загрузка документа...</p></div>';
                if (typeof lucide !== 'undefined') lucide.createIcons();

                try {
                    await waitFor(() => typeof pdfjsLib !== 'undefined');
                    pdfjsLib.GlobalWorkerOptions.workerSrc =
                        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                } catch (e) {
                    flow.innerHTML = '';
                    flow.appendChild(partError('Не удалось загрузить просмотрщик PDF. Проверьте доступ к сети.'));
                    loadingDoc = false;
                    return;
                }

                flow.innerHTML = '';

                setDocProgress('Загрузка печатной формы…');
                await renderPrintFormPart();

                for (const part of ATT_PARTS) {
                    flow.appendChild(partSeparator(part.name));
                    try {
                        setDocProgress('Загрузка: ' + part.name + '…');
                        await renderPdfPart(part);
                    } catch (e) {
                        flow.appendChild(partError('Не удалось загрузить «' + part.name + '»: ' + e.message));
                    }
                }

                setDocProgress('');
                if (!flow.children.length) {
                    flow.appendChild(partError('Документ пока недоступен для просмотра.'));
                }
                updatePageHeight();   // высота области зависит от размера листа
                loadingDoc = false;
            }

            /* ── Печать ──────────────────────────────────────────────────────
               Оба режима собирают ОДИН документ в отдельном окне и вызывают
               печать. «Документ» — только печатная форма; «С вложениями» —
               она же плюс страницы вложений. Дублирования нет: источник
               основной части всегда один и тот же HTML печатной формы. */

            /**
             * Страницы части → картинки печатного качества (blob-URL).
             *
             * Три вещи, из-за которых раньше печать собиралась медленно:
             *  - последовательный рендер: pdf.js держит воркер, пока canvas ждёт;
             *    теперь страницы идут пулом по PRINT_CONCURRENCY;
             *  - toDataURL: синхронно блокирует поток и раздувает данные на треть
             *    base64, из-за чего document.write в окне печати ещё и долго парсил;
             *    теперь toBlob + createObjectURL — в окно уходит короткая ссылка;
             *  - фиксированный масштаб 3.2: пикселей квадратично много. Масштаб
             *    подбирается под объём документа (см. resolvePrintScale).
             * Результат кэшируется — повторная печать открывается мгновенно.
             */
            async function partToImages(part, scale, onPage) {
                if (printImages.has(part.key)) return printImages.get(part.key);

                const pdf  = await getPdf(part);
                const urls = new Array(pdf.numPages);

                let next = 1;
                let done = 0;

                const worker = async () => {
                    while (true) {
                        const p = next++;
                        if (p > pdf.numPages) return;

                        const page   = await pdf.getPage(p);
                        const vp     = page.getViewport({ scale: scale });
                        const canvas = document.createElement('canvas');
                        canvas.width  = Math.floor(vp.width);
                        canvas.height = Math.floor(vp.height);

                        await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;

                        const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.85));
                        urls[p - 1] = URL.createObjectURL(blob);

                        canvas.width = canvas.height = 0;   // освобождаем память сразу
                        if (onPage) onPage(++done, pdf.numPages);
                    }
                };

                await Promise.all(
                    Array.from({ length: Math.min(PRINT_CONCURRENCY, pdf.numPages) }, worker)
                );

                printImages.set(part.key, urls);
                return urls;
            }

            /** Сколько всего страниц предстоит растеризовать. */
            async function countPrintPages(parts) {
                let total = 0;
                for (const part of parts) {
                    try { total += (await getPdf(part)).numPages; } catch (e) { /* пропускаем */ }
                }
                return total;
            }

            /**
             * Масштаб печати под объём: для короткого вложения не жалко 2.5×
             * (≈180 dpi), для многостраничного важнее скорость.
             */
            function resolvePrintScale(totalPages) {
                if (totalPages > 30) return 1.6;
                if (totalPages > 12) return 2.0;
                return 2.5;
            }

            /** Вклеивает печатные стили и страницы-картинки в HTML печатной формы. */
            function composePrintDocument(mainHtml, images) {
                const style = '<style>@page{margin:8mm}'
                    + 'body{margin:0}'
                    + '.edo-att-page{display:block;page-break-before:always;break-before:page;}'
                    + '.edo-att-page img{display:block;width:100%;height:auto;}'
                    + '</style>';

                const imgs = images.map(src => '<div class="edo-att-page"><img src="' + src + '"></div>').join('');

                let doc = (mainHtml && mainHtml.trim())
                    ? mainHtml
                    : '<!doctype html><html><head><meta charset="utf-8"><title></title></head><body></body></html>';

                doc = /<\/head>/i.test(doc) ? doc.replace(/<\/head>/i, style + '</head>') : (style + doc);
                doc = /<\/body>/i.test(doc) ? doc.replace(/<\/body>/i, imgs + '</body>')  : (doc + imgs);

                return doc;
            }

            /** Ждём, пока в окне печати догрузятся все картинки. */
            function waitWindowImages(w) {
                return new Promise((resolve) => {
                    const check = () => {
                        const imgs = Array.from(w.document.images || []);
                        let left = imgs.filter(i => !i.complete).length;
                        if (!left) return resolve();

                        const done = () => { if (--left <= 0) resolve(); };
                        imgs.forEach((i) => {
                            if (i.complete) return;
                            i.addEventListener('load',  done, { once: true });
                            i.addEventListener('error', done, { once: true });
                        });
                        setTimeout(resolve, 15000);   // страховка от зависания
                    };

                    if (w.document.readyState === 'complete') check();
                    else w.addEventListener('load', check, { once: true });
                });
            }

            let printing = false;

            async function printDocument(withAttachments) {
                if (printing) return;
                printing = true;

                const btn = document.getElementById(withAttachments ? 'btn-print-all' : 'btn-print-main');
                if (btn) btn.disabled = true;

                // Окно открываем СРАЗУ, синхронно по клику — иначе браузер
                // посчитает его непрошеным попапом и заблокирует.
                const w = window.open('', '_blank');
                if (!w) {
                    toastOrAlert('Разрешите всплывающие окна для этого сайта — печать открывается в отдельном окне.', 'warning');
                    if (btn) btn.disabled = false;
                    printing = false;
                    return;
                }

                const printWindowStub = (text) =>
                    '<!doctype html><meta charset="utf-8"><title>' + DOC_TITLE + '</title>'
                    + '<div id="edo-wait" style="font:14px system-ui,sans-serif;color:#475569;padding:32px">'
                    + text + '</div>';

                w.document.write(printWindowStub('Подготовка к печати…'));

                const showWait = (text) => {
                    setDocProgress(text);
                    try {
                        const el = w.document.getElementById('edo-wait');
                        if (el) el.textContent = text;
                    } catch (e) { /* окно закрыли */ }
                };

                try {
                    let mainHtml = null;
                    try { mainHtml = await fetchPrintHtml(); } catch (e) { /* ниже — картинками */ }

                    // Части, которые придётся растеризовать
                    const rasterParts = [];
                    if (!mainHtml) {
                        // Печатная форма недоступна как HTML — берём её страницы из PDF
                        rasterParts.push({ key: 'print-form', name: 'Печатная форма', url: PRINT_PDF_URL });
                    }
                    if (withAttachments) rasterParts.push(...ATT_PARTS);

                    const totalPages = await countPrintPages(rasterParts);
                    const scale      = resolvePrintScale(totalPages);

                    const images = [];
                    let ready = 0;

                    for (const part of rasterParts) {
                        try {
                            const urls = await partToImages(part, scale, (n, of) => {
                                showWait('Подготовка страниц: ' + (ready + n)
                                    + (totalPages ? ' из ' + totalPages : '') + '…');
                            });
                            ready += urls.length;
                            images.push(...urls);
                        } catch (e) {
                            toastOrAlert('«' + part.name + '» не попало в печать: ' + e.message, 'warning');
                        }
                    }

                    showWait('Открываем окно печати…');

                    w.document.open();
                    w.document.write(composePrintDocument(mainHtml, images));
                    w.document.close();

                    await waitWindowImages(w);
                    w.focus();
                    w.print();
                } catch (e) {
                    try { w.close(); } catch (e2) {}
                    toastOrAlert('Не удалось подготовить печать: ' + e.message, 'error');
                } finally {
                    setDocProgress('');
                    if (btn) btn.disabled = false;
                    printing = false;
                }
            }

            document.getElementById('btn-print-main')?.addEventListener('click', () => printDocument(false));
            document.getElementById('btn-print-all')?.addEventListener('click',  () => printDocument(true));

            document.getElementById('btn-toggle-parts')?.addEventListener('click', function () {
                document.getElementById('doc-parts-strip')?.classList.toggle('open');
            });

            /* ── Переключатель вида ──────────────────────────────────────────
               По умолчанию — сплошная лента: каждая страница занимает свою
               область, скроллится вся карточка. Включённый режим ограничивает
               просмотрщик высотой одного листа и скроллит внутри него. */
            const VIEW_MODE_KEY = 'didox_doc_view_mode';
            const viewBtn = document.getElementById('btn-view-mode');

            let pagedMode = false;
            try { pagedMode = localStorage.getItem(VIEW_MODE_KEY) === 'paged'; } catch (e) {}

            /** Высота области = высота первого листа, но не выше 85% экрана. */
            function updatePageHeight() {
                if (!pagedMode) return;

                // Меряем лист БЕЗ ограничения по высоте: если контейнер уже
                // ограничен, измерение вернёт сжатую высоту и режим схлопнется.
                const wasPaged = flow.classList.contains('mode-paged');
                if (wasPaged) flow.classList.remove('mode-paged');
                flow.style.removeProperty('--doc-page-h');

                const sheet   = flow.querySelector('.doc-sheet');
                const natural = sheet ? sheet.offsetHeight + 32 : 0;

                if (wasPaged) flow.classList.add('mode-paged');

                const cap    = Math.round(window.innerHeight * 0.85);
                const height = (natural > 200) ? Math.min(natural, cap) : cap;

                flow.style.setProperty('--doc-page-h', height + 'px');
            }

            function applyViewMode() {
                flow.classList.toggle('mode-paged', pagedMode);
                if (viewBtn) {
                    viewBtn.classList.toggle('is-primary', pagedMode);
                    viewBtn.title = pagedMode
                        ? 'Вид: сплошная лента страниц'
                        : 'Вид: одна страница в области';
                }
                if (pagedMode) updatePageHeight();
                else flow.style.removeProperty('--doc-page-h');
            }

            viewBtn?.addEventListener('click', function () {
                pagedMode = !pagedMode;
                try { localStorage.setItem(VIEW_MODE_KEY, pagedMode ? 'paged' : 'flow'); } catch (e) {}
                applyViewMode();
                flow.scrollTop = 0;
            });

            let resizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(updatePageHeight, 200);
            });

            applyViewMode();

            document.getElementById('btn-load-print')?.addEventListener('click', loadDocumentFlow);

            // Авто-загрузка при открытии страницы.
            // Согласующим личный токен Didox не нужен — сервер использует токен организации.
            loadDocumentFlow();

            /*
             * Подписание в Didox.
             * Ключи E-IMZO грузит штатный партиал проекта (наполняет select[name=key]
             * в форме testform, та же схема, что в подписании протоколов).
             * signDidox() использует EIMZOClient напрямую, но вместо внутреннего
             * ecp-sign-doc гонит Didox-поток: tosign, createPkcs7, timestamp, sign.
             */

            /* Показывает пользователю, что сессия Didox истекла, со ссылкой на вход. */
            function didoxAuthExpired(el) {
                toastOrAlert('Сессия Didox истекла — требуется вход.', 'warning');
                if (el) {
                    el.innerHTML = 'Сессия Didox истекла. <a href="' + LOGIN_URL + '">Перейти ко входу →</a>';
                }
            }

            /* Низкоуровневый помощник: подписать ГОТОВЫЙ base64 ключом из testform.
               Возвращает { pkcs7, signatureHex, signerSerial }. */
            window.eimzoSignBase64 = function (dataB64) {
                return new Promise(function (resolve, reject) {
                    const form = document.testform;
                    if (!form || !form.key || !form.key.value) {
                        reject(new Error('Выберите ключ ЭЦП.'));
                        return;
                    }
                    const opt = document.getElementById(form.key.value);
                    if (!opt) { reject(new Error('Ключ ЭЦП не найден.')); return; }
                    const vo = JSON.parse(opt.getAttribute('vo'));

                    EIMZOClient.loadKey(vo, function (keyId) {
                        CAPIWS.callFunction(
                            { plugin: 'pkcs7', name: 'create_pkcs7',
                                arguments: [dataB64, keyId, 'no'] },
                            function (ev, data) {
                                if (data.success) {
                                    resolve({ pkcs7: data.pkcs7_64, signatureHex: data.signature_hex,
                                        signerSerial: data.signer_serial_number || null });
                                } else {
                                    reject(new Error(data.reason || 'create_pkcs7 неуспешен'));
                                }
                            },
                            function (e) { reject(new Error('E-IMZO: ' + e)); }
                        );
                    }, function (e, r) { reject(new Error(r || e || 'loadKey error')); });
                });
            };

            /* Помощник: подписать текстовую строку (кодирует в base64 сам). */
            window.eimzoSignData = function (dataToSign) {
                return window.eimzoSignBase64(window.btoa(unescape(encodeURIComponent(dataToSign))));
            };

            window.signDidox = async function () {
                const form = document.testform;
                if (!form || !form.key || !form.key.value) {
                    toastOrAlert('Выберите ключ ЭЦП.', 'warning');
                    return;
                }

                const btn = document.getElementById('btn-sign');
                btn.disabled = true;

                /*
                 * Поток подписания ВХОДЯЩЕГО документа (раздел 7 документации Didox,
                 * требуется E-IMZO версии 6.3.5+):
                 *  1. GET documentBase64 — base64 документа
                 *  2. create_pkcs7 из этого base64 (без повторного кодирования)
                 *  3. timestamp к нашей подписи
                 *  4. toSign из деталей документа (подпись продавца)
                 *  5. signature/join: подпись продавца + наша
                 *  6. объединённый pkcs7B64 → sign
                 */
                try {
                    progress('Шаг 1/6: получаем документ из Didox...');
                    const docB64Resp = await fetch('{{ route('external-docs.document-base64', $doc->id) }}', {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json());
                    if (!docB64Resp.success) throw new Error('documentBase64: ' + (docB64Resp.message || JSON.stringify(docB64Resp.error)));
                    const docB64 = typeof docB64Resp.data === 'string'
                        ? docB64Resp.data
                        : (docB64Resp.data?.data ?? docB64Resp.data?.documentBase64 ?? '');
                    if (!docB64) throw new Error('documentBase64: пустой ответ');

                    progress('Шаг 2/6: подписываем документ через E-IMZO...');
                    const { pkcs7, signatureHex } = await window.eimzoSignBase64(docB64);

                    progress('Шаг 3/6: прикрепляем timestamp...');
                    const ts = await jsonPost('{{ route('external-docs.timestamp') }}', { pkcs7: pkcs7, signatureHex: signatureHex });
                    if (!ts.success) throw new Error('timestamp: ' + JSON.stringify(ts.error ?? ts.message));
                    const mySignature = ts.data?.timeStampTokenB64;
                    if (!mySignature) throw new Error('timestamp: пустой timeStampTokenB64');

                    progress('Шаг 4/6: получаем подпись продавца (toSign)...');
                    const toSignResp = await fetch('{{ route('external-docs.tosign-value', $doc->id) }}', {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json());
                    if (!toSignResp.success) throw new Error('toSign: ' + (toSignResp.message || 'нет данных'));

                    progress('Шаг 5/6: объединяем подписи...');
                    const joined = await jsonPost('{{ route('external-docs.signature-join') }}', {
                        signature1: toSignResp.toSign,
                        signature2: mySignature,
                    });
                    if (!joined.success) throw new Error('join: ' + JSON.stringify(joined.error ?? joined.message));
                    const pkcs7B64 = joined.data?.pkcs7B64 ?? joined.data?.pkcs7b64 ?? joined.data?.timeStampTokenB64;
                    if (!pkcs7B64) throw new Error('join: пустой pkcs7B64');

                    progress('Шаг 6/6: отправляем подпись в Didox...');
                    const signed = await jsonPost('{{ route('external-docs.sign', $doc->id) }}', { signature: pkcs7B64 });
                    if (!signed.success) throw new Error('sign: ' + JSON.stringify(signed.error ?? signed.message));

                    progress('Готово!');
                    toastOrAlert('Документ успешно подписан в Didox.', 'success');
                    setTimeout(() => location.reload(), 1000);
                } catch (e) {
                    if (isDidoxAuthError(e.message)) {
                        progress('');
                        didoxAuthExpired(document.getElementById('sign-progress'));
                    } else {
                        progress('');
                        toastOrAlert('Ошибка подписания: ' + e.message, 'error');
                    }
                    btn.disabled = false;
                }
            };

            /* Подписание ИСХОДЯЩЕГО документа (раздел 6 документации Didox):
             *  1. GET outgoing-sign-data — base64 значения json документа
             *  2. create_pkcs7 из этого base64
             *  3. timestamp
             *  4. sign — Didox отправляет документ контрагенту
             */
            window.signDidoxOutgoing = async function () {
                const form = document.testform;
                if (!form || !form.key || !form.key.value) {
                    toastOrAlert('Выберите ключ ЭЦП.', 'warning');
                    return;
                }

                const btn = document.getElementById('btn-sign');
                btn.disabled = true;

                try {
                    progress('Шаг 1/4: получаем данные документа...');
                    const dataResp = await fetch('{{ route('external-docs.outgoing-sign-data', $doc->id) }}', {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json());
                    if (!dataResp.success) throw new Error('sign-data: ' + (dataResp.message || 'нет данных'));
                    const jsonB64 = dataResp.data;
                    if (!jsonB64) throw new Error('sign-data: пустой ответ');

                    progress('Шаг 2/4: подписываем через E-IMZO...');
                    const { pkcs7, signatureHex } = await window.eimzoSignBase64(jsonB64);

                    progress('Шаг 3/4: прикрепляем timestamp...');
                    const ts = await jsonPost('{{ route('external-docs.timestamp') }}', { pkcs7: pkcs7, signatureHex: signatureHex });
                    if (!ts.success) throw new Error('timestamp: ' + JSON.stringify(ts.error ?? ts.message));
                    const signature = ts.data?.timeStampTokenB64;
                    if (!signature) throw new Error('timestamp: пустой timeStampTokenB64');

                    progress('Шаг 4/4: отправляем контрагенту...');
                    const signed = await jsonPost('{{ route('external-docs.sign', $doc->id) }}', { signature: signature });
                    if (!signed.success) throw new Error('sign: ' + JSON.stringify(signed.error ?? signed.message));

                    progress('Готово!');
                    toastOrAlert('Документ подписан и отправлен контрагенту.', 'success');
                    setTimeout(() => location.reload(), 1000);
                } catch (e) {
                    if (isDidoxAuthError(e.message)) {
                        progress('');
                        didoxAuthExpired(document.getElementById('sign-progress'));
                    } else {
                        progress('');
                        toastOrAlert('Ошибка отправки: ' + e.message, 'error');
                    }
                    btn.disabled = false;
                }
            };

            /* ── Отправка на внутреннее согласование ── */
            document.getElementById('btn-send-review')?.addEventListener('click', async function () {
                const ids = Array.from(document.querySelectorAll('input[name="didox_agree[]"]:checked')).map(cb => +cb.value);
                if (!ids.length) { toastOrAlert('Выберите хотя бы одного согласующего.', 'warning'); return; }

                this.disabled = true;
                const data = await jsonPost('{{ route('external-docs.send-review', $doc->id) }}', { user_ids: ids });
                if (data.success) {
                    toastOrAlert('Отправлено на согласование.', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastOrAlert('Ошибка: ' + (data.message || 'неизвестно'), 'error');
                    this.disabled = false;
                }
            });

            /* ── Решение согласующего ── */
            document.querySelectorAll('[data-decide]').forEach(btn => btn.addEventListener('click', async function () {
                const approved = this.dataset.decide === '1';
                const comment  = document.getElementById('decide-comment').value.trim();
                if (!approved && !comment) { toastOrAlert('При отклонении комментарий обязателен.', 'warning'); return; }

                const dprog = (t) => { const el = document.getElementById('decide-progress'); if (el) el.textContent = t; };
                this.disabled = true;

                try {
                    // Решение подписывается ЭЦП согласующего (юридически значимое подтверждение)
                    dprog('Подписываем решение через E-IMZO...');
                    const decisionPayload = JSON.stringify({
                        didox_id: '{{ $doc->didox_id }}',
                        doc_number: @json($doc->doc_number),
                        decision: approved ? 'approved' : 'rejected',
                        comment: comment,
                        user_id: {{ auth()->id() }},
                        datetime: new Date().toISOString(),
                    });

                    const sig = await window.eimzoSignData(decisionPayload);

                    dprog('Сохраняем решение...');
                    const data = await jsonPost('{{ route('external-docs.decide', $doc->id) }}', {
                        approved: approved,
                        comment: comment,
                        pkcs7: sig.pkcs7,
                        signature_hex: sig.signatureHex,
                        signer_serial: sig.signerSerial,
                    });

                    if (data.success) {
                        dprog('Готово!');
                        toastOrAlert('Решение подписано и сохранено.', 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        throw new Error(data.message || 'неизвестно');
                    }
                } catch (e) {
                    dprog('');
                    toastOrAlert('Ошибка: ' + e.message, 'error');
                    this.disabled = false;
                }
            }));
        })();
    </script>
@endsection
