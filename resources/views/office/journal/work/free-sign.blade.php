{{--
    ┌─────────────────────────────────────────────────────────────────────┐
    │  РЕДАКТОР РАЗМЕЩЕНИЯ ПОДПИСИ (free-sign)                             │
    │                                                                     │
    │  Это самодостаточный шаблон с ПРИМЕРАМИ данных (без моделей/роутов). │
    │  Работает из коробки на демо-PDF. Чтобы подключить реальные данные: │
    │                                                                     │
    │  1. Замени массив $demoSigners своими подписантами.                 │
    │  2. Пропиши реальные endpoints в window.FS_DEMO (pdfUrl, save...).  │
    │  3. При наличии backend — замени демо-PDF на загрузку по pdfUrl.    │
    │                                                                     │
    │  Ассеты:                                                            │
    │    public/css/free-sign/editor.css                                 │
    │    public/js/free-sign/editor.js                                   │
    └─────────────────────────────────────────────────────────────────────┘
--}}

@extends('layouts.app')

{{-- ВНИМАНИЕ: в layouts.app НЕТ @stack('styles') — @push('styles') уходит в никуда.
     Проект подключает CSS напрямую <link> между @extends и @section (см. execution/sign/view). --}}
{{-- Общий стиль страниц документов (шапка, левая/правая панели) --}}
<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
<link rel="stylesheet" href="{{ asset('css/free-sign/editor.css') }}">
<style>
    /* Высота шапки layout, которую вычитаем, чтобы редактор занял область контента. */
    .fs-editor { --fs-header-h: 64px; }
</style>


@section('title', 'Редактор размещения подписи')

<style>
    .wrapper .page-wrap .main-content {
        padding: 0 0 0 255px !important;

    }

    .fse-side-title {
        display: none !important;
    }

    .fse-coords-out {
        display: none;
    }
</style>
@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | ПРИМЕРЫ ДАННЫХ — замени своими
        |--------------------------------------------------------------------------
        | Подписанты документа. В реальном проекте построй этот массив из своих
        | моделей (например, FreeSignSigner) прямо в контроллере и передай во view.
        |
        | Поля:
        |   id    — уникальный id подписанта
        |   name  — ФИО (для карточки и штампа)
        |   info  — должность / email (вторая строка карточки)
        |   role  — Подписант | Согласующий | Утверждающий | Ознакамливающийся
        |   color — цвет карточки и элементов (hex)
        */
        // Сохранённые подписанты из БД (шаги маршрута). Пусто = новый документ.
        $demoSigners = $savedSigners ?? [];
        // (было демо-заполнение; теперь подписанты добавляются из списка кандидатов
        //  через форму «Добавить подписанта». Для теста можешь вернуть примеры.)

        /*
        |--------------------------------------------------------------------------
        | РОЛИ УЧАСТНИКОВ — из document_execution_roles + перевод из language_lines
        |--------------------------------------------------------------------------
        | Подставь $roles из своего контроллера. Каждый элемент: {id, title, label}
        | где label — локализованное имя. Ниже нормализуем к [id, label] + фолбэк.
        */
        $roleList = collect($roles ?? [])->map(function ($r) {
            return [
                'id'    => is_array($r) ? $r['id'] : $r->id,
                'label' => is_array($r) ? ($r['label'] ?? $r['title'] ?? '') : ($r->label ?? $r->title ?? ''),
            ];
        })->values();

        // Фолбэк, если $roles не передан из контроллера.
        if ($roleList->isEmpty()) {
            $roleList = collect([
                ['id' => 1, 'label' => 'Подписант'],
                ['id' => 2, 'label' => 'Согласующий'],
                ['id' => 3, 'label' => 'Утверждающий'],
                ['id' => 4, 'label' => 'Ознакамливающийся'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | КАНДИДАТЫ В ПОДПИСАНТЫ — люди компании
        |--------------------------------------------------------------------------
        | Подставь сюда $people из своего контроллера (см. запрос по org_unit_id).
        | Формат: [ ['id'=>users.id, 'name'=>'ФИО', 'position'=>'должность|null'], ... ]
        | Ниже — фолбэк-пример, чтобы форма работала до подключения контроллера.
        */
        $candidates = collect($people ?? [])->map(function ($p) {
            return [
                'id'       => is_array($p) ? $p['id'] : $p->id,
                'name'     => trim(preg_replace('/\s+/', ' ', is_array($p) ? ($p['full_name'] ?? $p['name'] ?? '') : ($p->full_name ?? ''))),
                'position' => is_array($p) ? ($p['position'] ?? null) : ($p->position ?? null),
            ];
        })->values();

        // Фолбэк-демо, если $people не передан из контроллера.
        if ($candidates->isEmpty()) {
            $candidates = collect([
                ['id' => 101, 'name' => 'Иванов Иван Иванович',   'position' => null],
                ['id' => 102, 'name' => 'Петров Пётр Петрович',   'position' => null],
                ['id' => 103, 'name' => 'Сидорова Анна Сергеевна','position' => null],
            ]);
        }

        /*
        | Уже сохранённые поля (при редактировании существующего документа).
        | Пусто для нового. Формат элемента:
        |   ['page'=>1,'type'=>'signature','signer_id'=>1,
        |    'x'=>0.1,'y'=>0.35,'width'=>0.2,'height'=>0.06,'params'=>[]]
        */
        // Сохранённые метки из БД. Пусто = ещё не расставляли.
        $demoFields = $savedFields ?? [];
    @endphp

    <div class="fs-editor">
        <div class="fse-app">
            <div class="fse-toolbar">
                <div class="fse-brand">EDO<span>LINE</span> · Редактор подписи</div>
                <input type="file" id="file" accept="application/pdf">
                <div class="fse-toolgroup" id="toolGroup">
                    <div class="fse-tool fse-active" data-tool="select" title="Выбор / перемещение">✥</div>
                    <div class="fse-tool" data-tool="draw" title="Нарисовать рамку">▭</div>
                </div>
                <div class="fse-spacer"></div>
                <div class="fse-zoom">
                    <button class="fse-iconbtn" id="zoomOut">−</button>
                    <div class="fse-val" id="zoomVal">100%</div>
                    <button class="fse-iconbtn" id="zoomIn">+</button>
                </div>
                <button class="fse-tbtn" id="clearBtn">Очистить</button>
                <button class="fse-tbtn fse-primary" id="saveBtn">Сохранить и отправить</button>
                {{-- Кнопка подписи: видна, когда сейчас моя очередь --}}
                @if(!empty($canSign) && $canSign)
                    {{-- Автор: подпись подтверждает отправку и запускает маршрут.
                         Участник: обычная подпись со штампом меток. --}}
                    <button class="fse-tbtn fse-primary" id="fsSignBtn" style="background:#20a37b;border-color:#20a37b;">
                        @if(!empty($isAuthorConfirm) && $isAuthorConfirm)
                            ✍ Подписать и отправить
                        @else
                            ✍ Подписать
                        @endif
                    </button>
                @endif
            </div>

            <div class="fse-stage-area" id="stageArea">
                <div class="fse-loading" id="loading">Готовлю демо-документ…</div>
            </div>

            <aside class="fse-sidebar">

                {{-- ═══════ ИНФОРМАЦИЯ О ДОКУМЕНТЕ (как в execution) ═══════ --}}
                <div class="fse-doc-info">
                    <div class="fse-doc-info-head">
                        <i data-lucide="file-text"></i>
                        <span>{{ $document->doc_name ?? '—' }}</span>
                    </div>
                    <div class="fse-doc-info-body">
                        @if($document->doc_number)
                            <div class="fse-di-row">
                                <span class="fse-di-label">{{ __('document.doc_number') }}</span>
                                <span class="fse-di-value">{{ $document->doc_number }}</span>
                            </div>
                        @endif
                        <div class="fse-di-row">
                            <span class="fse-di-label">{{ __('document.doc_date') }}</span>
                            <span class="fse-di-value">
                                {{ $document->doc_date ? \Carbon\Carbon::parse($document->doc_date)->format('d.m.Y') : '—' }}
                            </span>
                        </div>
                        <div class="fse-di-row">
                            <span class="fse-di-label">{{ __('status.exec_status') }}</span>
                            <span class="fse-di-badge">
                                {{ $journal->statusWork->lang($journal->statusWork->id) ?? '—' }}
                            </span>
                        </div>
                        @isset($originalFile)
                            <div class="fse-di-row">
                                <span class="fse-di-label">{{ __('document.document') }}</span>
                                <a href="{{ route('file.pre-view', $originalFile->id) }}" target="_blank" class="fse-di-link">
                                    {{ __('action.view') }} (оригинал)
                                </a>
                            </div>
                        @endisset
                    </div>
                </div>

                <p class="fse-side-title">Порядок подписания</p>
                <div class="fse-order-switch" id="orderSwitch">
                    <div class="fse-order-opt fse-active" data-order="sequential">
                        <span class="fse-order-ic">↓</span>
                        <span>По очереди</span>
                    </div>
                    <div class="fse-order-opt" data-order="parallel">
                        <span class="fse-order-ic">⇉</span>
                        <span>Параллельно</span>
                    </div>
                </div>

                <p class="fse-side-title">Подписанты <span class="fse-cnt" id="signerCnt">0</span></p>
                <div class="fse-signer-list" id="signerList"></div>

                <div class="fse-add-signer" id="addSignerBtn">＋ Добавить подписанта</div>
                <div class="fse-add-form" id="addForm">
                    <input type="text" id="afSearch" placeholder="Поиск сотрудника…" autocomplete="off">
                    <div class="fse-candidate-list" id="afCandidates"></div>
                    <select id="afRole">
                        @foreach($roleList as $role)
                            <option value="{{ $role['id'] }}">{{ $role['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="fse-row">
                        <div class="fse-tbtn" id="afCancel">Отмена</div>
                    </div>
                </div>

                <div class="fse-sep"></div>

                <p class="fse-side-title">Элементы подписи</p>
                <div class="fse-active-signer-hint" id="activeHint"><span class="fse-dot"></span><span id="activeHintText">—</span></div>
                <div class="fse-palette" id="palette"></div>

                <div class="fse-props" id="props">
                    <p class="fse-side-title">Выбранный элемент</p>
                    <div class="fse-prop-row"><label>Тип</label><span id="pType" style="font-weight:600"></span></div>
                    <div class="fse-prop-row"><label>Подписант</label><span id="pSigner"></span></div>
                    <div class="fse-prop-row"><label>Страница</label><span id="pPage"></span></div>
                    <div class="fse-prop-row"><label>X / Y</label><span id="pXY"></span></div>
                    <div class="fse-prop-row"><label>Ш × В</label><span id="pWH"></span></div>
                    <div class="fse-prop-row" id="fsRow"><label>Размер шрифта</label><input type="number" id="pFont" min="6" max="48" value="10"></div>
                    <div class="fse-prop-row" id="textRow"><label>Текст</label><input type="text" id="pText" placeholder="Текст"></div>
                    <button class="fse-tbtn fse-danger" id="delBtn" style="width:100%;justify-content:center;margin-top:6px;">🗑 Удалить элемент</button>
                </div>

                {{-- ═══════════ РЕЗОЛЮЦИЯ ═══════════ --}}
                @if(!empty($canResolve) && $canResolve)
                    <div class="fse-sep"></div>
                    <p class="fse-side-title">{{ __('action.create_resolution') }}</p>

                    <form method="POST" action="{{ route('free-sign.resolution.store', $journal->id) }}" id="fsResForm">
                        @csrf

                        {{-- Поиск исполнителей --}}
                        <div class="fse-res-search">
                            <input type="text" id="fsPerfSearch" class="fse-input"
                                   placeholder="{{ __('action.search') }} исполнителя…" autocomplete="off">
                            <div class="fse-cand-list" id="fsPerfResults"></div>
                        </div>

                        {{-- Выбранные исполнители --}}
                        <div class="fse-selected-perf" id="fsSelectedPerf">
                            <div class="fse-empty-hint" id="fsPerfEmpty">Исполнители не выбраны</div>
                        </div>

                        <div class="fse-prop-row">
                            <label>{{ __('field.resolution_type') }}</label>
                            <select name="r_type" class="fse-input-sm">
                                @foreach($resolutionTypes as $t)
                                    <option value="{{ $t->id }}">{{ $t->lang($t->lang_id) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fse-prop-row">
                            <label>{{ __('field.perf_term') }} <span style="color:#dc2626">*</span></label>
                            <input type="date" name="perf_term" class="fse-input-sm" required>
                        </div>

                        <div style="margin:8px 0;">
                            <label style="font-size:12px;color:var(--muted);">{{ __('field.resolution_text') }}</label>
                            <textarea name="ecp_r_text" class="fse-input" rows="3"></textarea>
                        </div>

                        <button type="submit" class="fse-tbtn fse-primary" style="width:100%;justify-content:center;">
                            {{ __('action.create_resolution') }}
                        </button>
                    </form>
                @endif

                {{-- ═══════════ РЕЗОЛЮЦИЯ (создана — показываем) ═══════════ --}}
                @if(!empty($showResolution) && $showResolution && !empty($directorResolution))
                    <div class="fse-sep"></div>
                    <p class="fse-side-title">{{ __('title.res_text') }}</p>
                    <div class="fse-res-view">
                        <div class="fse-di-row">
                            <span class="fse-di-label">{{ __('title.res_type') }}</span>
                            <span class="fse-di-badge">
                                {{ $directorResolution->resType->lang($directorResolution->resType->lang_id) ?? '—' }}
                            </span>
                        </div>
                        @if($directorResolution->res_term)
                            <div class="fse-di-row">
                                <span class="fse-di-label">{{ __('field.perf_term') }}</span>
                                <span class="fse-di-value">
                                    {{ \Carbon\Carbon::parse($directorResolution->res_term)->format('d.m.Y') }}
                                </span>
                            </div>
                        @endif
                        @if($directorResolution->res_text)
                            <div class="fse-res-text">{!! $directorResolution->res_text !!}</div>
                        @endif
                    </div>
                @endif

                {{-- ═══════════ ИСПОЛНИТЕЛИ ═══════════ --}}
                @if(!empty($showExecution) && $showExecution && !empty($performerSteps) && $performerSteps->count())
                    <div class="fse-sep"></div>
                    <p class="fse-side-title">{{ __('title.execution') }}
                        <span class="fse-cnt">{{ $performerSteps->count() }}</span>
                    </p>
                    <div class="fse-perf-list">
                        @foreach($performerSteps as $ps)
                            <div class="fse-perf-item">
                                <div class="fse-perf-name">{{ $ps->toUser->personal->fullName() ?? '—' }}</div>
                                <div class="fse-perf-meta">
                                    {{ $ps->resExecRole->lang($ps->resExecRole->lang_id) ?? '' }}
                                    @if((int)$ps->status === 4)
                                        <span class="fse-status fse-status-signed">✓ отчитался</span>
                                    @elseif((int)$ps->status === 5)
                                        <span class="fse-status fse-status-signed">✓ выполнено</span>
                                    @elseif((int)$ps->status === 2)
                                        <span class="fse-status fse-status-turn">● в работе</span>
                                    @else
                                        <span class="fse-status fse-status-wait">○ ожидает</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ═══════════ ОТЧЁТ ИСПОЛНИТЕЛЯ ═══════════ --}}
                @if(!empty($showExecution) && $showExecution && !empty($myExecStep) && (int)$myExecStep->status !== 5)
                    <div class="fse-sep"></div>
                    <p class="fse-side-title">Мой отчёт об исполнении</p>

                    <form method="POST" action="{{ route('free-sign.reply', $myExecStep->id) }}"
                          enctype="multipart/form-data" id="fsReplyForm">
                        @csrf
                        <textarea name="r_text" class="fse-input" rows="3"
                                  placeholder="Текст отчёта…" required></textarea>
                        <div style="margin:8px 0;">
                            <label style="font-size:11.5px;color:var(--muted);">Файлы (необязательно)</label>
                            <input type="file" name="doc_files[]" multiple
                                   class="fse-input" style="display:block;padding:6px;">
                        </div>
                        <button type="submit" class="fse-tbtn fse-primary" style="width:100%;justify-content:center;">
                            {{ (int)$myExecStep->status === 4 ? 'Обновить отчёт' : 'Отправить отчёт' }}
                        </button>
                    </form>
                @endif

                <p class="fse-side-title" style="margin-top:18px;">JSON шаблона</p>
                <div class="fse-coords-out" id="jsonOut">[]</div>
            </aside>
        </div>

        <div class="fse-type-popover" id="typePopover">
            <div class="fse-tp-title">Тип элемента</div>
            <div class="fse-tp-sub" id="tpSub"><span class="fse-dot"></span><span id="tpSubName"></span></div>
            <div id="tpList"></div>
        </div>
    </div>{{-- /.fs-editor --}}

{{-- ═══════════════ МОДАЛКА ПОДПИСИ E-IMZO ═══════════════
     Механизм тот же, что на штатных страницах (execution/sign):
       - form name="testform" с select name="key" (E-IMZO его заполняет)
       - sign() читает форму и запускает подпись
       - ecp_doc_type='free_sign_doc' → handleFreeSignDocument()
     ВАЖНО: select ДОЛЖЕН быть видимым — sign() читает testform.key.value  --}}
@if(!empty($canSign) && $canSign)
    <div class="modal fade" id="fsSignModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i data-lucide="pen-tool"></i> {{ __('action.to_sign') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(!empty($isAuthorConfirm) && $isAuthorConfirm)
                        <div class="alert alert-info" style="font-size:13px;">
                            Ваша подпись подтвердит отправку документа.
                            После этого он уйдёт первому участнику маршрута.
                        </div>
                    @endif
                    @csrf
                    <form name="testform" id="fsSignForm">
                        <div class="form-group">
                            <label>{{ __('action.leave_comment') }}..</label>
                            <textarea name="ecp_r_text" class="form-control" rows="2"></textarea>
                        </div>

                        @if(($signType->doc_sign_type ?? 1) === 2)
                            {{-- ═══ РЕЖИМ ЛОГИН (doc_sign_type = 2), как на штатных
                                 страницах подписи: нажал — подписал, без пароля
                                 (в штатном loginSignForm поля пароля тоже нет).
                                 Отправка на свой эндпоинт free-sign.login-sign
                                 (штатный login.sign.doc не знает free_sign_doc). ═══ --}}
                            <div class="alert alert-light" style="font-size:12.5px; border:1px solid rgba(15,23,42,0.06);">
                                <i data-lucide="log-in" style="width:14px;height:14px;"></i>
                                Ваш тип подписи — через логин: нажмите «{{ __('action.to_sign') }} (LOGIN)»,
                                и документ будет подписан от вашего имени
                            </div>
                        @else
                            {{-- Селект ключей ДОЛЖЕН быть в DOM: sign() читает testform.key.value,
                                 а E-IMZO заполняет его через cbChanged/uiLoadKey. --}}
                            <div class="form-group">
                                <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}</label>
                                <label id="message"></label>
                                <select name="key" class="form-control form-control-primary"
                                        style="width:100% !important;" onchange="cbChanged(this)"></select>
                            </div>
                        @endif

                        <input name="ecp_doc_type" value="free_sign_doc" hidden/>
                        <input name="ecp_doc_id" value="{{ $document->id }}" hidden/>
                        <input name="ecp_confirm_id" id="ecp_confirm_id" hidden>
                        <label id="keyId" hidden></label>
                        <textarea name="pkcs7" hidden></textarea>
                    </form>
                    <img src="{{ url('loading.gif') }}" style="display:none" width="60px" class="loadingGif">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('action.close') }}</button>
                    @if(($signType->doc_sign_type ?? 1) === 2)
                        <button type="button" class="btn btn-success" id="fsLoginSignBtn" onclick="fsLoginSign()">
                            <span class="confirm_type">{{ __('action.to_sign') }}</span> (LOGIN)
                        </button>
                    @else
                        <button type="button" class="btn btn-success" onclick="sign()">
                            <span class="confirm_type">{{ __('action.to_sign') }}</span> (ERI)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if(!empty($canSign) && $canSign && ($signType->doc_sign_type ?? 1) !== 2)
    <script>
        /* v71: после успешной подписи-отправки (ERI) уходим на страницу этого
           документа (view-work-doc) вместо перезагрузки редактора. Штатный sign() из
           общего e-imzo.blade вызывает location.reload() — не трогаем его;
           глобальный ajaxSuccess-хук срабатывает ПОСЛЕ локального success,
           и наша навигация (последняя) побеждает reload. canSign здесь
           только у АВТОРА (v50) — хук не заденет другие сценарии. */
        $(document).ajaxSuccess(function (e, xhr) {
            try {
                var r = xhr.responseJSON;
                if (r && r.success === 200) {
                    window.location.href = "{{ route('office.view-work-doc', [$journal->id, $document->doc_hash]) }}";
                }
            } catch (err) {}
        });
    </script>
    {{-- E-IMZO подключаем В КОНЦЕ content, как штатные страницы подписи
         (execution/sign/view.blade.php). КРИТИЧНО: e-imzo.blade.php делает
         window.onload = AppLoad — если подключить его в @push('scripts'),
         onload уже отработает, селект ключей не заполнится, sign() выйдет молча.
         Для doc_sign_type=2 (логин) НЕ подключаем вовсе: ключи не нужны,
         и у пользователя может не быть установленного E-IMZO клиента. --}}
    @include('e-imzo.e-imzo')
@endif

@if(!empty($canSign) && $canSign && ($signType->doc_sign_type ?? 1) === 2)
    <script type="text/javascript">
        /* Подпись логином (doc_sign_type = 2): нажал — подписал, без
           пароля (как штатный loginSignForm). Свой эндпоинт
           free-sign.login-sign, формат ответа и UX — как у штатного
           (success 200 -> перезагрузка). Защита от даблклика — disable. */
        function fsLoginSign() {
            var $btn = $('#fsLoginSignBtn').prop('disabled', true);
            $('.loadingGif').show();

            $.ajax({
                url: "{{ route('free-sign.login-sign') }}",
                method: 'POST',
                data: {
                    _token:       $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
                    ecp_doc_id:   $('#fsSignForm input[name="ecp_doc_id"]').val(),
                    ecp_doc_type: 'free_sign_doc',
                    ecp_r_text:   $('#fsSignForm textarea[name="ecp_r_text"]').val()
                }
            }).done(function (response) {
                $('.loadingGif').hide();
                if (response && response.success === 200) {
                    // v71.1: после отправки — на страницу ЭТОГО документа (view-work-doc)
                    window.location.href = "{{ route('office.view-work-doc', [$journal->id, $document->doc_hash]) }}";
                } else {
                    $btn.prop('disabled', false);
                    alert((response && response.message) || 'Ошибка подписи');
                }
            }).fail(function () {
                $('.loadingGif').hide();
                $btn.prop('disabled', false);
                alert('Ошибка сети — попробуйте ещё раз');
            });
        }
    </script>
@endif

@if(!empty($canSign) && $canSign)

    <script type="text/javascript">
        // Модалки переносим в body — иначе backdrop перекрывает (паттерн проекта).
        $('.modal').each(function () {
            $(this).appendTo('body');
        });

        // Кнопка в тулбаре открывает модалку подписи.
        $(document).ready(function () {
            $('#fsSignBtn').on('click', function () {
                $('#fsSignModal').modal('show');
            });
        });

        /* ═══════════ ПОИСК ИСПОЛНИТЕЛЕЙ ДЛЯ РЕЗОЛЮЦИИ ═══════════
           Форма отдаёт work_history.id в user_id[] — именно он идёт
           в document_execution_steps.to_user (как в штатной резолюции). */
        (function () {
            var $search  = $('#fsPerfSearch');
            if (!$search.length) return;

            var $results = $('#fsPerfResults');
            var $selected = $('#fsSelectedPerf');
            var picked = {};                       // work_id → true
            var roles = @json(($resolutionExecRoles ?? collect())->map(fn($r) => ['id' => $r->id, 'label' => $r->lang($r->lang_id)])->values());
            var timer = null;

            function renderPicked() {
                $('#fsPerfEmpty').toggle(Object.keys(picked).length === 0);
            }

            function addPerformer(p) {
                if (picked[p.id]) return;
                picked[p.id] = true;

                var opts = roles.map(function (r, i) {
                    return '<option value="' + r.id + '"' + (i === 0 ? ' selected' : '') + '>' + r.label + '</option>';
                }).join('');

                var html =
                    '<div class="fse-perf-picked" data-id="' + p.id + '">' +
                      '<button type="button" class="fse-perf-del">&times;</button>' +
                      '<div class="fse-perf-name">' + (p.full_name || '—') + '</div>' +
                      '<div class="fse-perf-meta">' + (p.position || '') + '</div>' +
                      '<select name="user_role[]" class="fse-input-sm" style="width:100%;margin-top:4px;">' + opts + '</select>' +
                      '<input type="hidden" name="user_id[]" value="' + p.id + '">' +
                      '<input type="hidden" name="dep_id[]" value="' + (p.dep_id || '') + '">' +
                    '</div>';

                $selected.append(html);
                renderPicked();
                $results.empty();
                $search.val('');
            }

            $selected.on('click', '.fse-perf-del', function () {
                var $box = $(this).closest('.fse-perf-picked');
                delete picked[$box.data('id')];
                $box.remove();
                renderPicked();
            });

            $search.on('input', function () {
                var q = $(this).val().trim();
                clearTimeout(timer);
                if (q.length < 2) { $results.empty(); return; }

                timer = setTimeout(function () {
                    $.get('{{ route('free-sign.performers.search') }}', {q: q}, function (resp) {
                        $results.empty();
                        (resp.data || []).forEach(function (p) {
                            if (picked[p.id]) return;
                            $('<div class="fse-cand-item">')
                                .html('<span class="fse-cand-ava">' +
                                      (p.full_name || '?').split(' ').slice(0,2).map(function(w){return w[0]||'';}).join('') +
                                      '</span><span>' + (p.full_name || '—') +
                                      '<br><small style="color:var(--muted)">' + (p.position || '') + '</small></span>')
                                .on('click', function () { addPerformer(p); })
                                .appendTo($results);
                        });
                        if (!(resp.data || []).length) {
                            $results.html('<div class="fse-cand-empty">Никого не найдено</div>');
                        }
                    });
                }, 300);
            });

            // Не отправлять форму без исполнителей.
            $('#fsResForm').on('submit', function (e) {
                if (Object.keys(picked).length === 0) {
                    e.preventDefault();
                    alert('Выберите хотя бы одного исполнителя');
                }
            });
        })();
    </script>
@endif

@endsection

@push('scripts')
    {{-- Библиотеки редактора (CDN) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/konva@10.3.0/konva.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <script src="https://unpkg.com/@pdf-lib/fontkit@1.1.1/dist/fontkit.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

    {{-- Данные-заглушки для редактора. Замени содержимое своими данными. --}}
    <script>
        window.FS_DEMO = {
            candidates: @json($candidates),
            signers: @json($demoSigners),
            fields:  @json($demoFields),

            // Режим и статусы приходят с сервера — редактор открывается
            // сразу в правильном виде, не дожидаясь первого polling.
            mode: @json($mode ?? 'edit'),
            signStatus: @json((object) ($signStatus ?? [])),

            csrf: @json(csrf_token()),

            // v49: тумблер «обязательное заполнение резолюции».
            // resolRoleId — id роли RESOL (по нему видимость тумблера),
            // controlEnabled — при включённом контроле тумблер заблокирован,
            // resolutionRequired — текущее значение.
            resolRoleId: @json($fsResolRoleId ?? null),
            controlEnabled: @json((bool) ($fsControlEnabled ?? false)),
            resolutionRequired: @json((bool) ($fsResolutionRequired ?? true)),

            // PDF документа через существующий preView (диск ftp_13).
            // $documentFile — запись document_files с исходным PDF.
            @isset($documentFile)
            pdfUrl: @json(route('file.pre-view', $documentFile->id)),
            @endisset

            // Начальный порядок подписания (true = по очереди, false = параллельно).
            // sequential: true,

            // Сохранение маршрута + полей → saveFromEditor.
            // $documentId — documents.id. Передай его из контроллера.
            @isset($document->id)
            saveUrl: @json(route('free-sign.save', $document->id)),
            // Polling статуса документа (режим view). Раскомментируй когда готов эндпоинт:
            statusUrl: @json(route('free-sign.status', $document->id)),
            @endisset

            // Куда перейти после успешного сохранения (необязательно):
            // redirectAfterSave: @json(url('/documents')),
        };
    </script>

    <script src="{{ asset('js/free-sign-editor.js') }}"></script>


@endpush
