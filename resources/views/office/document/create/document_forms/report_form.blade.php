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
                   placeholder="Hujjat nomini kiriting..."
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
                   placeholder="Raqam">
        </div>
    </div>

    <!-- Document Text Editor -->
    <div class="form-group-modern">
        <label>
            <i data-lucide="align-left"></i>
            {{ __('document.doc_text') }}
            <span class="required">*</span>
        </label>
        <textarea name="doc_text" id="div_editor1" class="form-control-modern" rows="6"></textarea>
        {!! $errors->first('r_text', '<p class="error-text">:message</p>') !!}
    </div>

    <!-- Report Form Builder -->
    <div class="report-form-builder">
        <div class="builder-header">
            <i data-lucide="layout"></i>
            <h3>Hisobot formasini yarating</h3>
        </div>

        <div class="info-message">
            <i data-lucide="info"></i>
            <span>Hisobot formasini yarating...</span>
        </div>

        <div id="fields-container" class="fields-container">
            <div class="field-row" id="field_0">
                <div class="field-inputs">
                    <div class="form-group-modern" style="flex: 3;">
                        <label>
                            <i data-lucide="text"></i>
                            Maydon nomi 1
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="fields[0][label]" class="form-control-modern"
                               placeholder="Maydon nomini kiriting (Masalan: Nomi, Soni, Sana)" required>
                    </div>

                    <div class="form-group-modern">
                        <label>
                            <i data-lucide="list"></i>
                            Turi
                        </label>
                        <select name="fields[0][type]" class="form-control-modern">
                            <option value="text">Matn</option>
                            <option value="number">Raqam</option>
                            <option value="date">Sana</option>
                            <option value="textarea">Katta matn</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" id="add-field" class="btn-add-field">
            <i data-lucide="plus"></i>
            Maydon qo'shish
        </button>
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

    <!-- Form Actions -->
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
