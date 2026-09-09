@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/protocol/secretary-create.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/templates/edit-execution-steps.css') }}">

@section('content')

    <div class="modern-container">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="page-icon">
                        <i data-lucide="edit-3"></i>
                    </div>
                    <div class="page-info">
                        <h1>{{ __('action.edit_document') }}</h1>
                        <p>{{ $document->docType->lang($document->docType->id) }}</p>
                    </div>
                </div>

                <nav class="breadcrumb-modern">
                    <a href="{{ route('home') }}">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <a href="{{ url('protocol/secretary/view', [$document->id, $document->doc_hash]) }}">
                        {{ $document->doc_name }}
                    </a>
                    <i data-lucide="chevron-right" class="separator"></i>
                    <span class="current">{{ __('action.edit_document') }}</span>
                </nav>
            </div>
        </div>

        @include('templates.alert')

        <div class="form-card">
            <form id="editForm" method="POST" action="{{ route('prt-secretary-update', [$document->id, $document->doc_hash]) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <input name="document_key" value="{{ $document_key }}" hidden>

                    <div class="form-section">
                        <div class="form-group-modern">
                            <label for="doc_lang">
                                <i data-lucide="file-type"></i>
                                {{ __('field.doc_lang') }}
                            </label>
                            <select name="doc_lang" class="form-control-modern select2">
                                <option value="uz" {{ $document->doc_lang == 'uz' ? 'selected': '' }}>{{ __('lang.uzbek') }}</option>
                                <option value="kr" {{ $document->doc_lang == 'kr' ? 'selected': '' }}>{{ __('lang.cyrillic') }}</option>
                                <option value="ru" {{ $document->doc_lang == 'ru' ? 'selected': '' }}>{{ __('lang.russian') }}</option>
                                <option value="en" {{ $document->doc_lang == 'en' ? 'selected': '' }}>{{ __('lang.english') }}</option>
                            </select>
                        </div>

                        <div class="form-group-modern">
                            <label>
                                <i data-lucide="eye"></i>
                                {{ __('field.visibility') }}
                            </label>
                            <select name="doc_visibility" class="form-control-modern select2">
                                <option value="2" {{ $document->visibility == 2 ? 'selected': '' }}>1. {{ __('field.public') }}</option>
                                <option value="1" {{ $document->visibility == 1 ? 'selected': '' }}>2. {{ __('field.secret') }}</option>
                            </select>
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_date') }}</label>
                            <input type="date" name="doc_date"
                                   class="form-control-modern {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                                   value="{{ date('Y-m-d', strtotime($document->doc_date)) }}"/>
                            {!! $errors->first('doc_date', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_term') }}</label>
                            <input type="date" name="doc_term" class="form-control-modern"
                                   value="{{ $document->doc_term ? date('Y-m-d', strtotime($document->doc_term)) : '' }}"/>
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_name') }} <span class="text-red">*</span></label>
                            <input type="text" name="doc_name"
                                   class="form-control-modern {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                   value="{{ $document->doc_name }}" placeholder="{{__('field.doc_name')}}"/>
                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group-modern">
                            <label>{{ __('field.doc_number') }}</label>
                            <input type="text" name="doc_number" class="form-control-modern"
                                   value="{{ $document->doc_number }}" placeholder="{{ __('field.doc_number') }}">
                        </div>

                        <!-- Existing Files Section -->
                        @if(count($documentFiles) > 0)
                            <div class="form-group-modern">
                                <label>
                                    <i data-lucide="files"></i>
                                    {{ __('field.doc_files') }}
                                </label>
                                <div class="existing-files-list">
                                    @foreach($documentFiles as $key => $file)
                                        <div class="file-item-modern" id="file_id_{{ $file->id }}">
                                            <div class="file-item-icon">
                                                @switch(strtolower($file->file_ext))
                                                    @case('jpg')
                                                    @case('jpeg')
                                                    @case('png')
                                                        <i data-lucide="image"></i>
                                                        @break
                                                    @case('pdf')
                                                        <i data-lucide="file-text"></i>
                                                        @break
                                                    @case('doc')
                                                    @case('docx')
                                                        <i data-lucide="file-text"></i>
                                                        @break
                                                    @case('xls')
                                                    @case('xlsx')
                                                        <i data-lucide="table"></i>
                                                        @break
                                                    @default
                                                        <i data-lucide="file"></i>
                                                @endswitch
                                            </div>
                                            <div class="file-item-info">
                                                <a href="#" target="_blank" class="file-item-name"
                                                   onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>',
                                                       'modal',
                                                       'width=800,height=900,top=30,left=500');
                                                       return false;">
                                                    {{ $file->file_name }}
                                                </a>
                                                <span class="file-item-size">{{ number_format($file->file_size/1024, 2) }} KB</span>
                                            </div>
                                            <div class="file-item-actions">
                                                <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}"
                                                   class="btn-file-action btn-file-download"
                                                   title="{{ __('action.download') }}">
                                                    <i data-lucide="download"></i>
                                                </a>
                                                <button type="button" class="btn-file-action btn-file-delete deleteItem"
                                                        data-id="{{$file->id}}"
                                                        title="{{ __('action.delete') }}">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

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
                            <button type="submit" name="action" value="file" id="submitBtn" class="btn-submit"
                                    onclick="setFormAction('file')">
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
                                                {{ in_array($item->user_id, $selectedUsersToArray) ? 'checked' : '' }}
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
                                                        @if($item->deleted_at)
                                                            <span class="badge-removed">{{ __('field.removed') }}</span>
                                                        @endif
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
                                                                {{ in_array($item->user_id, $selectedUsersToArray) ? ($selectedUserRoles->where('user_id', $item->user_id)->first()?->role_id == $role->code ? 'selected' : '') : ($role->code == 3 ? 'selected' : '') }}>
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

                            <ul id="sortable_hr_emp" class="users-list-modern sortable-list">
                                @foreach($selectedHrEmp as $hrUser)
                                    <li class="select-hr-user sortable-item permanent">
                                        <div class="user-checkbox-card-extended">
                                            <input type="checkbox"
                                                   name="hr_user_id[]"
                                                   id="{{ $hrUser->userWork->id }}"
                                                   value="{{ $hrUser->userWork->id  }}"
                                                   class="checkbox"
                                                   checked>
                                            <div class="user-card-content-extended">
                                                <div class="checkbox-indicator">
                                                    <i data-lucide="check" class="text-white"></i>
                                                </div>
                                                <div class="user-avatar" style="background: #22c55e !important;">
                                                    <i data-lucide="user"></i>
                                                </div>
                                                <div class="user-info">
                                                    <span class="user-name">
                                                        {{ $hrUser->userWork->personal->l_name ?? '' }} {{ $hrUser->userWork->personal->f_name ?? '' }}
                                                    </span>
                                                    <span class="user-position">
                                                        {{ $hrUser->userWork->department ? $hrUser->userWork->department->lang($hrUser->userWork->department->id) : '' }}
                                                    </span>
                                                    <span class="user-department">
                                                        {{ $hrUser->userWork->position ? $hrUser->userWork->position->lang($hrUser->userWork->position->id) : '' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirm Delete Modal -->
        <div class="modal fade" id="ConfirmModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i data-lucide="alert-triangle"></i>
                            {{ __('action.confirm_delete') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('action.confirm_delete_message') }}</p>
                        <input type="hidden" id="item_conf_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-dismiss="modal">
                            <i data-lucide="x"></i>
                            {{ __('action.cancel') }}
                        </button>
                        <button type="button" class="btn-delete deleteItemConfirm">
                            <i data-lucide="trash-2"></i>
                            {{ __('action.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Response Modal -->
        <div class="modal fade" id="responseModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="responseModalLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-submit" data-dismiss="modal">
                            {{ __('action.close') }}
                        </button>
                    </div>
                </div>
            </div>
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

            var selected = @json($selectedHrEmp->pluck('id')->map(function($id) { return (string)$id; })->toArray());

            function searchHrEmpFunction() {
                clearTimeout(window.searchHrEmpTimeout);
                window.searchHrEmpTimeout = setTimeout(function () {
                    let query = $('#hrEmpSearch').val();

                    $.ajax({
                        url: '/protocol/search-hr-emp/{{ $document->docType->doc_hash }}',
                        method: 'GET',
                        data: {
                            query: query,
                            doc_id: {{ $document->id }}
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
                                            <input type="checkbox" name="hr_user_id[]" id="${item.id}" value="${item.id}" class="checkbox">
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

                // Click handler for user cards
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
            })

            $('#sortable_hr_emp').on('change', '.checkbox', function() {
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

            $('.deleteItem').on('click', function () {
                let file_id = $(this).data('id');
                $('#ConfirmModal').modal('show');
                $('#item_conf_id').val(file_id);
            });

            $('.deleteItemConfirm').on('click', function () {
                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                let id = $('#item_conf_id').val();
                let url = '/protocol/file/delete/' + id;

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN, id: id},
                    dataType: 'JSON',
                    beforeSend: function(){
                        $('#ConfirmModal').modal('toggle');
                    },
                    success: function(res){
                        $('#responseModalLabel').text(res.message);
                        $('#responseModal').modal('toggle');
                        if (res.res === 2) {
                            $('#file_id_' + res.id).hide();
                        } else {
                            $('#responseModalLabel').text('Error! Delete file');
                        }
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            });
        </script>

        <style>
            /* Additional styles for edit page */
            .existing-files-list {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .file-item-modern {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem;
                background: white;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                transition: all 0.3s;
            }

            .file-item-modern:hover {
                border-color: #794DFD;
                background: #faf5ff;
            }

            .file-item-icon {
                width: 48px;
                height: 48px;
                background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .file-item-icon i {
                width: 24px;
                height: 24px;
                color: white;
            }

            .file-item-info {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
                min-width: 0;
            }

            .file-item-name {
                font-size: 14px;
                font-weight: 500;
                color: #1e293b;
                text-decoration: none;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                transition: color 0.3s;
            }

            .file-item-name:hover {
                color: #794DFD;
            }

            .file-item-size {
                font-size: 12px;
                color: #64748b;
            }

            .file-item-actions {
                display: flex;
                gap: 0.5rem;
                flex-shrink: 0;
            }

            .btn-file-action {
                width: 36px;
                height: 36px;
                border: none;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
            }

            .btn-file-download {
                background: #dbeafe;
                color: #3b82f6;
            }

            .btn-file-download:hover {
                background: #3b82f6;
                color: white;
                transform: translateY(-2px);
            }

            .btn-file-delete {
                background: #fee2e2;
                color: #ef4444;
            }

            .btn-file-delete:hover {
                background: #ef4444;
                color: white;
                transform: translateY(-2px);
            }

            .btn-file-action i {
                width: 18px;
                height: 18px;
            }

            /* Modal Styles */
            .modern-modal {
                border-radius: 16px;
                border: none;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            }

            .modern-modal .modal-header {
                background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
                color: white;
                border-radius: 16px 16px 0 0;
                padding: 1.5rem;
                border: none;
            }

            .modern-modal .modal-title {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 18px;
                font-weight: 600;
            }

            .modern-modal .modal-title i {
                width: 24px;
                height: 24px;
            }

            .modern-modal .modal-body {
                padding: 2rem;
            }

            .modern-modal .modal-footer {
                border: none;
                padding: 1rem 2rem 2rem;
                gap: 1rem;
            }

            .btn-cancel {
                padding: 0.75rem 1.5rem;
                background: #f1f5f9;
                color: #475569;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-cancel:hover {
                background: #e2e8f0;
            }

            .btn-cancel i {
                width: 18px;
                height: 18px;
            }

            .btn-delete {
                padding: 0.75rem 1.5rem;
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-delete:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            }

            .btn-delete i {
                width: 18px;
                height: 18px;
            }

            /* Removed user styling */
            .removed-user .user-checkbox-card-extended {
                opacity: 0.6;
                border-color: #cbd5e1;
            }

            .removed-user .user-avatar {
                background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%) !important;
            }

            .badge-removed {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                background: #fee2e2;
                color: #991b1b;
                border-radius: 4px;
                font-size: 10px;
                font-weight: 600;
                margin-left: 0.5rem;
                text-transform: uppercase;
            }

            .bg-secondary {
                background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%) !important;
            }

            /* Sortable placeholder */
            .sortable-placeholder {
                background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
                border: 2px dashed #794DFD;
                border-radius: 10px;
                height: 80px;
                margin-bottom: 0.75rem;
                visibility: visible !important;
            }
        </style>
    </div>
@endsection
