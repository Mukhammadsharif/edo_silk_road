@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/office/journal/sent/index.css') }}">
@section('content')
    <div class="outgoing-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="send"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.outgoing') }}</h1>
                        <p>{{ __('message.manage_outgoing_documents') ?? 'Управление исходящими документами' }}</p>
                    </div>
                </div>
                <div class="header-center">
                    @include('templates.alert')
                </div>
                <div class="header-right">
                    <nav class="breadcrumb-modern">
                        <a href="{{ route('home') }}" class="breadcrumb-item">
                            <i data-lucide="home"></i>
                            <span>{{ __('menu.home') ?? 'Главная' }}</span>
                        </a>
                        <i data-lucide="chevron-right" class="breadcrumb-separator"></i>
                        <span class="breadcrumb-item active">{{ __('title.outgoing') }}</span>
                    </nav>
                </div>
            </div>
        </div>

        <div class="content-wrapper-modern">
            <div class="filters-card-modern">
                <div class="filters-header">
                    <div class="filters-title">
                        <i data-lucide="filter"></i>
                        <span>{{ __('action.filters') ?? 'Фильтры' }}</span>
                    </div>
                    <div class="filters-count">
                        <span class="count-badge">{{ $models->total() }}</span>
                        <span class="count-label">{{ __('field.documents') ?? 'документов' }}</span>
                    </div>
                </div>

                <form action="{{ route('office.outgoing-docs') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label>
                                <i data-lucide="building-2"></i>
                                {{ __('field.to_org') }}
                            </label>
                            <select name="doc_org" class="form-control-modern select2">
                                <option value="">{{ __('action.all') ?? 'Все' }}</option>
                                @forelse($doc_organizations as $key => $doc_organization)
                                    <option value="{{ $doc_organization->id }}" {{ ($doc_org == $doc_organization->id ? "selected":"") }}>
                                        {{ $key+1 }}. {{ $doc_organization->lang($doc_organization->id)??'' }}
                                    </option>
                                @empty
                                    <option>{{ __('field.not_found') }}</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="hash"></i>
                                {{ __('field.doc_#') }}
                            </label>
                            <input type="text" name="doc_number" placeholder="{{ __('field.doc_#') }}" value="{{ $doc_number??'' }}" class="form-control-modern">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="file-text"></i>
                                {{ __('field.doc_name') }}
                            </label>
                            <input type="text" name="doc_name" placeholder="{{ __('field.doc_name') }}" value="{{ $doc_name??'' }}" class="form-control-modern">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('field.from_day') }}
                            </label>
                            <input type="text" name="date_from" class="form-control-modern my-bootstrap-datepicker" placeholder="{{ __('field.from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('field.till_day') }}
                            </label>
                            <input type="text" name="date_till" class="form-control-modern my-bootstrap-datepicker" placeholder="{{ __('field.till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </label>
                            <select name="status" class="form-control-modern select2">
                                <option value="">{{ __('field.status_all') }}</option>
                                @forelse($statusNames as $key => $val)
                                    <option value="{{ $val->code }}" {{ ($status == $val->code ? "selected":"") }}>
                                        {{ $val->lang($val->id) }}
                                    </option>
                                @empty
                                    <option>{{ __('field.not_found') }}</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-modern btn-primary">
                                <i data-lucide="search"></i>
                                <span>{{ __('action.search') }}</span>
                            </button>
                            <a href="{{ route('office.outgoing-docs') }}" class="btn-modern btn-secondary">
                                <i data-lucide="rotate-ccw"></i>
                                <span>{{ __('action.clear') }}</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-card-modern">
                <div class="table-wrapper-modern">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th><i data-lucide="hash"></i> {{ __('field.#') }}</th>
                                <th><i data-lucide="clock"></i> {{ __('field.created_at') }}</th>
                                <th><i data-lucide="building-2"></i> {{ __('field.to_org') }}</th>
                                <th><i data-lucide="file-text"></i> {{ __('field.doc_name') }}</th>
                                <th><i data-lucide="calendar"></i> {{ __('field.doc_date') }}</th>
                                <th><i data-lucide="activity"></i> {{ __('field.status') }}</th>
                                <th><i data-lucide="timer"></i> {{ __('field.signing_term') }}</th>
                                <th><i data-lucide="user"></i> {{ __('field.creator') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($models as $key => $model)
                                <tr class="table-row-modern">
                                    <td class="td-number">
                                        {{ $model->createdUser->department->dep_code_doc??'' }}{{ $model->j_number }}
                                    </td>
                                    <td class="td-datetime">
                                        <div class="datetime-cell">
                                            <span class="date">{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y') }}</span>
                                            <span class="time">{{ \Carbon\Carbon::parse($model->created_at)->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="td-organization">
                                        <div class="org-cell">
                                            <i data-lucide="building-2"></i>
                                            <span>{{ $model->docOrg ? $model->docOrg->organization->lang($model->docOrg->organization->id)??'' : '' }}</span>
                                        </div>
                                    </td>
                                    <td class="td-doc-name">
                                        <a href="{{ route('office.view-sent-doc', [$model->id, $model->doc->doc_hash??'-']) }}" class="doc-link">
                                            <i data-lucide="file-text"></i>
                                            <span>{{ $model->doc->doc_name??__('unk') }}</span>
                                            <i data-lucide="arrow-right" class="arrow-icon"></i>
                                        </a>
                                    </td>
                                    <td class="td-date">{{ date('d.m.Y', strtotime($model->doc->doc_date??now())) }}</td>
                                    <td class="td-status">
                                        <span class="status-badge-modern {{ $model->statusOut->badgeClass??'' }}">
                                            <i data-lucide="circle"></i>
                                            {{ $model->statusOut->lang($model->statusOut->id)??'unk' }}
                                        </span>
                                    </td>
                                    <td class="td-term">@include('templates.execution-time')</td>
                                    <td class="td-creator">
                                        <div class="creator-cell">
                                            <i data-lucide="user"></i>
                                            <span>{{ $model->fromUser->personal->shortName()??'' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <div class="empty-state-content">
                                            <i data-lucide="send"></i>
                                            <p>{{ __('field.not_found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper-modern">
                    {!! $models->links() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
