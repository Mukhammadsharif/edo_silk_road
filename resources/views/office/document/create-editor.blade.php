@extends('layouts.app_create_doc')
@section('content')

    <script src="{{ url('http://172.16.100.126:8000/web-apps/apps/api/documents/api.js') }}"></script>

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
                <a href="{{ route('office.doc.create', ['document_key' => $document_params['document_key']]) }}"
                   class="btn btn-outline-primary">
                    Davom ettirish <i class="ik ik-chevrons-right"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
        var docEditor = new DocsAPI.DocEditor("placeholder", {
            document: {
                fileType: "{{ $document_params['fileType'] }}",
                key: "{{ $document_params['document_key'] }}",
                title: "{{ $document_params['title'] }}",
                url: "{{ $document_params['example_document_url'] }}",
                permissions: {
                    "edit": true,
                    "download": true,
                    "save": true
                }
            },
            token: "{{ $token }}",
            documentType: "word",
            editorConfig: {
                callbackUrl: 'http://172.17.110.44:8083/api/callback',
                mode: "edit",
                lang: "en",
                user: {
                    id: "1",
                    name: "Muxiddin"
                },
                customization: {
                    "autosave": false,
                    "forcesave": true
                },
                coEditing: {
                    "mode": "strict",
                    "change": false
                },
                events: {
                    onDocumentReady: function () {
                        console.log("Document is ready");
                        document.getElementById("saveButton").disabled = false;
                    }
                }
            },
        });
    </script>
@endsection

