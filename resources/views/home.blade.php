@extends('layouts.app')

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


@section('content')
    <div class="container-fluid">
        <!-- <div class="row clearfix">
            <div class="col-md-3">
                <div class="card" style="">
                    <div class="card-body text-center"> 
                        <div class="profile-pic mb-20">
                            @if(file_exists(public_path().'/profile_img/'.'img_'.Auth::user()->id.'.png'))
                                <img src="{{ url('/profile_img/'.'img_'.Auth::user()->id.'.png') }}" width="150"
                                     height="150" class="rounded-circle" alt="user" style="object-fit: cover;">
                            @else
                                <img src="{{ url('/images/profile_image1.png') }}" width="150" height="150"
                                     class="rounded-circle" alt="user" style="object-fit: cover;">
                            @endif

                            @php($user_name = Auth::user()->userPersonal??0)
                            <h4 class="mt-20 mb-0">
                                @if($user_name)
                                    {{ $user_name->fullName() }}
                                @endif
                            </h4>
                            <a href="#">
                                @php($user_department = Auth::user()->currentWork->department??0)
                                @if($user_department)
                                    {{ $user_department->lang($user_department->id) }}
                                @endif
                                @php($user_position = Auth::user()->currentWork->position??0)
                                @if($user_position)
                                    {{ $user_position->lang($user_position->id) }}
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="p-4 border-top mt-15">
                        <div class="row text-center">
                            <div class="col-6">
                                <a href="{{ url('/profile/my') }}"
                                   class="link d-flex align-items-center justify-content-center">
                                    <i class="ik ik-user f-20 mr-5"></i>{{ __('field.profile') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                @if(in_array('Director', $authRoleName))
                                    <p class="mb-30 bg-red lbl-card"><i class="ik ik-check"></i> {{ __('menu.for_resolution') }}</p>
                                    <div class="text-center">
                                        <h2 class="mb-0 d-inline-block text-red"><span>{{ $docs_count['doc_conf_menu_id'] }}</span></h2>
                                    </div>
                                @else
                                    <p class="mb-30 bg-red lbl-card"><i class="ik ik-file-minus"></i> {{ __('menu.for_execution') }}</p>
                                    <div class="text-center">
                                        <h2 class="mb-0 d-inline-block text-red"><span>{{ $docs_count['doc_exec_menu_id'] }}</span></h2>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                <p class="mb-30 bg-warning lbl-card"><i class="fas fa-feather"></i> {{ __('menu.for_sign') }}</p>
                                <div class="text-center">
                                    <h2 class="mb-0 d-inline-block text-warning"><span>{{ $docs_count['doc_sign_menu_id'] }}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                <p class="mb-30 bg-blue lbl-card"><i class="fa fa-handshake"></i> {{ __('menu.for_coillaborate') }}</p>
                                <div class="text-center">
                                    <h2 class="mb-0 d-inline-block text-blue"><span>{{ $docs_count['doc_approve_menu_id'] }}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card ticket-card">
                            <div class="card-body">
                                <p class="mb-30 bg-green lbl-card"><i class="fas fa-file-alt"></i> {{ __('menu.my_docs') }}</p>
                                <div class="text-center">
                                    <h2 class="mb-0 d-inline-block text-green"><span>{{ $docs_count['my_docs_menu_id'] }}</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4 col-md-12">
                        <div class="card analytic-card card-green">
                            <div class="card-body">
                                <div class="row align-items-center mb-30">
                                    <div class="col-auto">
                                        <i class="fas fa-users text-green f-18 analytic-icon"></i>
                                    </div>
                                    <div class="col text-right">
                                        <h3 class="mb-5 text-white">{{ $employees['totalEmpCount'] }}</h3>
                                        <h6 class="mb-0 text-white">{{ __('menu.all_users') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-12">
                        <div class="card analytic-card card-blue">
                            <div class="card-body">
                                <div class="row align-items-center mb-30">
                                    <div class="col-auto">
                                        <i class="fas fa-check text-blue f-18 analytic-icon"></i>
                                    </div>
                                    <div class="col text-right">
                                        <h3 class="mb-5 text-white">{{ $employees['activeEmpCount'] }}</h3>
                                        <h6 class="mb-0 text-white">{{ __('menu.active_users') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-12">
                        <div class="card analytic-card card-yellow">
                            <div class="card-body">
                                <div class="row align-items-center mb-30">
                                    <div class="col-auto">
                                        <i class="fas fa-trash-alt text-red f-18 analytic-icon"></i>
                                    </div>
                                    <div class="col text-right">
                                        <h3 class="mb-5 text-white">{{ $employees['deletedEmpCount'] }}</h3>
                                        <h6 class="mb-0 text-white">{{ __('menu.deleted_users') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header" hidden>
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-plus bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.fast_create_doc') }}</h5>
                            <span>{{ __('field.app_temps') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" hidden>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-timeline-tab" data-toggle="pill" href="#current-month" role="tab" aria-controls="pills-timeline" aria-selected="true">{{ __('document.app') }}</a>
                        </li>
                        <li class="nav-item" hidden>
                            <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#last-month" role="tab" aria-controls="pills-profile" aria-selected="false">{{ __('menu.outgoing_messages') }}</a>
                        </li>
                        <li class="nav-item" hidden>
                            <a class="nav-link" id="pills-setting-tab" data-toggle="pill" href="#previous-month" role="tab" aria-controls="pills-setting" aria-selected="false">{{ __('menu.working_documents') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="current-month" role="tabpanel" aria-labelledby="pills-timeline-tab">
                            <div class="card-body">
                                <div class="row">
                                    @if(count($app_temps) > 0)
                                        @foreach($app_temps as $app_temp)
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card sos-st-card box">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col">
                                                                <div class="icon">
                                                                    <h4 class="mb-0 text-blue">
                                                                        <i class="fas fa-pencil-alt text-primary"></i> {{ $app_temp->lang($app_temp->lang_id) }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <a href="#" class="btn btn-outline-primary">Yaratmoq</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="pills-profile-tab">
                            <div class="card-body">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="previous-month" role="tabpanel" aria-labelledby="pills-setting-tab">
                            <div class="card-body">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row hidden">
            <div class="col-md-4">
                <div class="card" style="">
                    <div class="card-body text-center">
                        <div class="profile-pic mb-20">
                            @if(file_exists(public_path().'/profile_img/'.'img_'.Auth::user()->id.'.png'))
                                <img src="{{ url('/profile_img/'.'img_'.Auth::user()->id.'.png') }}" width="150"
                                     height="150" class="rounded-circle" alt="user" style="object-fit: cover;">
                            @else
                                <img src="{{ url('/images/profile_image1.png') }}" width="150" height="150"
                                     class="rounded-circle" alt="user" style="object-fit: cover;">
                            @endif

                            @php($user_name = Auth::user()->userPersonal??0)
                            <h4 class="mt-20 mb-0">
                                @if($user_name)
                                    {{ $user_name->fullName() }}
                                @endif
                            </h4>
                            <a href="#">
                                @php($user_department = Auth::user()->currentWork->department??0)
                                @if($user_department)
                                    {{ $user_department->lang($user_department->id) }}
                                @endif
                                @php($user_position = Auth::user()->currentWork->position??0)
                                @if($user_position)
                                    {{ $user_position->lang($user_position->id) }}
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="p-4 border-top mt-15">
                        <div class="row text-center">
                            <div class="col-6">
                                <a href="{{ url('/profile/my') }}"
                                   class="link d-flex align-items-center justify-content-center">
                                    <i class="ik ik-user f-20 mr-5"></i>{{ __('field.profile') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    
 <div class="profile-container">
   
 <div class="layer-container">
     <div class="profile-layer">
     <img style="width: 150px" src="{{ asset('images/layer.svg') }}" alt="Layer">
     <div class="layer-content"> 
        <span>25</span>
        <p>Kelgan xabarlar</p>
       
     </div>
  </div>
   <div class="profile-layer">
     <img style="width: 150px" src="{{ asset('images/layer.svg') }}" alt="Layer">
     <div class="layer-content"> 
        <span>25</span>
        <p>Kelgan xabarlar</p>
       
     </div>
  </div>
   <div class="profile-layer">
     <img style="width: 150px" src="{{ asset('images/layer.svg') }}" alt="Layer">
     <div class="layer-content"> 
        <span>25</span>
        <p>Kelgan xabarlar</p>
       
     </div>
  </div>
   <div class="profile-layer">
     <img style="width: 150px" src="{{ asset('images/layer.svg') }}" alt="Layer">
     <div class="layer-content"> 
        <span>25</span>
        <p>Kelgan xabarlar</p>
       
     </div>
  </div>
 </div>


     <div class="chart-container">
        <svg id="donutChart" viewBox="0 0 240 240"></svg>
        
    </div>
 </div>
        
     <div class="container">

    <!-- ================= TABS ================= -->
    <div class="tabs">
        <button class="tab active" data-status="all">
            Barchasi <span class="count count-all">5</span>
        </button>
        <button class="tab" data-status="Yuborildi">
            Yuborildi <span class="count count-yuborildi">2</span>
        </button>
        <button class="tab" data-status="Ko'rib chiqilmoqda">
            Ko'rib chiqilmoqda <span class="count count-korib">1</span>
        </button>
        <button class="tab" data-status="Tasdiqlandi">
            Tasdiqlandi <span class="count count-tasdiq">2</span>
        </button>
        <button class="tab" data-status="Imzolangan">
            Imzolangan <span class="count count-imzo">1</span>
        </button>
    </div>

    <!-- ================= TABLE ================= -->
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Hujjat raqami</th>
                <th>Sarlavha</th>
                <th>Yuboruvchi</th>
                <th>Sana</th>
                <th>Holat</th>
                <th>Arizalar</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>1</td>
                <td>DOC-2024-001</td>
                <td>Xodimlar ro'yxati</td>
                <td>HR Bo'limi</td>
                <td>2024-01-15</td>
                <td><span class="badge-status badge-yuborildi">Yuborildi</span></td>
                <td>
                    <button class="btn-korish">
                        <i class="fas fa-file-alt"></i> Ko'rish
                    </button>
                </td>
            </tr>

            <tr>
                <td>2</td>
                <td>DOC-2024-002</td>
                <td>Moliyaviy hisobot</td>
                <td>Moliya Bo'limi</td>
                <td>2024-01-14</td>
                <td><span class="badge-status badge-korib">Ko'rib chiqilmoqda</span></td>
                <td>
                    <button class="btn-korish">
                        <i class="fas fa-file-alt"></i> Ko'rish
                    </button>
                </td>
            </tr>

            <tr>
                <td>3</td>
                <td>DOC-2024-003</td>
                <td>Shartnoma loyihasi</td>
                <td>Yuridik Bo'lim</td>
                <td>2024-01-13</td>
                <td><span class="badge-status badge-tasdiq">Tasdiqlandi</span></td>
                <td>
                    <button class="btn-korish">
                        <i class="fas fa-file-alt"></i> Ko'rish
                    </button>
                </td>
            </tr>

            <tr>
                <td>4</td>
                <td>DOC-2024-004</td>
                <td>Shartnoma</td>
                <td>Direktor</td>
                <td>2024-01-12</td>
                <td><span class="badge-status badge-imzo">Imzolangan</span></td>
                <td>
                    <button class="btn-korish">
                        <i class="fas fa-file-alt"></i> Ko'rish
                    </button>
                </td>
            </tr>

            <tr>
                <td>5</td>
                <td>DOC-2024-005</td>
                <td>Buyruq</td>
                <td>Kadrlar</td>
                <td>2024-01-11</td>
                <td><span class="badge-status badge-tasdiq">Tasdiqlandi</span></td>
                <td>
                    <button class="btn-korish">
                        <i class="fas fa-file-alt"></i> Ko'rish
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

    </div>

    <script>
const tabs = document.querySelectorAll('.tab');
const rows = document.querySelectorAll('tbody tr');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const status = tab.dataset.status;

        rows.forEach(row => {
            const rowStatus = row.querySelector('.badge-status').innerText;

            if (status === 'all' || rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
 <script>
        var data = [
            { value: 35, color: '#7B40F2' },
            { value: 25, color: '#8753F4' },
            { value: 20, color: '#9467F6' },
            { value: 15, color: '#A07AF7' },
            { value: 5, color: '#AE8EF9' }
        ];

        function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
            var angleInRadians = (angleInDegrees * Math.PI) / 180.0;
            return {
                x: centerX + radius * Math.cos(angleInRadians),
                y: centerY + radius * Math.sin(angleInRadians)
            };
        }

        function createDonutSegment(startAngle, endAngle, outerRadius, innerRadius) {
            var start = polarToCartesian(120, 120, outerRadius, endAngle);
            var end = polarToCartesian(120, 120, outerRadius, startAngle);
            var innerStart = polarToCartesian(120, 120, innerRadius, endAngle);
            var innerEnd = polarToCartesian(120, 120, innerRadius, startAngle);
            
            var largeArcFlag = endAngle - startAngle <= 180 ? 0 : 1;
            
            var d = [
                'M ' + start.x + ' ' + start.y,
                'A ' + outerRadius + ' ' + outerRadius + ' 0 ' + largeArcFlag + ' 0 ' + end.x + ' ' + end.y,
                'L ' + innerEnd.x + ' ' + innerEnd.y,
                'A ' + innerRadius + ' ' + innerRadius + ' 0 ' + largeArcFlag + ' 1 ' + innerStart.x + ' ' + innerStart.y,
                'Z'
            ].join(' ');
            
            return d;
        }

        function renderChart() {
            var svg = document.getElementById('donutChart');
            var total = data.reduce(function(sum, item) {
                return sum + item.value;
            }, 0);
            
            svg.innerHTML = '';
            
            var currentAngle = -90;
            var outerRadius = 100;
            var innerRadius = 65;
            
            data.forEach(function(item) {
                var angle = (item.value / total) * 360;
                var endAngle = currentAngle + angle;
                
                var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', createDonutSegment(currentAngle, endAngle, outerRadius, innerRadius));
                path.setAttribute('fill', item.color);
                path.classList.add('segment');
                
                svg.appendChild(path);
                currentAngle = endAngle;
            });
        }

        renderChart();
    </script>
    <script>
function renderChart() {
    var svg = document.getElementById('donutChart');
    var total = data.reduce(function(sum, item) {
        return sum + item.value;
    }, 0);

    svg.innerHTML = '';

    var currentAngle = -90;
    var outerRadius = 100;
    var innerRadius = 65;

    data.forEach(function(item) {
        var angle = (item.value / total) * 360;
        var endAngle = currentAngle + angle;

        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', createDonutSegment(currentAngle, endAngle, outerRadius, innerRadius));
        path.setAttribute('fill', item.color);
        path.classList.add('segment');

        svg.appendChild(path);
        currentAngle = endAngle;
    });

    /* ===== CENTER TEXT ===== */
    var textGroup = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    textGroup.setAttribute('x', '120');
    textGroup.setAttribute('y', '115');
    textGroup.setAttribute('text-anchor', 'middle');
    textGroup.setAttribute('class', 'chart-total');

    textGroup.innerHTML = `
        <tspan x="120" dy="0" class="chart-label">Total</tspan>
        <tspan x="120" dy="20" class="chart-value">85%</tspan>
    `;

    svg.appendChild(textGroup);
}

renderChart();
</script>


  
@endsection




 <style>

    .layer-container{
        display: flex;
        gap: 2rem;
        margin-right: 2rem;
    }

    .profile-layer{
        position: relative;

  transition: all 0.3s ease;
        display: inline-block;  
      
    }

    .profile-layer:hover{
  transform: translateY(-10px);
    }


    .layer-content span{
        position: absolute;
        top: 20%;
        left: 20%;
        transform: translate(-50%, -50%);
        text-align: left;
        color:#fff;
        font-size: 20px;
        font-weight: 700;
    }   
    .layer-content p{
        position: absolute;
        top: 60%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: left;
        color:#fff;
        font-size: 14px;
        font-weight: 600;  
        white-space: nowrap ;


    }

    .chart-value{
        font-size:14px;
    }
    .chart-label{
        font-size: 18px !important;
    }
    .profile-container{
        background: #fefefe;
        border-radius: 8px;
        box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
        padding: 1rem;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
        .chart-container {
            padding: 1rem;
            max-width: 220px;
            width: 100%;
            margin-left: auto;
        }

        svg {
            width: 100%;
            max-width: 200px;
            display: block;
            margin: 0 auto;
        }

      

        .segment {
            transition: opacity 0.3s;
            cursor: pointer;
        }

        .segment:hover {
            opacity: 0.8;
        }
   
        .container {
            padding: 40px;
        }

        /* ================= TABS ================= */
        .tabs {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .tab {
            background: none;
            border: none;
            padding: 12px 0;
            font-size: 13px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
        }

        .tab.active {
            color: #9c27b0;
        }

        .tab.active::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #9c27b0;
            border-radius: 2px;
        }

        /* ================= TAB COUNTERS ================= */
        .tab .count {
            margin-left: 8px;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.6;
        }

        .count-all {
            background: #ede7f6;
            color: #5e35b1;
        }

        .count-yuborildi {
            background: #e3f2fd;
            color: #1976d2;
        }

        .count-korib {
            background: #fff8e1;
            color: #f9a825;
        }

        .count-tasdiq {
            background: #e8f5e9;
            color: #388e3c;
        }

        .count-imzo {
            background: #fbe9e7;
            color: #d84315;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        thead tr {
           background: linear-gradient(to bottom right, #794dfd, #7C3AED);

        }

        thead th {
            color: white;
            padding: 10px 12px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            border-right: 1px solid rgba(255,255,255,0.2);
        }

        thead th:last-child {
            border-right: none;
        }

        tbody td {
            padding: 16px 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
            border-right: 1px solid #eee;
        }

        tbody td:last-child {
            border-right: none;
        }

        tbody tr:hover {
            background-color: #f5f0ff;
        }

        /* ================= STATUS BADGES ================= */
        .badge-status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            min-width: 120px;
        }

        .badge-yuborildi {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .badge-korib {
            background-color: #fff8e1;
            color: #f9a825;
        }

        .badge-tasdiq {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .badge-imzo {
            background-color: #fbe9e7;
            color: #d84315;
        }

        /* ================= BUTTON ================= */
        .btn-korish {
            background: none;
            border: none;
            color: #9c27b0;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0 auto;
        }

        .btn-korish:hover {
            color: #ea4c89;
        }
        tbody td{
            font-size: 14px;
        }
    </style>