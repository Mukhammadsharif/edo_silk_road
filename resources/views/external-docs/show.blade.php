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

        #print-form-holder { min-height:420px; max-width:100%; overflow-x:auto; }
        .spin { animation: didox-spin 1s linear infinite; }
        @keyframes didox-spin { from { transform:rotate(0); } to { transform:rotate(360deg); } }
        #print-form-holder iframe { width:100%; height:640px; border:0; }
        #print-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center;
            min-height:380px; color:#94a3b8; gap:10px; }

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
                    <!-- Печатная форма -->
                    <div class="didox-panel">
                        <div class="panel-head">
                            <span class="title"><i data-lucide="printer"></i> Печатная форма</span>
                            <button type="button" class="btn-modern btn-secondary" id="btn-load-print"
                                    onclick="if(typeof loadPrintForm==='function')loadPrintForm()">
                                <i data-lucide="refresh-cw"></i> Обновить
                            </button>
                        </div>
                        <div class="panel-body" id="print-form-holder">
                            <div id="print-placeholder">
                                <i data-lucide="loader-2" class="spin" style="width:48px;height:48px;"></i>
                                <p>Загрузка печатной формы...</p>
                            </div>
                        </div>
                    </div>

                    {{-- Файл документа и вложения — под основным документом --}}
                    @if(!empty($attachments))
                        <div class="filters-card-modern" style="padding:16px 20px;">
                            <div style="font-weight:600; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                                <i data-lucide="paperclip" style="width:18px;height:18px;"></i>
                                Файл документа и вложения
                            </div>

                            @foreach($attachments as $att)
                                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 0; border-bottom:1px solid #eef2f7;">
                                <span style="display:flex; align-items:center; gap:8px; font-size:14px; min-width:0;">
                                    <i data-lucide="file-text" style="width:18px;height:18px; flex-shrink:0;"></i>
                                    <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $att['name'] }}</span>
                                    @if($att['main'])
                                        <span style="font-size:11px; color:#7c3aed; flex-shrink:0;">(основной)</span>
                                    @endif
                                </span>
                                    <a href="{{ route('external-docs.file', [$doc->id, $att['id']]) }}" target="_blank"
                                       class="btn-modern" style="font-size:13px; flex-shrink:0;">
                                        <i data-lucide="external-link" style="width:15px;height:15px;"></i> Открыть
                                    </a>
                                </div>
                            @endforeach

                            {{-- Инлайн-просмотр: прямой storage-URL Didox (раздел показан только
                                 когда реальный файл есть — резолвится в контроллере). --}}
                            <iframe src="{{ $attachments[0]['file_url'] }}"
                                    title="Файл документа"
                                    style="width:100%; height:640px; border:0; border-radius:8px; margin-top:12px; background:#f8fafc;"></iframe>
                        </div>
                    @endif
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

            /* ── Печатная форма ── */
            window.loadPrintForm = loadPrintForm;
            async function loadPrintForm() {
                const holder = document.getElementById('print-form-holder');
                holder.innerHTML = '<div id="print-placeholder"><i data-lucide="loader-2" class="spin"></i><p>Загрузка печатной формы...</p></div>';
                if (typeof lucide !== 'undefined') lucide.createIcons();

                try {
                    const data = await fetch('{{ route('external-docs.print', $doc->id) }}', {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json());

                    if (data.success) {
                        const html = typeof data.data === 'string' ? data.data : (data.data?.data ?? '');
                        holder.innerHTML = '';
                        if (data.cached) {
                            const note = document.createElement('div');
                            note.style.cssText = 'font-size:12px; color:#94a3b8; padding:4px 0 8px; display:flex; align-items:center; gap:6px;';
                            note.innerHTML = '<i data-lucide="database"></i> Сохранённая копия от ' + (data.cached_at || '');
                            holder.appendChild(note);
                        }
                        const frame = document.createElement('iframe');
                        holder.appendChild(frame);
                        frame.srcdoc = html;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    } else if (isDidoxAuthError(data.message)) {
                        holder.innerHTML = '<div id="print-placeholder"><i data-lucide="lock"></i>'
                            + '<p>Сессия Didox истекла, а сохранённой копии формы ещё нет.<br>'
                            + '<a href="' + LOGIN_URL + '">Войти в Didox →</a></p></div>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    } else {
                        holder.innerHTML = '<div id="print-placeholder"><i data-lucide="file-x"></i><p>Ошибка: '
                            + (data.message || JSON.stringify(data.error) || 'нет данных') + '</p></div>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                } catch (e) {
                    holder.innerHTML = '<div id="print-placeholder"><i data-lucide="wifi-off"></i><p>Ошибка сети: ' + e.message + '</p></div>';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }

            document.getElementById('btn-load-print')?.addEventListener('click', loadPrintForm);

            // Авто-загрузка печатной формы при открытии страницы.
            // Согласующим личный токен Didox не нужен — сервер использует токен организации.
            loadPrintForm();

            /*
             * Подписание в Didox.
             * Ключи E-IMZO грузит штатный партиал проекта (наполняет select[name=key]
             * в форме testform, та же схема, что в подписании протоколов).
             * signDidox() использует EIMZOClient напрямую, но вместо внутреннего
             * ecp-sign-doc гонит Didox-поток: tosign, createPkcs7, timestamp, sign.
             */
            const progress = (msg) => { const el = document.getElementById('sign-progress'); if (el) el.textContent = msg; };

            const LOGIN_URL = '{{ route('external-docs.index') }}';
            const isDidoxAuthError = (msg) => /re-authentication|token expired|token.*missing|авторизац/i.test(String(msg || ''));

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
