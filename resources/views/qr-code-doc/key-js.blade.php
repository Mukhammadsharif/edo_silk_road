<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        let content = $('#doc_content');
        let key_form = $('#key_form');
        let result = $('#result');

        content.hide();

        $('#submitBtn').on('click', function () {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            let key = $('#key').val();
            let type = $('#type').val();
            let id = $('#id').val();
            let url = "{{ route('confirm-key') }}";

            if(key?.length < 4){
                result.html(`<p class="text-center text-danger" id="message">4 xonali kodni kiriting</p>`)

                setTimeout(() => {
                    result.html('')
                }, 1500)
            } else {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {_token: CSRF_TOKEN, key: key, type: type, id: id},
                    dataType: 'JSON',
                    success: function (response) {
                        console.log(response)
                        if(response.success){
                            result.html(`<p class="text-center text-success" id="message">${response.message}</p>`)
                            setTimeout(() => {
                                key_form.hide();
                                content.show();
                            }, 2000)
                        } else {
                            result.html(`<p class="text-center text-danger" id="message">${response.message}</p>`)

                            setTimeout(() => {
                                result.html('')
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
    });
</script>
