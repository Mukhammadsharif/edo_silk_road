@extends('layouts.app')

@section('title', __('storage.trash') ?? 'Корзина — Хранилище')
<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-left.css') }}">

@section('content')
    <style>
        .trash-page { padding: 0 0 40px; }
        .trash-header {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(245, 158, 11, 0.05));
            border: 1px solid rgba(239, 68, 68, 0.15);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .trash-header-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #ef4444, #f59e0b);
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 8px 20px -4px rgba(239, 68, 68, 0.45);
        }
        .trash-header-title { font-size: 18px; font-weight: 700; line-height: 1.3; }
        .trash-header-sub { font-size: 13px; color: #64748b; margin-top: 2px; }
        .trash-back {
            margin-left: auto;
            padding: 9px 14px;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.10);
            border-radius: 9px;
            text-decoration: none;
            color: #475569;
            font-size: 13px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 6px;
        }

        .trash-section-title {
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin: 18px 0 10px 4px;
        }

        .trash-list { display: flex; flex-direction: column; gap: 8px; }
        .trash-row {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex; align-items: center; gap: 12px;
        }
        .trash-row-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(15, 23, 42, 0.05);
            color: #64748b;
            flex-shrink: 0;
        }
        .trash-row-icon i { width: 18px; height: 18px; }
        .trash-row-info { flex: 1; min-width: 0; }
        .trash-row-name {
            font-size: 13px; font-weight: 600;
            word-break: break-word;
        }
        .trash-row-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .trash-row-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .trash-action-btn {
            padding: 7px 12px;
            font-size: 12px; font-weight: 600;
            border-radius: 7px; border: none; cursor: pointer;
            font-family: inherit;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .trash-action-btn i { width: 13px; height: 13px; }
        .restore-btn {
            background: rgba(16, 185, 129, 0.10);
            color: #059669;
        }
        .restore-btn:hover { background: rgba(16, 185, 129, 0.18); }
        .purge-btn {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
        }
        .purge-btn:hover { background: rgba(239, 68, 68, 0.18); }

        .trash-empty {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
            font-size: 14px;
            background: #fff;
            border: 1px dashed rgba(15, 23, 42, 0.10);
            border-radius: 12px;
        }
    </style>

    <div class="work-view-page-modern">
        <div class="trash-header">
            <div class="trash-header-icon"><i data-lucide="trash-2"></i></div>
            <div>
                <div class="trash-header-title">{{ __('storage.trash') ?? 'Корзина' }}</div>
                <div class="trash-header-sub">
                    {{ $organization->name_uz ?? '—' }}
                </div>
            </div>
            <a href="{{ route('storage.index', request()->only('org')) }}" class="trash-back">
                <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
                {{ __('storage.back_to_storage') ?? 'К хранилищу' }}
            </a>
        </div>

        @if($availableOrgs->count() > 0)
            <div style="display:flex; align-items:center; gap:8px; padding:8px 12px;
                    background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.25);
                    border-radius:10px; font-size:13px; margin-bottom:14px;">
                <i data-lucide="shield-check" style="width:16px;height:16px;color:#d97706;"></i>
                <span>{{ __('storage.viewing_as_admin') ?? 'Режим Super Admin — выберите организацию:' }}</span>
                <select onchange="if(this.value)window.location.href='{{ route('storage.trash') }}?org='+this.value"
                        style="flex:1;background:#fff;border:1px solid rgba(15,23,42,0.1);border-radius:8px;padding:6px 10px;font-size:13px;font-family:inherit;">
                    @foreach($availableOrgs as $org)
                        <option value="{{ $org->id }}" {{ $org->id === $orgUnitId ? 'selected' : '' }}>
                            {{ $org->name_uz }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($folders->count() > 0)
            <div class="trash-section-title">{{ __('storage.deleted_folders') ?? 'Удалённые папки' }} ({{ $folders->count() }})</div>
            <div class="trash-list">
                @foreach($folders as $f)
                    <div class="trash-row">
                        <div class="trash-row-icon"><i data-lucide="folder"></i></div>
                        <div class="trash-row-info">
                            <div class="trash-row-name">{{ $f->name }}</div>
                            <div class="trash-row-meta">
                                {{ rtrim($f->path, '/') }}/{{ $f->name }} ·
                                {{ __('storage.deleted_at') ?? 'Удалено' }}: {{ $f->deleted_at?->format('d.m.Y H:i') }}
                            </div>
                        </div>
                        <div class="trash-row-actions">
                            <form method="POST" action="{{ route('storage.trash.folder.restore', $f->id) }}" style="margin:0;">
                                @csrf
                                <button class="trash-action-btn restore-btn" type="submit">
                                    <i data-lucide="undo-2"></i>
                                    {{ __('storage.restore') ?? 'Восстановить' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('storage.trash.folder.purge', $f->id) }}" style="margin:0;"
                                  onsubmit="return confirm('{{ __('storage.confirm_purge_folder') ?? 'Удалить ОКОНЧАТЕЛЬНО? Это действие необратимо!' }}')">
                                @csrf @method('DELETE')
                                <button class="trash-action-btn purge-btn" type="submit">
                                    <i data-lucide="trash"></i>
                                    {{ __('storage.purge') ?? 'Удалить навсегда' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($files->count() > 0)
            <div class="trash-section-title">{{ __('storage.deleted_files') ?? 'Удалённые файлы' }} ({{ $files->count() }})</div>
            <div class="trash-list">
                @foreach($files as $f)
                    <div class="trash-row">
                        <div class="trash-row-icon"><i data-lucide="{{ $f->iconName() }}"></i></div>
                        <div class="trash-row-info">
                            <div class="trash-row-name">{{ $f->name }}</div>
                            <div class="trash-row-meta">
                                {{ strtoupper($f->extension) }} · {{ $f->humanSize() }} ·
                                {{ __('storage.deleted_at') ?? 'Удалено' }}: {{ $f->deleted_at?->format('d.m.Y H:i') }}
                            </div>
                        </div>
                        <div class="trash-row-actions">
                            <form method="POST" action="{{ route('storage.trash.file.restore', $f->id) }}" style="margin:0;">
                                @csrf
                                <button class="trash-action-btn restore-btn" type="submit">
                                    <i data-lucide="undo-2"></i>
                                    {{ __('storage.restore') ?? 'Восстановить' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('storage.trash.file.purge', $f->id) }}" style="margin:0;"
                                  onsubmit="return confirm('{{ __('storage.confirm_purge_file') ?? 'Удалить ОКОНЧАТЕЛЬНО? Это действие необратимо!' }}')">
                                @csrf @method('DELETE')
                                <button class="trash-action-btn purge-btn" type="submit">
                                    <i data-lucide="trash"></i>
                                    {{ __('storage.purge') ?? 'Удалить навсегда' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($folders->count() === 0 && $files->count() === 0)
            <div class="trash-empty">
                <i data-lucide="check-circle" style="width:32px;height:32px;margin-bottom:8px;opacity:0.5;"></i>
                <div>{{ __('storage.trash_empty') ?? 'Корзина пуста' }}</div>
            </div>
        @endif
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
@endsection
