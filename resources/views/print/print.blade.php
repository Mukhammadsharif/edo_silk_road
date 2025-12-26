

<style>
    @media print {
        #printPage {
            padding: 30px;
        }
    }
</style>
<link rel="stylesheet" href="{{ asset('/print/print.css') }}" type="text/css">
<script src='{{ asset('/print/print.js') }}'></script>
<script type="text/javascript">
    function printPageNewOld() {
        var new_str = document.getElementById('printPage').innerHTML;
        var old_str = document.body.innerHTML;
        //window.write('<body style="font-size:12pt">');
        //document.body.write('<body style="font-size:12pt">');
        document.body.innerHTML = new_str;
        //document.title = org;
        window.print();
        document.body.innerHTML = old_str;
        return false;
    }
    function printPageNew() {
        printJS({
            printable: 'printPage',
            type: 'html',
            css: [
                "{{ asset('/assets/dist/css/main.css') }}",
                "{{ asset('/print/print.css') }}",
            ],
            style: ['@page { size:A4; margin: 1cm 1.5cm 2cm 3cm;}'],
            //style: ['@page { size:A4; margin-left: 0 !important;} body {margin-left: 0 !important;}'],
            //style: ['@page .MsoTableGrid {margin-left: 0 !important;}'],
            scanStyles: false
        })
    }
</script>

<script src="{{ asset('/js/html2pdf.bundle.min.js') }}"></script>

<script>
    document.getElementById("generatePdf").addEventListener("click", () => {
        const content = document.getElementById("printPage");

        // Create an instance of the html2pdf library
        const pdfOptions = {
            margin: 10,
            filename: "UzMRC_document.pdf",
            image: { type: "jpeg", quality: 1 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: "mm", format: "a4", orientation: "portrait" }
        };

        html2pdf().from(content).set(pdfOptions).save();
    });
</script>
