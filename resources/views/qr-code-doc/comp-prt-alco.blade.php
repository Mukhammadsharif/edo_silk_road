<!DOCTYPE html>
<html>
@include('qr-code-doc.qr-head')

<body class="hold-transition skin-blue layout-top-nav" onselectstart="return false">

@include('qr-code-doc.qr-header')

<div class="wrapper">
    <div class="content-wrapper">
        <div class="container" style="margin-top: 7em">
            <div class="row">

                @include('qr-code-doc.qr-comp-prt-left')

                <div class="col-md-8 offset-0">
                    <div class="card table-card" style="max-width: 794px;margin: 0 auto">

                        <div class="card-header">
                            <h3 class="text-center"></h3>
                        </div>

                        <div class="card-body">
                            @if($document->status == 5)
                                <input id="id" value="{{ $document->id }}" hidden>
                                <input id="type" value="com_protocol" hidden>

                                @include('qr-code-doc.key-form')

                                @include('qr-code-doc.doc-contents.comp')

                            @else
                                <h6 class="text-center text-danger"> {{ __('field.doc_is_not_full_signed') }}</h6>
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
