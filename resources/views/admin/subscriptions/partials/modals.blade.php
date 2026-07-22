<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-modern modal-success">
                <div class="modal-icon-large">
                    <i data-lucide="check-circle" class="text-white"></i>
                </div>
                <h3 class="modal-title-modern">{{ __('field.subscription_approval') }}</h3>
                <button type="button" class="modal-close-modern" data-dismiss="modal">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>
            <div class="modal-body-modern">
                <div class="modal-success-icon">
                    <i data-lucide="thumbs-up"></i>
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
                    <i data-lucide="help-circle"></i>
                    <p>{{ __('field.are_you_sure_to_approve') }}</p>
                </div>
            </div>
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
            <div class="modal-header-modern modal-danger">
                <div class="modal-icon-large">
                    <i data-lucide="x-circle" class="text-white"></i>
                </div>
                <h3 class="modal-title-modern">{{ __('field.reject_subscription') }}</h3>
                <button type="button" class="modal-close-modern" data-dismiss="modal">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>
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
            <div class="modal-footer-modern">
                <button type="button" class="btn-modal btn-cancel" data-dismiss="modal">
                    <i data-lucide="x"></i>
                    {{ __('action.cancel') }}
                </button>
                <form id="rejectForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-modal btn-danger">
                        <i data-lucide="trash-2"></i>
                        {{ __('field.yes_reject') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">
            <div class="modal-header-modern modal-edit">
                <div class="modal-icon-large">
                    <i data-lucide="pencil" class="text-white"></i>
                </div>
                <h3 class="modal-title-modern">{{ __('field.edit_subscription') }}</h3>
                <button type="button" class="modal-close-modern" data-dismiss="modal">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>
            <div class="modal-body-modern" id="editModalBody">
                <div id="editLoadingSpinner" class="edit-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p>Yuklanmoqda...</p>
                </div>
                <div id="editFormWrapper" style="display:none;">
                    <div class="edit-org-header">
                        <i data-lucide="building-2"></i>
                        <h4 id="editOrgName"></h4>
                    </div>
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="edit-fields-grid">
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="box"></i>
                                    {{ __('field.tariff') }}
                                </label>
                                <select name="tariff_id" id="editTariffId" class="edit-select select2" style="width: 100%">
                                    @foreach($tariffs as $tariff)
                                        <option value="{{ $tariff->id }}">{{ $tariff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </label>
                                <select name="status" id="editStatus" class="edit-select select2" style="width: 100%">
                                    <option value="2">⏳ {{ __('field.pending') }}</option>
                                    <option value="1">✅ {{ __('field.active') }}</option>
                                    <option value="0">❌ {{ __('field.rejected') }}</option>
                                </select>
                            </div>
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="calendar-check"></i>
                                    {{ __('field.starts_at') }}
                                </label>
                                <input type="date" name="starts_at" id="editStartsAt" class="edit-input">
                            </div>
                            <div class="edit-field-group">
                                <label class="edit-label">
                                    <i data-lucide="calendar-x"></i>
                                    {{ __('field.ends_at') }}
                                </label>
                                <input type="date" name="ends_at" id="editEndsAt" class="edit-input">
                            </div>

                            <!-- Kanban access toggle -->
                            <div class="edit-field-group" style="grid-column: 1 / -1;">
                                <label class="edit-label">
                                    <i data-lucide="trello"></i>
                                    Kanban doskalar
                                </label>
                                <label class="kanban-switch">
                                    <input type="checkbox" name="has_kanban_access" id="editKanbanAccess" value="1">
                                    <span class="kanban-slider"></span>
                                    <span class="kanban-switch-text">Trello doskalarga ruxsat berish</span>
                                </label>
                            </div>

                            <!-- Didox access toggle -->
                            <div class="edit-field-group" style="grid-column: 1 / -1;">
                                <label class="edit-label">
                                    <i data-lucide="files"></i>
                                    Didox (tashqi hujjat aylanmasi)
                                </label>
                                <label class="kanban-switch">
                                    <input type="checkbox" name="has_didox_access" id="editDidoxAccess" value="1" onchange="toggleDidoxRole('edit')">
                                    <span class="kanban-slider"></span>
                                    <span class="kanban-switch-text">Didox modulini yoqish</span>
                                </label>
                            </div>
                            <div class="edit-field-group" id="editDidoxRoleWrap" style="grid-column: 1 / -1; display:none;">
                                <label class="edit-label">
                                    <i data-lucide="users"></i>
                                    Didox uchun mas'ul xodimlar
                                </label>
                                <select name="didox_user_ids[]" id="editDidoxUsers" class="edit-select" multiple style="width:100%;"></select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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

<!-- ========================================
     CREATE SUBSCRIPTION MODAL (Admin)
     ======================================== -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">

            <!-- Header -->
            <div class="modal-header-modern modal-create">
                <div class="modal-icon-large">
                    <i data-lucide="plus-circle" class="text-white"></i>
                </div>
                <h3 class="modal-title-modern">Yangi obuna yaratish</h3>
                <button type="button" class="modal-close-modern" data-dismiss="modal">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body-modern" style="text-align:left;">

                <div class="create-info-banner">
                    <i data-lucide="shield-check"></i>
                    <div>
                        <p class="create-banner-title">Admin tomonidan yaratish</p>
                        <p class="create-banner-desc">Kompaniya so'rovini kutmasdan, admin bevosita obuna yaratib faollashtirishi mumkin.</p>
                    </div>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.subscription-requests.store') }}">
                    @csrf

                    <div class="edit-fields-grid" style="margin-top:1.5rem;">

                        <!-- Organization — full width -->
                        <div class="edit-field-group" style="grid-column: 1 / -1;">
                            <label class="edit-label">
                                <i data-lucide="building-2"></i>
                                Tashkilot
                            </label>
                            <select name="org_unit_id" id="createOrgSelect" class="edit-select select2" style="width:100%;">
                                <option value="">— Tashkilotni tanlang —</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">
                                        {{ $org->name_uz }}{{ $org->tax_number ? ' — ' . $org->tax_number : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tariff -->
                        <div class="edit-field-group">
                            <label class="edit-label">
                                <i data-lucide="box"></i>
                                Tarif
                            </label>
                            <select name="tariff_id" id="createTariffId" class="edit-select select2" style="width: 100%">
                                <option value="">— Tarifni tanlang —</option>
                                @foreach($tariffs as $tariff)
                                    <option value="{{ $tariff->id }}"
                                            data-price="{{ $tariff->price_monthly ?? 0 }}"
                                            data-users="{{ $tariff->max_users ?? '—' }}"
                                            data-docs="{{ $tariff->max_documents ?? '—' }}">
                                        {{ $tariff->name }}
                                    </option>
                                @endforeach
                            </select>
                            <!-- Tariff info preview -->
                            <div id="tariffPreview" class="tariff-preview" style="display:none;">
                                <div class="tariff-preview-item">
                                    <i data-lucide="wallet"></i>
                                    <span id="previewPrice"></span>
                                </div>
                                <div class="tariff-preview-item">
                                    <i data-lucide="users"></i>
                                    <span id="previewUsers"></span>
                                </div>
                                <div class="tariff-preview-item">
                                    <i data-lucide="file-text"></i>
                                    <span id="previewDocs"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="edit-field-group">
                            <label class="edit-label">
                                <i data-lucide="activity"></i>
                                Status
                            </label>
                            <select name="status" id="createStatus" class="edit-select select2" style="width: 100%">
                                <option value="1">✅ Darhol faollashtirish</option>
                                <option value="2">⏳ Kutilmoqda (pending)</option>
                                <option value="0">❌ Rad etilgan</option>
                            </select>
                        </div>

                        <!-- Starts At -->
                        <div class="edit-field-group">
                            <label class="edit-label">
                                <i data-lucide="calendar-check"></i>
                                Boshlanish sanasi
                            </label>
                            <input type="date" name="starts_at" id="createStartsAt" class="edit-input">
                        </div>

                        <!-- Ends At -->
                        <div class="edit-field-group">
                            <label class="edit-label">
                                <i data-lucide="calendar-x"></i>
                                Tugash sanasi
                            </label>
                            <input type="date" name="ends_at" id="createEndsAt" class="edit-input">
                        </div>

                        <!-- Kanban access toggle -->
                        <div class="edit-field-group" style="grid-column: 1 / -1;">
                            <label class="edit-label">
                                <i data-lucide="trello"></i>
                                Kanban doskalar
                            </label>
                            <label class="kanban-switch">
                                <input type="checkbox" name="has_kanban_access" id="createKanbanAccess" value="1">
                                <span class="kanban-slider"></span>
                                <span class="kanban-switch-text">Trello doskalarga ruxsat berish</span>
                            </label>
                        </div>

                        <!-- Didox access toggle -->
                        <div class="edit-field-group" style="grid-column: 1 / -1;">
                            <label class="edit-label">
                                <i data-lucide="files"></i>
                                Didox (tashqi hujjat aylanmasi)
                            </label>
                            <label class="kanban-switch">
                                <input type="checkbox" name="has_didox_access" id="createDidoxAccess" value="1" onchange="toggleDidoxRole('create')">
                                <span class="kanban-slider"></span>
                                <span class="kanban-switch-text">Didox modulini yoqish</span>
                            </label>
                        </div>
                        <div class="edit-field-group" id="createDidoxRoleWrap" style="grid-column: 1 / -1; display:none;">
                            <label class="edit-label">
                                <i data-lucide="users"></i>
                                Didox uchun mas'ul xodimlar
                            </label>
                            <select name="didox_user_ids[]" id="createDidoxUsers" class="edit-select" multiple style="width:100%;"></select>
                        </div>

                    </div>

                    <!-- Duration quick-pick -->
                    <div class="duration-quickpick">
                        <span class="duration-label">Tez tanlash:</span>
                        <button type="button" class="duration-btn" data-months="1">1 oy</button>
                        <button type="button" class="duration-btn" data-months="3">3 oy</button>
                        <button type="button" class="duration-btn" data-months="6">6 oy</button>
                        <button type="button" class="duration-btn" data-months="12">1 yil</button>
                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer-modern">
                <button type="button" class="btn-modal btn-cancel" data-dismiss="modal">
                    <i data-lucide="x"></i>
                    Bekor qilish
                </button>
                <button type="button" class="btn-modal btn-create-save" id="createSaveBtn">
                    <i data-lucide="plus-circle"></i>
                    Yaratish
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">

<style>
    /* ========================================
       MODAL BASE
       ======================================== */
    .modal-modern {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .modal-header-modern {
        padding: 2rem;
        text-align: center;
        position: relative;
        border-bottom: none;
    }

    .modal-success { background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%); }
    .modal-danger  { background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%); }
    .modal-edit    { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    .modal-create  { background: linear-gradient(135deg, #794DFD 0%, #5b35d5 100%); }

    .modal-icon-large {
        width: 80px; height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        animation: modalIconPulse 2s infinite;
    }
    @keyframes modalIconPulse {
        0%,100% { transform: scale(1); }
        50%      { transform: scale(1.05); }
    }
    .modal-icon-large i { width: 40px; height: 40px; color: white; }

    .modal-title-modern { font-size: 24px; font-weight: 700; color: white; margin: 0; }

    .modal-close-modern {
        position: absolute; top: 1rem; right: 1rem;
        width: 36px; height: 36px;
        background: rgba(255,255,255,0.2);
        border: none; border-radius: 8px;
        cursor: pointer; transition: all 0.3s;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-close-modern:hover { background: rgba(255,255,255,0.3); transform: rotate(90deg); }
    .modal-close-modern i { width: 20px; height: 20px; color: white; }

    .modal-body-modern { padding: 2.5rem 2rem; text-align: center; }

    .modal-success-icon, .modal-danger-icon {
        width: 100px; height: 100px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        animation: iconBounce 0.6s ease;
    }
    @keyframes iconBounce {
        0%,100% { transform: scale(1); }
        50%      { transform: scale(1.1); }
    }
    .modal-success-icon { background: linear-gradient(135deg,#e8f5e9,#c8e6c9); border: 4px solid #4caf50; }
    .modal-danger-icon  { background: linear-gradient(135deg,#fbe9e7,#ffccbc); border: 4px solid #f44336; }
    .modal-success-icon i { width: 50px; height: 50px; color: #388e3c; }
    .modal-danger-icon  i { width: 50px; height: 50px; color: #d32f2f; }

    .modal-info-section { margin-bottom: 2rem; }
    .organization-name  { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 1rem; }
    .modal-description  { font-size: 15px; color: #64748b; line-height: 1.6; margin: 0; }
    .tariff-highlight   { color: #794DFD; font-weight: 700; font-size: 16px; }

    .modal-confirmation {
        display: flex; align-items: center; justify-content: center;
        gap: 0.75rem; padding: 1.25rem;
        background: linear-gradient(135deg,#e8f5e9,#c8e6c9);
        border: 2px solid #4caf50; border-radius: 12px; margin-top: 1.5rem;
    }
    .modal-confirmation i { width: 24px; height: 24px; color: #388e3c; flex-shrink: 0; }
    .modal-confirmation p { margin: 0; font-size: 15px; font-weight: 700; color: #388e3c; }

    .modal-warning-box  { background: linear-gradient(135deg,#fff8e1,#ffe0b2); border-color: #ff9800; flex-direction: column; gap: 1rem; }
    .modal-warning-box i { color: #f57c00; }
    .confirmation-text  { font-size: 16px; font-weight: 700; color: #d84315; margin-bottom: 0.5rem; }
    .warning-text       { font-size: 13px; font-weight: 500; color: #f57c00; margin: 0; }

    .modal-footer-modern {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-top: 2px solid #e2e8f0;
        display: flex; gap: 1rem; justify-content: center;
    }

    /* Buttons */
    .btn-modal {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.875rem 2rem; border: none; border-radius: 12px;
        font-size: 15px; font-weight: 700; cursor: pointer;
        transition: all 0.3s; white-space: nowrap;
    }
    .btn-modal i { width: 18px; height: 18px; }

    .btn-cancel     { background: #e2e8f0; color: #64748b; }
    .btn-cancel:hover { background: #cbd5e1; transform: translateY(-2px); }

    .btn-success    { background: linear-gradient(135deg,#4caf50,#388e3c); color: white; box-shadow: 0 4px 12px rgba(76,175,80,.3); }
    .btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(76,175,80,.4); }

    .btn-danger     { background: linear-gradient(135deg,#f44336,#d32f2f); color: white; box-shadow: 0 4px 12px rgba(244,67,54,.3); }
    .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(244,67,54,.4); }

    .btn-edit-save  { background: linear-gradient(135deg,#3b82f6,#2563eb); color: white; box-shadow: 0 4px 12px rgba(59,130,246,.3); }
    .btn-edit-save:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(59,130,246,.4); }

    .btn-create-save { background: linear-gradient(135deg,#794DFD,#5b35d5); color: white; box-shadow: 0 4px 12px rgba(121,77,253,.3); }
    .btn-create-save:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(121,77,253,.4); }

    /* Modal animation */
    .modal.fade .modal-dialog { transform: scale(0.8); opacity: 0; transition: all 0.3s; }
    .modal.show .modal-dialog { transform: scale(1); opacity: 1; }

    /* ========================================
       EDIT / CREATE SHARED FORM STYLES
       ======================================== */
    .edit-loading { display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 2rem 0; color: #64748b; }

    .edit-org-header {
        display: flex; align-items: center; gap: 0.75rem;
        justify-content: center; margin-bottom: 2rem;
        padding-bottom: 1.5rem; border-bottom: 2px solid #f1f5f9;
    }
    .edit-org-header i { width: 28px; height: 28px; color: #3b82f6; }
    .edit-org-header h4 { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; }

    .edit-fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; text-align: left; }

    .edit-field-group { display: flex; flex-direction: column; gap: 0.5rem; }

    .edit-label {
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 13px; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .edit-label i { width: 16px; height: 16px; color: #794DFD; }

    .edit-select, .edit-input {
        width: 100%; padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0; border-radius: 10px;
        font-size: 15px; color: #1e293b; background: white;
        transition: all 0.2s; outline: none;
    }
    .edit-select:focus, .edit-input:focus {
        border-color: #794DFD;
        box-shadow: 0 0 0 3px rgba(121,77,253,0.1);
    }

    /* ========================================
       CREATE MODAL SPECIFICS
       ======================================== */
    .create-info-banner {
        display: flex; align-items: flex-start; gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg,#ede9fe,#ddd6fe);
        border: 2px solid #794DFD; border-radius: 14px;
        margin-bottom: 0.5rem;
    }
    .create-info-banner i { width: 28px; height: 28px; color: #794DFD; flex-shrink: 0; margin-top: 2px; }
    .create-banner-title { font-size: 15px; font-weight: 700; color: #4c2f9e; margin: 0 0 0.25rem; }
    .create-banner-desc  { font-size: 13px; color: #6d4fc7; margin: 0; line-height: 1.5; }

    /* Tariff preview pill row */
    .tariff-preview {
        display: flex; gap: 0.75rem; flex-wrap: wrap;
        margin-top: 0.75rem;
        padding: 0.75rem 1rem;
        background: #f8fafc; border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    .tariff-preview-item {
        display: flex; align-items: center; gap: 0.4rem;
        font-size: 13px; font-weight: 600; color: #475569;
    }
    .tariff-preview-item i { width: 14px; height: 14px; color: #794DFD; }

    /* ========================================
       KANBAN ACCESS TOGGLE
       ======================================== */
    .kanban-switch {
        display: flex; align-items: center; gap: 0.75rem;
        cursor: pointer; user-select: none;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0; border-radius: 10px;
        transition: all 0.2s;
    }
    .kanban-switch:hover { border-color: #794DFD; }
    .kanban-switch input { display: none; }

    .kanban-slider {
        position: relative;
        width: 46px; height: 26px;
        background: #cbd5e1; border-radius: 20px;
        transition: background 0.25s; flex-shrink: 0;
    }
    .kanban-slider::before {
        content: ''; position: absolute;
        top: 3px; left: 3px;
        width: 20px; height: 20px;
        background: white; border-radius: 50%;
        transition: transform 0.25s;
        box-shadow: 0 2px 4px rgba(0,0,0,.2);
    }
    .kanban-switch input:checked + .kanban-slider { background: #794DFD; }
    .kanban-switch input:checked + .kanban-slider::before { transform: translateX(20px); }

    .kanban-switch-text { font-size: 14px; font-weight: 600; color: #475569; }

    /* Duration quick pick */
    .duration-quickpick {
        display: flex; align-items: center; gap: 0.75rem;
        flex-wrap: wrap; margin-top: 1.5rem;
        padding: 1rem 1.25rem;
        background: #f8fafc; border-radius: 12px;
        border: 2px dashed #e2e8f0;
    }
    .duration-label { font-size: 13px; font-weight: 700; color: #64748b; }
    .duration-btn {
        padding: 0.5rem 1.25rem;
        border: 2px solid #794DFD; border-radius: 20px;
        background: white; color: #794DFD;
        font-size: 13px; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
    }
    .duration-btn:hover, .duration-btn.active {
        background: #794DFD; color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(121,77,253,.3);
    }

    /* Responsive */
    @media (max-width: 576px) {
        .modal-header-modern { padding: 1.5rem; }
        .modal-body-modern   { padding: 1.5rem 1rem; }
        .modal-footer-modern { flex-direction: column; padding: 1rem; }
        .btn-modal           { width: 100%; justify-content: center; }
        .edit-fields-grid    { grid-template-columns: 1fr; }
        .modal-icon-large    { width: 64px; height: 64px; }
        .modal-icon-large i  { width: 32px; height: 32px; }
        .modal-title-modern  { font-size: 20px; }
    }
</style>

<script>
    // Мультиселект ответственных за Didox сотрудников организации.
    window.__didoxSelected = { edit: [], create: [] };
    window.__didoxOrg = { edit: null, create: null };

    function didoxOrgId(prefix) {
        if (prefix === 'create') {
            var o = document.getElementById('createOrgSelect');
            return o ? o.value : null;
        }
        return window.__didoxOrg.edit;
    }

    function loadDidoxUsers(prefix) {
        var on  = document.getElementById(prefix + 'DidoxAccess');
        var sel = document.getElementById(prefix + 'DidoxUsers');
        var orgId = didoxOrgId(prefix);
        if (!sel || !on || !on.checked || !orgId) return;

        var selected = (window.__didoxSelected[prefix] || []).map(String);

        fetch("{{ url('/subscription-requests/org-users') }}/" + orgId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var $sel = window.jQuery ? $(sel) : null;
                if ($sel && $.fn.select2 && $sel.data('select2')) { $sel.select2('destroy'); }
                sel.innerHTML = '';
                (res.users || []).forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.user_id;
                    opt.textContent = u.name + (u.position ? ' — ' + u.position : '');
                    if (selected.indexOf(String(u.user_id)) !== -1) opt.selected = true;
                    sel.appendChild(opt);
                });
                if ($sel && $.fn.select2) {
                    $sel.select2({
                        width: '100%',
                        placeholder: "{{ __('action.choose') }}",
                        dropdownParent: $sel.closest('.modal')
                    });
                }
            })
            .catch(function () {});
    }

    function toggleDidoxRole(prefix) {
        var on = document.getElementById(prefix + 'DidoxAccess');
        var wrap = document.getElementById(prefix + 'DidoxRoleWrap');
        var show = on && on.checked;
        if (wrap) wrap.style.display = show ? 'block' : 'none';
        if (show) {
            loadDidoxUsers(prefix);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {

        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.choose') }}",
                allowClear: true
            });
        });

        $('#createModal').on('shown.bs.modal', function () {
            $('.select2').select2({
                dropdownParent: $('#createModal')
            });
        });

        // Смена организации в форме создания → перезагрузить список сотрудников Didox.
        $(document).on('change', '#createOrgSelect', function () {
            window.__didoxSelected.create = [];
            loadDidoxUsers('create');
        });

        $('#editModal').on('shown.bs.modal', function () {
            $('.select2').select2({
                dropdownParent: $('#editModal')
            });
        });

        // Re-init Lucide on any modal open
        $('#approveModal, #rejectModal, #editModal, #createModal').on('shown.bs.modal', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        // ---- APPROVE ----
        $(document).on('click', '.approve-btn', function () {
            $('#approveOrgName').text($(this).data('org'));
            $('#approveTariffName').text($(this).data('tariff'));
            $('#approveForm').attr('action',
                '{{ route("admin.subscription-requests.approve", ":id") }}'.replace(':id', $(this).data('id'))
            );
            $('#approveModal').modal('show');
        });

        // ---- REJECT ----
        $(document).on('click', '.reject-btn', function () {
            $('#rejectOrgName').text($(this).data('org'));
            $('#rejectTariffName').text($(this).data('tariff'));
            $('#rejectForm').attr('action',
                '{{ route("admin.subscription-requests.reject", ":id") }}'.replace(':id', $(this).data('id'))
            );
            $('#rejectModal').modal('show');
        });

        // ---- EDIT ----
        $(document).on('click', '.edit-btn', function () {
            const id        = $(this).data('id');
            const editUrl   = '{{ route("admin.subscription-requests.edit", ":id") }}'.replace(':id', id);
            const updateUrl = '{{ route("admin.subscription-requests.update", ":id") }}'.replace(':id', id);

            $('#editLoadingSpinner').show();
            $('#editFormWrapper').hide();
            $('#editModal').modal('show');

            $.ajax({
                url: editUrl,
                method: 'GET',
                success: function (data) {
                    $('#editOrgName').text(data.org_name);
                    $('#editTariffId').val(data.tariff_id);
                    $('#editStatus').val(data.status);
                    $('#editStartsAt').val(data.starts_at ?? '');
                    $('#editEndsAt').val(data.ends_at ?? '');
                    $('#editKanbanAccess').prop('checked', !!data.has_kanban_access);
                    window.__didoxOrg.edit = data.org_unit_id;
                    window.__didoxSelected.edit = data.didox_user_ids || [];
                    $('#editDidoxAccess').prop('checked', !!data.has_didox_access);
                    toggleDidoxRole('edit');
                    $('#editForm').attr('action', updateUrl);
                    $('#editLoadingSpinner').hide();
                    $('#editFormWrapper').show();
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                },
                error: function () {
                    alert('Xatolik yuz berdi.');
                    $('#editModal').modal('hide');
                }
            });
        });

        $('#editSaveBtn').on('click', function () {
            $('#editForm').submit();
        });

        // ---- CREATE ----

        // Set today as default starts_at when modal opens
        $('#createModal').on('show.bs.modal', function () {
            const today = new Date().toISOString().split('T')[0];
            if (!$('#createStartsAt').val()) {
                $('#createStartsAt').val(today);
            }
            // default 1 year
            if (!$('#createEndsAt').val()) {
                const next = new Date();
                next.setFullYear(next.getFullYear() + 1);
                $('#createEndsAt').val(next.toISOString().split('T')[0]);
            }
            // reset kanban toggle (default off)
            $('#createKanbanAccess').prop('checked', false);
            // highlight active duration btn
            updateDurationHighlight();
        });

        // Tariff preview
        $('#createTariffId').on('change', function () {
            const opt = $(this).find('option:selected');
            if (!opt.val()) { $('#tariffPreview').hide(); return; }

            const price = Number(opt.data('price')).toLocaleString('uz-UZ');
            const users = opt.data('users');
            const docs  = opt.data('docs');

            $('#previewPrice').text(price + ' so\'m / oy');
            $('#previewUsers').text('Max foydalanuvchilar: ' + users);
            $('#previewDocs').text('Max hujjatlar: ' + docs);
            $('#tariffPreview').show();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        // Duration quick-pick
        $(document).on('click', '.duration-btn', function () {
            const months    = parseInt($(this).data('months'));
            const startsVal = $('#createStartsAt').val();
            const start     = startsVal ? new Date(startsVal) : new Date();

            if (!startsVal) {
                $('#createStartsAt').val(start.toISOString().split('T')[0]);
            }

            const end = new Date(start);
            end.setMonth(end.getMonth() + months);
            $('#createEndsAt').val(end.toISOString().split('T')[0]);

            $('.duration-btn').removeClass('active');
            $(this).addClass('active');
        });

        // Update highlight when dates change manually
        $('#createStartsAt, #createEndsAt').on('change', function () {
            updateDurationHighlight();
        });

        function updateDurationHighlight() {
            const s = $('#createStartsAt').val();
            const e = $('#createEndsAt').val();
            if (!s || !e) return;

            const months = Math.round(
                (new Date(e) - new Date(s)) / (1000 * 60 * 60 * 24 * 30.44)
            );

            $('.duration-btn').each(function () {
                $(this).toggleClass('active', parseInt($(this).data('months')) === months);
            });
        }

        $('#createSaveBtn').on('click', function () {
            // Basic validation
            if (!$('#createOrgSelect').val()) {
                alert('Tashkilotni tanlang!'); return;
            }
            if (!$('#createTariffId').val()) {
                alert('Tarifni tanlang!'); return;
            }
            if (!$('#createStartsAt').val() || !$('#createEndsAt').val()) {
                alert('Sanalarni kiriting!'); return;
            }
            $('#createForm').submit();
        });

    });
</script>
