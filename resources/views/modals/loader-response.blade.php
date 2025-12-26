<!-- Loading Modal -->
<div class="loadModal modal fade" tabindex="-1" role="dialog" id="spinnerModal">
    <div class="modal-dialog modal-dialog-centered text-center" role="document">
        <span class="fa fa-spinner fa-spin fa-3x w-100"></span>
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
                    {{ __('action.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
