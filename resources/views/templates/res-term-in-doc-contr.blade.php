@php
    $res_term = $model->directorResolution->res_term??0
@endphp
@if($res_term)
    <div class="term-display-modern">
        <span class="term-date">{{ \Carbon\Carbon::parse($res_term)->format('d.m.Y') }}</span>
        @if(!in_array($model->status, [10,11]))
            @php
                $resTermDate = Carbon\Carbon::parse($res_term)->startOfDay();
                $currentDate = now()->startOfDay();
            @endphp
            @if($res_term < date('Y-m-d', strtotime(now())))
                @php
                    $diffDays = $resTermDate->diffInDays($currentDate);
                @endphp
                <span class="term-badge term-overdue">
                    <i data-lucide="alert-circle"></i>
                    {{ __('field.overdue') }} -{{ $diffDays }} {{ __('field.day') }}
                </span>
            @else
                @php
                    $diffDays = $currentDate->diffInDays($resTermDate);
                @endphp
                <span class="term-badge term-active">
                    <i data-lucide="clock"></i>
                    {{ $diffDays }} {{ __('field.day') }} {{ __('field.remainder') }}
                </span>
            @endif
        @endif
    </div>
@endif

<link rel="stylesheet" href="{{ asset('css/templates/templates/res-term-in-doc-contr.css') }}">
