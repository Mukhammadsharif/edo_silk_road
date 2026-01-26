@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/templates/common-document-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/templates/select2.css') }}">
@section('content')
    <div class="document-page-modern">
        <div class="page-header-modern">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i data-lucide="file-check"></i>
                    </div>
                    <div class="header-text">
                        <h1>{{ __('title.work_documents') }}</h1>
                        <p>{{ __('message.manage_execution_documents') ?? 'Управление документами на исполнение' }}</p>
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
                        <span class="breadcrumb-item active">{{ __('title.work_documents') }}</span>
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

                <form action="{{ route('documents.for-execution.index') }}" method="post" class="filters-form-modern">
                    @csrf
                    <div class="filters-grid">
                        <div class="filter-item">
                            <label>
                                <i data-lucide="folder"></i>
                                {{ __('document.document') }}
                            </label>
                            <select name="doc_type" class="form-control-modern select2">
                                <option value="all">{{ __('document.document') }}</option>
                                @forelse($journal_types as $key => $journal_type)
                                    <option value="{{ $journal_type->id }}" {{ ($doc_type == $journal_type->id ? "selected":"") }}>
                                        {{ $key+1 }}.
                                        {{ $journal_type->lang($journal_type->lang_id) }}
                                    </option>
                                @empty
                                    <option>{{ __('field.not_found') }}</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="file-text"></i>
                                {{ __('document.doc_name') }}
                            </label>
                            <input type="text" name="doc_name" placeholder="{{ __('document.doc_name') }}" value="{{ $doc_name??'' }}" class="form-control-modern">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('document.from_day') }}
                            </label>
                            <input type="text" name="date_from" class="form-control-modern my-bootstrap-datepicker" placeholder="{{ __('document.from_day') }}" value="{{ $date_from??'' }}" autocomplete="off">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="calendar"></i>
                                {{ __('document.till_day') }}
                            </label>
                            <input type="text" name="date_till" class="form-control-modern my-bootstrap-datepicker" placeholder="{{ __('document.till_day') }}" value="{{ $date_till??'' }}" autocomplete="off">
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="activity"></i>
                                {{ __('field.status') }}
                            </label>
                            <select name="status" class="form-control-modern select2">
                                <option value="all">{{ __('select.status_all') }}</option>
                                @forelse($statusNames as $key => $val)
                                    <option value="{{ $val->code }}" {{ ($status == $val->code ? "selected":"") }}>
                                        {{ $val->lang($val->id) }}
                                    </option>
                                @empty
                                    <option>{{ __('not_found') }}</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="filter-item">
                            <label>
                                <i data-lucide="user"></i>
                                {{ __('field.creator') }}
                            </label>
                            <select name="created_user" class="form-control-modern select2">
                                <option value="">{{ __('select.status_all_creators') }}</option>
                                @forelse($cr_users as $key => $item)
                                    <option value="{{ $item->id }}" {{ ($created_user == $item->id ? "selected":"") }}>
                                        {{ $item->userPersonal->shortName()??'' }}
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
                            <a href="{{ route('documents.for-execution.index') }}" class="btn-modern btn-secondary">
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
                            <th><i data-lucide="folder"></i> {{ __('field.document') }}</th>
                            <th><i data-lucide="file-text"></i> {{ __('field.doc_name') }}</th>
                            <th><i data-lucide="tag"></i> {{ __('field.doc_#') }}</th>
                            <th><i data-lucide="calendar"></i> {{ __('field.doc_date') }}</th>
                            <th><i data-lucide="activity"></i> {{ __('field.status') }}</th>
                            <th><i data-lucide="timer"></i> {{ __('field.signing_term') }}</th>
                            <th><i data-lucide="clock"></i> {{ __('field.perf_term') }}</th>
                            <th><i data-lucide="user"></i> {{ __('field.creator') }}</th>
                            <th><i data-lucide="calendar-clock"></i> {{ __('field.created_at') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($models as $key => $model)
                            <tr class="table-row-modern">
                                <td class="td-number">{{ $key+1 }}</td>
                                <td>
                                        <span class="badge-modern badge-success">
                                            {{ $model->journal->lang($model->journal->lang_id)??'' }}
                                        </span>
                                </td>
                                <td class="td-doc-name">
                                    <a href="{{ route('document.exec.view', [$model->id, $model->doc->doc_hash??'-']) }}" class="doc-link">
                                        <i data-lucide="file-text"></i>
                                        <span>{{ $model->doc->doc_name??'*' }}</span>
                                        <i data-lucide="arrow-right" class="arrow-icon"></i>
                                    </a>
                                </td>
                                <td>{{ $model->doc->doc_number??'' }}</td>
                                <td class="td-date">{{ date('d.m.Y', strtotime($model->doc->doc_date??now())) }}</td>
                                <td class="td-status">
                                        <span class="status-badge-modern {{ $model->execStep->statusExec->badgeClass??'' }}">
                                            <i data-lucide="circle"></i>
                                            {{ $model->execStep->statusExec->lang($model->execStep->statusExec->id)??'unk' }}
                                        </span>
                                </td>
                                <td class="td-term">@include('templates.execution-time')</td>
                                <td class="td-term">@include('templates.res-term-in-doc-contr')</td>
                                <td class="td-creator">
                                    <div class="creator-cell">
                                        <i data-lucide="user"></i>
                                        <span>{{ $model->fromUser->personal->shortName()??'' }}</span>
                                    </div>
                                </td>
                                <td class="td-datetime">
                                    <div class="datetime-cell">
                                        <span class="date">{{ \Carbon\Carbon::parse($model->created_at)->format('d.m.y') }}</span>
                                        <span class="time">{{ \Carbon\Carbon::parse($model->created_at)->format('H:i') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="empty-state">
                                    <div class="empty-state-content">
                                        <i data-lucide="file-x"></i>
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
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection

