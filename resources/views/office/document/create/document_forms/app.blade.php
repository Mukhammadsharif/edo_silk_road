<link rel="stylesheet" href="{{ asset('css/templates/office/document/create/document_forms/styles.css') }}">

<div class="modern-form-body">
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

    <div class="info-message">
        <i data-lucide="info"></i>
        <span>Mehnat ta'tili boshlanish va tugash sanasini tanlang...</span>
    </div>

    <div class="form-row-modern">
        <div class="form-group-modern">
            <label>
                <i data-lucide="calendar"></i>
                {{ __('field.from_date') }}
                <span class="required">*</span>
            </label>
            <input type="date" name="from_date" class="form-control-modern" value="{{ date('Y-m-d') }}" required />
        </div>

        <div class="form-group-modern">
            <label>
                <i data-lucide="calendar"></i>
                {{ __('field.to_date') }}
                <span class="required">*</span>
            </label>
            <input type="date" name="to_date" class="form-control-modern" value="{{ date('Y-m-d', strtotime('+30 day')) }}" required />
        </div>
    </div>

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

    <div class="form-actions">
        @if(!Auth::user()->currentSubscription)
            <div class="subscription-warning">
                <i data-lucide="alert-circle"></i>
                <span>Hujjat yaratish uchun faol obuna kerak</span>
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
