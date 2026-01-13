@extends('layouts.app_create_doc')
<link rel="stylesheet" href="{{ asset('css/templates/office/document/edit-only.css') }}">
@section('content')
    <script src="{{ $onlyOfficeJsUrl }}"></script>

    <div class="document-editor-modern">
        <div class="editor-layout">
            <!-- OnlyOffice Editor Section -->
            <div class="editor-section">
                <div class="editor-card-modern">
                    <div class="editor-header">
                        <div class="header-left">
                            <i data-lucide="file-edit"></i>
                            <span class="editor-title">{{ __('field.document_editing') ?? 'Редактирование документа' }}</span>
                        </div>
                        <div class="header-right">
                            <div class="status-badge status-editing">
                                <i data-lucide="circle"></i>
                                <span>{{ __('status.editing') ?? 'Редактирование' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="editor-body">
                        <div id="placeholder"></div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="actions-sidebar">
                <div class="sidebar-card-modern">
                    <div class="sidebar-header">
                        <i data-lucide="settings"></i>
                        <h3>{{ __('action.actions') ?? 'Действия' }}</h3>
                    </div>

                    <div class="sidebar-body">
                        <form action="{{ route('office.doc.update', ['j_id' => $j_id, 'hash' => $doc_hash]) }}" method="POST" id="continueForm">
                            @csrf

                            @php
                                function renderInputs($name, $value) {
                                    if (is_array($value)) {
                                        foreach ($value as $k => $v) {
                                            $childName = is_int($k) ? $name . '[]' : $name . '[' . $k . ']';
                                            renderInputs($childName, $v);
                                        }
                                    } else {
                                        echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">' . PHP_EOL;
                                    }
                                }
                            @endphp

                            @foreach($data as $key => $value)
                                @php renderInputs($key, $value); @endphp
                            @endforeach

                            <input type="hidden" name="document_key" value="{{ $document_params['key'] }}">

                            <div class="info-card">
                                <div class="info-item">
                                    <i data-lucide="file-text"></i>
                                    <div class="info-content">
                                        <span class="info-label">{{ __('field.document') ?? 'Документ' }}</span>
                                        <span class="info-value">{{ $document_params['title'] }}</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i data-lucide="user"></i>
                                    <div class="info-content">
                                        <span class="info-label">{{ __('field.author') ?? 'Автор' }}</span>
                                        <span class="info-value">{{ $document_params['name'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="actions-group">
                                <button type="submit" class="btn-modern btn-primary">
                                    <i data-lucide="arrow-right"></i>
                                    <span>{{ __('field.continue') ?? 'Продолжить' }}</span>
                                </button>
                            </div>

                            <div class="help-card">
                                <i data-lucide="info"></i>
                                <p>{{ __('message.save_before_continue') ?? 'Сохраните документ перед продолжением работы' }}</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <script>
        var docEditor = new DocsAPI.DocEditor("placeholder", {
            document: {
                fileType: "{{ $document_params['fileType'] }}",
                key: "{{ $document_params['key'] }}",
                title: "{{ $document_params['title'] }}",
                url: "{{ $document_params['url'] }}",
                permissions: {
                    "edit": true,
                    "download": true,
                    "save": true
                }
            },
            token: "{{ $token }}",
            documentType: "word",
            editorConfig: {
                callbackUrl: "{{ $document_params['call_back_url'] }}",
                mode: "edit",
                lang: "ru",
                user: {
                    id: "{{ $document_params['id'] }}",
                    name: "{{ $document_params['name'] }}"
                },
                customization: {
                    "autosave": false,
                    "forcesave": true
                },
                coEditing: {
                    "mode": "strict",
                    "change": false
                }
            },
        });
    </script>
@endsection
