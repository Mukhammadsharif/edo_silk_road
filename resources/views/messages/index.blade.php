@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-2">
                    <div class="page-header-title">
                        <i class="fa fa-sitemap bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('field.messages') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('field.messages') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <div class="col-xl-12 col-md-12">
                    <a href="{{ route('messages.list', ['type' => 'inbox']) }}" class="card prod-p-card card-blue">
                        <div class="card-body">
                            <h6 class="mb-5 text-white">{{ __('field.inbox_messages') }}</h6>

                            <div class="row align-items-center mb-30">
                                <div class="col">
                                    <h3 class="mb-0 fw-700 text-white">{{ $inboxMessagesCount }}</h3>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-inbox text-blue f-18"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-12 col-md-12">
                    <a href="{{ route('messages.list', ['type' => 'archive']) }}" class="card prod-p-card card-green">
                        <div class="card-body">
                            <h6 class="mb-5 text-white">{{ __('field.archive_messages') }}</h6>

                            <div class="row align-items-center mb-30">
                                <div class="col">
                                    <h3 class="mb-0 fw-700 text-white">{{ $archivedCount }}</h3>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box-archive text-green f-18"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-12 col-md-12">
                    <a href="{{ route('messages.list', ['type' => 'outbox']) }}" class="card prod-p-card card-yellow">
                        <div class="card-body">
                            <h6 class="mb-5 text-white">{{ __('field.out_messages') }}</h6>

                            <div class="row align-items-center mb-30">
                                <div class="col">
                                    <h3 class="mb-0 fw-700 text-white">{{ $outMessagesCount }}</h3>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-arrow-up text-warning f-18"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-xl-12 col-md-12">
                    <a href="{{ route('messages.list', ['type' => 'deleted']) }}" class="card prod-p-card card-red">
                        <div class="card-body">
                            <h6 class="mb-5 text-white">{{ __('field.cart') }}</h6>

                            <div class="row align-items-center mb-30">
                                <div class="col">
                                    <h3 class="mb-0 fw-700 text-white">{{ $deletedCount }}</h3>
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-cart-shopping text-red f-18"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>


            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('action.write_new_message') }}</h5>
                    </div>
                    <div class="card-body">
                        <form id="createForm" method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label>{{ __('field.subject') }} <span class="text-red">*</span></label>
                                            <input type="text" name="subject" class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}" value="{{ old('subject') }}" required/>
                                            {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label>{{ __('field.message_text') }}</label>
{{--                                            <div class="u-flow-xs">--}}
{{--                                                <div id="editor-loading" style="display: flex; justify-content: center; align-items: center; min-height: 100px;">--}}
{{--                                                    <img src="{{ url('loading.gif') }}" width="60px" class="loadingGif" alt="loading-gif">--}}
{{--                                                </div>--}}

{{--                                                <div class="b-demo__editor" id="b-demo-editor">--}}
{{--                                                    <div class="js-editor-wrapper" data-demo-type="feature-rich">--}}
{{--                                                        <textarea name="message_text" id="cke5-feature-rich-demo" style="display: none;">{{ old('message_text') }}</textarea>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
                                            <textarea name="message_text" id="cke5-feature-rich-demo" style="display: none;height: 400px">{{ old('message_text') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label>{{ __('action.attach_file') }}</label>
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
                                </div>

                                @include('messages.templates.user-selection')
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary float-right">
                                <i class="ik ik-save"></i> {{ __('action.send') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{--    @include('rich-editor.ckeditor5')--}}

    <link rel="stylesheet" href="{{ asset('/richeditor//richtexteditor/rte_theme_default.css') }}" />
    <script type="text/javascript" src="{{ asset('/richeditor//richtexteditor/rte.js') }}"></script>
    <script type="text/javascript" src='{{ asset('/richeditor//richtexteditor/plugins/all_plugins.js') }}'></script>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script>
        let editor1cfg = {}
        editor1cfg.toolbar = "mytoolbar";
        editor1cfg.toolbar_mytoolbar = "{bold,italic,justifyleft,justifycenter,justifyright,fontsize,forecolor,backcolor}";
        let editor1 = new RichTextEditor("#cke5-feature-rich-demo", editor1cfg);
        editor1.setHeight(500);
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            function checkEditorLoaded() {
                const editorWrapper = document.querySelector('.js-editor-wrapper');
                const editorElement = editorWrapper.querySelector('.ck-editor__editable');

                const ckPlaceholder = document.querySelector('.ck-placeholder')
                if(ckPlaceholder) {
                    ckPlaceholder.setAttribute('data-placeholder', '{{ __('field.type_text') }}')
                }

                if (editorElement) {
                    // Editor is loaded, hide loader and show editor
                    document.getElementById('editor-loading').style.display = 'none';
                    document.getElementById('b-demo-editor').style.display = 'block';
                    return true;
                }
                return false;
            }

            const pollingInterval = setInterval(() => {
                if (checkEditorLoaded()) {
                    clearInterval(pollingInterval); // Stop polling once loaded
                }
            }, 100); // Check every 100ms

            setTimeout(() => {
                clearInterval(pollingInterval);
                if (!checkEditorLoaded()) {
                    // Show error if editor still not loaded
                    document.getElementById('editor-loading').innerHTML = '<p>Error loading editor. Please refresh the page.</p>';
                }
            }, 100000);


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        })

        let fileInputCounter = 1;

        document.addEventListener('DOMContentLoaded', function () {
            // Handle Add Button Click
            document.querySelector('.add-file-input').addEventListener('click', function () {
                const container = document.getElementById('file-inputs-container');
                const newFileInputGroup = document.createElement('div');
                newFileInputGroup.className = 'file-input-group d-flex align-items-center mb-2';

                // Use local counter to ensure correct ID
                const currentCounter = fileInputCounter;

                // Create new file input
                const newFileInputDiv = document.createElement('div');
                newFileInputDiv.className = 'custom-file';
                newFileInputDiv.style.display = 'none';
                newFileInputDiv.innerHTML = `
        <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-${currentCounter}">
        <label class="custom-file-label" for="customFile-${currentCounter}">{{ __('action.choose') }}</label>
    `;
                newFileInputGroup.appendChild(newFileInputDiv);

                // Create readonly text input to display file name
                const textInput = document.createElement('input');
                textInput.type = 'text';
                textInput.className = 'form-control';
                textInput.readOnly = true;
                textInput.placeholder = "{{ __('action.choose') }}";
                textInput.style.cursor = 'pointer';
                newFileInputGroup.appendChild(textInput);

                // Create Remove Button
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-icon btn-outline-warning btn-sm ms-2 remove-file-input ml-2';
                removeButton.innerHTML = '<i class="ik ik-minus"></i>';
                {{--removeButton.innerHTML = '<i class="fas fa-trash"></i> {{ __("action.remove") }}';--}}
                removeButton.addEventListener('click', function () {
                    newFileInputGroup.remove();
                });
                newFileInputGroup.appendChild(removeButton);

                // Append to container before attaching event listeners
                container.appendChild(newFileInputGroup);

                // Now attach event listeners
                const fileInput = newFileInputDiv.querySelector(`#customFile-${currentCounter}`);
                textInput.onclick = () => {
                    const input = document.getElementById(`customFile-${currentCounter}`);
                    if (input) input.click();
                };

                // Update text input when file is selected
                fileInput.addEventListener('change', function () {
                    textInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.upload_files') }}";
                });

                // Increment counter after everything is set
                fileInputCounter++;
            });

            // Update initial file input text when file is selected
            const initialFileInput = document.getElementById('customFile-0');
            const initialTextInput = document.querySelector('.file-input-group .form-control');
            initialFileInput.addEventListener('change', function () {
                initialTextInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.upload_files') }}";
            });
        })

    </script>

@endsection
