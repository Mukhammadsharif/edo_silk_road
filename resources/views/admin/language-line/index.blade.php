@extends('layouts.app')

@section('content')
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/languages/languages-modern.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="languages"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.language') }}</h1>
                        <p>{{ __('title.language') }} / {{ __('menu.list') }}</p>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <a href="#" class="breadcrumb-item">
                        <span>{{ __('title.language') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.list') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Filters Card -->
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="filter"></i>
                        <span>{{ __('action.search') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('language.index') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="language-search-grid">
                        <!-- Search Input -->
                        <div class="search-input-with-icon">
                            <i data-lucide="search"></i>
                            <input type="text"
                                   name="s_text"
                                   value="{{ $s_text ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('action.search') }}...">
                        </div>

                        <!-- Group Filter -->
                        <div class="group-select-wrapper">
                            <select name="s_field" class="group-select-modern select2">
                                <option value="">{{ __('field.fields') }}</option>
                                @foreach($groups as $key => $item)
                                    <option value="{{ $item->group }}" {{ $s_field == $item->group ? 'selected' : '' }}>
                                        {{ $key+1 }}. {{ $item->group ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Search Button -->
                        <button type="submit" class="btn-modern btn-primary">
                            <i data-lucide="search"></i>
                            {{ __('action.search') }}
                        </button>

                        <!-- Clear Button -->
                        <a href="{{ route('language.index') }}" class="btn-modern btn-secondary">
                            <i data-lucide="refresh-ccw"></i>
                            {{ __('action.clear') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Main Table Card -->
            <div class="table-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="list"></i>
                        <span>{{ __('title.language') }}</span>
                    </div>
                    @can('language-create')
                        <button type="button" class="btn-modern btn-primary" data-toggle="modal" data-target="#AddModal" style="width: auto;">
                            <i data-lucide="circle-plus"></i>
                            {{ __('action.create') }}
                        </button>
                    @endcan
                </div>

                <div class="table-wrapper-modern">
                    <table class="modern-table languages-table-modern">
                        <thead>
                        <tr>
                            <th style="width: 60px;">
                                <i data-lucide="hash"></i>
                                {{ __('field.#') }}
                            </th>
                            <th style="width: 150px;">
                                <i data-lucide="folder"></i>
                                {{ __('field.group') }}
                            </th>
                            <th style="width: 180px;">
                                <i data-lucide="key"></i>
                                {{ __('field.key') }}
                            </th>
                            <th>
                                <i data-lucide="flag"></i>
                                {{ __('field.lang_uz') }}
                            </th>
                            <th>
                                <i data-lucide="flag"></i>
                                {{ __('field.lang_ru') }}
                            </th>
                            <th>
                                <i data-lucide="flag"></i>
                                {{ __('field.lang_en') }}
                            </th>
                            <th style="width: 180px;" colspan="2">
                                <i data-lucide="settings"></i>
                                {{ __('action.action') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $item)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td class="group-cell">{{ $item->group ?? '' }}</td>
                                <td class="key-cell">{{ $item->key ?? '' }}</td>
                                <td class="translation-cell" title="{{ $item->text['uz'] ?? '' }}">
                                    {{ $item->text['uz'] ?? '' }}
                                </td>
                                <td class="translation-cell" title="{{ $item->text['ru'] ?? '' }}">
                                    {{ $item->text['ru'] ?? '' }}
                                </td>
                                <td class="translation-cell" title="{{ $item->text['en'] ?? '' }}">
                                    {{ $item->text['en'] ?? '' }}
                                </td>
                                <td>
                                    <button type="button" value="{{ $item->id }}" class="lang-edit-btn edit_btn">
                                        <i data-lucide="square-pen"></i>
                                        {{ __('action.edit') }}
                                    </button>
                                </td>
                                <td>
                                    <button type="button" value="{{ $item->id }}" class="lang-delete-btn delete_btn">
                                        <i data-lucide="trash-2"></i>
                                        {{ __('action.delete') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="folder-x"></i>
                                        <p>{{ __('field.not_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($models->hasPages())
                    <div class="pagination-wrapper-modern">
                        {!! $models->links() !!}
                    </div>
                @endif
            </div>

        </div>
    </div>
    {{-- MODALS SECTION --}}

    <!-- Create Modal -->
    <div class="modal fade language-modal-modern" id="AddModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i data-lucide="circle-plus"></i>
                        {{ __('action.create') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="save_msgList" class="alert-list"></ul>

                    <!-- Group Input -->
                    <div class="modal-form-group">
                        <label>
                            <i data-lucide="folder"></i>
                            {{ __('field.group') }}
                        </label>
                        <input type="text" required class="group_input modal-input-modern" placeholder="Masalan: menu">
                    </div>

                    <!-- Key Input -->
                    <div class="modal-form-group">
                        <label>
                            <i data-lucide="key"></i>
                            {{ __('field.key') }}
                        </label>
                        <input type="text" required class="key_input modal-input-modern" id="key_input_c" placeholder="Masalan: home">
                    </div>

                    <!-- Translations Section -->
                    <div class="translations-section">
                        <div class="translations-section-title">
                            <i data-lucide="languages"></i>
                            <span>{{ __('field.translations') }}</span>
                        </div>

                        <!-- UZ Translation -->
                        <div class="translation-input-group">
                            <label>
                                <div class="language-flag-icon flag-uz"></div>
                                {{ __('field.lang_uz') }}
                            </label>
                            <input type="text" required class="text_uz_input modal-input-modern" placeholder="O'zbekcha tarjima">
                        </div>

                        <!-- RU Translation -->
                        <div class="translation-input-group">
                            <label>
                                <div class="language-flag-icon flag-ru"></div>
                                {{ __('field.lang_ru') }}
                            </label>
                            <input type="text" required class="text_ru_input modal-input-modern" placeholder="Перевод на русский">
                        </div>

                        <!-- EN Translation -->
                        <div class="translation-input-group">
                            <label>
                                <div class="language-flag-icon flag-en"></div>
                                {{ __('field.lang_en') }}
                            </label>
                            <input type="text" required class="text_en_input modal-input-modern" id="text_en_input_c" placeholder="English translation">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn-cancel" data-dismiss="modal">
                        <i data-lucide="x"></i>
                        {{ __('action.cancel') }}
                    </button>
                    <button type="submit" class="modal-btn-save save_btn">
                        <i data-lucide="save"></i>
                        {{ __('action.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade language-modal-modern" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i data-lucide="square-pen"></i>
                        {{ __('field.edit') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="update_msgList" class="alert-list"></ul>
                    <input type="hidden" id="model_id" />

                    <!-- Group Input -->
                    <div class="modal-form-group">
                        <label>
                            <i data-lucide="folder"></i>
                            {{ __('field.group') }}
                        </label>
                        <input type="text" id="group_input" required class="modal-input-modern">
                    </div>

                    <!-- Key Input -->
                    <div class="modal-form-group">
                        <label>
                            <i data-lucide="key"></i>
                            {{ __('field.key') }}
                        </label>
                        <input type="text" id="key_input" required class="modal-input-modern">
                    </div>

                    <!-- Translations Section -->
                    <div class="translations-section">
                        <div class="translations-section-title">
                            <i data-lucide="languages"></i>
                            <span>{{ __('field.translations') }}</span>
                        </div>

                        <!-- UZ Translation -->
                        <div class="translation-input-group">
                            <label>
                                <div class="language-flag-icon flag-uz"></div>
                                {{ __('field.lang_uz') }}
                            </label>
                            <input type="text" id="text_uz_input" required class="modal-input-modern">
                        </div>

                        <!-- RU Translation -->
                        <div class="translation-input-group">
                            <label>
                                <div class="language-flag-icon flag-ru"></div>
                                {{ __('field.lang_ru') }}
                            </label>
                            <input type="text" id="text_ru_input" required class="modal-input-modern">
                        </div>

                        <!-- EN Translation -->
                        <div class="translation-input-group">
                            <label>
                                <div class="language-flag-icon flag-en"></div>
                                {{ __('field.lang_en') }}
                            </label>
                            <input type="text" id="text_en_input" required class="modal-input-modern">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn-cancel" data-dismiss="modal">
                        <i data-lucide="x"></i>
                        {{ __('action.cancel') }}
                    </button>
                    <button type="button" class="modal-btn-save update_btn">
                        <i data-lucide="save"></i>
                        {{ __('action.save_change') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade language-modal-modern delete-modal-modern" id="DeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i data-lucide="alert-octagon"></i>
                        {{ __('text.delete_confirm') }}
                    </h4>
                    <button type="button" class="close cancel_delete_btn" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="delete-icon-container">
                        <div class="delete-icon-wrapper">
                            <i data-lucide="trash-2"></i>
                        </div>
                    </div>
                    <div class="delete-confirmation-text">
                        <h4>{{ __('text.delete_confirm') }}</h4>
                        <p>{{ __('text.delete_confirm_text') }}</p>
                    </div>
                    <input type="hidden" id="deleteing_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn-cancel cancel_delete_btn">
                        <i data-lucide="x"></i>
                        {{ __('action.cancel') }}
                    </button>
                    <button type="button" class="modal-btn-save modal-btn-danger yes_delete_btn">
                        <i data-lucide="trash-2"></i>
                        {{ __('action.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS SECTION --}}
    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Select2
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });

            // Save Language
            $(document).on('click', '.save_btn', function(e){
                e.preventDefault();

                var data = {
                    'group_input': $('.group_input').val(),
                    'key_input': $('.key_input').val(),
                    'text_uz_input': $('.text_uz_input').val(),
                    'text_ru_input': $('.text_ru_input').val(),
                    'text_en_input': $('.text_en_input').val(),
                }

                $.ajax({
                    url: '/admin/lang/store',
                    method: 'POST',
                    data: data,
                    success: function(response){
                        if (response.status === 400) {
                            $('#save_msgList').html("");
                            $('#save_msgList').addClass('alert alert-danger');
                            $.each(response.errors, function (key, err_value) {
                                $('#save_msgList').append('<li>' + err_value + '</li>');
                            });
                            $('.save_btn').text('Save');
                        } else {
                            $('#save_msgList').html("");
                            $('#AddModal').find('input').val('');
                            $('.save_btn').html('<i data-lucide="save"></i>{{ __("action.save") }}');
                            $('#AddModal').modal('hide');
                            setTimeout(() => window.location.reload(), 500);
                        }
                    },
                }).error(function(err){
                    console.log(err);
                });
            });

            // Edit Language
            $(document).on('click', '.edit_btn', function (e) {
                e.preventDefault();
                let model_id = $(this).val();
                $('#editModal').modal('show');

                $.ajax({
                    type: "GET",
                    url: "/admin/lang/edit-lang/" + model_id,
                    success: function (response) {
                        let text = JSON.parse(JSON.stringify(response.model.text));

                        if (response.status === 404) {
                            $('#editModal').modal('hide');
                        } else {
                            $('#group_input').val(response.model.group);
                            $('#key_input').val(response.model.key);
                            $('#text_uz_input').val(text.uz);
                            $('#text_ru_input').val(text.ru);
                            $('#text_en_input').val(text.en);
                            $('#model_id').val(model_id);
                        }
                    }
                });
            });

            // Update Language
            $(document).on('click', '.update_btn', function(e){
                e.preventDefault();

                $(this).html('<i data-lucide="loader" class="rotating"></i> Updating...');

                let id = $('#model_id').val();
                var data1 = {
                    'group_input': $('#group_input').val(),
                    'key_input': $('#key_input').val(),
                    'text_uz_input': $('#text_uz_input').val(),
                    'text_ru_input': $('#text_ru_input').val(),
                    'text_en_input': $('#text_en_input').val(),
                }

                $.ajax({
                    type: "PUT",
                    url: "/admin/lang/update-lang/" + id,
                    data: data1,
                    dataType: "json",
                    success: function (response) {
                        if (response.status === 400) {
                            $('#update_msgList').html("");
                            $('#update_msgList').addClass('alert alert-danger');
                            $.each(response.errors, function (key, err_value) {
                                $('#update_msgList').append('<li>' + err_value + '</li>');
                            });
                            $('.update_btn').html('<i data-lucide="save"></i>{{ __("action.save_change") }}');
                        } else {
                            $('#update_msgList').html("");
                            $('#editModal').find('input').val('');
                            $('.update_btn').html('<i data-lucide="save"></i>{{ __("action.save_change") }}');
                            $('#editModal').modal('hide');
                            setTimeout(() => window.location.reload(), 500);
                        }
                    }
                });
            });

            // Delete Language
            $(document).on('click', '.delete_btn', function () {
                let model_id = $(this).val();
                $('#DeleteModal').modal('show');
                $('#deleteing_id').val(model_id);
            });

            $(document).on('click', '.yes_delete_btn', function (e) {
                e.preventDefault();

                $(this).html('<i data-lucide="loader" class="rotating"></i> Deleting...');
                let id = $('#deleteing_id').val();

                $.ajax({
                    type: "DELETE",
                    url: "/admin/lang/delete-lang/" + id,
                    dataType: "json",
                    success: function (response) {
                        if (response.status === 404) {
                            $('.yes_delete_btn').html('<i data-lucide="trash-2"></i>{{ __("action.yes_delete") }}');
                        } else {
                            $('.yes_delete_btn').html('<i data-lucide="trash-2"></i>{{ __("action.yes_delete") }}');
                            $('#DeleteModal').modal('hide');
                            setTimeout(() => window.location.reload(), 500);
                        }
                    }
                });
            });

            $(document).on('click', '.cancel_delete_btn', function (e){
                $('#DeleteModal').modal('hide');
            });

            // Auto-fill EN from Key
            const input_c = document.getElementById("key_input_c");
            const duplicateFieldC = document.getElementById("text_en_input_c");

            if(input_c && duplicateFieldC) {
                input_c.addEventListener('keyup',() => {
                    duplicateFieldC.value = capitalizeFirstLetter(input_c.value);
                });
            }

            const input = document.getElementById("key_input");
            const duplicateField = document.getElementById("text_en_input");

            if(input && duplicateField) {
                input.addEventListener('keyup',() => {
                    duplicateField.value = capitalizeFirstLetter(input.value);
                });
            }

            function capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }
        });
    </script>

    <style>
        .rotating {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endsection
