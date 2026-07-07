@extends('layouts.app')

@section('content')
    <div class="container py-3" style="max-width:560px">

        <div class="board-header px-4 py-3 mb-4">
            <h5 class="text-white mb-0 fw-bold">
                <i class="fa fa-plus me-2"></i> Новая доска
            </h5>
        </div>

        <form action="{{ route('boards.store') }}" method="POST"
              class="p-4 rounded-4 border"
              style="background:var(--bs-body-bg)">
            @csrf

            <div class="mb-3">
                <label class="form-label small text-muted fw-medium">Название</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="form-control @error('title') is-invalid @enderror" required autofocus>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted fw-medium">Цвет</label>
                <input type="color" name="color" value="{{ old('color', '#7C3AED') }}"
                       class="form-control form-control-color">
            </div>

            <div class="mb-4">
                <label class="form-label small text-muted fw-medium">Описание</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('boards.index') }}" class="btn btn-link text-muted text-decoration-none">Отмена</a>
                <button type="submit" class="btn text-white fw-medium" style="background:#7C3AED">
                    <i class="fa fa-check me-1"></i> Создать
                </button>
            </div>
        </form>

    </div>
@endsection
