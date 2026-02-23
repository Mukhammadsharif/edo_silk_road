<script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.deleteItem').on('click', function () {

            let dd = $(this).data('id');

            let delete_text = "{{ __('message.delete_you_want_doc') }}";

            $('#ConfirmModal').modal('show');
            $('.delete_text').html(delete_text);

            $('#item_conf_id').val(dd);

        });

        $('.deleteConfItem').on('click', function (){
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            let id = $('#item_conf_id').val();

            let url = "{{ route('office.del-doc', ':id') }}";

            url = url.replace(':id', id);

            $.ajax({

                url: url,
                type: 'GET',
                data: {_token: CSRF_TOKEN, id: id},
                dataType: 'JSON',
                beforeSend: function(){
                    $('#ConfirmModal').modal('toggle')
                    $('.loadModal').modal('toggle');
                },
                success: function(response){
                    if (response.success === 200) {
                        $('.loadModal').modal('toggle');
                        $('#resModalTitle').text(response.title);
                        $('#resModalMessage').text(response.message);
                        $('#responseModal').modal('toggle');
                        $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                        setTimeout(function () {
                            window.location.href = response.url;
                        }, 2000);
                    } else {
                        $('.loadModal').modal('toggle');
                        $('#resModalTitle').text(response.title);
                        $('#resModalMessage').text(response.message);
                        $('#responseModal').modal('toggle');
                        $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                        setTimeout(function () {
                            window.location.href = response.url;
                        }, 4000);
                    }

                },
                complete:function(res){

                },

                error: function (data) {
                    console.log(data)
                }
            });
        });
    });
</script>
