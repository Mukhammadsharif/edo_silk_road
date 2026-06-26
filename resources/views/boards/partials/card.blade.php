@php
    $labels = [
      'urgent' => ['bg' => '#FCEBEB', 'fg' => '#A32D2D', 't' => 'Срочно'],
      'review' => ['bg' => '#FAEEDA', 'fg' => '#854F0B', 't' => 'На проверке'],
      'done'   => ['bg' => '#E1F5EE', 'fg' => '#0F6E56', 't' => 'Готово'],
      'sign'   => ['bg' => '#EEEDFE', 'fg' => '#3C3489', 't' => 'Подпись E-IMZO'],
    ];
    $label = $card->label_color ? ($labels[$card->label_color] ?? null) : null;
    $initials = \Illuminate\Support\Str::of($card->assignee?->name ?? '')
        ->explode(' ')->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))
        ->take(2)->implode('');
@endphp

<div class="kanban-card" data-card-id="{{ $card->id }}" @click="openCard({{ $card->id }})">
    @if($label)
        <span class="card-label" style="background:{{ $label['bg'] }};color:{{ $label['fg'] }}">
      {{ $label['t'] }}
    </span>
    @endif

    <div class="card-title">{{ $card->title }}</div>

    <div class="card-foot">
        @if($card->due_date)
            <span class="card-due {{ $card->isOverdue() ? 'overdue' : '' }}">
        <i class="fa fa-clock"></i> {{ $card->due_date->translatedFormat('d MMM') }}
      </span>
        @else
            <span></span>
        @endif

        @if($card->assignee)
            <div class="card-avatar" title="{{ $card->assignee->name }}">{{ $initials }}</div>
        @endif
    </div>
</div>
