<p>
    <strong>{{ __('field.perf_term') }}:</strong>
    {{ \Carbon\Carbon::parse($directorResolution->res_term)->format('d.m.Y') }}
    @if(!in_array($journal->status, [10,11]))
        @php $date = \Carbon\Carbon::parse($directorResolution->res_term)->startOfDay();
        $now = \Carbon\Carbon::parse($directorDocument->created_at)->startOfDay();
        if ($directorDocument->status > 1) {
            $now = \Carbon\Carbon::now()->startOfDay();
        }
        $diff = round($now->diffInDays($date)); @endphp
        @if($directorResolution->res_term < date('Y-m-d', strtotime(now())))
            <span class="badge badge-pill badge-yellow">
                <i class="ik ik-alert-circle"></i> {{ __('field.overdue') }}<br>
                {{ $diff }} {{ __('field.day') }}
            </span>
        @else
            <span class="badge badge-pill badge-light-blue {{ ($diff === 0 ? "text-danger":"") }}">
                <i class="ik ik-clock"></i>
                {{ $diff }} {{ __('field.day') }} {{ __('field.remainder') }}
            </span>
        @endif
    @endif
</p>
