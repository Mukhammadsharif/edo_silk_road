<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="ik ik-check-circle"></i> {{ __('field.subscription_approval') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <i class="ik ik-thumbs-up display-4 text-success mb-3"></i>
                <h5><strong id="approveOrgName"></strong></h5>
                <p>{{ __('field.for_organization') }} <strong id="approveTariffName"></strong> {{ __('field.subscription_request_for_tariff') }}</p>
                <p class="text-success fw-bold">{{ __('field.are_you_sure_to_approve') }}</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('action.cancel') }}</button>
                <form id="approveForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg px-5">{{ __('field.yes_approve') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ik ik-x-circle"></i> {{ __('field.reject_subscription') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <i class="ik ik-alert-octagon display-4 text-danger mb-3"></i>
                <h5><strong id="rejectOrgName"></strong></h5>
                <p>{{ __('field.for_organization') }} <strong id="rejectTariffName"></strong> {{ __('field.subscription_request_for_tariff') }}</p>
                <p class="text-danger fw-bold">{{ __('field.are_you_sure_to_reject') }}</p>
                <p class="text-muted small">{{ __('field.action_cannot_be_undone') }}</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('action.cancel') }}</button>
                <form id="rejectForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-lg px-5">{{ __('field.yes_approve') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
