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
                <form action="{{ route('office.doc.update', ['j_id' => $j_id, 'hash' => $doc_hash]) }}" method="POST">
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

                    <button type="submit"
                            class="btn btn-outline-primary">
                        {{ __('field.continue') }} <i class="ik ik-chevrons-right"></i>
                    </button>
                </form>
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
