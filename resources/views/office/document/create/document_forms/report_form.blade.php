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
        <label for="confidentiality">{{ __('document.confidentiality') }}</label>
        <select name="confidence" class="form-control">
            @foreach($confidences as $key => $confidence)
                <option value="{{ $confidence->id }}">{{ $key+1 }}.
                    {{ $confidence->lang($confidence->lang_id) }}
                </option>
            @endforeach
        </select>
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

<div class="form-group row">
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('document.doc_text') }} <span class="text-red">*</span></label>
            <textarea name="doc_text" id="div_editor1"></textarea>
            {!! $errors->first('r_text', '<p class="text-danger">:message</p>') !!}
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-12 mt-10 mb-5">
        <span class="text-muted"><i>Hisobor formasini yarating...</i> </span>
    </div>
    <div class="col-sm-10 mt-10 mb-5 report-form-class">
        <div id="fields-container">
            <div class="flex gap-2 mb-2" id="field_0">
                <div class="form-group row">
                    <div class="col-sm-10"><label>Nomi 1</label><span class="text-red">*</span>
                        <input type="text" name="fields[0][label]" class="form-control form-control-success" placeholder="Maydon nomini kiriting.. (Masalan: Nomi, Soni, Sana)" required>
                    </div>
                    <div class="col-sm-2"><label>Turi</label>
                        <select name="fields[0][type]" class="form-control">
                            <option value="text">Matn</option>
                            <option value="number">Raqam</option>
                            <option value="date">Sana</option>
                            <option value="textarea">Katta matn</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" id="add-field" class="btn btn-success rounded"><i class="ik ik-plus"></i> Maydon qo‘shish</button>

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

<style>
    .report-form-class {
        background-color: #ff980026;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #FF9800;
        margin: 7px 13px;
    }
</style>

@include('templates.mini_editor_js')

<script>
    var editor1 = new RichTextEditor("#div_editor1");
    editor1.setHTMLCode("Use inline HTML or setHTMLCode to init the default content.");
</script>

<script>
    let fieldIndex = 1; // har bir yangi maydon uchun unique index

    document.getElementById('add-field').addEventListener('click', function() {
        const container = document.getElementById('fields-container');

        // unique id yaratamiz
        const fieldId = 'field_' + fieldIndex++;

        // yangi divni yasaymiz
        const newDiv = document.createElement('div');
        newDiv.classList.add('flex', 'gap-2', 'mb-2');
        newDiv.id = fieldId;
        newDiv.innerHTML = `<div class="form-group row">
        <div class="col-sm-8">
            <label>Nomi ${fieldIndex}</label><span class="text-red">*</span>
            <input type="text" name="fields[${fieldIndex}][label]" class="form-control form-control-success" placeholder="Maydon nomini kiriting.. (Masalan: Nomi, Soni, Sana)" required>
        </div>
        <div class="col-sm-2"><label>Turi</label>
            <select name="fields[${fieldIndex}][type]" class="form-control">
                <option value="text">Matn</option>
                <option value="number">Raqam</option>
                <option value="date">Sana</option>
                <option value="textarea">Katta matn</option>
            </select>
        </div>
        <div class="col-sm-2"><label>Harakat</label>
            <button type="button" class="btn btn-outline-danger btn-rounded remove-field" data-id="${fieldId}">O'chirmoq</button>
        </div>
</div>
    `;
        container.appendChild(newDiv);
    });

    // ❌ remove-field
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-field')) {
            e.preventDefault();
            const fieldId = e.target.getAttribute('data-id');
            const divToRemove = document.getElementById(fieldId);
            if (divToRemove) divToRemove.remove();
        }
    });
</script>

<script type="text/javascript">
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

