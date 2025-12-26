<div class="card table-card">
    <div class="card-header">
        <h3>{{ __('action.action') }}</h3>
    </div>
    <div class="card-body">
        @can('prt-docs-delete')
            <button type="button" class="btn btn-outline-danger btn-rounded mr-5 deleteItem" data-id="{{ $document->id }}">
                <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
            </button>
        @endcan
        @can('prt-docs-edit')
            <a href="{{ route('prt-secretary-edit', [$document->id, $document->doc_hash]) }}"
               class="btn btn-outline-info btn-rounded mr-5">
                <i class="ik ik-edit"></i> {{ __('action.edit') }}
            </a>
        @endcan
    </div>
</div>

<div class="modal fade" id="deleteDocumentModal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title"><i class="ik ik-trash-2"></i>  {{ __('message.delete_confirm') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="text" name="item_conf_id" id="item_conf_id" hidden>
                <h5><i class="ik ik-info"></i> {{ __('message.delete_you_want_document') }}</h5>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary pull-left" data-dismiss="modal">
                    <i class="ik ik-x"></i> {{ __('action.cancel') }}
                </button>
                <button type="button" class="btn btn-danger deleteItemConfirm">
                    <i class="ik ik-check-circle"></i> {{ __('action.delete_yes') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Response (success, error) Modal -->
<div class="modal fade" id="responseModal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-content-class">
                <h5 class="modal-title" id="resModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 id="resModalMessage"></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">
                    <i class="ik ik-x"></i> {{ __('action.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.deleteItem').on('click', function () {

            let doc_id = $(this).data('id');

            $('#deleteDocumentModal').modal('show');

            $('#item_conf_id').val(doc_id);

        });

        $('.deleteItemConfirm').on('click', function () {

            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

            let id = $('#item_conf_id').val();

            let url = '/protocol/secretary/delete/' +id;

            $.ajax({
                url: url,
                type: 'GET',
                data: {_token: CSRF_TOKEN, id: id},
                dataType: 'JSON',
                beforeSend: function(){
                    $('#deleteDocumentModal').modal('toggle')
                },
                success: function(response){
                    console.log(response)
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    if (response.success === 200) {
                        $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                        setTimeout(function () {
                            window.location.href = '/protocol/secretary/'+response.doc_type;
                        }, 2000);
                    } else {
                        $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                        setTimeout(function () {
                            window.location.href = '/protocol/secretary/'+response.doc_type;
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
