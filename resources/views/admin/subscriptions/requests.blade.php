@extends('layouts.app')

@section('content')
    <!-- Modern Subscription Requests Page -->
    <div class="document-page-modern">

        <!-- Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="package"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.all_subscriptions') }}</h1>
                        <p>{{ __('field.active_pending_rejected_expired') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Main Card -->
            <div class="subscription-requests-card">

                <!-- Card Header -->
                <div class="subscription-card-header">
                    <div class="header-title">
                        <i data-lucide="settings"></i>
                        <h3>{{ __('field.subscription_management') }}</h3>
                    </div>

                    <!-- Statistics -->
                    <div class="stats-badges">
                        <div class="stat-badge pending">
                            <i data-lucide="clock"></i>
                            <div class="stat-info">
                                <span class="stat-value">{{ $pendingCount }}</span>
                                <span class="stat-label">{{ __('field.pending') }}</span>
                            </div>
                        </div>
                        <div class="stat-badge active">
                            <i data-lucide="check-circle"></i>
                            <div class="stat-info">
                                <span class="stat-value">{{ $activeCount }}</span>
                                <span class="stat-label">{{ __('field.active') }}</span>
                            </div>
                        </div>
                        <div class="stat-badge expired">
                            <i data-lucide="x-circle"></i>
                            <div class="stat-info">
                                <span class="stat-value">{{ $expiredCount }}</span>
                                <span class="stat-label">{{ __('field.expired') }}</span>
                            </div>
                        </div>
                        <div class="stat-badge rejected">
                            <i data-lucide="alert-circle"></i>
                            <div class="stat-info">
                                <span class="stat-value">{{ $rejectedCount }}</span>
                                <span class="stat-label">{{ __('field.rejected') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="card-alerts">
                    @include('templates.alert')
                </div>

                <!-- Tabs Navigation -->
                <div class="tabs-modern">
                    <button class="tab-item active" data-tab="pending">
                        <i data-lucide="clock"></i>
                        <span>{{ __('field.pending') }}</span>
                        <span class="tab-badge pending">{{ $pendingCount }}</span>
                    </button>
                    <button class="tab-item" data-tab="active">
                        <i data-lucide="check-circle"></i>
                        <span>{{ __('field.active') }}</span>
                        <span class="tab-badge active">{{ $activeCount }}</span>
                    </button>
                    <button class="tab-item" data-tab="expired">
                        <i data-lucide="calendar-x"></i>
                        <span>{{ __('field.expired') }}</span>
                        <span class="tab-badge expired">{{ $expiredCount }}</span>
                    </button>
                    <button class="tab-item" data-tab="rejected">
                        <i data-lucide="x-circle"></i>
                        <span>{{ __('field.rejected') }}</span>
                        <span class="tab-badge rejected">{{ $rejectedCount }}</span>
                    </button>
                </div>

                <!-- Tabs Content -->
                <div class="tabs-content-modern">
                    <div class="tab-content-item active" id="pending-content">
                        @include('admin.subscriptions.partials.table', ['subscriptions' => $pending])
                    </div>

                    <div class="tab-content-item" id="active-content">
                        @include('admin.subscriptions.partials.table', ['subscriptions' => $active])
                    </div>

                    <div class="tab-content-item" id="expired-content">
                        @include('admin.subscriptions.partials.table', ['subscriptions' => $expired, 'showActions' => false])
                    </div>

                    <div class="tab-content-item" id="rejected-content">
                        @include('admin.subscriptions.partials.table', ['subscriptions' => $rejected, 'showActions' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.subscriptions.partials.modals', ['tariffs' => $tariffs])

    <style>
        /* ========================================
   PAGE HEADER SECTION
   ======================================== */
        .page-header-modern {
            margin-bottom: 2rem;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .header-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 16px rgba(121, 77, 253, 0.3);
        }

        .header-icon i {
            width: 32px;
            height: 32px;
            color: white;
        }

        .header-text h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }

        .header-text p {
            margin: 0.25rem 0 0 0;
            font-size: 14px;
            color: #64748b;
        }

        .header-center {
            flex: 1;
        }
        /* ========================================
           SUBSCRIPTION REQUESTS PAGE STYLES
           ======================================== */
        .document-page-modern {
            padding: 0 2rem 2rem 2rem;
            background: #f8fafc;
            min-height: 100vh;
        }

        .subscription-requests-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Card Header */
        .subscription-card-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-title i {
            width: 28px;
            height: 28px;
            color: #794DFD;
        }

        .header-title h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Statistics Badges */
        .stats-badges {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .stat-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .stat-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .stat-badge i {
            width: 32px;
            height: 32px;
            padding: 6px;
            border-radius: 8px;
        }

        .stat-badge.pending i {
            background: #fff8e1;
            color: #f57c00;
        }

        .stat-badge.active i {
            background: #e8f5e9;
            color: #388e3c;
        }

        .stat-badge.expired i {
            background: #f5f5f5;
            color: #616161;
        }

        .stat-badge.rejected i {
            background: #fbe9e7;
            color: #d84315;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        /* Card Alerts */
        .card-alerts {
            padding: 0 2rem;
            margin-top: 1rem;
        }

        /* Tabs Modern */
        .tabs-modern {
            display: flex;
            gap: 0.5rem;
            padding: 1rem 2rem 0;
            border-bottom: 2px solid #f1f5f9;
            overflow-x: auto;
        }

        .tab-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            color: #64748b;
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
            position: relative;
        }

        .tab-item i {
            width: 20px;
            height: 20px;
        }

        .tab-item:hover {
            color: #794DFD;
            background: #faf8ff;
        }

        .tab-item.active {
            color: #794DFD;
            border-bottom-color: #794DFD;
            background: #faf8ff;
        }

        .tab-badge {
            padding: 0.25rem 0.625rem;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            margin-left: 0.25rem;
        }

        .tab-badge.pending {
            background: #fff8e1;
            color: #f57c00;
        }

        .tab-badge.active {
            background: #e8f5e9;
            color: #388e3c;
        }

        .tab-badge.expired {
            background: #f5f5f5;
            color: #616161;
        }

        .tab-badge.rejected {
            background: #fbe9e7;
            color: #d84315;
        }

        /* Tabs Content */
        .tabs-content-modern {
            padding: 2rem;
        }

        .tab-content-item {
            display: none;
        }

        .tab-content-item.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .subscription-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-badges {
                width: 100%;
                justify-content: space-between;
            }

            .stat-badge {
                flex: 1;
                min-width: 140px;
            }
        }

        @media (max-width: 768px) {
            .stats-badges {
                flex-direction: column;
            }

            .stat-badge {
                width: 100%;
            }

            .tabs-modern {
                padding: 1rem;
            }

            .tab-item {
                flex: 1;
                justify-content: center;
                padding: 0.75rem 1rem;
            }

            .tabs-content-modern {
                padding: 1rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            const tabButtons = document.querySelectorAll('.tab-item');
            const tabContents = document.querySelectorAll('.tab-content-item');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.dataset.tab;

                    // Remove active from all
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active to clicked
                    this.classList.add('active');
                    document.getElementById(tabName + '-content').classList.add('active');
                });
            });

            // Approve button
            $(document).on('click', '.approve-btn', function() {
                let id = $(this).data('id');
                let org = $(this).data('org');
                let tariff = $(this).data('tariff');

                $('#approveOrgName').text(org);
                $('#approveTariffName').text(tariff);
                $('#approveForm').attr('action', '{{ route("admin.subscription-requests.approve", ":id") }}'.replace(':id', id));

                $('#approveModal').modal('show');
            });

            // Reject button
            $(document).on('click', '.reject-btn', function() {
                let id = $(this).data('id');
                let org = $(this).data('org');
                let tariff = $(this).data('tariff');

                $('#rejectOrgName').text(org);
                $('#rejectTariffName').text(tariff);
                $('#rejectForm').attr('action', '{{ route("admin.subscription-requests.reject", ":id") }}'.replace(':id', id));

                $('#rejectModal').modal('show');
            });

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
