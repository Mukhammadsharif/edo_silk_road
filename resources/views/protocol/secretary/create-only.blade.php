@extends('layouts.app_create_doc')
@section('content')
    <script src="{{ $onlyOfficeJsUrl }}"></script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9" style="height: 100%;">
                <div class="card" style="position: fixed; width: 60%; height: 92%;">
                    <div class="card-body" style="padding: 0; border: 1px solid rgba(0,123,255,0.58)">
                        <div id="placeholder"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mt-15">
                <a href="{{ route('prt-secretary-create', ['doc_type' => $doc_type, 'document_key' => $document_params['key']]) }}"
                   class="btn btn-outline-primary">
                    {{ __('field.continue') }} <i class="ik ik-chevrons-right"></i>
                </a>
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
