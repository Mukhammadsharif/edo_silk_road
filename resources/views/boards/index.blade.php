@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">

        <div class="board-header d-flex align-items-center justify-content-between px-4 py-4 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="board-icon-box">
                    <i class="fa fa-table-columns text-white fs-4"></i>
                </div>
                <div>
                    <h5 class="text-white mb-0 fw-bold">Доски</h5>
                    <small class="text-white-50">Управление задачами и документами</small>
                </div>
            </div>
            <a href="{{ route('boards.create') }}" class="btn btn-light btn-sm fw-medium">
                <i class="fa fa-plus me-1"></i> Новая доска
            </a>
        </div>

        <div class="row g-3">
            @forelse($boards as $board)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <a href="{{ route('boards.show', $board) }}" class="board-tile"
                       style="background:linear-gradient(135deg,{{ $board->color }},{{ $board->color }}cc)">
                        <i class="fa fa-table-columns fs-4 d-block mb-2 opacity-75"></i>
                        <h6 class="fw-bold mb-1">{{ $board->title }}</h6>
                        <small class="opacity-75">{{ $board->lists->count() }} списков</small>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="board-empty">
                        <i class="fa fa-table-columns"></i>
                        <p class="mb-2">Пока нет ни одной доски</p>
                        <a href="{{ route('boards.create') }}" style="color:#7C3AED" class="fw-medium">Создать первую</a>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
@endsection
