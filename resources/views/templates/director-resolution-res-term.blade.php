<div class="res-term-display-modern">
    <strong class="term-label">{{ __('field.perf_term') }}:</strong>
    <span class="term-date">{{ \Carbon\Carbon::parse($directorResolution->res_term)->format('d.m.Y') }}</span>
    @if(!in_array($journal->status, [10,11]))
        @php $date = \Carbon\Carbon::parse($directorResolution->res_term)->startOfDay();
        $now = \Carbon\Carbon::parse($directorDocument->created_at)->startOfDay();
        if ($directorDocument->status > 1) {
            $now = \Carbon\Carbon::now()->startOfDay();
        }
        $diff = round($now->diffInDays($date)); @endphp
        @if($directorResolution->res_term < date('Y-m-d', strtotime(now())))
            <span class="term-badge term-overdue">
                <i data-lucide="alert-circle"></i>
                {{ __('field.overdue') }} {{ $diff }} {{ __('field.day') }}
            </span>
        @else
            <span class="term-badge term-active {{ ($diff === 0 ? 'term-urgent':'') }}">
                <i data-lucide="clock"></i>
                {{ $diff }} {{ __('field.day') }} {{ __('field.remainder') }}
            </span>
        @endif
    @endif
</div>

<link rel="stylesheet" href="{{ asset('css/templates/templates/director-resolution-res-temps.css') }}">
