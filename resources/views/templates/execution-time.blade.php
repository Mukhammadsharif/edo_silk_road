@php
    $doc_term = $model->doc->doc_term??0
@endphp
@if($doc_term)
    {{ \Carbon\Carbon::parse($doc_term)->format('d.m.Y') }}<br>
    @if(!in_array($model->status, [9,10,11]))
        @php $date = \Carbon\Carbon::parse($doc_term)->startOfDay();
        $now = \Carbon\Carbon::now()->startOfDay();
        $diff = round($now->diffInDays($date)); @endphp
        @if($doc_term <= date('Y-m-d', strtotime(now())))
            <span class="badge badge-pill badge-yellow">
                <i class="ik ik-alert-circle"></i> {{ __('field.overdue') }}<br>
                {{ $diff }} {{ __('field.day') }}
            </span>
        @else
            <span class="badge badge-pill badge-success">
                <i class="ik ik-clock"></i>
                {{ $diff }} {{ __('field.day') }} {{ __('field.remainder') }}
            </span>
        @endif
    @endif
@endif
