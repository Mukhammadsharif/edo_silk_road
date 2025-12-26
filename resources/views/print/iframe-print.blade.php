<script>
    function printContent() {
        // const iframes = document.querySelectorAll('.iframe-content')
        // const iframe_texts = document.querySelectorAll('.iframe-text')
        //
        // if(iframes?.length) {
        //     iframes.forEach((item) => {
        //         item.style.display = 'none'
        //     })
        // }
        //
        // if(iframe_texts?.length) {
        //     iframe_texts.forEach((item) => {
        //         item.style.display = 'none'
        //     })
        // }

        printPageNew();

        // setTimeout(() => {
        //     printFrames()
        // }, 800)
    }

    function printFrames() {
        const iframes = document.querySelectorAll('.iframe-content')
        const iframe_texts = document.querySelectorAll('.iframe-text')

        if(iframes?.length) {
            iframes.forEach((item) => {
                item.style.display = 'block'
            })
        }

        if(iframe_texts?.length) {
            iframe_texts.forEach((item) => {
                item.style.display = 'block'
            })
        }

        if(iframes?.length) {
            iframes.forEach((item) => {
                if (item) {
                    var iframeWindow = item.contentWindow || item.contentDocument;

                    if (iframeWindow.document) {
                        iframeWindow.print();
                    } else {
                        console.error('Unable to access iframe content document');
                    }
                } else {
                    console.error('No iframe found');
                }
            })
        }
    }
</script>
