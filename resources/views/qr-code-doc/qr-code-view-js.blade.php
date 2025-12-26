<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        let key_content = $('#key_content');
        let show_code = $('#show_code');
        let hide_code = $('#hide_code');
        let create_code = $('#create_code');

        key_content.hide();
        hide_code.hide();

        show_code.on('click', function () {
            key_content.show()
            show_code.hide()
            hide_code.show()
        });

        hide_code.on('click', function () {
            key_content.hide()
            show_code.show()
            hide_code.hide()
        });

        $('#update_code').on('click', function () {
            updateCode()
        });

        create_code.on('click', function () {
            createCode()
        });

        function updateCode() {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            let type = $('#doc_type').val();
            let id = $('#doc_id').val();
            let url = "{{ route('create-key') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: {_token: CSRF_TOKEN, type: type, id: id},
                dataType: 'JSON',
                success: function (response) {
                    if(response){
                        key_content.html(response)
                        key_content.show()
                        show_code.hide()
                        hide_code.show()
                    } else {
                        let old_code = document.getElementById('key_content').innerHTML
                        key_content.html("Xatolik!")
                        key_content.show()
                        setTimeout(() => {
                            key_content.html(old_code)
                            key_content.hide()
                        }, 2000)
                    }
                },
                complete: function (res) {

                },

                error: function (data) {
                    console.log(data)
                }
            });
        }


        function createCode() {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            let type = $('#doc_type').val();
            let id = $('#doc_id').val();
            let url = "{{ route('create-key') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: {_token: CSRF_TOKEN, type: type, id: id},
                dataType: 'JSON',
                success: function (response) {
                    if(response){
                       window.location.reload()
                    } else {
                        key_content.html("Xatolik!")
                        key_content.show()
                        setTimeout(() => {
                            key_content.hide()
                        }, 2000)
                    }
                },
                complete: function (res) {

                },

                error: function (data) {
                    console.log(data)
                }
            });
        }
    });
</script>
