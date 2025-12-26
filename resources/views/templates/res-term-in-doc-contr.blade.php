@php
    $res_term = $model->directorResolution->res_term??0
@endphp
@if($res_term)
    {{ \Carbon\Carbon::parse($res_term)->format('d.m.Y') }}
    @if(!in_array($model->status, [10,11]))
        @php
            $resTermDate = Carbon\Carbon::parse($res_term)->startOfDay();
            $currentDate = now()->startOfDay();
        @endphp
        @if($res_term < date('Y-m-d', strtotime(now())))
            @php
                $diffDays = $resTermDate->diffInDays($currentDate);
            @endphp
            <br>
            <span class="badge badge-pill badge-yellow">
                <i class="ik ik-alert-circle"></i> {{ __('field.overdue') }}<br>
                -  {{ $diffDays }} {{ __('field.day') }}
            </span>
        @else
            @php
                $diffDays = $currentDate->diffInDays($resTermDate);
            @endphp
            <br>
            <span class="badge badge-pill badge-success">
                <i class="ik ik-clock"></i>
                {{ $diffDays }} {{ __('field.day') }} {{ __('field.remainder') }}
            </span>
        @endif
    @endif
@endif
