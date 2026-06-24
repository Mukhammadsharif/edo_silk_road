@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/templates/pricing.css') }}">

@section('title', 'Tariflar - Obuna tanlash')

@section('content')
    <!-- Alerts -->
    @if(session('success'))
        <div class="alert-modern alert-success-modern">
            <i data-lucide="check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i data-lucide="x"></i>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert-modern alert-warning-modern">
            <i data-lucide="alert-triangle"></i>
            <span>{{ session('warning') }}</span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i data-lucide="x"></i>
            </button>
        </div>
    @endif
    <!-- Modern Pricing Page -->
    <div class="pricing-page-modern">
        <!-- Page Header -->
        <div class="pricing-header-modern">
            <div class="header-content-center">
                <div class="header-icon-large">
                    <i data-lucide="package" class="text-white"></i>
                </div>
                <div class="ml-4">
                    <h1 class="page-title-modern">{{ __('field.our_tariff_plans') }}</h1>
                    <p class="page-subtitle-modern">{{ __('field.choose_tariff_and_period') }}</p>
                </div>
            </div>

            <!-- Current Subscription Card -->
            @if($currentSubscription)
                <div class="subscription-status-card active-subscription">
                    <div class="subscription-header">
                        <div class="subscription-icon">
                            <i data-lucide="check-circle" class="text-white"></i>
                        </div>
                        <h3>{{ __('field.your_current_subscription') }}</h3>
                    </div>
                    <div class="subscription-body">
                        <div class="subscription-tariff-name">{{ $currentSubscription->tariff->name }}</div>
                        <div class="subscription-period">
                            <div class="period-item">
                                <span class="period-label">{{ __('field.validity_period') }}</span>
                                <span class="period-value">
                                {{ \Carbon\Carbon::parse($currentSubscription->starts_at)->format('d.m.Y') }} —
                                {{ \Carbon\Carbon::parse($currentSubscription->ends_at)->format('d.m.Y') }}
                            </span>
                            </div>
                            {{--                            <div class="period-item">--}}
                            {{--                                <span class="period-label">{{ __('field.until_end') }}</span>--}}
                            {{--                                <span class="period-value">{{ \Carbon\Carbon::parse($currentSubscription->ends_at)->diffForHumans() }}</span>--}}
                            {{--                            </div>--}}
                        </div>
                        {{--                        @if($currentSubscription->ends_at < now()->addDays(30))--}}
                        {{--                            <div class="subscription-badge warning">--}}
                        {{--                                <i data-lucide="clock"></i>--}}
                        {{--                                {{ __('field.expires_soon') }}--}}
                        {{--                            </div>--}}
                        {{--                        @endif--}}
                    </div>
                </div>
            @elseif($pendingSubscription)
                <!-- Pending Subscription Card -->
                <div class="subscription-status-card pending-subscription">
                    <div class="subscription-header">
                        <div class="subscription-icon">
                            <i data-lucide="clock" class="text-white"></i>
                        </div>
                        <h3>{{ __('field.subscription_request_pending') }}</h3>
                    </div>
                    <div class="subscription-body">
                        <div class="subscription-tariff-name">{{ $pendingSubscription->tariff->name }}</div>
                        <p class="pending-text">
                            {{ __('field.you') }} <strong>{{ $pendingSubscription->tariff->name }}</strong> {{ __('field.subscription_request_sent') }}
                        </p>
                        <p class="pending-description">
                            {{ __('field.request_under_review') }}<br>
                            {{ __('field.subscription_after_approval') }}
                        </p>
                        <div class="subscription-badge pending">
                            <i data-lucide="loader"></i>
                            {{ __('field.pending') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="subscription-status-card no-subscription">
                    <div class="no-subscription-content">
                        <i data-lucide="info"></i>
                        <h4>{{ __('field.no_active_subscription') }}</h4>
                    </div>
                </div>
            @endif
        </div>

        <!-- Pricing Cards Grid -->
        @if($tariffs->isNotEmpty())
            <div class="pricing-cards-grid">
                @foreach($tariffs as $tariff)
                    <div class="pricing-card-modern {{ $currentSubscription?->tariff_id == $tariff->id ? 'current-tariff' : '' }} {{ $pendingSubscription?->tariff_id == $tariff->id ? 'pending-tariff' : '' }}">

                        <!-- Ribbon for Current/Pending -->
                        @if($currentSubscription?->tariff_id == $tariff->id)
                            <div class="pricing-ribbon current">
                                <i data-lucide="star"></i>
                                {{ __('field.current') }}
                            </div>
                        @endif

                        @if($pendingSubscription?->tariff_id == $tariff->id)
                            <div class="pricing-ribbon pending">
                                <i data-lucide="clock"></i>
                                {{ __('field.pending') }}
                            </div>
                        @endif

                        <!-- Card Header -->
                        <div class="pricing-card-header">
                            <div class="tariff-icon">
                                <i data-lucide="package" class="text-white"></i>
                            </div>
                            <h3 class="tariff-name">{{ $tariff->name }}</h3>
                        </div>

                        <!-- Card Body -->
                        <div class="pricing-card-body">

                            <!-- Period Toggle -->
                            <div class="period-toggle-modern">
                                <label class="period-option active">
                                    <input type="radio" name="period_{{ $tariff->id }}" value="month" checked>
                                    <span class="period-text">
                                    <i data-lucide="calendar"></i>
                                    {{ __('field.per_month') }}
                                </span>
                                </label>
                                <label class="period-option">
                                    <input type="radio" name="period_{{ $tariff->id }}" value="year">
                                    <span class="period-text">
                                    <i data-lucide="calendar-range"></i>
                                    {{ __('field.yearly') }}
                                </span>
                                </label>
                            </div>

                            <!-- Pricing Display -->
                            <div class="pricing-display">
                                <div class="price-block monthly-price">
                                    <div class="price-amount">{{ number_format($tariff->price_monthly) }}</div>
                                    <div class="price-period">{{ __('field.price_per_month') }}</div>
                                </div>
                                <div class="price-block yearly-price d-none">
                                    <div class="price-amount">{{ number_format($tariff->price_yearly) }}</div>
                                    <div class="price-period">{{ __('field.price_per_year') }}</div>
                                </div>

                                @if($tariff->price_yearly > 0 && $tariff->price_yearly < ($tariff->price_monthly * 12))
                                    <div class="savings-badge d-none yearly-saving">
                                        <i data-lucide="trending-down"></i>
                                        {{ __('field.save_amount') }} {{ number_format(($tariff->price_monthly * 12) - $tariff->price_yearly) }} {{ __('field.currency_sum') }}
                                        <span class="discount-percent">
                                        −{{ round((1 - $tariff->price_yearly / ($tariff->price_monthly * 12)) * 100) }}%
                                    </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Features List -->
                            <div class="features-list-modern">
                                <div class="feature-item">
                                    <i data-lucide="users"></i>
                                    <span><strong>{{ $tariff->max_users }}</strong> {{ __('field.user') }}</span>
                                </div>
                                <div class="feature-item">
                                    <i data-lucide="file-text"></i>
                                    <span><strong>{{ number_format($tariff->max_documents) }}</strong> {{ __('field.documents_per_month') }}</span>
                                </div>
                                <div class="feature-item">
                                    <i data-lucide="check-square"></i>
                                    <span>{{ __('field.full_functionality') }}</span>
                                </div>
                                <div class="feature-item">
                                    <i data-lucide="headphones"></i>
                                    <span>{{ __('field.support_24_7') }}</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="pricing-card-footer">
                                @if($currentSubscription?->tariff_id == $tariff->id)
                                    <button class="btn-modern btn-current" disabled>
                                        <i data-lucide="check"></i>
                                        {{ __('field.current_tariff') }}
                                    </button>
                                @else
                                    <button type="button" class="btn-modern btn-select subscribe-btn"
                                            data-tariff-id="{{ $tariff->id }}"
                                            data-tariff-name="{{ $tariff->name }}">
                                        <i data-lucide="arrow-right"></i>
                                        {{ __('field.select') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Support Section -->
        <div class="support-section-modern">
            <i data-lucide="help-circle"></i>
            <p>
                {{ __('field.have_questions') }}
                <a href="mailto:info@edoline.uz">info@edoline.uz</a>
            </p>
        </div>
    </div>

    <!-- Subscribe Modal -->
    <div class="modal-overlay-modern" id="subscribeModal">
        <div class="modal-container-modern">
            <div class="modal-header-modern">
                <div class="modal-icon">
                    <i data-lucide="package" class="text-white"></i>
                </div>
                <h3>{{ __('field.approve_subscription') }}</h3>
                <button type="button" class="modal-close" onclick="closeModal()">
                    <i data-lucide="x" class="text-white"></i>
                </button>
            </div>

            <div class="modal-body-modern">
                <div class="modal-tariff-info">
                    <h4><strong id="modalTariffName"></strong> {{ __('field.to_tariff') }}</h4>
                    <div class="modal-period" id="modalPeriodText"></div>
                </div>

                <p class="modal-question">{{ __('field.want_to_subscribe') }}</p>

                <div class="modal-dates">
                    <div class="date-row">
                        <span class="date-label">{{ __('field.start_date') }}:</span>
                        <span class="date-value">{{ now()->format('d.m.Y') }}</span>
                    </div>
                    <div class="date-row">
                        <span class="date-label">{{ __('field.end_date') }}:</span>
                        <span class="date-value" id="modalEndDate"></span>
                    </div>
                </div>

                <div class="modal-notice">
                    <i data-lucide="info"></i>
                    {{ __('field.wait_for_admin_approval') }}
                </div>
            </div>

            <div class="modal-footer-modern">
                <button type="button" class="btn-modern btn-cancel" onclick="closeModal()" style="width: 45% !important;">
                    <i data-lucide="x"></i>
                    {{ __('action.cancel') }}
                </button>
                <form id="subscribeForm" method="POST" style="display:block; width: 45% !important;">
                    @csrf
                    <input type="hidden" name="period" id="hiddenPeriod" value="month">
                    <button type="submit" class="btn-modern btn-confirm">
                        <i data-lucide="check"></i>
                        {{ __('field.yes_send_request') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('subscribeModal').classList.remove('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Period toggle functionality
            document.querySelectorAll('input[name^="period_"]').forEach(input => {
                input.addEventListener('change', function() {
                    const label = this.closest('label');
                    const toggleGroup = label.closest('.period-toggle-modern');
                    const card = label.closest('.pricing-card-modern');

                    // Update active state
                    toggleGroup.querySelectorAll('.period-option').forEach(opt => opt.classList.remove('active'));
                    label.classList.add('active');

                    // Toggle price display
                    if (this.value === 'year') {
                        card.querySelector('.monthly-price').classList.add('d-none');
                        card.querySelector('.yearly-price').classList.remove('d-none');
                        const savings = card.querySelector('.yearly-saving');
                        if (savings) savings.classList.remove('d-none');
                    } else {
                        card.querySelector('.monthly-price').classList.remove('d-none');
                        card.querySelector('.yearly-price').classList.add('d-none');
                        const savings = card.querySelector('.yearly-saving');
                        if (savings) savings.classList.add('d-none');
                    }
                });
            });

            // Subscribe button functionality
            document.querySelectorAll('.subscribe-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tariffId = this.dataset.tariffId;
                    const tariffName = this.dataset.tariffName;
                    const period = document.querySelector(`input[name="period_${tariffId}"]:checked`).value;

                    document.getElementById('modalTariffName').textContent = tariffName;
                    document.getElementById('hiddenPeriod').value = period;

                    if (period === 'year') {
                        document.getElementById('modalPeriodText').textContent = 'Yillik obuna';
                        document.getElementById('modalEndDate').textContent = '{{ now()->addYear()->format('d.m.Y') }}';
                    } else {
                        document.getElementById('modalPeriodText').textContent = 'Oylik obuna';
                        document.getElementById('modalEndDate').textContent = '{{ now()->addMonth()->format('d.m.Y') }}';
                    }

                    document.getElementById('subscribeForm').action =
                        '{{ route("pricing.subscribe", ":id") }}'.replace(':id', tariffId);

                    document.getElementById('subscribeModal').classList.add('show');
                });
            });

            // Close modal on overlay click
            document.getElementById('subscribeModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
