@extends('layouts.app')
@section('content')
    <link href="{{ asset('css/departments/tree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/common-document-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templates/organizations/index.css') }}" rel="stylesheet">

    <!-- Modern Document Page Container -->
    <div class="document-page-modern">

        <!-- Modern Page Header -->
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="building-2"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('menu.organizations') }}</h1>
                        <p>{{ __('menu.home') }} / {{ __('menu.organizations') }}</p>
                    </div>
                </div>

                <div class="header-center">
                    @include('templates.alert')
                </div>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-modern" style="margin-top: 1.5rem;">
                    <a href="{{ route('home') }}" class="breadcrumb-item">
                        <i data-lucide="home"></i>
                        <span>{{ __('menu.home') }}</span>
                    </a>
                    <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                    <span class="breadcrumb-item active">{{ __('menu.organizations') }}</span>
                </nav>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper-modern">

            <!-- Filters Card -->
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="search"></i>
                        <span>{{ __('action.search') }} & {{ __('field.filters') }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $organizations->total() }}</span>
                        <span class="count-label">{{ __('field.total') }}</span>
                    </div>
                </div>

                <form action="{{ route('organizations.index') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label>
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </label>
                            <input type="text"
                                   name="s_text"
                                   value="{{ $s_text ?? '' }}"
                                   class="form-control-modern"
                                   placeholder="{{ __('field.search_placeholder_tin_and_name') }}">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </label>
                            <select name="s_status" class="form-control-modern">
                                <option value="">{{ __('field.status_all') }}</option>
                                <option value="1" {{ ($s_status ?? '') == '1' ? 'selected' : '' }}>{{ __('status.active') }}</option>
                                <option value="2" {{ ($s_status ?? '') == '2' ? 'selected' : '' }}>{{ __('status.inactive') }}</option>
                                <option value="3" {{ ($s_status ?? '') == '3' ? 'selected' : '' }}>{{ __('status.delete') }}</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }}
                            </button>
                            <a href="{{ route('organizations.index') }}" class="btn-modern btn-secondary">
                                <i data-lucide="refresh-ccw"></i>
                                {{ __('field.clear') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; align-items: start;">

                {{--                <!-- Organizations Tree Sidebar -->--}}
                {{--                <div class="table-card-modern" style="position: sticky; top: 2rem;">--}}
                {{--                    <div class="filters-header">--}}
                {{--                        <div class="filters-title">--}}
                {{--                            <i data-lucide="folder-tree"></i>--}}
                {{--                            <span>{{ __('menu.organizations') }} {{ __('field.tree') }}</span>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                    <div style="padding: 1.5rem;">--}}
                {{--                        @php $lang_key = session()->get('locale') ?? 'uz'; @endphp--}}
                {{--                        <section class="list-tree">--}}
                {{--                            <ul>--}}
                {{--                                @foreach($parentOrganizations as $key => $item)--}}
                {{--                                    <li>--}}
                {{--                                        <strong>{{ $item['name_'.$lang_key] ?? $item['name_uz'] }}</strong>--}}
                {{--                                        @if($item->childs && count($item->childs))--}}
                {{--                                            @include('admin.organizations.manageChild', ['childs' => $item->childs])--}}
                {{--                                        @endif--}}
                {{--                                    </li>--}}
                {{--                                @endforeach--}}
                {{--                            </ul>--}}
                {{--                        </section>--}}
                {{--                    </div>--}}
                {{--                </div>--}}

                <!-- Organizations Table -->
                <div class="table-card-modern">
                    <div class="filters-header">
                        <div class="filters-title">
                            <i data-lucide="list"></i>
                            <span>{{ __('menu.organizations') }} {{ __('menu.list') }}</span>
                        </div>
                        <a href="{{ route('organizations.create') }}" class="btn-modern btn-primary"
                           style="width: auto;">
                            <i data-lucide="circle-plus"></i>
                            {{ __('action.create') }}
                        </a>
                    </div>

                    <div class="table-wrapper-modern">
                        <table class="modern-table">
                            <thead>
                            <tr>
                                <th style="width: 60px;">
                                    <i data-lucide="hash"></i>
                                    {{ __('field.number') }}
                                </th>
                                <th style="text-align: left;">
                                    <i data-lucide="building"></i>
                                    {{ __('field.name') }}
                                </th>
                                <th style="text-align: left;">
                                    <i data-lucide="award"></i>
                                    {{ __('field.current_subscription') }}
                                </th>
                                <th style="text-align: left;">
                                    <i data-lucide="users"></i>
                                    {{ __('field.users') }}
                                </th>
                                <th style="width: 120px;">
                                    <i data-lucide="activity"></i>
                                    {{ __('field.status') }}
                                </th>
                                <th style="width: 140px;">
                                    <i data-lucide="settings"></i>
                                    {{ __('field.action') }}
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($organizations as $key => $item)
                                <tr class="table-row-modern" id="rowID{{ $item->id }}">
                                    <td class="td-number">{{ $organizations->firstItem() + $key }}</td>
                                    <td class="td-doc-name">
                                        <a href="{{ route('departments.index', ['id' => $item->id]) }}"
                                           class="doc-link">
                                            <i data-lucide="building-2"></i>
                                            <strong>{{ $item->lang($item->id) }}</strong>
                                            <i data-lucide="arrow-right" class="arrow-icon"></i>
                                        </a>
                                    </td>
                                    <td>
                                        @if($item->currentSubscription && $item->currentSubscription->tariff)
                                            <div class="tariff-badge">
                                                <i data-lucide="zap"></i>
                                                {{ $item->currentSubscription->tariff->name??'' }}

                                                {!! $item->currentSubscription->status_badge??'' !!}
                                            </div>
                                        @elseif($item->pendingSubscription && $item->pendingSubscription->tariff)
                                            <div class="tariff-badge">
                                                <i data-lucide="zap"></i>
                                                {{ $item->pendingSubscription->tariff->name??'' }}

                                                {!! $item->pendingSubscription->status_badge??'' !!}
                                            </div>
                                        @else
                                            {{ __('field.no_subscription') }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-modern badge-info">
                                            @if($item->users && $item->users->count())
                                                {{ $item->users->count() }}
                                            @else
                                                0
                                            @endif
                                        </span>
                                    </td>
                                    <td class="td-status">
                                        @if($item->status == 1)
                                            <span class="status-badge-modern badge-success">
                                                    <i data-lucide="check-circle"></i>
                                                    {{ __('field.active') }}
                                                </span>
                                        @elseif($item->status == 3)
                                            <span class="status-badge-modern badge-danger">
                                                    <i data-lucide="trash-2"></i>
                                                    {{ __('status.delete') }}
                                                </span>
                                        @else
                                            <span class="status-badge-modern badge-warning">
                                                    <i data-lucide="x-circle"></i>
                                                    {{ __('status.inactive') }}
                                                </span>
                                        @endif
                                    </td>
                                    <td class="td-action">
                                        <div style="display:flex; gap:6px; align-items:center; justify-content:center;">
                                            <a href="{{ route('organizations.edit', $item->id) }}"
                                               class="action-btn view-btn" title="{{ __('action.edit') }}">
                                                <i data-lucide="square-pen"></i>
                                            </a>

                                            @if($item->status == 3)
                                                <button type="button" value="{{ $item->id }}"
                                                        class="action-btn edit-btn restore_btn"
                                                        title="Tiklash">
                                                    <i data-lucide="rotate-ccw"></i>
                                                </button>
                                            @else
                                                <button type="button" value="{{ $item->id }}"
                                                        class="action-btn delete-btn delete_btn"
                                                        title="{{ __('action.delete') }}">
                                                    <i data-lucide="trash-2"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <div class="empty-state-content">
                                            <i data-lucide="folder-x"></i>
                                            <p>{{ __('field.not_found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($organizations->hasPages())
                        <div class="pagination-wrapper-modern">
                            {!! $organizations->links() !!}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="DeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <div class="modal-icon-wrapper danger">
                        <i data-lucide="alert-triangle"></i>
                    </div>
                    <h4>{{ __('action.delete') }}</h4>
                    <button type="button" class="close-modal" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <p><i data-lucide="message-circle"></i> Tanlangan tashkilot o`chiriladi. Keyinchalik "O`chirilgan" filtri orqali tiklash mumkin.</p>
                    <input type="hidden" id="deleteing_id">
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                        <i data-lucide="x"></i>
                        {{ __('action.close') }}
                    </button>
                    <button type="button" class="btn-modern btn-danger yes_delete_btn">
                        <i data-lucide="trash-2"></i>
                        {{ __('action.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script>
        /* Удаление организации: мягкое (status = 3), с подтверждением.
           Бэкенд блокирует удаление, если внутри есть активные сотрудники
           или дочерние организации — текст ошибки показываем как есть. */
        $(document).on('click', '.delete_btn', function () {
            $('#deleteing_id').val($(this).val());
            $('#DeleteModal').modal('show');
        });

        $(document).on('click', '.yes_delete_btn', function (e) {
            e.preventDefault();

            let btn = $(this);
            let id  = $('#deleteing_id').val();

            btn.html("<i data-lucide='loader-2' class='spinner'></i> O`chirilmoqda...");

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            $.ajax({
                type: "DELETE",
                url: "{{ url('admin/organizations/destroy') }}/" + id,
                dataType: "json",
                success: function (response) {
                    btn.html("<i data-lucide='trash-2'></i> {{ __('action.yes_delete') }}");
                    $('#DeleteModal').modal('hide');

                    if (response.status === 200) {
                        $("#rowID" + id).addClass("table-danger").fadeOut(1200, function () {
                            this.remove();
                        });
                        showSuccessMessageInTopCenter('top-center');
                    } else {
                        alert(response.message || 'Xatolik');
                    }
                },
                error: function (xhr) {
                    btn.html("<i data-lucide='trash-2'></i> {{ __('action.yes_delete') }}");
                    $('#DeleteModal').modal('hide');
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Xatolik';
                    alert(msg);
                }
            });
        });

        /* Восстановление удалённой организации (виден только в фильтре «O`chirilgan»). */
        $(document).on('click', '.restore_btn', function () {
            let id = $(this).val();

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            $.ajax({
                type: "POST",
                url: "{{ url('admin/organizations/restore') }}/" + id,
                dataType: "json",
                success: function (response) {
                    if (response.status === 200) {
                        location.reload();
                    } else {
                        alert(response.message || 'Xatolik');
                    }
                },
                error: function (xhr) {
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Xatolik';
                    alert(msg);
                }
            });
        });
    </script>
@endsection
