@if($subscriptions->count() > 0)
    <div class="subscriptions-table-container">
        <div class="table-responsive-modern">
            <table class="subscriptions-table-modern">
                <thead>
                <tr>
                    <th class="col-number">#</th>
                    <th class="col-org">
                        <i data-lucide="building-2"></i>
                        Tashkilot
                    </th>
                    <th class="col-tariff">
                        <i data-lucide="box"></i>
                        Tarif
                    </th>
                    <th class="col-price">
                        <i data-lucide="wallet"></i>
                        Narxi (oylik)
                    </th>
                    <th class="col-date">
                        <i data-lucide="calendar-check"></i>
                        Boshlanish
                    </th>
                    <th class="col-date">
                        <i data-lucide="calendar"></i>
                        Tugash
                    </th>
                    <th class="col-status">
                        <i data-lucide="activity"></i>
                        Holati
                    </th>
                    <th class="col-created">
                        <i data-lucide="clock"></i>
                        Yaratilgan
                    </th>
                    @if($showActions ?? true)
                        <th class="col-actions">
                            <i data-lucide="settings"></i>
                            Harakat
                        </th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach($subscriptions as $key => $req)
                    <tr>
                        <!-- Number -->
                        <td class="cell-number">
                            {{ $loop->iteration }}
                        </td>

                        <!-- Organization -->
                        <td class="cell-org">
                            <div class="org-info">
                                <i data-lucide="building-2"></i>
                                <span>{{ $req->organization->name_uz ?? 'Noma\'lum' }}</span>
                            </div>
                        </td>

                        <!-- Tariff -->
                        <td class="cell-tariff">
                            <div class="tariff-badge">
                                <i data-lucide="zap"></i>
                                <span>{{ $req->tariff->name }}</span>
                            </div>
                        </td>

                        <!-- Price -->
                        <td class="cell-price">
                            <div class="price-info">
                                <span class="amount">{{ number_format($req->tariff->price_monthly, 0, ',', ' ') }}</span>
                                <span class="currency">so'm</span>
                            </div>
                        </td>

                        <!-- Start Date -->
                        <td class="cell-date">
                            <div class="date-info">
                                <i data-lucide="calendar"></i>
                                <span>{{ $req->starts_at?->format('d.m.Y') ?? '—' }}</span>
                            </div>
                        </td>

                        <!-- End Date -->
                        <td class="cell-date">
                            <div class="date-info">
                                <i data-lucide="calendar"></i>
                                <span>{{ $req->ends_at?->format('d.m.Y') ?? '—' }}</span>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="cell-status">
                            @switch($req->status)
                                @case(1)
                                    @if($req->ends_at > now())
                                        <span class="status-pill status-active">
                                                <i data-lucide="check-circle"></i>
                                                Faol
                                            </span>
                                    @else
                                        <span class="status-pill status-expired">
                                                <i data-lucide="x-circle"></i>
                                                Muddat tugagan
                                            </span>
                                    @endif
                                    @break
                                @case(2)
                                    <span class="status-pill status-pending">
                                            <i data-lucide="clock"></i>
                                            Kutilmoqda
                                        </span>
                                    @break
                                @case(0)
                                    <span class="status-pill status-rejected">
                                            <i data-lucide="x-circle"></i>
                                            Rad etilgan
                                        </span>
                                    @break
                            @endswitch
                        </td>

                        <!-- Created At -->
                        <td class="cell-created">
                            <div class="datetime-info">
                                <span class="date">{{ $req->created_at->format('d.m.Y') }}</span>
                                <span class="time">{{ $req->created_at->format('H:i') }}</span>
                            </div>
                        </td>

                        <!-- Actions -->
                        @if($showActions ?? true)
                            <td class="cell-actions">
                                <div class="action-group">
                                    @if($req->status == 2)
                                        <button type="button"
                                                class="action-btn btn-approve approve-btn"
                                                data-id="{{ $req->id }}"
                                                data-org="{{ $req->organization->name_uz ?? 'Noma\'lum' }}"
                                                data-tariff="{{ $req->tariff->name }}">
                                            <i data-lucide="check"></i>
                                            <span>Tasdiqlash</span>
                                        </button>

                                        <button type="button"
                                                class="action-btn btn-reject reject-btn"
                                                data-id="{{ $req->id }}"
                                                data-org="{{ $req->organization->name_uz ?? 'Noma\'lum' }}"
                                                data-tariff="{{ $req->tariff->name }}">
                                            <i data-lucide="x"></i>
                                            <span>Rad etish</span>
                                        </button>
                                    @endif

                                    <!-- Edit button — always visible -->
                                    <button type="button"
                                            class="action-btn btn-edit edit-btn"
                                            data-id="{{ $req->id }}">
                                        <i data-lucide="pencil"></i>
                                        <span>Tahrirlash</span>
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subscriptions->hasPages())
            <div class="table-pagination">
                {!! $subscriptions->links() !!}
            </div>
        @endif
    </div>
@else
    <div class="empty-table-state">
        <div class="empty-content">
            <i data-lucide="inbox"></i>
            <h3>Ma'lumot topilmadi</h3>
            <p>Hozircha bu bo'limda ma'lumot yo'q</p>
        </div>
    </div>
@endif

<style>
    /* ========================================
       SUBSCRIPTIONS TABLE - FIXED VERSION
       ======================================== */

    .subscriptions-table-container {
        width: 100%;
    }

    .table-responsive-modern {
        overflow-x: auto;
        border-radius: 12px;
        background: white;
    }

    /* Table Base */
    .subscriptions-table-modern {
        width: 100%;
        border-collapse: collapse;
        background: white;
        font-size: 14px;
    }

    /* ========================================
       TABLE HEADER
       ======================================== */
    .subscriptions-table-modern thead {
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .subscriptions-table-modern thead tr {
        border: none;
    }

    .subscriptions-table-modern thead th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        color: white;
        white-space: nowrap;
        border: none;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    .subscriptions-table-modern thead th:last-child {
        border-right: none;
    }

    .subscriptions-table-modern thead th i {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        vertical-align: middle;
        color: white;
        opacity: 0.9;
    }

    /* Column Widths */
    .col-number { width: 60px; text-align: center; }
    .col-org { min-width: 200px; }
    .col-tariff { min-width: 150px; }
    .col-price { min-width: 130px; }
    .col-date { min-width: 120px; }
    .col-status { min-width: 140px; }
    .col-created { min-width: 140px; }
    .col-actions { min-width: 280px; text-align: center; }

    /* ========================================
       TABLE BODY
       ======================================== */
    .subscriptions-table-modern tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .subscriptions-table-modern tbody tr:last-child {
        border-bottom: none;
    }

    .subscriptions-table-modern tbody tr:hover {
        background: linear-gradient(90deg, #faf8ff 0%, #ffffff 100%);
    }

    .subscriptions-table-modern tbody td {
        padding: 14px 20px;
        vertical-align: middle;
        color: #374151;
    }

    /* Number Cell */
    .cell-number {
        text-align: center;
        font-weight: 700;
        color: #94a3b8;
        font-size: 13px;
    }

    /* Org Cell */
    .org-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .org-info i {
        width: 18px;
        height: 18px;
        color: #794DFD;
        flex-shrink: 0;
    }

    .org-info span {
        font-weight: 600;
        color: #1e293b;
    }

    /* Tariff Cell */
    .tariff-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        border-radius: 20px;
        color: #794DFD;
        font-size: 13px;
    }

    .tariff-badge i {
        width: 16px;
        height: 16px;
    }

    /* Price Cell */
    .price-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .price-info .amount {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }

    .price-info .currency {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }

    /* Date Cell */
    .date-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .date-info i {
        width: 16px;
        height: 16px;
        color: #64748b;
        flex-shrink: 0;
    }

    .date-info span {
        font-weight: 500;
        color: #475569;
    }

    /* Status Cell */
    .cell-status {
        text-align: center;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        border: 2px solid;
    }

    .status-pill i {
        width: 16px;
        height: 16px;
    }

    .status-active {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        color: #2e7d32;
        border-color: #4caf50;
    }

    .status-pending {
        background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
        color: #f57c00;
        border-color: #ffa726;
    }

    .status-expired {
        background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
        color: #616161;
        border-color: #9e9e9e;
    }

    .status-rejected {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #c62828;
        border-color: #ef5350;
    }

    /* Created Cell */
    .datetime-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .datetime-info .date {
        font-weight: 600;
        color: #1e293b;
        font-size: 13px;
    }

    .datetime-info .time {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }

    /* Actions Cell */
    .cell-actions {
        text-align: center;
    }

    .action-group {
        display: flex;
        gap: 8px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .action-btn i {
        width: 16px;
        height: 16px;
    }

    .btn-approve {
        background: linear-gradient(135deg, #4caf50 0%, #43a047 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
    }

    .btn-reject {
        background: linear-gradient(135deg, #f44336 0%, #e53935 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    }

    /* Edit button */
    .btn-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .no-action {
        color: #cbd5e1;
        font-size: 18px;
        font-weight: 500;
    }

    /* ========================================
       PAGINATION
       ======================================== */
    .table-pagination {
        padding: 20px;
        background: white;
        border-top: 2px solid #f1f5f9;
        display: flex;
        justify-content: center;
    }

    /* ========================================
       EMPTY STATE
       ======================================== */
    .empty-table-state {
        background: white;
        border-radius: 12px;
        padding: 80px 40px;
        text-align: center;
    }

    .empty-content {
        max-width: 400px;
        margin: 0 auto;
    }

    .empty-content i {
        width: 80px;
        height: 80px;
        color: #cbd5e1;
        margin-bottom: 20px;
    }

    .empty-content h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .empty-content p {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }

    /* ========================================
       RESPONSIVE
       ======================================== */
    @media (max-width: 1400px) {
        .subscriptions-table-modern {
            min-width: 1200px;
        }
    }

    @media (max-width: 768px) {
        .subscriptions-table-modern thead th {
            padding: 12px 16px;
            font-size: 12px;
        }

        .subscriptions-table-modern tbody td {
            padding: 12px 16px;
        }

        .action-btn span {
            display: none;
        }

        .action-btn {
            padding: 8px 12px;
        }

        .action-group {
            gap: 6px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
