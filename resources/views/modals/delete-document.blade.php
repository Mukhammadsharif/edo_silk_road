<!-- Delete Confirm Modal -->
<div id="ConfirmModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white"><i class="ik ik-trash-2"></i> {{ __('message.delete_confirm') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="text" name="item_conf_id" id="item_conf_id" hidden>
                <h5 class="delete_text"><i class="ik ik-info"></i> {{ __('message.delete_you_want_doc') }}</h5>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary pull-left" data-dismiss="modal">{{ __('action.cancel') }}</button>
                <button type="button" class="btn btn-warning deleteConfItem">{{ __('action.delete_yes') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="loadModal modal fade" tabindex="-1" role="dialog" id="spinnerModal">
    <div class="modal-dialog modal-dialog-centered text-center" role="document">
        <span class="fa fa-spinner fa-spin fa-3x w-100"></span>
    </div>
</div>

<!--Success Modal -->
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
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('action.close') }}</button>
            </div>
        </div>
    </div>
</div>
