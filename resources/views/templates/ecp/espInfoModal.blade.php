<div class="modal fade" id="infoModel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i class="ik ik-feather"></i> {{ __('field.esp_info_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <h5 class="text-bold"><span id="modalText">{{ __('field.qr_signed_by') }}</span></h5>
                <p id="qr_signed_by"></p>

                <h5 class="text-bold"><span id="modalText">{{ __('field.registered') }}:</span></h5>
                <p id="registered"></p>

                <h5 class="text-bold"><span id="modalText">{{ __('field.serial_number') }}:</span></h5>
                <p id="serialNumber"></p>

                <h5 class="text-bold"><span id="modalText">{{ __('field.signing_time') }}:</span></h5>
                <p id="signingTime"></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
                    <i class="fa fa-close"></i> {{ __('action.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{--<script src="{{ asset ("/js/jquery.validate.js") }}"></script>--}}

