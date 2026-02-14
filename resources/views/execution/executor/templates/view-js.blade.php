<script type="text/javascript">

    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // For Users
        $('#edoUsers').hide();

        $('#resDocForm').hide();

        $('.selected-additional-performers').hide();

        $('.btnSave').hide();

        $('#letterChip').on('click', function () {

            $('#edoUsers').slideToggle(400);
            $('#resDocForm').slideToggle(400);
            $('#depHeads').slideToggle(400);
            $('.selected-additional-performers').slideToggle(400);

        });

        $('tbody').on('click', '.select-user', function () {

            $(this).removeClass('select-user').addClass('unselect-user').appendTo('tbody#selectedUsers');

            $('#selectedUsers .performer_type').show();

            $('.unselect-user .removeItem').show();

            $('.btnSave').show();

        });

        // remove selected user (in left)
        $('.removeItem').hide();

        $('.removeItem').on('click', function () {

            var listVal = $(this).val();

            var listItem = '.listItem' + listVal;

            $(listItem).removeClass('unselect-user').addClass('select-user').appendTo('tbody#unSelectedUsers');

            $(listItem + '.select-user span').addClass('performer_type');

            $('.select-user .performer_type').hide();
        });

        // close Modal
        $('.closeModal').click(function () {

            $('#myModal').hide();

        });

        $('.deleteItem').on('click', function () {

            let dd = $(this).data('id');

            $('#ConfirmModal').modal('show');

            $('#item_conf_id').val(dd);

        });

        $('.deleteComment').on('click', function () {
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            let id = $('#item_conf_id').val();

            let url = "{{ route('delete.reply-comment', ':id') }}";

            url = url.replace(':id', id);

            $.ajax({

                url: url,
                type: 'GET',
                data: {_token: CSRF_TOKEN, id: id},
                dataType: 'JSON',
                beforeSend: function () {
                    $('#ConfirmModal').modal('toggle')
                },
                success: function (response) {
                    $('#responseModal').modal('toggle');
                    $('#resModalTitle').text('Muvaffaqiyatli xabar');
                    $('#resModalMessage').text(response.message);
                    $('.modal-content-class').addClass('bg-success')
                    $('#doc_' + id).hide();
                    setTimeout(() => {
                        window.location.reload()
                    }, 2000)

                },
                complete: function (res) {

                },

                error: function (data) {
                    console.log(data)
                }
            });
        });

    });

    function searchUserFunction() {
        let input = document.getElementById("userSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-user');

        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
                nodes[i].style = "border-top: 1px solid #dee2e6; padding: .75rem";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function printPage() {
        let prtContent = document.getElementById("printPage");
        let WinPrint = window.open();
        WinPrint.document.write(prtContent.innerHTML);
        WinPrint.document.head.insertAdjacentHTML('beforeend', '<link rel="stylesheet" type="text/css" href="/assets/node_modules/bootstrap/dist/css/bootstrap.min.css"/>');
        WinPrint.document.head.insertAdjacentHTML('beforeend', '<link rel="stylesheet" type="text/css" href="/assets/dist/css/theme.min.css"/>');
        WinPrint.document.close();
        WinPrint.focus();
        WinPrint.print();
        WinPrint.close();
    }


</script>
