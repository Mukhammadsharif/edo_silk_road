<div class="execution-card-modern actions-card">
    <div class="card-header-modern">
        <i data-lucide="printer" class="text-white"></i>
        <h3>{{ __('action.print') }}</h3>
    </div>

    <div class="actions-body-modern-print">
        <button type="button" onclick="printContent()" class="btn-modern btn-primary btn-rounded">
            <i data-lucide="printer"></i> {{ __('field.print') }}
        </button>

        @if(in_array('IT admin', $authrole) && $document->status != 6)
            <button type="button" class="btn-modern btn-danger btn-rounded" data-toggle="modal" data-target="#cancelModel" data-id="{{ $document->id }}">
                <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
            </button>
        @endif

        <button type="button" onclick="downloadAsPDF()" class="btn-modern btn-dark btn-rounded">
            <i data-lucide="download"></i> {{ __('action.download') }}
        </button>
        <input id="downloadName" value="{{ $document->docType->lang($document->docType->id)??'' }} - {{ $document->doc_number??'' }}" hidden/>

        {{--<a href="{{ route('pdf.hr-prt-doc', [$document->id, $document->doc_hash]) }}"
           class="btn btn-outline-secondary btn-rounded">
            <i data-lucide="download"></i> {{ __('field.download') }}
        </a>--}}

        @if($document->status == 3)
            <br><br>
            @include('qr-code-doc.qr-code-view')
            <input id="doc_id" value="{{ $document->id }}" hidden>
            <input id="doc_type" value="PRT_DOC" hidden>
        @endif
    </div>


    <div class="modal fade" id="cancelModel">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="cancelForm" action="" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-content modal-modern">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4>{{ __('action.delete') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Modal Body -->
                    <div class="modal-body">
                        <input type="hidden" id="cancel_id" name="doc_id" value="">
                        <p><i class="ik ik-message-circle"></i> Siz, tanlagan yozuvlar o`chiriladi!</p>
                    </div>
                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn-modern btn-light" data-dismiss="modal">
                            <i class="ik ik-x"></i>{{ __('action.close') }}
                        </button>
                        <button type="submit" class="btn-modern btn-danger">
                            <i class="ik ik-trash-2"></i>{{ __('action.yes_delete') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

@include('qr-code-doc.qr-code-view-js')
@include('protocol.del_protocol_js')
@include('templates.download')
<style>
    @media print {
        #printPage {
            padding: 30px;
        }
    }
</style>

<link rel="stylesheet" href="{{ asset('/print/print.css') }}" type="text/css">

<script src='{{ asset('/print/print.js') }}'></script>

<script src="{{ asset('/js/html2pdf.bundle.min.js') }}"></script>

<script>

    $(document).ready(function () {
        $('.modal').each(function () {
            $(this).appendTo('body');
        });
    });

    function printPageNew() {
        printJS({
            printable: 'printPage',
            type: 'html',
            css: [
                "{{ asset('/assets/dist/css/main.css') }}",
                "{{ asset('/print/print.css') }}",
            ],
            style: ['@page { size:A4; margin: 2cm 1.5cm 2cm 3cm;}'],
            //style: ['@page { size:A4; margin-left: 0 !important;} body {margin-left: 0 !important;}'],
            //style: ['@page .MsoTableGrid {margin-left: 0 !important;}'],
            scanStyles: false
        })
    }

    document.getElementById("generatePdf")?.addEventListener("click", () => {
        const content = document.getElementById("printPage");

        // Create an instance of the html2pdf library
        const pdfOptions = {
            margin: 10,
            filename: "EAI_document.pdf",
            image: { type: "jpeg", quality: 1 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: "mm", format: "a4", orientation: "portrait" }
        };

        html2pdf().from(content).set(pdfOptions).save();
    });
</script>
