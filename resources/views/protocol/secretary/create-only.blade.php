@extends('layouts.app_create_doc')
<link rel="stylesheet" href="{{ asset('css/templates/office/document/create-only.css') }}">
@section('content')
    <script src="{{ $onlyOfficeJsUrl }}"></script>

    <div class="document-editor-modern">
        <div class="editor-layout">
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

            <div class="actions-sidebar">
                <div class="sidebar-card-modern">
                    <div class="sidebar-header">
                        <i data-lucide="settings"></i>
                        <h3>{{ __('action.actions') ?? 'Действия' }}</h3>
                    </div>

                    <div class="sidebar-body">
                        <div>
                            <div class="actions-group">
                                <a href="{{ route('prt-secretary-create', ['doc_type' => $doc_type, 'document_key' => $document_params['key']]) }}"
                                   class="btn-modern btn-primary">
                                    <i data-lucide="arrow-right"></i>
                                    <span>{{ __('field.continue') ?? 'Продолжить' }}</span>
                                </a>
                            </div>

                            <div class="help-card">
                                <i data-lucide="info"></i>
                                <p>{{ __('message.save_before_continue') ?? 'Сохраните документ перед продолжением работы' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

<style>
    .file-edit{
        color:#fff !important;
    }
</style>
