@php
    use App\Models\Admin\OrganizationUnits;
@endphp

@php $isOwner = in_array(Auth::id(), OrganizationUnits::where('status', 1)->pluck('owner_user_id')->toArray()); @endphp
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_new.svg') }}">

    <title>EDOLINE</title>
</head>

@extends('layouts.app')


<link rel="stylesheet" href="{{ asset('css/templates/pricing.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/home.css') }}">

@section('content')
    <div class="container-fluid dashboard-modern">
        <div class="page-header-modern mb-4">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="home"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('field.main') }}</h1>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') }}</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card gradient-red">
                    <div class="card-icon">
                        <i data-lucide="file-check"></i>
                    </div>
                    <div class="card-content">
                        <h3>{{ $docs_count['doc_conf_menu_id'] ?? 0 }}</h3>
                        <p>{{ __('menu.for_resolution') }}</p>
                    </div>
                    <div class="stat-overlay"></div>
                    <div class="stat-overlay2"></div>
                    <div class="access">
                        <a href="{{ route('documents.for-confirm.index') }}"><i data-lucide="arrow-right"
                                                                                class="text-white"></i></a>
                    </div>
                </div>
            </div>


            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card gradient-orange">
                    <div class="card-icon">
                        <i data-lucide="pen-tool"></i>
                    </div>
                    <div class="card-content">
                        <h3>{{ $docs_count['doc_sign_menu_id'] ?? 0 }}</h3>
                        <p>{{ __('menu.for_sign') }}</p>
                    </div>
                    <div class="stat-overlay"></div>
                    <div class="stat-overlay2"></div>
                    <div class="access">
                        <a href="{{ route('documents.for-sign.index') }}"><i data-lucide="arrow-right"
                                                                             class="text-white"></i></a>
                    </div>
                </div>
            </div>


            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card gradient-blue">
                    <div class="card-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="card-content">
                        <h3>{{ $docs_count['doc_approve_menu_id'] ?? 0 }}</h3>
                        <p>{{ __('menu.for_coillaborate') }}</p>
                    </div>
                    <div class="stat-overlay"></div>
                    <div class="stat-overlay2"></div>
                    <div class="access">
                        <a href="{{ route('documents.for-approval.index') }}"><i data-lucide="arrow-right"
                                                                                 class="text-white"></i></a>
                    </div>
                </div>
            </div>

            <!-- 4 -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card gradient-green">
                    <div class="card-icon">
                        <i data-lucide="folder"></i>
                    </div>
                    <div class="card-content">
                        <h3>{{ $docs_count['my_docs_menu_id'] ?? 0 }}</h3>
                        <p>{{ __('menu.my_docs') }}</p>
                    </div>
                    <div class="stat-overlay"></div>
                    <div class="stat-overlay2"></div>
                    <div class="access">
                        <a href="{{ route('office.work-docs') }}"><i data-lucide="arrow-right"
                                                                     class="text-white"></i></a>
                    </div>
                </div>
            </div>
        </div>

        @if(Auth::user()->organization?->pendingSubscription && $isOwner)
            <div class="pending">
                <div class="subscription-card">
                    <button class="card-close" onclick="closeSubscriptionCard()">
                        <i data-lucide="x"></i>
                    </button>
                    <div class="icon-wrap">⏳</div>

                    <h1>Obuna so‘rovingiz qabul qilindi!</h1>

                    <div class="status">
                        Administrator tasdiqlashini kutmoqdamiz
                    </div>

                    <p class="pending-text">
                        Tez orada obunangiz faollashadi (odatda 1–2 ish kuni ichida).
                        Sabr qilganingiz uchun rahmat!
                    </p>

                    <div class="checklist">
                        <div class="check-item done">Ro‘yxatdan o‘tish</div>
                        <div class="check-item done">Tarif tanlash</div>
                        <div class="check-item pending-item">
                            <span class="spinner"></span>
                            Admin tasdiqlashi
                        </div>
                        <div class="check-item pending-item">
                            <span class="spinner"></span>
                            Dashboardga kirish va boshlash
                        </div>
                    </div>

                    <div class="actions">
                        <a href="{{ route('pricing') }}" class="sub-btn sub-btn-outline">
                            <i data-lucide="receipt-text"></i> So‘rov tafsilotlari
                        </a>
                        <a href="mailto:info@edoline.uz" class="sub-btn sub-btn-primary">
                            <i data-lucide="headset"></i> Support bilan bog‘lanish
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if(!Auth::user()->organization?->pendingSubscription && !Auth::user()->organization?->currentSubscription && $isOwner)
            <div class="pending">
                <div class="subscription-card">
                    <button class="card-close" onclick="closeSubscriptionCard()">
                        <i data-lucide="x"></i>
                    </button>
                    <div class="icon-wrap">⏳</div>

                    <h1>Sizda faol obuna mavjud emas</h1>

                    <div class="status">
                        Tariflar bo'limidan o'zingizga mos tarifni tanlang
                    </div>

                    <p class="pending-text">
                        Administrator tomonidan tasdiqlangandan so'ng tarif faollashadi.
                    </p>

                    <div class="checklist">
                        <div class="check-item done">Ro‘yxatdan o‘tish</div>
                        <div class="check-item pending-item">
                            <span class="spinner"></span>
                            Tarif tanlash
                        </div>
                        <div class="check-item pending-item">
                            <span class="spinner"></span>
                            Admin tasdiqlashi
                        </div>
                        <div class="check-item pending-item">
                            <span class="spinner"></span>
                            Dashboardga kirish va boshlash
                        </div>
                    </div>

                    <div class="actions">
                        <a href="{{ route('pricing') }}" class="sub-btn sub-btn-primary">
                            <i data-lucide="award"></i> {{ __('field.tariffs') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        const chartData = [
            {value: 35, color: '#7B40F2'},
            {value: 25, color: '#8753F4'},
            {value: 20, color: '#9467F6'},
            {value: 15, color: '#A07AF7'},
            {value: 5, color: '#AE8EF9'}
        ];

        function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
            const angleInRadians = (angleInDegrees * Math.PI) / 180;
            return {
                x: centerX + radius * Math.cos(angleInRadians),
                y: centerY + radius * Math.sin(angleInRadians)
            };
        }

        function createDonutSegment(startAngle, endAngle, outerRadius, innerRadius) {
            const start = polarToCartesian(120, 120, outerRadius, endAngle);
            const end = polarToCartesian(120, 120, outerRadius, startAngle);
            const innerStart = polarToCartesian(120, 120, innerRadius, endAngle);
            const innerEnd = polarToCartesian(120, 120, innerRadius, startAngle);

            const largeArcFlag = endAngle - startAngle <= 180 ? 0 : 1;

            return [
                'M', start.x, start.y,
                'A', outerRadius, outerRadius, 0, largeArcFlag, 0, end.x, end.y,
                'L', innerEnd.x, innerEnd.y,
                'A', innerRadius, innerRadius, 0, largeArcFlag, 1, innerStart.x, innerStart.y,
                'Z'
            ].join(' ');
        }

        function renderDonutChart() {
            const svg = document.getElementById('donutChart');
            if (!svg) return;

            const total = chartData.reduce((sum, item) => sum + item.value, 0);
            svg.innerHTML = '';

            let currentAngle = -90;
            const outerRadius = 100;
            const innerRadius = 65;

            chartData.forEach(item => {
                const angle = (item.value / total) * 360;
                const endAngle = currentAngle + angle;

                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', createDonutSegment(currentAngle, endAngle, outerRadius, innerRadius));
                path.setAttribute('fill', item.color);
                path.classList.add('segment');
                svg.appendChild(path);

                currentAngle = endAngle;
            });

            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', '120');
            text.setAttribute('y', '120');
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('dominant-baseline', 'middle');
            text.innerHTML = `
                <tspan x="120" class="chart-label">Umumiy</tspan>
                <tspan x="120" dy="24" class="chart-value">100%</tspan>
            `;
            svg.appendChild(text);
        }

        renderDonutChart();
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tabs = document.querySelectorAll(".custom-tab");
            const contents = document.querySelectorAll(".custom-tab-content");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    const target = tab.dataset.tab;

                    tabs.forEach(t => t.classList.remove("active"));
                    contents.forEach(c => c.classList.remove("active"));

                    tab.classList.add("active");
                    document.getElementById(target).classList.add("active");

                    if (window.lucide) lucide.createIcons();
                });
            });
        });

        function closeSubscriptionCard() {
            const card = document.querySelector('.subscription-card');
            if (card) {
                card.style.opacity = "0";
                card.style.transform = "translateY(-10px)";
                setTimeout(() => {
                    card.style.display = "none";
                }, 200);
            }
        }
    </script>

@endsection
