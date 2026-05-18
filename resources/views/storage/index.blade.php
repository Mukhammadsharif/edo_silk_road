@extends('layouts.app')

@section('title', __('menu.org_storage') ?? 'Хранилище организации')
<link rel="stylesheet" href="{{ asset('css/templates/universal-document-view.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/view-left.css') }}">

@section('content')
    <style>
        /* ════════════════════════════════════════════════════════════════════
           Storage tree-view layout
           ════════════════════════════════════════════════════════════════════ */
        /*.storage-page { padding: 0 0 40px; }*/

        /* Header bar */
        .storage-header {
            background: linear-gradient(135deg, rgba(121, 77, 253, 0.04), rgba(157, 111, 255, 0.06));
            border: 1px solid rgba(121, 77, 253, 0.12);
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .storage-header-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .storage-header-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 6px 16px -4px rgba(121, 77, 253, 0.45);
        }
        .storage-header-title { font-size: 15px; font-weight: 700; line-height: 1.3; }
        .storage-header-sub { font-size: 12px; color: #64748b; margin-top: 2px; }

        .storage-actions { display: flex; gap: 6px; flex-wrap: wrap; }

        .btn-storage {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px; font-weight: 600;
            cursor: pointer; border: none;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
            font-family: inherit;
        }
        .btn-storage:active { transform: scale(0.97); }
        .btn-storage svg, .btn-storage i { width: 14px; height: 14px; }

        .btn-storage-primary {
            background: linear-gradient(135deg, #794DFD, #9d6fff);
            color: #fff;
            box-shadow: 0 4px 12px -3px rgba(121, 77, 253, 0.5);
        }
        .btn-storage-secondary {
            background: #fff;
            color: #475569;
            border: 1px solid rgba(15, 23, 42, 0.10);
        }
        .btn-storage-secondary:hover { background: #f8fafc; }

        .org-selector {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 12px;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .org-selector select {
            flex: 1;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 13px;
            font-family: inherit;
        }

        /* ════════════════════════════════════════════════════════════════════
           Two-panel layout
           ════════════════════════════════════════════════════════════════════ */
        .storage-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 14px;
            min-height: 600px;
        }
        @media (max-width: 980px) {
            .storage-layout { grid-template-columns: 1fr; }
        }

        /* ──────────────────────────────────────────────────
           LEFT: tree panel
           ────────────────────────────────────────────────── */
        .tree-panel {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 14px;
            padding: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 75vh;
        }
        .tree-panel-header {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 8px 12px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            margin-bottom: 8px;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }
        .tree-panel-header i { width: 14px; height: 14px; }

        .tree-search {
            margin-bottom: 8px;
            padding: 0 4px;
        }
        .tree-search input {
            width: 100%;
            padding: 7px 10px 7px 32px;
            border: 1px solid rgba(15, 23, 42, 0.10);
            border-radius: 8px;
            font-size: 12px;
            font-family: inherit;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E") no-repeat 10px center;
        }

        .tree-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 0 2px;
        }
        .tree-scroll::-webkit-scrollbar { width: 6px; }
        .tree-scroll::-webkit-scrollbar-thumb {
            background: rgba(15, 23, 42, 0.10);
            border-radius: 3px;
        }
        .tree-scroll::-webkit-scrollbar-thumb:hover { background: rgba(15, 23, 42, 0.18); }

        /* Tree nodes */
        .tree-node {
            list-style: none;
            margin: 0; padding: 0;
        }
        .tree-children {
            list-style: none;
            margin: 0;
            padding: 0;
            display: none;
        }
        .tree-children.expanded { display: block; }

        .tree-row {
            display: flex; align-items: center;
            padding: 5px 6px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            color: #334155;
            gap: 4px;
            text-decoration: none;
            transition: background 0.12s ease;
            user-select: none;
        }
        .tree-row:hover { background: rgba(121, 77, 253, 0.06); }
        .tree-row.active {
            background: linear-gradient(90deg, rgba(121, 77, 253, 0.12), rgba(157, 111, 255, 0.06));
            color: #1e293b;
            font-weight: 600;
        }
        .tree-row.active .tree-row-icon { color: #794DFD; }

        .tree-toggle {
            width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: #94a3b8;
            transition: transform 0.15s ease;
            cursor: pointer;
        }
        .tree-toggle.expanded { transform: rotate(90deg); }
        .tree-toggle.empty { visibility: hidden; }
        .tree-toggle svg, .tree-toggle i { width: 12px; height: 12px; }

        .tree-row-icon {
            width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: #64748b;
        }
        .tree-row-icon svg, .tree-row-icon i { width: 14px; height: 14px; }

        .tree-row-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ──────────────────────────────────────────────────
           RIGHT: content panel
           ────────────────────────────────────────────────── */
        .content-panel {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 14px;
            padding: 16px;
            min-height: 500px;
        }

        .breadcrumb-bar {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px;
            margin-bottom: 16px;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 9px;
            flex-wrap: wrap;
        }
        .breadcrumb-bar a {
            color: #794DFD;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .breadcrumb-bar a:hover { text-decoration: underline; }
        .breadcrumb-bar .sep { color: #cbd5e1; }
        .breadcrumb-bar .current { color: #1e293b; font-weight: 600; }

        .content-section-title {
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin: 18px 0 8px 4px;
        }
        .content-section-title:first-child { margin-top: 4px; }

        /* Item rows (compact, list-style for tree-view aesthetic) */
        .content-list {
            display: flex; flex-direction: column;
            gap: 4px;
        }
        .content-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px;
            border-radius: 9px;
            background: #fff;
            border: 1px solid transparent;
            transition: background 0.12s ease, border-color 0.12s ease;
            position: relative;
        }
        .content-row:hover {
            background: #f8fafc;
            border-color: rgba(15, 23, 42, 0.06);
        }

        .content-row-link {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none;
            color: inherit;
            flex: 1;
            min-width: 0;
        }

        .content-row-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .content-row-icon svg, .content-row-icon i { width: 18px; height: 18px; }
        .icon-folder { background: rgba(121, 77, 253, 0.10); color: #794DFD; }
        .icon-doc    { background: rgba(59, 130, 246, 0.10); color: #2563eb; }
        .icon-xls    { background: rgba(16, 185, 129, 0.10); color: #059669; }
        .icon-pdf    { background: rgba(239, 68, 68, 0.10);  color: #dc2626; }
        .icon-img    { background: rgba(245, 158, 11, 0.10); color: #d97706; }
        .icon-other  { background: rgba(100, 116, 139, 0.10); color: #475569; }

        .content-row-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .content-row-meta {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .content-row-actions {
            display: flex; gap: 4px;
            flex-shrink: 0;
        }
        .row-action {
            padding: 6px;
            border: none; background: transparent;
            border-radius: 6px;
            cursor: pointer;
            color: #94a3b8;
            font-family: inherit;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .row-action:hover { background: rgba(15, 23, 42, 0.06); color: #475569; }
        .row-action.danger:hover { background: rgba(239, 68, 68, 0.08); color: #dc2626; }
        .row-action svg, .row-action i { width: 15px; height: 15px; }

        .content-empty {
            text-align: center;
            padding: 50px 20px;
            color: #94a3b8;
            font-size: 14px;
        }
        .content-empty i { width: 36px; height: 36px; margin-bottom: 10px; opacity: 0.4; }

        /* Modal — простая, без bootstrap */
        .modal-storage {
            display: none;
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(15, 23, 42, 0.55);
            align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal-storage.show { display: flex; }
        .modal-storage-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
        }
        .modal-storage-title {
            font-size: 16px; font-weight: 700;
            margin-bottom: 16px;
        }
        .modal-storage label {
            display: block; font-size: 12px; font-weight: 600;
            color: #475569; margin-bottom: 6px;
        }
        .modal-storage input, .modal-storage select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 9px;
            font-size: 14px; font-family: inherit;
        }
        .modal-storage-buttons {
            display: flex; gap: 8px; justify-content: flex-end;
            margin-top: 16px;
        }
        .modal-storage-buttons button {
            padding: 9px 16px;
            font-size: 13px; font-weight: 600;
            border-radius: 9px; cursor: pointer; border: none;
            font-family: inherit;
        }
        .modal-cancel {
            background: #f1f5f9; color: #475569;
        }
        .modal-confirm {
            background: linear-gradient(135deg, #794DFD, #9d6fff); color: #fff;
        }
    </style>

    <div class="work-view-page-modern">

        {{-- Header --}}
        <div class="storage-header">
            <div class="storage-header-left">
                <div class="storage-header-icon">
                    <i data-lucide="folder-archive"></i>
                </div>
                <div>
                    <div class="storage-header-title">{{ __('menu.org_storage') ?? 'Хранилище организации' }}</div>
                    <div class="storage-header-sub">{{ $organization->name_uz ?? '—' }}</div>
                </div>
            </div>

            <div class="storage-actions">
                @if($canWrite)
                    <button class="btn-storage btn-storage-primary" onclick="openCreateFolderModal()">
                        <i data-lucide="folder-plus"></i>
                        <span>{{ __('storage.create_folder') ?? 'Создать папку' }}</span>
                    </button>
                    <button class="btn-storage btn-storage-primary" onclick="document.getElementById('file-upload-input').click()">
                        <i data-lucide="upload"></i>
                        <span>{{ __('storage.upload_file') ?? 'Загрузить файл' }}</span>
                    </button>
                    <a href="{{ route('storage.trash', request()->only('org')) }}" class="btn-storage btn-storage-secondary" style="text-decoration: none;">
                        <i data-lucide="trash-2"></i>
                        <span>{{ __('storage.trash') ?? 'Корзина' }}</span>
                    </a>
                @else
                    <span class="btn-storage btn-storage-secondary" style="cursor:default;">
                    <i data-lucide="eye"></i>
                    <span>{{ __('storage.read_only') ?? 'Режим просмотра' }}</span>
                </span>
                @endif
            </div>
        </div>

        {{-- Org selector for super_admin --}}
        @if($availableOrgs->count() > 0)
            <div class="org-selector">
                <i data-lucide="shield-check" style="width:16px;height:16px;color:#d97706;"></i>
                <span>{{ __('storage.viewing_as_admin') ?? 'Режим Super Admin — выберите организацию:' }}</span>
                <select onchange="if(this.value)window.location.href='{{ route('storage.index') }}?org='+this.value">
                    @foreach($availableOrgs as $org)
                        <option value="{{ $org->id }}" {{ $org->id === $orgUnitId ? 'selected' : '' }}>
                            {{ $org->name_uz }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Two-panel layout --}}
        <div class="storage-layout">

            {{-- ════════════════════════════════════════════════════════════════
                 LEFT: Tree panel
                 ════════════════════════════════════════════════════════════════ --}}
            <div class="tree-panel">
                <div class="tree-panel-header">
                    <i data-lucide="folder-tree"></i>
                    <span>{{ __('storage.folders') ?? 'Папки' }}</span>
                </div>

                <div class="tree-search">
                    <input type="text" id="tree-search-input"
                           placeholder="{{ __('storage.search_folder') ?? 'Поиск папки...' }}"
                           oninput="filterTree(this.value)">
                </div>

                <div class="tree-scroll" id="tree-scroll">
                    {{-- ROOT node --}}
                    <ul class="tree-node">
                        <li>
                            <a href="{{ route('storage.index', request()->only('org')) }}"
                               class="tree-row {{ $activeFolderId === null ? 'active' : '' }}"
                               data-folder-id="root">
                                <span class="tree-toggle empty"></span>
                                <span class="tree-row-icon"><i data-lucide="home"></i></span>
                                <span class="tree-row-name">{{ __('storage.root') ?? 'Корень' }}</span>
                            </a>
                            <ul class="tree-children expanded" id="children-root">
                                {{-- Заполняется JS-ом ниже --}}
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════
                 RIGHT: Content panel
                 ════════════════════════════════════════════════════════════════ --}}
            <div class="content-panel">

                {{-- Breadcrumb --}}
                <div class="breadcrumb-bar">
                    <a href="{{ route('storage.index', request()->only('org')) }}">
                        <i data-lucide="home" style="width:14px;height:14px;"></i>
                        {{ __('storage.root') ?? 'Корень' }}
                    </a>
                    @foreach($breadcrumb as $i => $crumb)
                        <span class="sep">/</span>
                        @if($i === count($breadcrumb) - 1)
                            <span class="current">{{ $crumb['name'] }}</span>
                        @else
                            <a href="{{ route('storage.folder.show', $crumb['id']) }}">{{ $crumb['name'] }}</a>
                        @endif
                    @endforeach
                </div>

                {{-- Subfolders --}}
                @if($childFolders->count() > 0)
                    <div class="content-section-title">
                        {{ __('storage.subfolders') ?? 'Подпапки' }} ({{ $childFolders->count() }})
                    </div>
                    <div class="content-list">
                        @foreach($childFolders as $sub)
                            <div class="content-row">
                                <a href="{{ route('storage.folder.show', $sub->id) }}" class="content-row-link">
                                    <div class="content-row-icon icon-folder">
                                        <i data-lucide="folder"></i>
                                    </div>
                                    <div style="min-width:0; flex:1;">
                                        <div class="content-row-name">{{ $sub->name }}</div>
                                    </div>
                                </a>
                                @if($canWrite)
                                    <div class="content-row-actions">
                                        <button class="row-action" title="{{ __('storage.rename') ?? 'Переименовать' }}"
                                                onclick="openRenameFolderModal({{ $sub->id }}, '{{ addslashes($sub->name) }}')">
                                            <i data-lucide="edit-2"></i>
                                        </button>
                                        <form method="POST" action="{{ route('storage.folder.delete', $sub->id) }}" style="margin:0;"
                                              onsubmit="return confirm('{{ __('storage.confirm_delete_folder') ?? 'Удалить папку и всё содержимое?' }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="row-action danger" title="{{ __('storage.delete') ?? 'Удалить' }}">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Files --}}
                @if($files->count() > 0)
                    <div class="content-section-title">
                        {{ __('storage.files') ?? 'Файлы' }} ({{ $files->count() }})
                    </div>
                    <div class="content-list">
                        @foreach($files as $f)
                            @php
                                $iconClass = match(strtolower($f->extension)) {
                                    'doc', 'docx' => 'icon-doc',
                                    'xls', 'xlsx' => 'icon-xls',
                                    'pdf'         => 'icon-pdf',
                                    'png', 'jpg', 'jpeg', 'gif', 'webp' => 'icon-img',
                                    default       => 'icon-other',
                                };
                            @endphp
                            <div class="content-row">
                                <a href="{{ $f->isPreviewable() ? route('storage.file.preview', $f->id) : route('storage.file.download', $f->id) }}"
                                   target="_blank" class="content-row-link">
                                    <div class="content-row-icon {{ $iconClass }}">
                                        <i data-lucide="{{ $f->iconName() }}"></i>
                                    </div>
                                    <div style="min-width:0; flex:1;">
                                        <div class="content-row-name">{{ $f->name }}</div>
                                        <div class="content-row-meta">
                                            {{ strtoupper($f->extension) }} · {{ $f->humanSize() }} ·
                                            {{ $f->created_at?->format('d.m.Y H:i') }}
                                        </div>
                                    </div>
                                </a>
                                <div class="content-row-actions">
                                    <a href="{{ route('storage.file.download', $f->id) }}" class="row-action" title="{{ __('storage.download') ?? 'Скачать' }}">
                                        <i data-lucide="download"></i>
                                    </a>
                                    @if($canWrite)
                                        <button class="row-action" title="{{ __('storage.rename') ?? 'Переименовать' }}"
                                                onclick="openRenameFileModal({{ $f->id }}, '{{ addslashes($f->name) }}')">
                                            <i data-lucide="edit-2"></i>
                                        </button>
                                        <form method="POST" action="{{ route('storage.file.delete', $f->id) }}" style="margin:0;"
                                              onsubmit="return confirm('{{ __('storage.confirm_delete_file') ?? 'Переместить файл в корзину?' }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="row-action danger" title="{{ __('storage.delete') ?? 'Удалить' }}">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Empty --}}
                @if($childFolders->count() === 0 && $files->count() === 0)
                    <div class="content-empty">
                        <i data-lucide="folder-open"></i>
                        <div>
                            @if($canWrite)
                                {{ __('storage.empty') ?? 'Папка пуста. Создайте папку или загрузите файл.' }}
                            @else
                                {{ __('storage.empty_readonly') ?? 'В этой папке пока нет файлов.' }}
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($canWrite)
        {{-- Hidden file upload form --}}
        <form id="file-upload-form" method="POST" action="{{ route('storage.file.upload') }}" enctype="multipart/form-data" style="display:none;">
            @csrf
            @if(isset($folder) && $folder)
                <input type="hidden" name="folder_id" value="{{ $folder->id }}">
            @endif
            @if(request('org'))
                <input type="hidden" name="org" value="{{ request('org') }}">
            @endif
            <input type="file" name="file" id="file-upload-input"
                   accept=".doc,.docx,.xls,.xlsx,.pdf,.png,.jpg,.jpeg,.gif,.webp"
                   onchange="if(this.files.length){document.getElementById('file-upload-form').submit();}">
        </form>

        {{-- Create folder modal --}}
        <div class="modal-storage" id="modal-create-folder">
            <div class="modal-storage-card">
                <div class="modal-storage-title">{{ __('storage.create_folder') ?? 'Создать папку' }}</div>
                <form method="POST" action="{{ route('storage.folder.create') }}">
                    @csrf
                    @if(isset($folder) && $folder)
                        <input type="hidden" name="parent_id" value="{{ $folder->id }}">
                    @endif
                    @if(request('org'))
                        <input type="hidden" name="org" value="{{ request('org') }}">
                    @endif
                    <label>{{ __('storage.folder_name') ?? 'Название папки' }}</label>
                    <input type="text" name="name" required maxlength="255" autofocus>
                    <div class="modal-storage-buttons">
                        <button type="button" class="modal-cancel" onclick="closeModal('modal-create-folder')">
                            {{ __('field.cancel') ?? 'Отмена' }}
                        </button>
                        <button type="submit" class="modal-confirm">
                            {{ __('storage.create') ?? 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rename folder modal --}}
        <div class="modal-storage" id="modal-rename-folder">
            <div class="modal-storage-card">
                <div class="modal-storage-title">{{ __('storage.rename_folder') ?? 'Переименовать папку' }}</div>
                <form method="POST" id="rename-folder-form">
                    @csrf @method('PATCH')
                    <label>{{ __('storage.folder_name') ?? 'Название папки' }}</label>
                    <input type="text" name="name" id="rename-folder-input" required maxlength="255">
                    <div class="modal-storage-buttons">
                        <button type="button" class="modal-cancel" onclick="closeModal('modal-rename-folder')">
                            {{ __('field.cancel') ?? 'Отмена' }}
                        </button>
                        <button type="submit" class="modal-confirm">
                            {{ __('storage.save') ?? 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rename file modal --}}
        <div class="modal-storage" id="modal-rename-file">
            <div class="modal-storage-card">
                <div class="modal-storage-title">{{ __('storage.rename_file') ?? 'Переименовать файл' }}</div>
                <form method="POST" id="rename-file-form">
                    @csrf @method('PATCH')
                    <label>{{ __('storage.file_name') ?? 'Название файла' }}</label>
                    <input type="text" name="name" id="rename-file-input" required maxlength="255">
                    <div class="modal-storage-buttons">
                        <button type="button" class="modal-cancel" onclick="closeModal('modal-rename-file')">
                            {{ __('field.cancel') ?? 'Отмена' }}
                        </button>
                        <button type="submit" class="modal-confirm">
                            {{ __('storage.save') ?? 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        @php
            // Подготовка данных для JS (вынесено из @json чтобы Blade не запутался
            // со скобками в стрелочной функции).
            $treeDataForJs = $allFolders->map(function ($f) {
                return [
                    'id'        => $f->id,
                    'parent_id' => $f->parent_id,
                    'name'      => $f->name,
                ];
            })->values();

            $expandedOnLoad = collect($breadcrumb)->pluck('id')->toArray();
        @endphp

        // ════════════════════════════════════════════════════════════════════
        // Tree-view: render dataset as nested <ul>
        // ════════════════════════════════════════════════════════════════════

        // Все папки организации, отсортированные по parent_id, name
        var TREE_DATA = {!! $treeDataForJs->toJson() !!};

        var ACTIVE_FOLDER_ID = {!! json_encode($activeFolderId) !!};

        // Папки на пути от корня до активной — нужно раскрыть автоматически
        var EXPANDED_ON_LOAD = {!! json_encode($expandedOnLoad) !!};

        var FOLDER_URL_BASE = '{{ url('/org-storage/folder') }}/';

        function buildTreeIndex(data) {
            var byParent = {};
            data.forEach(function(node) {
                var pid = node.parent_id == null ? 'root' : String(node.parent_id);
                if (!byParent[pid]) byParent[pid] = [];
                byParent[pid].push(node);
            });
            return byParent;
        }

        function renderTreeChildren(parentId, byParent, depth) {
            var children = byParent[parentId === null ? 'root' : String(parentId)] || [];
            if (!children.length) return '';

            var html = '';
            children.forEach(function(node) {
                var hasChildren = (byParent[String(node.id)] || []).length > 0;
                var isActive    = ACTIVE_FOLDER_ID === node.id;
                var isExpanded  = EXPANDED_ON_LOAD.indexOf(node.id) !== -1 || isActive;

                html += '<li class="tree-node">'
                    + '<div class="tree-row ' + (isActive ? 'active' : '') + '" data-folder-id="' + node.id + '" data-name="' + escapeHtml(node.name.toLowerCase()) + '">'
                    + '<span class="tree-toggle ' + (hasChildren ? '' : 'empty') + ' ' + (isExpanded ? 'expanded' : '') + '" onclick="toggleNode(event, ' + node.id + ')">'
                    + (hasChildren ? '<i data-lucide="chevron-right"></i>' : '')
                    + '</span>'
                    + '<span class="tree-row-icon" onclick="navigateFolder(' + node.id + ')"><i data-lucide="' + (isExpanded ? 'folder-open' : 'folder') + '"></i></span>'
                    + '<span class="tree-row-name" onclick="navigateFolder(' + node.id + ')" style="cursor:pointer;flex:1;">' + escapeHtml(node.name) + '</span>'
                    + '</div>';

                if (hasChildren) {
                    html += '<ul class="tree-children ' + (isExpanded ? 'expanded' : '') + '" id="children-' + node.id + '" style="padding-left:' + ((depth + 1) * 14) + 'px;">'
                        + renderTreeChildren(node.id, byParent, depth + 1)
                        + '</ul>';
                }

                html += '</li>';
            });
            return html;
        }

        function escapeHtml(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function toggleNode(ev, folderId) {
            ev.stopPropagation();
            var children = document.getElementById('children-' + folderId);
            var toggle   = ev.currentTarget;
            if (!children) return;
            var nowExpanded = !children.classList.contains('expanded');
            children.classList.toggle('expanded', nowExpanded);
            toggle.classList.toggle('expanded', nowExpanded);
        }

        function navigateFolder(folderId) {
            var url = FOLDER_URL_BASE + folderId;
            @if(request('org'))
                url += '?org={{ request('org') }}';
            @endif
                window.location.href = url;
        }

        function filterTree(query) {
            query = query.trim().toLowerCase();
            var rows = document.querySelectorAll('.tree-row[data-name]');
            if (!query) {
                // Reset: hide everything except the originally-expanded path
                rows.forEach(function(row) {
                    var li = row.closest('li');
                    if (li) li.style.display = '';
                });
                return;
            }
            // Show rows whose name matches; expand all parents on the way
            rows.forEach(function(row) {
                var li = row.closest('li');
                if (!li) return;
                var name = row.dataset.name || '';
                li.style.display = name.indexOf(query) !== -1 ? '' : 'none';
            });
            // Re-show parents of visible rows
            rows.forEach(function(row) {
                var li = row.closest('li');
                if (!li || li.style.display === 'none') return;
                // Walk up: each ancestor li.tree-node should be visible
                var parent = li.parentElement;
                while (parent && parent !== document.body) {
                    if (parent.tagName === 'LI' && parent.classList.contains('tree-node')) {
                        parent.style.display = '';
                    }
                    if (parent.classList && parent.classList.contains('tree-children')) {
                        parent.classList.add('expanded');
                    }
                    parent = parent.parentElement;
                }
            });
        }

        // Init
        document.addEventListener('DOMContentLoaded', function() {
            var byParent = buildTreeIndex(TREE_DATA);
            var rootContainer = document.getElementById('children-root');
            rootContainer.innerHTML = renderTreeChildren(null, byParent, 0);

            // Re-render lucide icons
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // Auto-scroll active row into view
            var active = document.querySelector('.tree-row.active');
            if (active && active.scrollIntoView) {
                setTimeout(function() {
                    active.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }, 100);
            }
        });

        // ════════════════════════════════════════════════════════════════════
        // Modals
        // ════════════════════════════════════════════════════════════════════
        function openModal(id) { var el = document.getElementById(id); if (el) el.classList.add('show'); }
        function closeModal(id) { var el = document.getElementById(id); if (el) el.classList.remove('show'); }

        function openCreateFolderModal() { openModal('modal-create-folder'); }

        function openRenameFolderModal(id, currentName) {
            var form = document.getElementById('rename-folder-form');
            if (!form) return;
            form.action = '{{ url('/org-storage/folder') }}/' + id;
            document.getElementById('rename-folder-input').value = currentName;
            openModal('modal-rename-folder');
        }

        function openRenameFileModal(id, currentName) {
            var form = document.getElementById('rename-file-form');
            if (!form) return;
            form.action = '{{ url('/org-storage/file') }}/' + id;
            document.getElementById('rename-file-input').value = currentName;
            openModal('modal-rename-file');
        }

        document.querySelectorAll('.modal-storage').forEach(function(m) {
            m.addEventListener('click', function(e) { if (e.target === m) m.classList.remove('show'); });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-storage.show').forEach(function(m) { m.classList.remove('show'); });
            }
        });
    </script>
@endsection
