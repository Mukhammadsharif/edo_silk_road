@php
    $doc_term = $model->doc->doc_term??0
@endphp
@if($doc_term)
    <div class="term-display-modern">
        <span class="term-date">{{ \Carbon\Carbon::parse($doc_term)->format('d.m.Y') }}</span>
        @if(!in_array($model->status, [9,10,11]))
            @php $date = \Carbon\Carbon::parse($doc_term)->startOfDay();
            $now = \Carbon\Carbon::now()->startOfDay();
            $diff = round($now->diffInDays($date)); @endphp
            @if($doc_term <= date('Y-m-d', strtotime(now())))
                <span class="term-badge term-overdue">
                    <i data-lucide="alert-circle"></i>
                    {{ __('field.overdue') }} {{ $diff }} {{ __('field.day') }}
                </span>
            @else
                <span class="term-badge term-active">
                    <i data-lucide="clock"></i>
                    {{ $diff }} {{ __('field.day') }} {{ __('field.remainder') }}
                </span>
            @endif
        @endif
    </div>

@endif

<link rel="stylesheet" href="{{ asset('css/templates/templates/execution-time.css') }}">
