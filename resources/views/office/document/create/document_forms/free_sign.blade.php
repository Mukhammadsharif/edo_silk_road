<link rel="stylesheet" href="{{ asset('css/templates/office/document/create/document_forms/styles.css') }}">

<style>
    .fs-hint-box {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        background: rgba(121, 77, 253, .06);
        border: 1px solid rgba(121, 77, 253, .25);
        border-radius: 10px;
    }
    .fs-hint-box i {
        width: 20px; height: 20px;
        color: #794DFD;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .fs-hint-box strong {
        display: block;
        font-size: 13.5px;
        margin-bottom: 3px;
        color: #794DFD;
    }
    .fs-hint-box p {
        margin: 0;
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.45;
    }
</style>

<div class="modern-form-body">
    <!-- Language, Confidentiality & Date -->
    <div class="form-row-modern">
        <div class="form-group-modern">
            <label>
                <i data-lucide="globe"></i>
                {{ __('field.doc_lang') }}
            </label>
            <select name="doc_lang" class="form-control-modern select2">
                <option value="uz">{{ __('lang.uzbek') }}</option>
                <option value="kr">{{ __('lang.cyrillic') }}</option>
                <option value="ru">{{ __('lang.russian') }}</option>
                <option value="en">{{ __('lang.english') }}</option>
            </select>
        </div>

        <div class="form-group-modern">
            <label>
                <i data-lucide="shield"></i>
                {{ __('document.confidentiality') }}
            </label>
            <select name="confidence" class="form-control-modern select2">
                @foreach($confidences as $key => $confidence)
                    <option value="{{ $confidence->id }}">
                        {{ $key+1 }}. {{ $confidence->lang($confidence->lang_id) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group-modern">
            <label>
                <i data-lucide="calendar"></i>
                {{ __('field.date') }}
                <span class="required">*</span>
            </label>

            <div class="date-picker-container">
                <div class="input-wrapper">
                    <svg class="calendar-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    {{-- Календарь: самописный (.date-input + .calendar-popup),
                         подключается через @include('templates.calendar.*') внизу.
                         НЕ bootstrap-datepicker — класс my-bootstrap-datepicker
                         тут не нужен, он от другого механизма.

                         ФОРМАТ: dd.mm.yyyy — так работает formatDateUz() в календаре
                         и так же парсит контроллер (parseDocDate).
                         В other_docs было value="{{ date('Y-m-d') }}" (2026-07-15) —
                         Carbon::createFromFormat('d.m.Y', ...) на этом падал.
                    --}}
                    <input
                        type="text"
                        name="doc_date"
                        class="date-input form-control-modern {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                        placeholder="{{ __('action.choose_date') }}"
                        value="{{ date('d.m.Y') }}"
                        autocomplete="off"
                        readonly
                        required
                    />
                    {!! $errors->first('doc_date', '<p class="error-text">:message</p>') !!}
                </div>

                <div class="calendar-popup">
                    <div class="calendar-header">
                        <button type="button" class="nav-btn prev">&lsaquo;</button>
                        <div class="month-year"></div>
                        <button type="button" class="nav-btn next">&rsaquo;</button>
                    </div>
                    <div class="days"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Name and Number -->
    <div class="form-row-modern">
        <div class="form-group-modern" style="flex: 2;">
            <label>
                <i data-lucide="type"></i>
                {{ __('document.doc_name') }}
                <span class="required">*</span>
            </label>
            <input type="text" name="doc_name"
                   class="form-control-modern {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                   value="{{ old('doc_name') }}"
                   placeholder="{{ __('field.type_doc_title') }}"
                   required />
            {!! $errors->first('doc_name', '<p class="error-text">:message</p>') !!}
        </div>

        <div class="form-group-modern">
            <label>
                <i data-lucide="hash"></i>
                {{ __('document.doc_number') }}
            </label>
            <input type="text" name="doc_number"
                   class="form-control-modern"
                   value="{{ old('doc_number') }}"
                   placeholder="{{ __('field.number') }}">
        </div>
    </div>

    <!-- File Upload Section -->
    <div class="form-group-modern file-upload-section">
        <label>
            <i data-lucide="paperclip"></i>
            {{ __('action.attach_file') }}
        </label>

        <div id="file-inputs-container">
            <div class="file-input-wrapper">
                <div class="custom-file-hidden">
                    <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-0">
                </div>

                <div class="file-display-area" onclick="document.getElementById('customFile-0').click();">
                    <div class="file-icon">
                        <i data-lucide="upload"></i>
                    </div>
                    <div class="file-text">
                        <p class="file-name">{{ __('action.choose') }}</p>
                        <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX (max 10MB)</span>
                    </div>
                </div>

                <button type="button" class="btn-add-file add-file-input">
                    <i data-lucide="plus"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════ FREE-SIGN: БЛОК «ТЕКСТ ДОКУМЕНТА» УБРАН ═══════════
         У внешаблонных документов текст не создаётся в редакторе —
         загружается готовый PDF, на котором потом расставляются метки
         подписи. Поэтому dropzone для .doc/.docx и Web Word Office
         здесь не нужны.

         Сам PDF грузится через блок «Файлы» выше (doc_files[]).
    --}}

    <div class="form-group-modern">
        <div class="fs-hint-box">
            <i data-lucide="pen-tool"></i>
            <div>
                <strong>Внешаблонный документ (свободная подпись)</strong>
                <p>Загрузите PDF в блоке «Файлы» выше. После сохранения
                   откроется редактор, где вы расставите места подписей
                   и выберете подписантов.</p>
            </div>
        </div>
    </div>

    {{-- ═══════════ КОНТРОЛЬ ИСПОЛНЕНИЯ (опция документа) ═══════════
         Индивидуальный выбор для КАЖДОГО документа. Хранится в ОТДЕЛЬНОЙ
         таблице free_sign_journal_controls (модель FreeSignJournalControl),
         document_journals не трогаем. Семантика:
           отмечен  -> enabled = true  -> после подписания документ
                       уходит контролёру (Ijro nazorati), тот готовит
                       проект резолюции
           снят     -> enabled = false -> сразу резолюционеру
         Настройка типа (journal_type_controls) для НОВЫХ free-sign
         документов больше не решает — только этот чекбокс.
         exec_control здесь — только ИМЯ ПОЛЯ ФОРМЫ ($request->boolean).

         $fsHasControllers передаётся из DocumentController (рендер формы):
         если в организации нет людей с ролью Ijro nazorati / Control_Emp,
         чекбокс выключен — иначе документ ушёл бы «в никуда»
         (FreeSignRouteService всё равно пропустит контроль с warning,
         но честнее не давать выбрать заведомо нерабочую опцию).
    --}}
    <div class="form-group-modern">
        <label class="fs-control-toggle {{ empty($fsHasControllers) ? 'is-disabled' : '' }}">
            <input type="checkbox" name="exec_control" value="1"
                   @if(empty($fsHasControllers)) disabled @endif>
            <span class="fs-control-toggle__box"><i data-lucide="check"></i></span>
            <span class="fs-control-toggle__text">
                <strong>{{ __('field.exec_control') ?? 'Контроль исполнения (Ijro nazorati)' }}</strong>
                @if(!empty($fsHasControllers))
                    <small>Перед резолюцией документ проверит контролёр
                           и подготовит проект резолюции</small>
                @else
                    <small>В организации нет сотрудников с ролью контролёра
                           (Ijro nazorati / Control_Emp) — опция недоступна</small>
                @endif
            </span>
        </label>
    </div>

    <style>
        .fs-control-toggle { display:flex; align-items:flex-start; gap:12px; cursor:pointer;
            padding:14px 16px; border:1px solid rgba(15,23,42,0.06); border-radius:12px;
            background:#fff; transition:border-color .15s; }
        .fs-control-toggle:hover { border-color:rgba(121,77,253,0.35); }
        .fs-control-toggle.is-disabled { cursor:not-allowed; opacity:.6; }
        .fs-control-toggle.is-disabled:hover { border-color:rgba(15,23,42,0.06); }
        .fs-control-toggle input { position:absolute; opacity:0; pointer-events:none; }
        .fs-control-toggle__box { flex:0 0 auto; width:22px; height:22px; margin-top:1px;
            border:2px solid #cbd5e1; border-radius:6px; display:flex; align-items:center;
            justify-content:center; transition:all .15s; }
        .fs-control-toggle__box i { width:14px; height:14px; color:#fff; opacity:0; }
        .fs-control-toggle input:checked + .fs-control-toggle__box {
            background:#794DFD; border-color:#794DFD; }
        .fs-control-toggle input:checked + .fs-control-toggle__box i { opacity:1; }
        .fs-control-toggle__text { display:flex; flex-direction:column; gap:2px; }
        .fs-control-toggle__text strong { font-size:14px; color:#1e293b; font-weight:600; }
        .fs-control-toggle__text small { font-size:12.5px; color:#94a3b8; line-height:1.45; }
    </style>

    <!-- Form Actions -->
    <div class="form-actions">
        @if(!Auth::user()->currentSubscription)
            <div class="subscription-warning">
                <i data-lucide="alert-circle"></i>
                <span>{{ __('field.need_subscription_for_creating_docs') }}</span>
            </div>
        @endif

        {{-- Кнопка видима сразу: у free-sign нет dropzone, который её показывал.
             Раньше style="display:none" + показ по загрузке .doc — из-за этого
             для внешаблонных сохранить было нечем. --}}
        <button type="submit" name="action" value="file" id="submitBtn" class="btn-submit"
                onclick="setFormAction('file')"
                @if(!Auth::user()->currentSubscription) disabled @endif>
            <i data-lucide="save"></i>
            {{ __('action.save') }}
        </button>
    </div>
</div>

@include('office.document.create.document_forms.js')

{{-- ═══════════ КАЛЕНДАРЬ ═══════════
     В проекте самописный календарь (.calendar-popup / .date-input),
     а НЕ bootstrap-datepicker. Его CSS и JS подключаются этими include —
     ровно как в other_docs.blade.php (строки 252-253).

     Раньше я их вырезал вместе с блоком дропзоны -> календарь не открывался.
--}}
@include('templates.calendar.modern-calendar-css')
@include('templates.calendar.modern-calendar-script')

<script>
    // Dropzone-логики нет: блок «Текст документа» для free-sign убран.
    // Иконки перерисовываем — форма подгружается динамически (AJAX loadForm).
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
