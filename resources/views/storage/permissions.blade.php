@extends('layouts.app')

@section('title', __('storage.permissions') ?? 'Права доступа — Хранилище')
<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-left.css') }}">

@section('content')
    <style>
        .perm-page { padding: 0 0 40px; }
        .perm-header {
            background: linear-gradient(135deg, rgba(121, 77, 253, 0.04), rgba(157, 111, 255, 0.06));
            border: 1px solid rgba(121, 77, 253, 0.12);
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .perm-header-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 8px 20px -4px rgba(121, 77, 253, 0.45);
        }
        .perm-header-title { font-size: 17px; font-weight: 700; line-height: 1.3; }
        .perm-header-sub { font-size: 13px; color: #64748b; margin-top: 2px; }
        .perm-back {
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

        .info-banner {
            background: rgba(59, 130, 246, 0.06);
            border: 1px solid rgba(59, 130, 246, 0.18);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #1e40af;
            margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .info-banner i { width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px; }

        .perm-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 16px;
        }
        @media (max-width: 980px) { .perm-grid { grid-template-columns: 1fr; } }

        .perm-card {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 14px;
            padding: 18px;
        }
        .perm-card-title {
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 12px;
            display: flex; align-items: center; gap: 6px;
        }
        .perm-card-title i { width: 14px; height: 14px; }

        /* Add form */
        .perm-form label {
            display: block; font-size: 12px; font-weight: 600;
            color: #475569; margin: 10px 0 6px;
        }
        .perm-form select, .perm-form input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 9px;
            font-size: 13px;
            font-family: inherit;
            background: #fff;
        }
        .perm-form-actions {
            display: flex; gap: 8px; justify-content: flex-end;
            margin-top: 14px;
        }
        .btn-perm {
            padding: 9px 14px;
            font-size: 13px; font-weight: 600;
            border-radius: 9px;
            cursor: pointer;
            border: none;
            font-family: inherit;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-perm svg, .btn-perm i { width: 14px; height: 14px; }
        .btn-perm-primary {
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            color: #fff;
            box-shadow: 0 4px 12px -3px rgba(121, 77, 253, 0.5);
        }

        /* Existing permissions list */
        .perm-list {
            display: flex; flex-direction: column;
            gap: 6px;
        }
        .perm-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 9px;
            border: 1px solid transparent;
        }
        .perm-row-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            color: #fff;
            font-size: 13px; font-weight: 700;
            flex-shrink: 0;
        }
        .perm-row-info { flex: 1; min-width: 0; }
        .perm-row-name {
            font-size: 13px; font-weight: 600; color: #1e293b;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .perm-row-meta { font-size: 11px; color: #94a3b8; margin-top: 1px; }

        .mode-badge {
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px; font-weight: 700;
            letter-spacing: -0.005em;
            flex-shrink: 0;
        }
        .mode-allow { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .mode-deny  { background: rgba(239, 68, 68, 0.10);  color: #dc2626; }

        .perm-row-revoke {
            padding: 6px;
            border: none; background: transparent;
            border-radius: 6px;
            cursor: pointer;
            color: #94a3b8;
            font-family: inherit;
            display: flex; align-items: center;
            transition: all 0.15s;
        }
        .perm-row-revoke:hover { background: rgba(239, 68, 68, 0.10); color: #dc2626; }
        .perm-row-revoke i { width: 14px; height: 14px; }

        .perm-empty {
            text-align: center;
            padding: 36px 16px;
            color: #94a3b8;
            font-size: 13px;
        }

        .folder-path {
            font-family: 'SF Mono', Menlo, monospace;
            font-size: 12px;
            background: rgba(121, 77, 253, 0.06);
            color: #794DFD;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>

    <div class="work-view-page-modern">
        <div class="perm-header">
            <div class="perm-header-icon">
                <i data-lucide="shield"></i>
            </div>
            <div>
                <div class="perm-header-title">
                    {{ __('storage.permissions') ?? 'Права доступа' }}: {{ $folder->name }}
                </div>
                <div class="perm-header-sub">
                    {{ $organization->name_uz ?? '—' }} ·
                    <span class="folder-path">{{ rtrim($folder->path, '/') }}/{{ $folder->name }}</span>
                </div>
            </div>
            <a href="{{ route('storage.folder.show', $folder->id) }}" class="perm-back">
                <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
                {{ __('storage.back_to_folder') ?? 'Назад в папку' }}
            </a>
        </div>

        <div class="info-banner">
            <i data-lucide="info"></i>
            <span>
            {{ __('storage.permissions_help') ?? 'Доступ к этой папке наследуется на все вложенные подпапки и файлы. Директор, IT_Admin и Super_Admin имеют доступ всегда и в списке не отображаются.' }}
        </span>
        </div>

        <div class="perm-grid">
            {{-- ────────────────── Add form ────────────────── --}}
            <div class="perm-card">
                <div class="perm-card-title">
                    <i data-lucide="user-plus"></i>
                    {{ __('storage.grant_access') ?? 'Предоставить доступ' }}
                </div>
                <form method="POST" action="{{ route('storage.folder.permissions.grant', $folder->id) }}" class="perm-form">
                    @csrf
                    <label>{{ __('storage.select_user') ?? 'Выберите сотрудника' }}</label>
                    <select name="user_id" required class="select2">
                        <option value="">— {{ __('field.select') ?? 'выберите' }} —</option>
                        @foreach($orgUsers as $u)
                            <option value="{{ $u->id }}">
                                {{ $u->userPersonal->fullName() ?? $u->username }}
                                ({{ $u->username }})
                            </option>
                        @endforeach
                    </select>

                    <label>{{ __('storage.mode') ?? 'Режим' }}</label>
                    <select name="mode" required class="select2">
                        <option value="allow">✓ {{ __('storage.mode_allow') ?? 'Разрешить' }}</option>
                        <option value="deny">✕ {{ __('storage.mode_deny') ?? 'Запретить (override)' }}</option>
                    </select>

                    <div class="perm-form-actions">
                        <button type="submit" class="btn-perm btn-perm-primary">
                            <i data-lucide="plus"></i>
                            {{ __('storage.add') ?? 'Добавить' }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- ────────────────── Existing permissions list ────────────────── --}}
            <div class="perm-card">
                <div class="perm-card-title">
                    <i data-lucide="users"></i>
                    {{ __('storage.granted_users') ?? 'Назначенные права' }} ({{ $permissions->count() }})
                </div>

                @if($permissions->count() > 0)
                    <div class="perm-list">
                        @foreach($permissions as $p)
                            @php
                                $u    = $p->user;
                                $name = $u?->userPersonal?->full_name ?? $u?->username ?? '—';
                                $initial = mb_substr($name, 0, 1);
                            @endphp
                            <div class="perm-row">
                                <div class="perm-row-avatar">{{ mb_strtoupper($initial) }}</div>
                                <div class="perm-row-info">
                                    <div class="perm-row-name">{{ $name }}</div>
                                    <div class="perm-row-meta">
                                        @ {{ $u?->username ?? '—' }} ·
                                        {{ __('storage.granted_at') ?? 'Добавлен' }}:
                                        {{ $p->created_at?->format('d.m.Y') }}
                                    </div>
                                </div>
                                <span class="mode-badge {{ $p->mode === 'allow' ? 'mode-allow' : 'mode-deny' }}">
                                {{ $p->mode === 'allow'
                                   ? (__('storage.mode_allow') ?? 'Разрешено')
                                   : (__('storage.mode_deny')  ?? 'Запрещено') }}
                            </span>
                                <form method="POST" action="{{ route('storage.folder.permissions.revoke', [$folder->id, $p->id]) }}" style="margin:0;"
                                      onsubmit="return confirm('{{ __('storage.confirm_revoke') ?? 'Отозвать право?' }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="perm-row-revoke" title="{{ __('storage.revoke') ?? 'Отозвать' }}">
                                        <i data-lucide="x"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="perm-empty">
                        <i data-lucide="users-round" style="width:32px;height:32px;opacity:0.4;margin-bottom:8px;"></i>
                        <div>{{ __('storage.no_permissions') ?? 'Никаких прав ещё не назначено. Управленцы (Director, IT_Admin) имеют доступ автоматически.' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
@endsection
