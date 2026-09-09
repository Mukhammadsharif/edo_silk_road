@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/protocol/secretary-create.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/templates/edit-execution-steps.css') }}">

@section('content')

    <div class="modern-container">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="page-icon">
                        <i data-lucide="file-plus-2"></i>
                    </div>
                    <div class="page-info">
                        <h1>{{ __('action.create_document') }}</h1>
                        <p>{{ $protocolType->lang($protocolType->id) }}</p>
                    </div>
                </div>

                <nav class="breadcrumb-modern">
                    <a href="{{ route('home') }}">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <a href="{{ route('prt-secretary', $protocolType->doc_hash) }}">{{ $protocolType->lang($protocolType->id) }}</a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <span class="current">{{ __('action.create_document') }}</span>
                </nav>
            </div>
        </div>

        @include('templates.alert')

        <div class="form-card">
            <form id="createForm" method="POST" action="{{ route('prt-secretary-store', $protocolType->doc_hash) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <input name="document_key" value="{{ $document_key }}" hidden>

                    <div class="form-section">
                        <div class="form-group-modern">
                            <label for="doc_lang">
                                <i data-lucide="file-type"></i>
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
                                <i data-lucide="file-type"></i>
                                {{ __('field.visibility') }}
                            </label>
                            <select name="doc_visibility" class="form-control-modern select2">
                                <option value="2">1. {{ __('field.public') }}</option>
                                <option value="1">2. {{ __('field.secret') }}</option>
                            </select>
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_date') }}</label>
                            <input type="date" name="doc_date"
                                   class="form-control-modern {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                                   value="{{ date('Y-m-d') }}"/>
                            {!! $errors->first('doc_date', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_term') }}</label>
                            <input type="date" name="doc_term" class="form-control-modern"/>
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_name') }} <span class="text-red">*</span></label>
                            <input type="text" name="doc_name"
                                   class="form-control-modern {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                   value="{{ old('doc_name') }}" placeholder="{{__('field.doc_name')}}"/>
                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_number') }}</label>
                            <input type="text" name="doc_number" class="form-control-modern"
                                   value="{{ old('doc_number') }}" placeholder="{{ __('field.doc_number') }}">
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
                                        <input type="file" name="doc_files[]" class="custom-file-input"
                                               id="customFile-0">
                                    </div>

                                    <div class="file-display-area"
                                         onclick="document.getElementById('customFile-0').click();">
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

                        <div class="form-actions">
                            @if(!Auth::user()->currentSubscription)
                                <div class="subscription-warning">
                                    <i data-lucide="alert-circle"></i>
                                    <span>Hujjat yaratish uchun faol obuna kerak</span>
                                </div>
                            @endif

                            {{-- onclick="setFormAction('file')" убран: функции с таким именем
                                 на этой странице нет (она определена только в формах документов),
                                 и каждый клик давал в консоли ReferenceError.
                                 Форма отправлялась всё равно — кнопка type="submit",
                                 а маршрут задан в самом теге <form>. --}}
                            <button type="submit" name="action" value="file" id="submitBtn" class="btn-submit"
                                    @if(!Auth::user()->currentSubscription) disabled @endif>
                                <i data-lucide="save"></i>
                                {{ __('action.save') }}
                            </button>
                        </div>
                    </div>

                    <div class="form-section-modern" style="max-height: 100vh; overflow-y: auto">

                        <!-- Select Members Section -->
                        <div class="section-title-modern">
                            <i data-lucide="pen-tool"></i>
                            <span>{{ __('field.select_members') }}</span>
                        </div>

                        <div class="search-box-modern">
                            <i data-lucide="search"></i>
                            <input type="text" class="form-control-modern border-none" id="userSearch"
                                   onkeyup="searchUserFunction()"
                                   placeholder="{{ __('action.search') }}...">
                        </div>

                        {!! $errors->first('user_id', '<p class="text-danger">:message</p>') !!}

                        @if(count($protocolType->docTypeUsers))
                            <ul id="sortable" class="users-list-modern sortable-list">
                                @foreach($protocolType->docTypeUsers as $key => $item)
                                    <li class="select-user sortable-item">
                                        <div class="user-checkbox-card-extended">
                                            <div class="drag-handle">
                                                <i data-lucide="grip-vertical"></i>
                                            </div>
                                            <input type="checkbox"
                                                   id="{{ $item->id }}"
                                                   value="{{ $item->user_id }}"
                                                   name="user_id[]"
                                            >
                                            <div class="user-card-content-extended">
                                                <div class="checkbox-indicator">
                                                    <i data-lucide="check" class="text-white"></i>
                                                </div>
                                                <div class="user-avatar">
                                                    <i data-lucide="user"></i>
                                                </div>
                                                <div class="user-info">
                                                    <span class="user-name">
                                                        {{ $item->userWork->personal->l_name??'' }}
                                                        {{ $item->userWork->personal->f_name??'' }}
                                                    </span>
                                                    <span class="user-position">
                                                        @php($department = $item->userWork->department??0)
                                                        @if($department)
                                                            {{ $department->lang($department->id) }}
                                                        @endif
                                                    </span>
                                                    <span class="user-department">
                                                        @php($position = $item->userWork->position??0)
                                                        @if($position)
                                                            {{ $position->lang($position->id) }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="user-role-select">
                                                    <select name="user_role_id[]" class="role-select-modern">
                                                        @foreach($user_roles as $role)
                                                            <option value="{{ $role->code }}"
                                                                    @if($role->code === 3) selected @endif>
                                                                {{ $role->lang($role->id) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if($isFamiliarExists)
                            <div class="section-divider"></div>

                            <!-- HR Order Employees Section -->
                            <div class="section-title-modern">
                                <i data-lucide="users"></i>
                                <span>{{ __('field.select_hr_order_emp') }}</span>
                            </div>

                            <div class="search-box-modern">
                                <i data-lucide="search"></i>
                                <input type="text" class="form-control-modern border-none" id="hrEmpSearch"
                                       onkeyup="searchHrEmpFunction()"
                                       placeholder="{{ __('action.search') }}...">
                            </div>

                            <ul id="sortable_hr_emp" class="users-list-modern sortable-list"></ul>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <script src="{{ asset('/assets/jquery-3.3.1.min.js') }}"></script>
        <script src="{{ asset('/assets/src/js/vendor/jquery-ui.min.js') }}"></script>

        <script type="text/javascript">

            $('.protocol_members_div').hide();

            function setAllUserCheckboxes(divId, sourceCheckbox) {
                let divElement = document.getElementById(divId);
                let inputElements = divElement.getElementsByTagName('input');
                for (let i = 0; i < inputElements.length; i++) {
                    if (inputElements[i].type !== 'checkbox')
                        continue;
                    inputElements[i].checked = sourceCheckbox.checked;
                }
            }

            function searchUserFunction() {
                let input = document.getElementById("userSearch");
                let filter = input.value.toLowerCase();
                let nodes = document.getElementsByClassName('select-user');

                for (i = 0; i < nodes.length; i++) {
                    if (nodes[i].innerText.toLowerCase().includes(filter)) {
                        nodes[i].style.display = "block";
                    } else {
                        nodes[i].style.display = "none";
                    }
                }
            }

            var selected = @json([]);

            function searchHrEmpFunction() {
                clearTimeout(window.searchHrEmpTimeout);
                window.searchHrEmpTimeout = setTimeout(function () {
                    let query = $('#hrEmpSearch').val();

                    $.ajax({
                        url: '/protocol/search-hr-emp/{{ $protocolType->doc_hash }}',
                        method: 'GET',
                        data: {
                            query: query,
                            doc_id: 0
                        },
                        success: function (data) {
                            let ul = $('#sortable_hr_emp');
                            let displayedIds = new Set();

                            // Собираем уже отображаемые ID из существующих элементов
                            ul.find('li').each(function () {
                                const existingCheckbox = $(this).find('.checkbox');
                                if (existingCheckbox.length) {
                                    const existingId = existingCheckbox.val();
                                    displayedIds.add(existingId);
                                }
                            });

                            // Убираем только непостоянные элементы
                            ul.find('li:not(.permanent)').remove();

                            // Добавляем ID постоянных элементов обратно в Set
                            ul.find('li.permanent').each(function () {
                                const checkbox = $(this).find('.checkbox');
                                if (checkbox.length) {
                                    displayedIds.add(checkbox.val());
                                }
                            });

                            data.forEach(item => {
                                const itemId = item.id.toString();

                                // ВАЖНО: проверяем ПЕРЕД добавлением
                                if (!displayedIds.has(itemId)) {
                                    const li = ` <li class="select-hr-user sortable-item">
                                        <div class="user-checkbox-card-extended">
                                            <input type="checkbox" name="hr_user_id[]" id="${item.id}" value="${item.id}">
                                            <div class="user-card-content-extended">
                                                <div class="checkbox-indicator">
                                                    <i data-lucide="check" class="text-white"></i>
                                                </div>
                                                <div class="user-avatar" style="background: #22c55e !important;">
                                                    <i data-lucide="user"></i>
                                                </div>
                                                <div class="user-info">
                                                    <span class="user-name">
                                                       ${item.l_name || ''} ${item.f_name || ''}
                                                    </span>
                                                    <span class="user-position">
                                                    ${item.department_name || ''}
                                                    </span>
                                                    <span class="user-department">
                                                    ${item.position_name || ''}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>`;

                                    ul.append(li);
                                    displayedIds.add(itemId); // Добавляем ПОСЛЕ проверки
                                }
                            });

                            lucide.createIcons();

                            document.querySelectorAll('.user-checkbox-card-extended').forEach(card => {
                                card.addEventListener('click', function(e) {
                                    if (e.target.closest('.drag-handle') || e.target.closest('.user-role-select')) {
                                        return;
                                    }

                                    const checkbox = this.querySelector('input[type="checkbox"]');
                                    if (checkbox) {
                                        checkbox.checked = !checkbox.checked;
                                        checkbox.closest('.sortable-item').classList.toggle('checked', checkbox.checked);
                                    }
                                });
                            });
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', status, error);
                        }
                    });
                }, 300);
            }

            // Attach event listener with debouncing
            $('#hrEmpSearch').on('input', searchHrEmpFunction);


            $('.checkbox').on('change', function () {
                $('#userSearch').val('');

                let nodes = document.getElementsByClassName('select-user');

                for (i = 0; i < nodes.length; i++) {
                    nodes[i].style.display = "flex";
                }
            });

            // File Upload Handler
            let fileInputCounter = 1;

            document.querySelector('.add-file-input').addEventListener('click', function () {
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

                const fileInput = newWrapper.querySelector(`#customFile-${currentCounter}`);
                const fileNameDisplay = newWrapper.querySelector('.file-name');

                fileInput.addEventListener('change', function () {
                    if (this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name;
                        newWrapper.querySelector('.file-display-area').classList.add('has-file');
                    }
                });

                newWrapper.querySelector('.btn-remove-file').addEventListener('click', function () {
                    newWrapper.remove();
                });

                lucide.createIcons();
                fileInputCounter++;
            });

            const initialFileInput = document.getElementById('customFile-0');
            const initialFileNameDisplay = document.querySelector('.file-name');

            initialFileInput.addEventListener('change', function () {
                if (this.files.length > 0) {
                    initialFileNameDisplay.textContent = this.files[0].name;
                    document.querySelector('.file-display-area').classList.add('has-file');
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                if ($("#sortable").length) {
                    $("#sortable").sortable({
                        handle: ".drag-handle",
                        placeholder: "sortable-placeholder",
                        cursor: "move",
                        opacity: 0.8,
                        tolerance: "pointer",
                        start: function (e, ui) {
                            ui.placeholder.height(ui.item.height());
                        },
                        update: function (event, ui) {
                            // You can add your custom logic here when order changes
                            console.log('Order updated');
                        }
                    });
                }

                searchHrEmpFunction(); // Load initial 5

                $('#sortable_hr_emp').on('change', '.checkbox', function () {
                    let id = $(this).val().toString();
                    if ($(this).is(':checked')) {
                        selected.push(id);
                        $(this).closest('li').addClass('permanent');
                    } else {
                        let index = selected.indexOf(id);
                        if (index > -1) {
                            selected.splice(index, 1);
                        }
                        $(this).closest('li').removeClass('permanent');
                    }
                })
            })
        </script>
    </div>
@endsection
