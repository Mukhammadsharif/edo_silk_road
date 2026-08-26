<!DOCTYPE html>
<html>
@include('qr-code-doc.qr-head')

<body class="hold-transition skin-blue layout-top-nav" onselectstart="return false">

@include('qr-code-doc.qr-header')

<div class="wrapper">
    <div class="content-wrapper">
        <div class="container" style="margin-top: 7em">
            <div class="row">

                @include('qr-code-doc.qr-doc-left')

                <div class="col-md-8 offset-0">
                    <div class="card table-card" style="max-width: 794px;margin: 0 auto">

                        <div class="card-header">
                            <h3 class="text-center"></h3>
                        </div>

                        <div class="card-body">
                            @if(in_array($journal->status, [9, 10, 11]))
                                <input id="id" value="{{ $document->id }}" hidden>
                                <input id="type" value="work_doc" hidden>

                                @include('qr-code-doc.key-form')

                                {{-- v69: free-sign — PDF viewer со штампами вместо HTML-шаблона --}}
                                @if(!empty($isFreeSignDoc) && $isFreeSignDoc)
                                    @include('execution.confirm.templates.free-sign-page')
                                @else
                                    @include('qr-code-doc.doc-contents.work-doc')
                                @endif

                            @else
                                <h6 class="text-center text-danger">{{ __('field.doc_is_not_full_signed') }}</h6>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('qr-code-doc.key-js')
</body>
</html>
