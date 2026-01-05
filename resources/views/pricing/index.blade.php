@extends('layouts.app')
<style>
    .period-toggle input[type="radio"] {
        display: none;
    }
</style>
@section('title', 'Tariflar - Obuna tanlash')

@section('content')
    <div class="container py-2">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Tarif rejalarimiz</h1>
            <p class="lead text-muted">O'zingizga mos tarif va to'lov davrini tanlang</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if($currentSubscription)
            @if($currentSubscription->status == 1)
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8">
                        <div class="card border-success shadow-sm">
                            <div class="card-header bg-success text-white text-center">
                                <h4 class="mb-0"><i class="ik ik-check-circle"></i> Sizning joriy obunangiz</h4>
                            </div>
                            <div class="card-body text-center py-4">
                                <h3 class="text-success fw-bold">{{ $currentSubscription->tariff->name }}</h3>
                                <p class="lead">
                                    <strong>Amal qilish muddati:</strong><br>
                                    {{ \Carbon\Carbon::parse($currentSubscription->starts_at)->format('d.m.Y') }} —
                                    {{ \Carbon\Carbon::parse($currentSubscription->ends_at)->format('d.m.Y') }}
                                </p>
                                <p class="text-muted">
                                    Oxirigacha {{ \Carbon\Carbon::parse($currentSubscription->ends_at)->diffForHumans() }}
                                </p>
                                @if($currentSubscription->ends_at < now()->addDays(30))
                                    <span class="badge badge-pill badge-warning">Tezda tugaydi!</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($currentSubscription->status == 2)
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8">
                        <div class="card border-warning shadow-sm">
                            <div class="card-header bg-warning text-dark text-center">
                                <h4 class="mb-0"><i class="ik ik-clock"></i> Obuna so'rovi kutilmoqda</h4>
                            </div>
                            <div class="card-body text-center py-4">
                                <h4 class="text-warning">{{ $currentSubscription->tariff->name }}</h4>
                                <p class="lead">
                                    Siz <strong>{{ $currentSubscription->tariff->name }}</strong> tarifiga obuna so'rovi yubordingiz.
                                </p>
                                <p class="text-muted">
                                    So'rov admin tomonidan ko'rib chiqilmoqda.<br>
                                    Tasdiqlangandan so'ng bu yerda faol obuna ko'rsatiladi.
                                </p>
                                <span class="badge badge-pill badge-warning">Kutilmoqda</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <div class="alert alert-info text-center py-4">
                        <i class="ik ik-info display-4 d-block mb-3"></i>
                        <h5>Hozircha faol obuna yo'q</h5>
                        <p class="mb-0">Quyidan o'zingizga mos tarifni tanlang va obuna bo'ling</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row justify-content-center">
            @foreach($tariffs as $tariff)
                <div class="col-md-4 mb-5">
                    <div class="card h-100 shadow-lg border-0 rounded-lg pricing-card position-relative
                    {{ $currentSubscription?->tariff_id == $tariff->id ? 'border-primary border-thick' : '' }}">

                        @if($currentSubscription?->tariff_id == $tariff->id)
                            <div class="ribbon ribbon-top-right"><span>Joriy</span></div>
                        @endif

                        <div class="card-header text-center py-4 bg-primary text-white">
                            <h3 class="my-0 fw-bold text-white text-center w-100">{{ $tariff->name }}</h3>
                        </div>

                        <div class="card-body text-center d-flex flex-column">
                            <div class="btn-group mb-4 period-toggle">
                                <label class="btn btn-outline-secondary active">
                                    <input type="radio" name="period_{{ $tariff->id }}" value="month" checked>
                                    Oyiga
                                </label>
                                <label class="btn btn-outline-secondary">
                                    <input type="radio" name="period_{{ $tariff->id }}" value="year">
                                    Yillik
                                </label>
                            </div>

                            <div class="price mb-4">
                                <h1 class="display-4 fw-bold monthly-price">
                                    {{ number_format($tariff->price_monthly) }}
                                    <small class="text-muted fs-4">so'm/oy</small>
                                </h1>
                                <h1 class="display-4 fw-bold yearly-price d-none">
                                    {{ number_format($tariff->price_yearly) }}
                                    <small class="text-muted fs-4">so'm/yil</small>
                                </h1>

                                @if($tariff->price_yearly > 0 && $tariff->price_yearly < ($tariff->price_monthly * 12))
                                    <p class="yearly-saving text-success fw-bold d-none mt-3">
                                        Tejoving: {{ number_format(($tariff->price_monthly * 12) - $tariff->price_yearly) }} so'm!
                                        <span class="badge badge-pill badge-success ml-2">
                                        –{{ round((1 - $tariff->price_yearly / ($tariff->price_monthly * 12)) * 100) }}%
                                    </span>
                                    </p>
                                @endif
                            </div>

                            <ul class="list-unstyled mb-4 flex-grow-1">
                                <li class="mb-2"><strong>{{ $tariff->max_users }}</strong> foydalanuvchi</li>
                                <li class="mb-2"><strong>{{ number_format($tariff->max_documents) }}</strong> hujjat/oy</li>
                                <li class="mb-2">To'liq funksionallik</li>
                                <li class="mb-2">24/7 texnik yordam</li>
                            </ul>

                            <div class="mt-auto">
                                @if($currentSubscription?->tariff_id == $tariff->id)
                                    <button class="btn btn-outline-primary btn-lg btn-block" disabled>
                                        Joriy tarif
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary btn-lg btn-block subscribe-btn"
                                            data-tariff-id="{{ $tariff->id }}"
                                            data-tariff-name="{{ $tariff->name }}">
                                        Tanlash
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">
                Savollaringiz bo'lsa: <a href="mailto:support@example.com">support@example.com</a>
            </p>
        </div>
    </div>

    <!-- Модальное окно подтверждения -->
    <div class="modal fade" id="subscribeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Obunani tasdiqlash</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <i class="ik ik-package display-1 text-primary mb-3"></i>
                    <h4><strong id="modalTariffName"></strong> tarifiga</h4>
                    <h5 id="modalPeriodText" class="text-success fw-bold"></h5>
                    <p>obuna bo'lmoqchimisiz?</p>

                    <p class="mt-4">
                        <strong>Boshlanish:</strong> {{ now()->format('d.m.Y') }}<br>
                        <strong>Tugash:</strong> <span id="modalEndDate"></span>
                    </p>

                    <p class="text-success fw-bold mt-4">
                        So'rov yuborilgandan so'ng admin tasdiqlashini kuting.
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Bekor qilish</button>
                    <form id="subscribeForm" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="period" id="hiddenPeriod" value="month">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            Ha, so'rov yuborish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pricing-card { transition: all 0.3s ease; }
        .pricing-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .border-thick { border-width: 5px !important; }
        .ribbon { width: 120px; height: 120px; overflow: hidden; position: absolute; top: -10px; right: -10px; z-index: 1; }
        .ribbon span {
            position: absolute; display: block; width: 225px; padding: 8px 0; background-color: #3498db;
            box-shadow: 0 5px 10px rgba(0,0,0,.1); color: #fff; font: 700 14px/1 'Lato', sans-serif;
            text-shadow: 0 1px 1px rgba(0,0,0,.2); text-transform: uppercase; text-align: center;
            right: -25px; top: 30px; transform: rotate(45deg);
        }
    </style>

    <script>
        $(document).ready(function () {
            $('input[name^="period_"]').on('change', function () {
                let $label = $(this).closest('label');
                let $group = $label.closest('.period-toggle');
                let $card = $label.closest('.pricing-card');

                $group.find('label').removeClass('active');
                $label.addClass('active');

                if (this.value === 'year') {
                    $card.find('.monthly-price').addClass('d-none');
                    $card.find('.yearly-price').removeClass('d-none');
                    $card.find('.yearly-saving').removeClass('d-none');
                } else {
                    $card.find('.monthly-price').removeClass('d-none');
                    $card.find('.yearly-price').addClass('d-none');
                    $card.find('.yearly-saving').addClass('d-none');
                }
            });

            $('.subscribe-btn').on('click', function () {
                let tariffId = $(this).data('tariff-id');
                let tariffName = $(this).data('tariff-name');

                let period = $('input[name="period_' + tariffId + '"]:checked').val() || 'month';

                $('#modalTariffName').text(tariffName);
                $('#hiddenPeriod').val(period);

                if (period === 'year') {
                    $('#modalPeriodText').text('Yillik obuna');
                    $('#modalEndDate').text('{{ now()->addYear()->format('d.m.Y') }}');
                } else {
                    $('#modalPeriodText').text('Oylik obuna');
                    $('#modalEndDate').text('{{ now()->addMonth()->format('d.m.Y') }}');
                }

                $('#subscribeForm').attr('action',
                    '{{ route("pricing.subscribe", ":id") }}'.replace(':id', tariffId)
                );

                $('#subscribeModal').modal('show');
            });
        });
    </script>
@endsection
