<!-- ============================================ -->
<!-- UNIVERSAL JAVASCRIPT FOR ALL FORMS -->
<!-- ============================================ -->

<script>
    // document.addEventListener('DOMContentLoaded', function () {
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        // lucide.createIcons();
    }

    // File Upload Handler
    let fileInputCounter = 1;
    // Handle Add Button Click
    document.addEventListener('click', function (e) {
        if (e.target.closest('.add-file-input')) {
            const container = document.getElementById('file-inputs-container');
            const newWrapper = document.createElement('div');
            newWrapper.className = 'file-input-wrapper mt-3';

            const currentCounter = fileInputCounter;

            newWrapper.innerHTML = `
                    <div class="custom-file-hidden">
                        <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-${currentCounter}">
                    </div>

                    <div class="file-display-area" onclick="document.getElementById('customFile-${currentCounter}').click();">
                        <div class="file-icon">
                            <i data-lucide="upload"></i>
                        </div>
                        <div class="file-text">
                            <p class="file-name">{{ __('action.choose') }}</p>
                            <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX (max 30MB)</span>
                        </div>
                    </div>

                    <button type="button" class="btn-remove-file">
                        <i data-lucide="trash-2"></i>
                    </button>
                `;

            container.appendChild(newWrapper);

            const fileInputCustom = newWrapper.querySelector(`#customFile-${currentCounter}`);
            const fileNameDisplay = newWrapper.querySelector('.file-name');

            fileInputCustom.addEventListener('change', function () {
                if (this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                    newWrapper.querySelector('.file-display-area').classList.add('has-file');
                }
            });

            newWrapper.querySelector('.btn-remove-file').addEventListener('click', function () {
                newWrapper.remove();
            });

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            fileInputCounter++;
        }
    });

    // Handle initial file input
    const initialFileInput = document.getElementById('customFile-0');
    if (initialFileInput) {
        const initialFileNameDisplay = document.querySelector('.file-name');
        initialFileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                initialFileNameDisplay.textContent = this.files[0].name;
                document.querySelector('.file-display-area').classList.add('has-file');
            }
        });
    }

    // Report Form Builder (for report_form.blade.php)
    let fieldIndex = 1;
    const addFieldBtn = document.getElementById('add-field');
    const fieldsContainer = document.getElementById('fields-container');

    if (addFieldBtn && fieldsContainer) {
        addFieldBtn.addEventListener('click', function () {
            const fieldId = 'field_' + fieldIndex;
            const newDiv = document.createElement('div');
            newDiv.classList.add('field-row');
            newDiv.id = fieldId;
            newDiv.innerHTML = `
                    <div class="field-inputs">
                        <div class="form-group-modern" style="flex: 3;">
                            <label>
                                <i data-lucide="text"></i>
                                Maydon nomi ${fieldIndex + 1}
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="fields[${fieldIndex}][label]" class="form-control-modern"
                                   placeholder="Maydon nomini kiriting (Masalan: Nomi, Soni, Sana)" required>
                        </div>

                        <div class="form-group-modern">
                            <label>
                                <i data-lucide="list"></i>
                                Turi
                            </label>
                            <select name="fields[${fieldIndex}][type]" class="form-control-modern">
                                <option value="text">Matn</option>
                                <option value="number">Raqam</option>
                                <option value="date">Sana</option>
                                <option value="textarea">Katta matn</option>
                            </select>
                        </div>

                        <button type="button" class="btn-remove-field remove-field" data-id="${fieldId}">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px; margin-right: 4px;"></i>
                            O'chirish
                        </button>
                    </div>
                `;
            fieldsContainer.appendChild(newDiv);
            fieldIndex++;

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-field')) {
                e.preventDefault();
                const btn = e.target.closest('.remove-field');
                const fieldId = btn.getAttribute('data-id');
                const divToRemove = document.getElementById(fieldId);
                if (divToRemove) divToRemove.remove();
            }
        });
    }
    // });

    /* Form action handler.
     |
     | Объявляем через window, а не как function setFormAction(...).
     |
     | Эта форма догружается по AJAX и вставляется через $formBody.html(html).
     | При таком выполнении скрипт может оказаться не в глобальной области —
     | тогда объявление функции остаётся локальным, а инлайновый onclick
     | ищет её в window и падает с «setFormAction is not defined».
     | Явное присваивание в window снимает зависимость от способа вставки.
     */
    window.setFormAction = function (type) {
        const form = document.getElementById('createForm');
        if (form) {
            if (type === 'file') {
                form.action = "{{ route('office.doc.store') }}";
            } else if (type === 'web') {
                form.action = "{{ route('document-only-office.create') }}";
            }
            form.submit();
        }
    };
</script>

@include('templates.mini_editor_js')

<script>
    // Initialize Rich Text Editor if available (for report_form.blade.php)
    if (typeof RichTextEditor !== 'undefined' && document.getElementById('div_editor1')) {
        var editor1 = new RichTextEditor("#div_editor1");
        editor1.setHTMLCode("Use inline HTML or setHTMLCode to init the default content.");
    }
</script>
