<script type="text/javascript">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#loginSignForm').on('submit', function(e){

            e.preventDefault();

            $.ajax({
                url: "{{ route('login.sign.doc') }}",
                method: 'POST',
                data: $(this).serialize(),
                beforeSend: function(){
                    $('.loadingGif').show();
                },
            }).done(function(response){
                if (response.success === 200) {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else if (response.success === 201) {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                } else {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }

            }).error(function(err){
                console.log(err)
            })

        });
    });
</script>
