<script src="{{ asset('/js/html2canvas.min.js') }}"></script>
<script src="{{ asset('/js/jspdf.umd.min.js') }}"></script>

<script>
    const { jsPDF } = window.jspdf;

    function downloadAsPDF() {
        const element = document.getElementById('printPage');

        const iframesTexts = Array.from(element.querySelectorAll('.iframe-text'))
        const originalDisplayTexts = iframesTexts.map(iframeText => iframeText.style.display);
        iframesTexts.forEach(iframeText => iframeText.style.display = 'none');
        const iframes = Array.from(element.querySelectorAll('iframe'));
        const originalDisplay = iframes.map(iframe => iframe.style.display);
        iframes.forEach(iframe => iframe.style.display = 'none');

        html2canvas(element, { scale: 2, useCORS: true }).then(canvas => {

            iframes.forEach((iframe, index) => iframe.style.display = originalDisplay[index]);
            iframesTexts.forEach((iframesText, index) => iframesText.style.display = originalDisplayTexts[index]);
            // Define custom margins (in mm)
            const marginTop = 15;
            const marginRight = 15;
            const marginBottom = 25;
            const marginLeft = 30;

            const pageWidth = 210;
            const pageHeight = 297;
            const pdf = new jsPDF('p', 'mm', 'a4');

            const canvasWidth = canvas.width;
            const canvasHeight = canvas.height;

            const pdfContentWidth = pageWidth - marginLeft - marginRight;
            const scaleRatio = pdfContentWidth / canvasWidth;
            const pdfContentHeight = canvasHeight * scaleRatio;

            const pageCanvas = document.createElement('canvas');
            const pageCtx = pageCanvas.getContext('2d');

            const onePageCanvasHeight = (pageHeight - marginTop - marginBottom) / scaleRatio;
            const totalPages = Math.ceil(canvasHeight / onePageCanvasHeight);

            for (let page = 0; page < totalPages; page++) {
                const startY = page * onePageCanvasHeight;
                const sliceHeight = Math.min(onePageCanvasHeight, canvasHeight - startY);

                pageCanvas.width = canvasWidth;
                pageCanvas.height = sliceHeight;
                pageCtx.clearRect(0, 0, canvasWidth, sliceHeight);
                pageCtx.drawImage(canvas, 0, -startY);

                const imgData = pageCanvas.toDataURL('image/jpeg', 1.0);
                if (page > 0) pdf.addPage();

                pdf.addImage(
                    imgData,
                    'JPEG',
                    marginLeft,
                    marginTop,
                    pdfContentWidth,
                    sliceHeight * scaleRatio
                );
            }

            const filename = document.querySelector('#downloadName')?.value || '1';
            pdf.save(`${filename}.pdf`);
        }).catch(error => {
            iframesTexts.forEach((iframesText, index) => iframesText.style.display = originalDisplayTexts[index]);
            iframes.forEach((iframe, index) => iframe.style.display = originalDisplay[index]);
            console.error('Error:', error);
            alert('Failed to generate PDF');
        });
    }
</script>

