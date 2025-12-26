<script type="text/javascript">

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

    // Cancel button AJAX call
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
                url: "/doc-controller/delete/protocol-document/" + id,
                type: "PATCH",
                data: form.serialize(),
                beforeSend: function(){
                    // You can add some loading animation here if needed
                },
                success: function(response){
                    $('#cancelModel').modal('hide');
                    if (response.status === 200) {
                        showSuccessMessageInTopCenter('top-center');
                        window.location.href = response.redirect;
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                    // else {
                    //     // Handle error scenario
                    //     showErrorMessageInTopCenter('top-center');
                    // }
                },
                // error: function(err){
                //     console.log(err);
                //     showErrorMessageInTopCenter('top-center');
                // }
            });
        });
    });

</script>
