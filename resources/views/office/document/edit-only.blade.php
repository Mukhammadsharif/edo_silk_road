@extends('layouts.app_create_doc')
<link rel="stylesheet" href="{{ asset('css/templates/office/document/edit-only.css') }}">
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
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <script>
        // Состояние «есть несохранённые изменения» (обновляется редактором)
        window.__ooModified = false;
        window.__ooOnSaved = null;

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
                    "autosave": true,
                    "forcesave": true
                },
                coEditing: {
                    "mode": "strict",
                    "change": false
                }
            },
            events: {
                // event.data === true  → есть несохранённые изменения
                // event.data === false → всё сохранено
                onDocumentStateChange: function (event) {
                    window.__ooModified = !!(event && event.data);
                    if (!window.__ooModified && typeof window.__ooOnSaved === 'function') {
                        var cb = window.__ooOnSaved;
                        window.__ooOnSaved = null;
                        cb();
                    }
                }
            }
        });
    </script>

    <script>
        // «Продолжить»: уходим только после подтверждённого сохранения.
        // Если изменений нет — уходим сразу. Если есть — инициируем
        // сохранение и ждём подтверждения. Не теряем правки молча.
        (function () {
            var form = document.getElementById('continueForm');
            if (!form) return;

            var docKey = @json($document_params['key']);
            var forceSaveUrl = @json(route('office.onlyoffice.forcesave'));
            var csrf = @json(csrf_token());
            var sent = false;

            // Нативный submit в обход возможного поля name="submit"/"action".
            var nativeSubmit = HTMLFormElement.prototype.submit;

            function finalizeAndSubmit() {
                if (sent) return;
                sent = true;
                // Документ сохранён — снимаем предупреждение браузера и
                // корректно закрываем редактор, затем отправляем форму.
                try { window.onbeforeunload = null; } catch (e) {}
                try {
                    if (window.docEditor && typeof window.docEditor.destroyEditor === 'function') {
                        window.docEditor.destroyEditor();
                    }
                } catch (e) {}
                setTimeout(function () {
                    try { nativeSubmit.call(form); }
                    catch (e) { form.submit(); }
                }, 150);
            }

            function triggerForceSave() {
                return fetch(forceSaveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ key: docKey })
                }).then(function (r) { return r.json().catch(function () { return {}; }); });
            }

            form.addEventListener('submit', function (e) {
                if (sent) return;            // программная отправка — пропускаем
                e.preventDefault();

                var btn = form.querySelector('button[type="submit"]');
                if (btn) btn.disabled = true;

                // ВСЕГДА инициируем сохранение перед уходом (не полагаемся
                // на флаг изменений — он есть не во всех версиях OnlyOffice).
                var resolved = false;
                function onSaved() {
                    if (resolved) return;
                    resolved = true;
                    finalizeAndSubmit();
                }

                // 1) Реакция на событие редактора «документ сохранён» (если есть)
                window.__ooOnSaved = onSaved;

                // 2) Серверный forcesave. При подтверждении даём callback'у
                //    мгновение долететь (сохранить docx) и уходим.
                triggerForceSave().then(function (res) {
                    if (res && (res.ok === true || res.error === 0 || res.error === 4)) {
                        setTimeout(onSaved, 800);
                    }
                }).catch(function () {});

                // 3) Если за 12 секунд не подтвердилось — спрашиваем,
                //    чтобы НЕ потерять изменения молча.
                setTimeout(function () {
                    if (resolved || sent) return;
                    window.__ooOnSaved = null;
                    if (window.confirm('Не удалось подтвердить сохранение документа. Продолжить без сохранения последних изменений?')) {
                        finalizeAndSubmit();
                    } else {
                        if (btn) btn.disabled = false;
                    }
                }, 12000);
            });
        })();
    </script>
@endsection


<style>
    .file-edit{
        color:#fff !important;
    }
    .header-left svg{
        color:#fff !important
    }



</style>
