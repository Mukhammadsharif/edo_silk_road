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

        {{-- ═══════════════════════════════════════════════════════════════
             ДАШБОРД РУКОВОДИТЕЛЯ  (виден только роли Director / владельцу)
        ═══════════════════════════════════════════════════════════════ --}}
        @if(!empty($isDirector) && !empty($directorStats))
            @php
                $loc = session()->get('locale', app()->getLocale());
                $T = [
                    'uz' => [
                        'title' => 'Rahbar dashboardi', 'subtitle' => "Tashkilot hujjat aylanmasi bo'yicha umumiy ko'rsatkichlar",
                        'total' => 'Jami hujjatlar', 'active' => 'Jarayonda', 'expired' => 'Muddati o\'tgan',
                        'stages' => 'Qayta ishlash bosqichlari', 'exec' => 'Ijroda', 'agree' => 'Kelishuvda', 'sign' => 'Imzoda', 'resol' => 'Rezolyutsiyada',
                        'status_title' => 'Hujjatlar holati', 'draft' => 'Qoralama', 'process' => 'Jarayonda', 'done' => 'Yakunlangan', 'ensured' => 'Ta\'minlangan',
                        'types' => 'Hujjat turlari kesimida', 'trend' => 'Hujjat aylanmasi dinamikasi (6 oy)', 'docs' => 'hujjat', 'live' => 'Jonli',
                    ],
                    'ru' => [
                        'title' => 'Дашборд руководителя', 'subtitle' => 'Сводные показатели документооборота организации',
                        'total' => 'Всего документов', 'active' => 'В работе', 'expired' => 'Просрочено',
                        'stages' => 'Стадии обработки', 'exec' => 'На исполнении', 'agree' => 'На согласовании', 'sign' => 'На подписи', 'resol' => 'На резолюции',
                        'status_title' => 'Статусы документов', 'draft' => 'Черновик', 'process' => 'В процессе', 'done' => 'Завершено', 'ensured' => 'Обеспечено',
                        'types' => 'В разрезе типов документов', 'trend' => 'Динамика документооборота (6 мес.)', 'docs' => 'док.', 'live' => 'Онлайн',
                    ],
                    'en' => [
                        'title' => 'Director Dashboard', 'subtitle' => 'Organization-wide document workflow overview',
                        'total' => 'Total documents', 'active' => 'In progress', 'expired' => 'Overdue',
                        'stages' => 'Processing stages', 'exec' => 'Execution', 'agree' => 'Agreement', 'sign' => 'Signing', 'resol' => 'Resolution',
                        'status_title' => 'Document statuses', 'draft' => 'Draft', 'process' => 'In process', 'done' => 'Completed', 'ensured' => 'Ensured',
                        'types' => 'By document type', 'trend' => 'Document flow dynamics (6 mo.)', 'docs' => 'docs', 'live' => 'Live',
                    ],
                ];
                $L = $T[$loc] ?? $T['uz'];
            @endphp

            <div class="director-dashboard" id="directorDashboard">
                <div class="dd-section-head">
                    <div class="dd-section-title">
                        <span class="dd-badge"><span class="dd-pulse"></span>{{ $L['live'] }}</span>
                        <div>
                            <h2>{{ $L['title'] }}</h2>
                            <p>{{ $L['subtitle'] }}</p>
                        </div>
                    </div>
                    <div class="dd-section-icon"><i data-lucide="layout-dashboard"></i></div>
                </div>

                {{-- KPI highlight cards --}}
                <div class="dd-kpi-row">
                    <div class="dd-kpi dd-kpi-primary">
                        <div class="dd-kpi-glow"></div>
                        <div class="dd-kpi-ic"><i data-lucide="files"></i></div>
                        <div class="dd-kpi-body">
                            <span class="dd-count" data-target="{{ $directorStats['totals']['documents'] }}">0</span>
                            <p>{{ $L['total'] }}</p>
                        </div>
                    </div>
                    <div class="dd-kpi dd-kpi-info">
                        <div class="dd-kpi-glow"></div>
                        <div class="dd-kpi-ic"><i data-lucide="activity"></i></div>
                        <div class="dd-kpi-body">
                            <span class="dd-count" data-target="{{ $directorStats['totals']['active'] }}">0</span>
                            <p>{{ $L['active'] }}</p>
                        </div>
                    </div>
                    <div class="dd-kpi dd-kpi-danger">
                        <div class="dd-kpi-glow"></div>
                        <div class="dd-kpi-ic"><i data-lucide="alarm-clock-off"></i></div>
                        <div class="dd-kpi-body">
                            <span class="dd-count" data-target="{{ $directorStats['totals']['expired'] }}">0</span>
                            <p>{{ $L['expired'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Charts grid --}}
                <div class="dd-grid">
                    <div class="dd-card dd-card-wide">
                        <div class="dd-card-head">
                            <h3><i data-lucide="trending-up"></i> {{ $L['trend'] }}</h3>
                        </div>
                        <div id="ddTrendChart"></div>
                    </div>

                    <div class="dd-card">
                        <div class="dd-card-head">
                            <h3><i data-lucide="git-branch"></i> {{ $L['stages'] }}</h3>
                        </div>
                        <div id="ddStageChart"></div>
                    </div>

                    <div class="dd-card">
                        <div class="dd-card-head">
                            <h3><i data-lucide="pie-chart"></i> {{ $L['status_title'] }}</h3>
                        </div>
                        <div id="ddStatusChart"></div>
                    </div>

                    <div class="dd-card dd-card-wide">
                        <div class="dd-card-head">
                            <h3><i data-lucide="bar-chart-3"></i> {{ $L['types'] }}</h3>
                        </div>
                        <div id="ddTypeChart"></div>
                    </div>
                </div>
            </div>

            <script>
                window.__ddStats = @json($directorStats);
                window.__ddLabels = @json($L);
            </script>
        @endif

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

@push('scripts')
    @if(!empty($isDirector) && !empty($directorStats))
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
        <script>
            (function () {
                const stats  = window.__ddStats || {};
                const L      = window.__ddLabels || {};
                if (!window.ApexCharts || !stats.stages) return;

                // Фирменная палитра EDOLINE
                const C = {
                    purple: '#794DFD', purpleLite: '#9d6fff',
                    blue:   '#3b82f6', cyan: '#06b6d4',
                    green:  '#10b981', amber: '#f59e0b',
                    red:    '#ef4444', slate: '#94a3b8'
                };

                const baseFont = "'Nunito Sans', 'Segoe UI', sans-serif";

                /* ── Анимированный счётчик в KPI-картах ── */
                function animateCounts() {
                    document.querySelectorAll('.dd-count').forEach(function (el) {
                        const target = parseInt(el.getAttribute('data-target'), 10) || 0;
                        const dur = 1200, start = performance.now();
                        function tick(now) {
                            const p = Math.min((now - start) / dur, 1);
                            const eased = 1 - Math.pow(1 - p, 3);
                            el.textContent = Math.round(target * eased).toLocaleString();
                            if (p < 1) requestAnimationFrame(tick);
                        }
                        requestAnimationFrame(tick);
                    });
                }

                /* ── 1. Динамика документооборота (area) ── */
                function trendChart() {
                    const m = stats.monthly || [];
                    new ApexCharts(document.querySelector('#ddTrendChart'), {
                        chart: { type: 'area', height: 300, fontFamily: baseFont, toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 1100, animateGradually: { enabled: true, delay: 150 } } },
                        series: [{ name: L.docs || 'docs', data: m.map(x => x.count) }],
                        colors: [C.purple],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 3 },
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.55, opacityTo: 0.05, stops: [0, 90, 100] } },
                        grid: { borderColor: '#eef0f5', strokeDashArray: 4 },
                        xaxis: { categories: m.map(x => x.label), labels: { style: { colors: '#8a94a6' } },
                            axisBorder: { show: false }, axisTicks: { show: false } },
                        yaxis: { labels: { style: { colors: '#8a94a6' } } },
                        markers: { size: 4, colors: ['#fff'], strokeColors: C.purple, strokeWidth: 3, hover: { size: 7 } },
                        tooltip: { theme: 'light' }
                    }).render();
                }

                /* ── 2. Стадии обработки (radialBar) ── */
                function stageChart() {
                    const s = stats.stages || {};
                    const vals = [s.exec || 0, s.agree || 0, s.sign || 0, s.resol || 0];
                    const max  = Math.max(1, vals.reduce((a, b) => a + b, 0));
                    new ApexCharts(document.querySelector('#ddStageChart'), {
                        chart: { type: 'radialBar', height: 340, fontFamily: baseFont,
                            animations: { enabled: true, speed: 1100 } },
                        series: vals.map(v => Math.round((v / max) * 100)),
                        labels: [L.exec, L.agree, L.sign, L.resol],
                        colors: [C.purple, C.blue, C.amber, C.green],
                        plotOptions: { radialBar: { hollow: { size: '38%' }, track: { background: '#f1f2f7' },
                                dataLabels: { name: { fontSize: '13px' }, value: {
                                        formatter: function (val, opts) {
                                            return (opts && opts.w) ? vals[opts.seriesIndex] : val;
                                        }, fontSize: '20px', fontWeight: 700 },
                                    total: { show: true, label: (L.docs || 'docs'),
                                        formatter: function () { return vals.reduce((a, b) => a + b, 0); } } } } },
                        stroke: { lineCap: 'round' },
                        legend: { show: true, position: 'bottom', fontSize: '13px',
                            formatter: function (seriesName, opts) { return seriesName + ': ' + vals[opts.seriesIndex]; } }
                    }).render();
                }

                /* ── 3. Статусы документов (donut) ── */
                function statusChart() {
                    const st = stats.statuses || {};
                    const series = [st.draft || 0, st.process || 0, st.done || 0, st.ensured || 0, st.expired || 0];
                    new ApexCharts(document.querySelector('#ddStatusChart'), {
                        chart: { type: 'donut', height: 340, fontFamily: baseFont,
                            animations: { enabled: true, speed: 1000, animateGradually: { enabled: true, delay: 120 } } },
                        series: series,
                        labels: [L.draft, L.process, L.done, L.ensured, L.expired],
                        colors: [C.slate, C.purple, C.green, C.cyan, C.red],
                        dataLabels: { enabled: true, formatter: function (val, o) { return o.w.config.series[o.seriesIndex]; },
                            style: { fontSize: '12px', fontWeight: 700 }, dropShadow: { enabled: false } },
                        stroke: { width: 2, colors: ['#fff'] },
                        plotOptions: { pie: { donut: { size: '62%', labels: { show: true,
                                        total: { show: true, label: (L.total || 'Total'), fontSize: '13px',
                                            formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); } } } } } },
                        legend: { position: 'bottom', fontSize: '12px' },
                        tooltip: { theme: 'light' }
                    }).render();
                }

                /* ── 4. По типам документов (horizontal bar) ── */
                function typeChart() {
                    const t = stats.types || [];
                    const grad = ['#794DFD', '#8a5cff', '#9b6bff', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'];
                    new ApexCharts(document.querySelector('#ddTypeChart'), {
                        chart: { type: 'bar', height: Math.max(260, t.length * 46 + 40), fontFamily: baseFont,
                            toolbar: { show: false }, animations: { enabled: true, speed: 1100,
                                animateGradually: { enabled: true, delay: 120 } } },
                        series: [{ name: L.docs || 'docs', data: t.map(x => x.count) }],
                        colors: [C.purple],
                        plotOptions: { bar: { horizontal: true, borderRadius: 8, barHeight: '62%', distributed: true } },
                        dataLabels: { enabled: true, style: { colors: ['#fff'], fontWeight: 700 }, offsetX: -6 },
                        stroke: { width: 0 },
                        colors: grad,
                        xaxis: { categories: t.map(x => x.name), labels: { style: { colors: '#8a94a6' } },
                            axisBorder: { show: false }, axisTicks: { show: false } },
                        yaxis: { labels: { style: { colors: '#4b5563', fontSize: '13px' }, maxWidth: 220 } },
                        grid: { borderColor: '#eef0f5', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                        legend: { show: false },
                        tooltip: { theme: 'light', y: { title: { formatter: function () { return ''; } } } }
                    }).render();
                }

                function initDashboard() {
                    animateCounts();
                    try { trendChart(); } catch (e) { console.warn('trend', e); }
                    try { stageChart(); } catch (e) { console.warn('stage', e); }
                    try { statusChart(); } catch (e) { console.warn('status', e); }
                    try { typeChart(); } catch (e) { console.warn('type', e); }
                    if (window.lucide) lucide.createIcons();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initDashboard);
                } else {
                    initDashboard();
                }
            })();
        </script>
    @endif
@endpush
