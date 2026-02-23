<div class="modal fade" id="infoModel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-modern">
            <div class="modal-header-modern">
                <div class="header-title-ecp">
                    <i data-lucide="shield-check" class="text-white"></i>
                    <h5>{{ __('field.esp_info_title') }}</h5>
                </div>
                <button type="button" class="close-modern" data-dismiss="modal" aria-label="Close">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>

            <div class="modal-body-modern">
                <div class="ecp-info-grid">
                    <div class="info-card-ecp">
                        <div class="info-label-ecp">
                            <i data-lucide="user"></i>
                            <span>{{ __('field.qr_signed_by') }}</span>
                        </div>
                        <div class="info-value-ecp" id="qr_signed_by"></div>
                    </div>

                    <div class="info-card-ecp">
                        <div class="info-label-ecp">
                            <i data-lucide="map-pin"></i>
                            <span>{{ __('field.registered') }}</span>
                        </div>
                        <div class="info-value-ecp" id="registered"></div>
                    </div>

                    <div class="info-card-ecp">
                        <div class="info-label-ecp">
                            <i data-lucide="hash"></i>
                            <span>{{ __('field.serial_number') }}</span>
                        </div>
                        <div class="info-value-ecp" id="serialNumber"></div>
                    </div>

                    <div class="info-card-ecp">
                        <div class="info-label-ecp">
                            <i data-lucide="clock"></i>
                            <span>{{ __('field.signing_time') }}</span>
                        </div>
                        <div class="info-value-ecp" id="signingTime"></div>
                    </div>

                    <div class="info-card-ecp full-width" hidden>
                        <div class="info-label-ecp">
                            <i data-lucide="fingerprint"></i>
                            <span>{{ __('field.digest') ?? 'Хеш' }}</span>
                        </div>
                        <div class="info-value-ecp digest-value" id="digest"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-modern">
                <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                    <i data-lucide="x"></i>
                    <span>{{ __('action.close') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#infoModel').on('shown.bs.modal', function () {
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        });
    });
</script>

<link rel="stylesheet" href="{{ asset('css/templates/templates/ecp/ecpInfoModal.css') }}">

