@if($document->status === (int)1 || $document->status === (int)4 || $document->status === (int)5)
    <div class="execution-card-modern actions-card">
        <div class="card-header-modern">
            <i data-lucide="settings" class="text-white"></i>
            <h3>{{ __('action.action') }}</h3>
        </div>

        <div class="actions-body-modern">
            @if($document->status === (int)1 || $document->status === (int)4 || $document->status === (int)5)
                <button type="button" class="btn-modern btn-danger btn-rounded mr-5 deleteItem" data-id="{{ $document->id }}">
                    <i data-lucide="trash-2"></i> {{ __('action.delete') }}
                </button>

                <a href="{{ route('prt-secretary-edit-only', [$document->id, $document->doc_hash]) }}"
                   class="btn-modern btn-warning btn-rounded mr-5">
                    <i data-lucide="edit"></i> {{ __('action.edit') }}
                </a>
            @endif

            @if($document->status === (int)1)
                <a href="{{ route('prt-secretary-send-members', [$document->id, $document->doc_hash]) }}"
                   class="btn-modern btn-success btn-rounded mr-5">
                    <i data-lucide="send"></i> {{ __('action.send_to_members') }}
                </a>
            @endif
        </div>
    </div>
@endif

<div class="modal fade" id="deleteDocumentModal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-modern">
            <div class="modal-header bg-danger">
                <h4 class="modal-title text-white"><i data-lucide="trash-2"></i>  {{ __('message.delete_confirm') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="text" name="item_conf_id" id="item_conf_id" hidden>
                <h5><i data-lucide="trash"></i> {{ __('message.delete_you_want_document') }}</h5>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn-modern btn-secondary pull-left" data-dismiss="modal">
                    <i data-lucide="circle-x"></i> {{ __('action.cancel') }}
                </button>
                <button type="button" class="btn-modern btn-danger deleteItemConfirm">
                    <i data-lucide="check"></i> {{ __('action.delete_yes') }}
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
