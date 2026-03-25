<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">

            <!-- Modal Header -->
            <div class="modal-header-modern modal-success">
                <div class="modal-icon-large">
                    <i data-lucide="check-circle" class="text-white"></i>
                </div>
                <h3 class="modal-title-modern">{{ __('field.subscription_approval') }}</h3>
                <button type="button" class="modal-close-modern" data-dismiss="modal">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body-modern">
                <div class="modal-success-icon">
                    <i data-lucide="thumbs-up" class="text-green"></i>
                </div>

                <div class="modal-info-section">
                    <h4 class="organization-name" id="approveOrgName"></h4>
                    <p class="modal-description">
                        {{ __('field.for_organization') }}
                        <strong class="tariff-highlight" id="approveTariffName"></strong>
                        {{ __('field.subscription_request_for_tariff') }}
                    </p>
                </div>

                <div class="modal-confirmation">
                    <i data-lucide="help-circle" class="text-green"></i>
                    <p>{{ __('field.are_you_sure_to_approve') }}</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer-modern">
                <button type="button" class="btn-modal btn-cancel" data-dismiss="modal">
                    <i data-lucide="x"></i>
                    {{ __('action.cancel') }}
                </button>
                <form id="approveForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-modal btn-success">
                        <i data-lucide="check"></i>
                        {{ __('field.yes_approve') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">

            <!-- Modal Header -->
            <div class="modal-header-modern modal-danger">
                <div class="modal-icon-large">
                    <i data-lucide="x-circle" class="text-white"></i>
                </div>
                <h3 class="modal-title-modern">{{ __('field.reject_subscription') }}</h3>
                <button type="button" class="modal-close-modern" data-dismiss="modal">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body-modern">
                <div class="modal-danger-icon">
                    <i data-lucide="alert-octagon"></i>
                </div>

                <div class="modal-info-section">
                    <h4 class="organization-name" id="rejectOrgName"></h4>
                    <p class="modal-description">
                        {{ __('field.for_organization') }}
                        <strong class="tariff-highlight" id="rejectTariffName"></strong>
                        {{ __('field.subscription_request_for_tariff') }}
                    </p>
                </div>

                <div class="modal-confirmation modal-warning-box">
                    <i data-lucide="alert-triangle"></i>
                    <div>
                        <p class="confirmation-text">{{ __('field.are_you_sure_to_reject') }}</p>
                        <p class="warning-text">{{ __('field.action_cannot_be_undone') }}</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer-modern">
                <button type="button" class="btn-modal btn-cancel" data-dismiss="modal">
                    <i data-lucide="x"></i>
                    {{ __('action.cancel') }}
                </button>
                <form id="rejectForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-modal btn-danger">
                        <i data-lucide="trash-2"></i>
                        {{ __('field.yes_approve') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================
     EDIT MODAL
     ======================================== -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">

            <!-- Modal Header -->
            <div class="modal-header-modern modal-edit">
                <div class="modal-icon-large">
                    <i data-lucide="pencil" class="text-white"></i>
                </div>
                <h3 class="modal-title-modern">{{ __('field.edit_subscription') }}</h3>
                <button type="button" class="modal-close-modern" data-dismiss="modal">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body-modern" id="editModalBody">

                <!-- Loading spinner -->
                <div id="editLoadingSpinner" class="edit-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p>Yuklanmoqda...</p>
                </div>

                <!-- Form (hidden until data loads) -->
                <div id="editFormWrapper" style="display:none;">

                    <div class="edit-org-header">
                        <i data-lucide="building-2"></i>
                        <h4 id="editOrgName"></h4>
                    </div>

                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="edit-fields-grid">

                            <!-- Tariff -->
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="box"></i>
                                    {{ __('field.tariff') }}
                                </label>
                                <select name="tariff_id" id="editTariffId" class="edit-select">
                                    @foreach($tariffs as $tariff)
                                        <option value="{{ $tariff->id }}">{{ $tariff->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </label>
                                <select name="status" id="editStatus" class="edit-select">
                                    <option value="2">⏳ {{ __('field.pending') }}</option>
                                    <option value="1">✅ {{ __('field.active') }}</option>
                                    <option value="0">❌ {{ __('field.rejected') }}</option>
                                </select>
                            </div>

                            <!-- Start Date -->
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="calendar-check"></i>
                                    {{ __('field.starts_at') }}
                                </label>
                                <input type="date" name="starts_at" id="editStartsAt" class="edit-input">
                            </div>

                            <!-- End Date -->
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="calendar-x"></i>
                                    {{ __('field.ends_at') }}
                                </label>
                                <input type="date" name="ends_at" id="editEndsAt" class="edit-input">
                            </div>

                        </div>

                    </form>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer-modern">
                <button type="button" class="btn-modal btn-cancel" data-dismiss="modal">
                    <i data-lucide="x"></i>
                    {{ __('action.cancel') }}
                </button>
                <button type="button" class="btn-modal btn-edit-save" id="editSaveBtn">
                    <i data-lucide="save"></i>
                    {{ __('action.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* ========================================
       MODERN MODALS STYLES
       ======================================== */

    /* Modal Content */
    .modal-modern {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    /* Modal Header */
    .modal-header-modern {
        padding: 2rem;
        text-align: center;
        position: relative;
        border-bottom: none;
    }

    .modal-success {
        background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
    }

    .modal-danger {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
    }

    .modal-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .modal-icon-large {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        animation: modalIconPulse 2s infinite;
    }

    @keyframes modalIconPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .modal-icon-large i {
        width: 40px;
        height: 40px;
        color: white;
    }

    .modal-title-modern {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    .modal-close-modern {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close-modern:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .modal-close-modern i {
        width: 20px;
        height: 20px;
        color: white;
    }

    /* Modal Body */
    .modal-body-modern {
        padding: 2.5rem 2rem;
        text-align: center;
    }

    .modal-success-icon,
    .modal-danger-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: iconBounce 0.6s ease;
    }

    @keyframes iconBounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .modal-success-icon {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border: 4px solid #4caf50;
    }

    .modal-danger-icon {
        background: linear-gradient(135deg, #fbe9e7 0%, #ffccbc 100%);
        border: 4px solid #f44336;
    }

    .modal-success-icon i { width: 50px; height: 50px; color: #388e3c; }
    .modal-danger-icon i  { width: 50px; height: 50px; color: #d32f2f; }

    /* Modal Info Section */
    .modal-info-section { margin-bottom: 2rem; }

    .organization-name {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .modal-description {
        font-size: 15px;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    .tariff-highlight {
        color: #794DFD;
        font-weight: 700;
        font-size: 16px;
    }

    /* Modal Confirmation */
    .modal-confirmation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border: 2px solid #4caf50;
        border-radius: 12px;
        margin-top: 1.5rem;
    }

    .modal-confirmation i { width: 24px; height: 24px; color: #388e3c; flex-shrink: 0; }
    .modal-confirmation p { margin: 0; font-size: 15px; font-weight: 700; color: #388e3c; }

    .modal-warning-box {
        background: linear-gradient(135deg, #fff8e1 0%, #ffe0b2 100%);
        border-color: #ff9800;
        flex-direction: column;
        gap: 1rem;
    }

    .modal-warning-box i { color: #f57c00; }
    .confirmation-text { font-size: 16px; font-weight: 700; color: #d84315; margin-bottom: 0.5rem; }
    .warning-text { font-size: 13px; font-weight: 500; color: #f57c00; margin: 0; }

    /* Modal Footer */
    .modal-footer-modern {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-top: 2px solid #e2e8f0;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    /* Modal Buttons */
    .btn-modal {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 2rem;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .btn-modal i { width: 18px; height: 18px; }

    .btn-cancel { background: #e2e8f0; color: #64748b; }
    .btn-cancel:hover { background: #cbd5e1; transform: translateY(-2px); }

    .btn-success {
        background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    }
    .btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4); }

    .btn-danger {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
    }
    .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(244, 67, 54, 0.4); }

    .btn-edit-save {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    .btn-edit-save:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4); }

    /* Modal Animation */
    .modal.fade .modal-dialog { transform: scale(0.8); opacity: 0; transition: all 0.3s; }
    .modal.show .modal-dialog { transform: scale(1); opacity: 1; }

    /* ========================================
       EDIT MODAL SPECIFIC STYLES
       ======================================== */
    .edit-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        padding: 2rem 0;
        color: #64748b;
    }

    .edit-org-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        justify-content: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .edit-org-header i {
        width: 28px;
        height: 28px;
        color: #3b82f6;
    }

    .edit-org-header h4 {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .edit-fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        text-align: left;
    }

    .edit-field-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .edit-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .edit-label i {
        width: 16px;
        height: 16px;
        color: #3b82f6;
    }

    .edit-select,
    .edit-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        color: #1e293b;
        background: white;
        transition: all 0.2s;
        outline: none;
    }

    .edit-select:focus,
    .edit-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Responsive */
    @media (max-width: 576px) {
        .modal-header-modern { padding: 1.5rem; }
        .modal-body-modern { padding: 2rem 1.5rem; }
        .modal-footer-modern { flex-direction: column; padding: 1rem 1.5rem; }
        .btn-modal { width: 100%; justify-content: center; }
        .modal-icon-large { width: 64px; height: 64px; }
        .modal-icon-large i { width: 32px; height: 32px; }
        .modal-success-icon, .modal-danger-icon { width: 80px; height: 80px; }
        .modal-success-icon i, .modal-danger-icon i { width: 40px; height: 40px; }
        .organization-name { font-size: 18px; }
        .modal-title-modern { font-size: 20px; }
        .edit-fields-grid { grid-template-columns: 1fr; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Reinitialize Lucide icons when modals are shown
        $('#approveModal, #rejectModal, #editModal').on('shown.bs.modal', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // ---- EDIT MODAL ----
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            const editUrl = '{{ route("admin.subscription-requests.edit", ":id") }}'.replace(':id', id);
            const updateUrl = '{{ route("admin.subscription-requests.update", ":id") }}'.replace(':id', id);

            // Show loading state
            $('#editLoadingSpinner').show();
            $('#editFormWrapper').hide();
            $('#editModal').modal('show');

            // Fetch current data via JSON
            $.ajax({
                url: editUrl,
                method: 'GET',
                success: function(data) {
                    $('#editOrgName').text(data.org_name);
                    $('#editTariffId').val(data.tariff_id);
                    $('#editStatus').val(data.status);
                    $('#editStartsAt').val(data.starts_at ?? '');
                    $('#editEndsAt').val(data.ends_at ?? '');

                    // Point the form to the correct update route
                    $('#editForm').attr('action', updateUrl);

                    $('#editLoadingSpinner').hide();
                    $('#editFormWrapper').show();

                    if (typeof lucide !== 'undefined') lucide.createIcons();
                },
                error: function() {
                    alert('Xatolik yuz berdi. Iltimos qayta urinib ko\'ring.');
                    $('#editModal').modal('hide');
                }
            });
        });

        // Save button submits the edit form
        $('#editSaveBtn').on('click', function() {
            $('#editForm').submit();
        });
    });
</script>
