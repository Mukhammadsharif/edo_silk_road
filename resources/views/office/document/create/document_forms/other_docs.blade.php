<link rel="stylesheet" href="{{ asset('css/templates/office/document/create/document_forms/styles.css') }}">

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
            <input type="date" name="date" class="form-control-modern" value="{{ date('Y-m-d') }}" required />
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

    <!-- Document Text Upload Options -->
    <div class="form-group-modern">
        <label>
            <i data-lucide="file-text"></i>
            {{ __('document.doc_text') }}
            <span class="required">*</span>
        </label>

        <div class="upload-options-grid">
            <!-- Dropzone Upload -->
            <div class="upload-option-card" id="dropzone">
                <div class="upload-icon">
                    <i data-lucide="upload-cloud"></i>
                </div>
                <h4>Faylni yuklash</h4>
                <p>DOC yoki DOCX faylni bu yerga qo'ying</p>
                <input type="file" accept=".doc, .docx" name="main_file" id="fileInput" style="display: none;">
            </div>

            <!-- Web Office -->
            <div class="upload-option-card" onclick="setFormAction('web')">
                <div class="upload-icon">
                    <i data-lucide="monitor"></i>
                </div>
                <h4>Web Word Office</h4>
                <p>Onlayn muharrir orqali yaratish</p>
            </div>
        </div>

        <div id="fileList" class="file-list-display"></div>
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
                onclick="setFormAction('file')" style="display: none;"
                @if(!Auth::user()->currentSubscription) disabled @endif>
            <i data-lucide="save"></i>
            {{ __('action.save') }}
        </button>
    </div>
</div>

@include('office.document.create.document_forms.js')

<script>
    // Dropzone functionality (for other_docs.blade.php)
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const submitBtn = document.getElementById('submitBtn');

    if (dropzone && fileInput && fileList) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('hover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('hover'), false);
        });

        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        dropzone.addEventListener('click', () => {
            fileInput.click();
        });

        function handleFiles(files) {
            for (const file of files) {
                displayFile(file);
            }
            if (submitBtn && files.length > 0) {
                submitBtn.style.display = 'inline-flex';
            }
        }

        function displayFile(file) {
            fileList.innerHTML = '';
            const item = document.createElement('div');
            item.style.cssText = 'padding: 0.75rem; background: white; border-radius: 8px; margin-top: 0.5rem;';
            item.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="file-text" style="width: 20px; height: 20px; color: #794DFD;"></i>
                        <span style="flex: 1; font-size: 14px; color: #1e293b;">${file.name}</span>
                        <span style="font-size: 12px; color: #64748b;">${(file.size / 1024).toFixed(2)} KB</span>
                    </div>
                `;
            fileList.appendChild(item);
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }
</script>
