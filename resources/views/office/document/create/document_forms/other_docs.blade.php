<div class="form-group row">
    <div class="col-sm-3">
        <label for="doc_lang">{{ __('field.doc_lang') }}</label><br>
        <select name="doc_lang" class="form-control select2">
            <option value="uz">1. {{ __('lang.uzbek') }}</option>
            <option value="kr">2. {{ __('lang.cyrillic') }}</option>
            <option value="ru">3. {{ __('lang.russian') }}</option>
            <option value="en">4. {{ __('lang.english') }}</option>
        </select>
    </div>
    <div class="col-sm-3">
        <label>{{ __('document.confidentiality') }}</label>
        <select name="confidence" class="form-control">
            @foreach($confidences as $key => $confidence)
                <option value="{{ $confidence->id }}">{{ $key+1 }}.
                    {{ $confidence->lang($confidence->lang_id) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <label>{{ __('field.date') }}</label><span class="text-red">*</span>
        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" />
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-6">
        <label>{{ __('document.doc_name') }} <span class="text-red">*</span></label>
        <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}" value="{{ old('doc_name') }}" required/>
        {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
    </div>
    <div class="col-sm-3">
        <label>{{ __('document.doc_number') }}</label>
        <input type="text" name="doc_number" class="form-control" value="{{ old('doc_number') }}">
    </div>
</div>

<div class="form-group row mt-10">
    <div class="col-sm-6">
        <label for="customFile1">{{ __('action.attach_file') }}</label>
        <div id="file-inputs-container">
            <div class="file-input-group d-flex flex-row align-items-baseline justify-content-between mb-2">
                <div class="custom-file" style="display: none;">
                    <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-0">
                    <label class="custom-file-label" for="customFile-0">{{ __('action.choose') }}</label>
                </div>
                <input type="text" class="form-control" readonly placeholder="{{ __('action.choose') }}"
                       onclick="document.getElementById('customFile-0').click();" style="cursor: pointer;">

                <button type="button" class="btn btn-icon btn-outline-secondary btn-sm ms-2 ml-2 add-file-input">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="form-group row" id="textContainer">
    <div class="col-sm-10">
        <label>{{ __('document.doc_text') }}<span class="text-red">*</span></label>
        <div class="row">
            <div class="col-sm-4">
                <div id="dropzone" class="dropzone">
                    <p>Faylni bu yerga qo`ying</p>
                    <i class="ik ik-upload-cloud"></i>
                    <input type="file" accept=".doc, .docx" name="main_file" id="fileInput" style="display: none;">
                </div>
                <div id="fileList"></div>
            </div>
            <div class="col-sm-4">
                <button name="action" value="office" style="display: contents" onclick="setFormAction('web')">
                    <div class="web-office">
                        <p>Web Word Office</p>
                        <i class="ik ik-file-text"></i>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    @if(!Auth::user()->currentSubscription)
        <small class="d-block text-warning mb-2">
            <i class="ik ik-info mr-1"></i>
            Hujjat yaratish uchun faol obuna kerak
        </small>
    @endif

    <button name="action" value="file" id="submitBtn" class="btn btn-primary" onclick="setFormAction('file')"
            @if(!Auth::user()->currentSubscription) disabled @endif>
        <i class="ik ik-save"></i> {{ __('action.save') }}
    </button>

    @if(!Auth::user()->currentSubscription)
        <div class="mt-2">
            <a href="{{ route('pricing') }}" class="btn btn-outline-warning btn-sm">
                Tariflarni ko'rish
            </a>
        </div>
    @endif
</div>

<script type="text/javascript">
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false); // Prevent default browser behavior
    });

    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight dropzone on drag enter/over
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropzone.classList.add('hover');
    }

    function unhighlight() {
        dropzone.classList.remove('hover');
    }

    // Handle dropped files
    dropzone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    // Handle file input change
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    // Simulate click on hidden input when dropzone is clicked
    dropzone.addEventListener('click', () => {
        fileInput.click();
    });

    function handleFiles(files) {
        for (const file of files) {
            displayFile(file);
            uploadFile(file); // Placeholder for actual upload logic
        }
    }

    function displayFile(file) {
        $('#fileList').empty();
        const item = document.createElement('div');
        item.classList.add('file-item');
        item.innerHTML = `
                                                <span>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
                                                `;
        fileList.appendChild(item);
    }

    function uploadFile(file) {
        // Implement actual file upload logic here (e.g., using FormData and XMLHttpRequest/Fetch API)
        console.log('Uploading file:', file.name);
        // Update status in the fileList element
    }

    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('fileInput');
        const submitBtn = document.getElementById('submitBtn');

        // изначально скрываем кнопку
        submitBtn.style.display = 'none';

        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                submitBtn.style.display = 'inline-block'; // показать
            } else {
                submitBtn.style.display = 'none'; // скрыть
            }
        });
    });

    function setFormAction(type) {
        const form = document.getElementById('createForm');

        if (type === 'file') {
            form.action = "{{ route('office.doc.store') }}";  // сюда нужный маршрут
        } else if (type === 'web') {
            form.action = "{{ route('document-only-office.create') }}";
        }

        form.submit();
    }

    // Attach file with add input file
    let fileInputCounter = 1;

    // --- Fayl tanlanganda fayl nomini ko'rsatish ---
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('custom-file-input')) {
            const fileInput = e.target;
            const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : "{{ __('action.choose') }}";
            // Tegishli text inputni topamiz
            const parentGroup = fileInput.closest('.file-input-group');
            const textInput = parentGroup.querySelector('input.form-control');
            if (textInput) {
                textInput.value = fileName;
            }
        }
    });

    // --- "+" tugmasi bosilganda yangi fayl input qo'shish ---
    document.addEventListener('click', function (e) {
        if (e.target.closest('.add-file-input')) {
            addFileInput();
        }
    });

    function addFileInput() {
        const container = document.getElementById('file-inputs-container');
        const newFileInputGroup = document.createElement('div');
        newFileInputGroup.className = 'file-input-group d-flex align-items-center mb-2';

        const currentCounter = fileInputCounter;

        newFileInputGroup.innerHTML = `
            <div class="custom-file" style="display: none;">
                <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-${currentCounter}">
                <label class="custom-file-label" for="customFile-${currentCounter}">{{ __('action.choose') }}</label>
            </div>
            <input type="text" class="form-control" readonly placeholder="{{ __('action.choose') }}"
                style="cursor: pointer;" onclick="document.getElementById('customFile-${currentCounter}').click();">
            <button type="button" class="btn btn-icon btn-outline-warning btn-sm ms-2 ml-2 remove-file-input">
                <i class="ik ik-minus"></i>
            </button>
        `;

        container.appendChild(newFileInputGroup);
        fileInputCounter++;
    }

    // --- "-" tugmasi bosilganda inputni o‘chirish ---
    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-file-input')) {
            e.target.closest('.file-input-group').remove();
        }
    });
</script>
