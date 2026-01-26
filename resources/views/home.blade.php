<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    <title>EDOFY</title>
</head>

@extends('layouts.app')

@section('content')
<script src="https://unpkg.com/lucide@latest"></script>

<div class="container-fluid dashboard-modern">
    <div class="page-header-modern mb-4">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon">
                    <i data-lucide="home"></i>
                </div>
                <div class="header-text">
                    <h1>Asosiy</h1>
                    <p>Protokol hujjatlari va jarayonlarni boshqarish</p>
                </div>
            </div>

            <div class="header-center">
                @include('templates.alert')
            </div>

            <div class="header-right">
                <nav class="breadcrumb-modern">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>Bosh sahifa</span>
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
                <p>Ko'rib chiqish uchun</p>
            </div>
            <div class="stat-overlay"></div>
            <div class="stat-overlay2"></div>
            <div class="access"><i data-lucide="arrow-right"></i></div>
        </div>
    </div>

 
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card gradient-orange">
            <div class="card-icon">
                <i data-lucide="pen-tool"></i>
            </div>
            <div class="card-content">
                <h3>{{ $docs_count['doc_sign_menu_id'] ?? 0 }}</h3>
                <p>Imzolash uchun</p>
            </div>
            <div class="stat-overlay"></div>
            <div class="stat-overlay2"></div>
            <div class="access"><i data-lucide="arrow-right"></i></div>
        </div>
    </div>

  
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card gradient-blue">
            <div class="card-icon">
                <i data-lucide="users"></i>
            </div>
            <div class="card-content">
                <h3>{{ $docs_count['doc_approve_menu_id'] ?? 0 }}</h3>
                <p>Hamkorlik uchun</p>
            </div>
            <div class="stat-overlay"></div>
            <div class="stat-overlay2"></div>
            <div class="access"><i data-lucide="arrow-right"></i></div>
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
                <p>Mening hujjatlarim</p>
            </div>
            <div class="stat-overlay"></div>
            <div class="stat-overlay2"></div>
            <div class="access"><i data-lucide="arrow-right"></i></div>
        </div>
    </div>

</div>




 
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="glass-card p-4">
               <div class="custom-tabs">
    <div class="custom-tabs__header">
        <button class="custom-tab active" data-tab="tab-1" >
              <i data-lucide="file-input" style="width: 18px"></i>
           Ishchi hujjatlar
        </button>
        <button class="custom-tab" data-tab="tab-2">
          
            <i data-lucide="message-circle-more" style="width: 18px"></i>
           Chiquvchi xatlar
        </button>
    </div>

    <div class="custom-tabs__body">
        <div class="custom-tab-content active" id="tab-1">
            <p> Ishchi hujjatlar</p>
        </div>

        <div class="custom-tab-content" id="tab-2">
            <p>  Chiquvchi xatlar</p>
        </div>
    </div>
</div>


            </div>

        </div>

    </div>
</div>

<script>
    lucide.createIcons();
</script>

<script>
    const chartData = [
            { value: 35, color: '#7B40F2' },
            { value: 25, color: '#8753F4' },
            { value: 20, color: '#9467F6' },
            { value: 15, color: '#A07AF7' },
            { value: 5,  color: '#AE8EF9' }
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
</script>

@endsection

<style>
    .dashboard-modern {
        padding: 1.5rem 1rem;
        min-height: calc(100vh - 70px);
    }

    .page-header-modern {
        background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
        border-radius: 16px;
        padding: 1.75rem 2.25rem;
        color: white;
        box-shadow: 0 12px 32px rgba(121, 77, 253, 0.28);
        margin-bottom: 2.5rem;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .header-icon i {
        width: 36px;
        height: 36px;
        opacity: 0.95;
    }

    .header-text h1 {
        margin: 0;
        font-size: 2.25rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .header-text p {
        margin: 0.25rem 0 0;
        opacity: 0.9;
        font-size: 1rem;
    }

    .breadcrumb-modern a {
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .breadcrumb-modern a:hover {
        opacity: 0.85;
    }

    .stat-card {
        border-radius: 16px;
        padding: 1rem 1.4rem 2rem;
        color: white;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(4px);
        position: relative;
    }

    .stat-overlay {
        content: "";
        position: absolute;
        top: -50px;
        right: -40px;
        width: 160px;
        height: 160px;
        transform: rotate(45deg);
        background: #C575D3;
        pointer-events: none;
        transition: opacity 0.4s ease;
        border-radius: 16px;
        opacity: 30%;
    }

    .stat-overlay2 {
        content: "";
        position: absolute;
        top: 15px;
        right: -50px;
        width: 160px;
        height: 160px;
        transform: rotate(45deg);
        background: #C575D3;
        pointer-events: none;
        transition: opacity 0.4s ease;
        border-radius: 16px;
        opacity: 30%;
    }

    .stat-overlay,
.stat-overlay2 {
    position: absolute;
    width: 160px;
    height: 160px;
    transform: rotate(45deg) translate(0, 0);
    border-radius: 16px;
    opacity: 0.28;
    background: var(--overlay-color);
    pointer-events: none;

    transition: 
        transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1),
        opacity 0.4s ease;
}

    .stat-card:hover {
        transform: translateY(-2px) scale(1);
        box-shadow: 0 4px 8px rgba(126, 126, 126, 0.22);
    }



    .gradient-red {
        background: linear-gradient(180deg, #A53FB6 68.51%, #B83F91 100%);
    }

    .gradient-warning {
        background: linear-gradient(180deg, #FC804A 68.51%, #F99B52 100%);
    }

    .gradient-blue {
        background: linear-gradient(135deg, #60a5fa, #3b82f6);
    }

    .gradient-green {
        background: linear-gradient(135deg, #4ade80, #22c55e);
    }

    .gradient-purple {
        background: linear-gradient(135deg, #a78bfa, #7c3aed);
    }

    .card-icon i {}

    .card-icon {
        background: rgba(255, 255, 255, 0.2) !important;
        border-radius: 12px;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-content h3 {
        font-size: 2.5rem;
        margin: 0.75rem 0 0.25rem;
        font-weight: 800;
    }

    .card-content p {
        margin: 0;
        font-size: 1rem;
        opacity: 0.92;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.07);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
    }

    .layer-container {
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    .profile-layer {
        position: relative;
        width: 60px;
        height: 60px;
        cursor: pointer;
    }

    .layer-content {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        text-align: center;
        z-index: 2;
        padding: 1rem;
    }

    .layer-content span {
        font-size: 3rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 0.5rem;
        text-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    }

    .layer-content p {
        font-size: 1rem;
        font-weight: 600;
        opacity: 0.95;
        margin: 0;
    }

    .chart-container {
        max-width: 260px;
        margin: 0 auto;
    }

    #donutChart {
        width: 100%;
        height: auto;
    }

    .chart-label {
        font-size: 1rem;
        fill: #64748b;
        font-weight: 600;
    }

    .chart-value {
        font-size: 1.5rem;
        fill: #1e293b;
        font-weight: 800;
    }

    .segment {
        transition: all 0.4s ease;
    }

    .segment:hover {
        opacity: 0.85;
        transform: scale(1.05);
    }

    .header-text h1 {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin-bottom: 0.25rem;

    }

    .header-text p {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.9);
    }

    .header-icon {
        margin: 8px 0 !important;
    }

    .access {
        position: absolute;
        bottom: 25px;
        right: 25px;
    }

    .layer-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .incoming-counter img {
        width: 150px;
        height: auto;
    }

   

    .col-xl-8 {
        min-width: 100% !important;
    }

.stat-card {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    padding: 1rem 1.4rem 2rem;
    color: #fff;
}

.stat-overlay,
.stat-overlay2 {
    position: absolute;
    width: 160px;
    height: 160px;
    transform: rotate(45deg);
    border-radius: 16px;
    opacity: 0.28;
    background: var(--overlay-color);
    pointer-events: none;
}

.stat-overlay {
    top: -50px;
    right: -40px;
}

.stat-overlay2 {
    top: 20px;
    right: -60px;
}


.gradient-red {
    background: linear-gradient(180deg, #A53FB6 0%, #B83F91 100%);
    --overlay-color: #d07cd8;
}


.gradient-orange {
    background: linear-gradient(180deg, #FC804A 0%, #F99B52 100%);
    --overlay-color: #ffd1a3;
}


.gradient-blue {
    background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
    --overlay-color: #9cc6ff;
}


.gradient-green {
    background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
    --overlay-color: #9ff0c0;
}


.custom-tabs {
    max-width: 34%;
}

.custom-tabs__header {
    display: flex;
    background: rgba(121, 77, 253, 0.1);
    border-radius: 12px;
    overflow: hidden;
}

.custom-tab {
    flex: 1;
    padding: 12px 14px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.35s ease;
}

.custom-tab.active {
    background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(121, 77, 253, 0.4);
}

.custom-tabs__body {
    position: relative;
    margin-top: 35px;
    min-height: 200px;
}

.custom-tab-content {
    display: none;
}

.custom-tab-content.active {
    display: block;
}

</style>