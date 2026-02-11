<div class="modal fade" id="commentModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-modern">
            <div class="modal-header-modern modal-header-notifications">
                <div class="header-title-modal">
                    <i data-lucide="bell" class="text-white"></i>
                    <h5>{{ __('message.notifications') }}</h5>
                </div>
                <button type="button" class="close-modern" data-dismiss="modal" aria-label="Close">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>

            <div class="modal-body-modern modal-comments"></div>

            <div class="modal-footer-modern">
                <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                    <i data-lucide="x"></i>
                    <span>{{ __('action.close') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/protocol/comment_user_modal.css') }}">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Move modal to end of body to avoid z-index issues
        if ($('#commentModal').length && $('#commentModal').parent()[0] !== document.body) {
            $('#commentModal').appendTo('body');
        }

        $('#commentModal').on('show.bs.modal', function (e) {
            // Ensure modal and backdrop have correct z-index
            let $modal = $(e.target);
            $modal.css({'z-index': 1055, 'display': 'block'});

            // Wait for backdrop to be created
            setTimeout(function() {
                $('.modal-backdrop').each(function() {
                    $(this).css({'z-index': 1040, 'opacity': 0.5});
                });
                $modal.css({'z-index': 1055, 'display': 'block'});
            }, 50);
        });

        $('#commentModal').on('shown.bs.modal', function (e) {
            // Double-check z-index after modal is fully shown
            $(e.target).css({'z-index': 1055, 'display': 'block'});
            $('.modal-backdrop').each(function() {
                $(this).css({'z-index': 1040, 'opacity': 0.5});
            });

            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        });
    });
</script>

