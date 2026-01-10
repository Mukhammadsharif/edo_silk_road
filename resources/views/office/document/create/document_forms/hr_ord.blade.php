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
    <div class="col-sm-12 mt-10 mb-5">
        <span class="text-muted"><i>Xodimlarning mehnat ta`tili arizalaridan birini tanlang...</i> </span>
    </div>
    <div class="col-sm-6">
        <label for="app_id">{{ __('document.apps') }}</label>
        <select name="app_id" class="form-control select2">
            <option value="">{{ __('field.not_selected') }}..</option>
            @foreach($labourApps as $key => $labourApp)
                <option value="{{ $labourApp->doc_id }}">{{ $key+1 }}.
                    {{ $labourApp->fromUser->personal->shortName()??'' }} - ({{ $labourApp->journal->title??'' }}, {{ date('d.m.Y', strtotime($labourApp->j_date)) }})
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-12 mt-10 mb-5">
        <span class="text-muted"><i>Mehnat ta`tili uchun ishlagan yillar boshlanish va tugash sanasini tanlang...</i> </span>
    </div>
    <div class="col-sm-3">
        <label for="from_date">{{ __('field.from_date') }}</label><span class="text-red">*</span>
        <input type="date" name="from_date" value="{{ date('Y-m-d') }}" class="form-control" />
    </div>
    <div class="col-sm-3">
        <label for="to_date">{{ __('field.to_date') }}</label><span class="text-red">*</span>
        <input type="date" name="to_date" value="{{ date('Y-m-d', strtotime('+30 day')) }}" class="form-control" />
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
</div>

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

