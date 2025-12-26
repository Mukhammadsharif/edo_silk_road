<div class="modal fade" id="commentModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="ik ik-bell"></i> {{ __('message.notifications') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body modal-comments"></div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
                    <i class="fa fa-close"></i> {{ __('action.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

