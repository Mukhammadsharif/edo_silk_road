<link rel="stylesheet" href="{{ asset('css/templates/office/document/create/document_forms/styles.css') }}">

<div class="modern-form-body">
    <!-- Language & Confidentiality -->
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
    </div>

    <!-- Info Message -->
    <div class="info-message">
        <i data-lucide="info"></i>
        <span>{{ __('action.choose_one_app_of_labour') }}</span>
    </div>

    <!-- Application Selection -->
    <div class="form-group-modern">
        <label>
            <i data-lucide="file-text"></i>
            {{ __('document.apps') }}
        </label>
        <select name="app_id" class="form-control-modern select2">
            <option value="">{{ __('field.not_selected') }}</option>
            @foreach($labourApps as $key => $labourApp)
                <option value="{{ $labourApp->doc_id }}">
                    {{ $key+1 }}. {{ $labourApp->fromUser->personal->shortName()??'' }} -
                    ({{ $labourApp->journal->title??'' }}, {{ date('d.m.Y', strtotime($labourApp->j_date)) }})
                </option>
            @endforeach
        </select>
    </div>

    <!-- Info Message -->
    <div class="info-message">
        <i data-lucide="info"></i>
        <span>{{ __('action.choose_period_of_labour_leave') }}</span>
    </div>

    <!-- Date Range -->
    <div class="form-row-modern">
        <div class="form-group-modern">
            <label>
                <i data-lucide="calendar"></i>
                {{ __('field.from_date') }}
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
                    <input
                        type="text"
                        name="from_date"
                        class="date-input form-control-modern {{ $errors->has('from_date') ? 'is-invalid' : '' }}"
                        placeholder="{{ __('action.choose_date') }}"
                        value="{{ old('from_date', date('Y-m-d')) }}"
                        readonly
                        required
                    />
                    {!! $errors->first('from_date', '<p class="error-text">:message</p>') !!}
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

        <div class="form-group-modern">
            <label>
                <i data-lucide="calendar"></i>
                {{ __('field.to_date') }}
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
                    <input
                        type="text"
                        name="to_date"
                        class="date-input form-control-modern {{ $errors->has('to_date') ? 'is-invalid' : '' }}"
                        placeholder="{{ __('action.choose_date') }}"
                        value="{{ date('Y-m-d', strtotime('+30 day')) }}"
                        readonly
                        required
                    />
                    {!! $errors->first('to_date', '<p class="error-text">:message</p>') !!}
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
                        <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX (max 30MB)</span>
                    </div>
                </div>

                <button type="button" class="btn-add-file add-file-input">
                    <i data-lucide="plus"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        @if(!Auth::user()->currentSubscription)
            <div class="subscription-warning">
                <i data-lucide="alert-circle"></i>
                <span>{{ __('field.need_subscription_for_creating_docs') }}</span>
            </div>
        @endif

        <button type="submit" name="action" value="file" id="submitBtn" class="btn-submit"
                onclick="setFormAction('file')"
                @if(!Auth::user()->currentSubscription) disabled @endif>
            <i data-lucide="save"></i>
            {{ __('action.save') }}
        </button>
    </div>
</div>

@include('office.document.create.document_forms.js')

@include('templates.calendar.modern-calendar-css')
@include('templates.calendar.modern-calendar-script')
