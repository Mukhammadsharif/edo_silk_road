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

    $('#cancel').click(function () {
        let order_id = $(this).data('id');
        $('#cancelModel').removeClass("modal-primary").addClass("modal-danger");
        $('#cancelBtnConfirm').val("confirm-cancel");
        $('#cancel_id').val(order_id);
        $('#cancel_type').val('cancel');
        $('#cancelModelHeading').html('<i class="fa fa-times-circle"></i> ');
        $('#cancelText').html("{{ __('text.Are you delete this work document') }}");
        $('#cancelModel').modal('show');
    });

    $(document).ready(function() {
        // Open modal and set the document ID
        $('#cancelModel').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var doc_id = button.data('id');
            var modal = $(this);
            modal.find('#cancel_id').val(doc_id);
        });

        // AJAX call for the delete confirmation button
        $('#cancelForm').submit(function(e) {
            e.preventDefault();

            let form = $(this);
            let id = $('#cancel_id').val();

            $.ajax({
                url: "/doc-controller/delete/work-document/" + id,
                type: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    // Optional: Show a loading animation here
                },
                success: function(response) {
                    $('#cancelModel').modal('hide');
                    if (response.status === 200) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                            showSuccessMessageInTopCenter('top-center');
                        }, 1000);
                    }
                    // else {
                    //     // Handle error scenario
                    //     showErrorMessageInTopCenter('top-center');
                    // }
                },
                // error: function(err) {
                //     console.log(err);
                //     showErrorMessageInTopCenter('top-center');
                // }
            });
        });
    });


</script>
