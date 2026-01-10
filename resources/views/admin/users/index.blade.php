@php use Carbon\Carbon; @endphp
@extends('layouts.app')

<style>
    .opacity-50 {
        opacity: 0.5;
        pointer-events: none;
    }

    .card.border-danger {
        border: 2px solid #dc3545 !important;
    }

    .card-header.bg-danger {
        background-color: #dc3545 !important;
    }
</style>

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.users') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-3">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('menu.users') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-block">
                        <form action="{{ route('user.search') }}" method="POST" role="search" class="form-inline w-100">
                            @csrf

                            @if($isSuperUser)
                                <div class="mb-2 mr-sm-2 w-15">
                                    <select name="s_org" class="form-control w-100 select2">
                                        <option value="">{{ __('field.organizations') }}</option>
                                        @foreach($organizations as $key => $organization)
                                            <option
                                                value="{{ $organization->id }}" {{ $s_org == $organization->id ? 'selected':''}}>
                                                {{ ++$key }}.{{ $organization->lang($organization->id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <input type="text" name="s_text" placeholder="{{ __('action.search') }}"
                                   value="{{ $s_text??'' }}" class="form-control w-25 mb-2 mr-sm-2">

                            <div class="mb-2 mr-sm-2 w-15">
                                <select name="s_status" class="form-control w-100">
                                    <option value="">{{ __('field.status_all') }}</option>
                                    @foreach($statusList as $key => $item)
                                        <option value="{{ $item->code }}" {{ $s_status == $item->code ? 'selected':''}}>
                                            {{ $key+1 }}.{{ $item->lang($item->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2 mr-sm-2">
                                <select name="s_role" class="form-control select2">
                                    <option value="">{{ __('field.role_all') }}</option>
                                    @foreach($userRoles as $key => $item)
                                        <option value="{{ $item->id }}" {{ $s_role == $item->id ? 'selected':''}}>
                                            {{ $key+1 }}. {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info mb-2 mr-10">
                                <i class="ik ik-search"></i>
                                {{ __('action.search') }} ({{ $models->total() }})
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-light mb-2">
                                <i class="ik ik-refresh-cw"></i> {{ __('action.clear') }}
                            </a>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.fam_name') }}</th>
                                    <th>{{ __('field.email') }}</th>
                                    <th>{{ __('field.department') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.roles') }}</th>
                                    <th>{{ __('field.action') }}</th>
                                    <th>{{ __('field.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $item)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            {{ $item->userPersonal->l_name??'-' }} {{ $item->userPersonal->f_name??'' }}
                                        </td>
                                        <td class="text-primary">{{ $item->email }}</td>
                                        <td>
                                            {{ $item->currentWork->department->lang($item->currentWork->department->id)??'-' }}
                                            <p class="text-muted">
                                                {{ $item->currentWork->position->lang($item->currentWork->position->id)??'-' }}
                                            </p>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-pill @if($item->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $item->isActive($item->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(count($item->userRoles))
                                                @foreach($item->userRoles as $key1 => $role)
                                                    <span class="badge badge-pill badge-primary mb-1">
                                                        {{ $role->lang($role->role_id) }}
                                                    </span><br>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('users.show', $item->user_hash) }}"
                                               class="btn btn-success">
                                                <i class="fa fa-eye"></i> {{ __('action.view') }}
                                            </a>
                                        </td>
                                        <td>{{ Carbon::parse($item->created_at)->format('d.m.y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('field.not_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            {!! $models->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @unless($isSuperUser)
            @php
                $user = \App\Models\User::find(auth()->id());
                $organization = \App\Models\Admin\OrganizationUnits::find($user->org_unit_id);
                $canAddUsers = $organization && $organization->canAddUser();
                $limits = $organization ? $organization->getSubscriptionLimits() : null;
            @endphp

            <div class="row mt-4">
                <div class="col-sm-12">
                    <div class="card {{ !$canAddUsers ? 'border-danger' : '' }}">
                        <div class="card-header {{ !$canAddUsers ? 'bg-danger text-white' : '' }}">
                            <h5>{{ __('menu.pending_join_requests') }} ({{ count($pendingRequests) }})</h5>
                            @if($limits)
                                <span class="d-block ml-10">
                                    {{ __('field.users_limit') }}: {{ $limits['users']['current'] }} / {{ $limits['users']['max'] }}
                                    ({{ __('field.available') }}: {{ $limits['users']['available'] }})
                                </span>
                            @endif
                            @if(!$canAddUsers)
                                <div class="alert alert-warning mt-2 mb-0">
                                    <i class="ik ik-alert-circle"></i>
                                    {{ __('field.user_limit_reached_warning') }}
                                </div>
                            @endif
                        </div>
                        <div class="card-body template-demo {{ !$canAddUsers ? 'opacity-50' : '' }}">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>{{ __('field.#') }}</th>
                                        <th>{{ __('field.fam_name') }}</th>
                                        <th>{{ __('field.email') }}</th>
                                        <th>{{ __('field.tax_number') }}</th>
                                        <th>{{ __('field.date') }}</th>
                                        <th>{{ __('field.action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($pendingRequests as $key => $request)
                                        @php
                                            $user = \App\Models\User::find($request->user_id);
                                            $personal = $user ? $user->userPersonal : null;
                                        @endphp
                                        <tr class="text-center">
                                            <td>{{ $key+1 }}</td>
                                            <td>
                                                {{ $personal->l_name ?? '-' }} {{ $personal->f_name ?? '' }}
                                            </td>
                                            <td class="text-primary">{{ $user->email ?? '-' }}</td>
                                            <td>{{ $request->tax_number }}</td>
                                            <td>{{ Carbon::parse($request->created_at)->format('d.m.y H:i') }}</td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-success btn-sm accept-btn"
                                                        data-id="{{ $request->id }}"
                                                        data-name="{{ $personal->l_name ?? '' }} {{ $personal->f_name ?? '' }}"
                                                        data-url="{{ route('users.accept-request', $request->id) }}"
                                                        {{ !$canAddUsers ? 'disabled' : '' }}
                                                        @if(!$canAddUsers)
                                                            title="{{ __('field.user_limit_reached') }}"
                                                    @endif>
                                                    <i class="ik ik-check"></i> {{ __('action.accept') }}
                                                </button>

                                                <button type="button"
                                                        class="btn btn-danger btn-sm reject-btn ml-1"
                                                        data-id="{{ $request->id }}"
                                                        data-name="{{ $personal->l_name ?? '' }} {{ $personal->f_name ?? '' }}"
                                                        data-url="{{ route('users.reject-request', $request->id) }}">
                                                    <i class="ik ik-x"></i> {{ __('action.reject') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6"
                                                class="text-center">{{ __('field.no_pending_requests') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endunless
    </div>

    <div class="modal fade" id="acceptModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="ik ik-check-circle"></i> Foydalanuvchini qabul qilish</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="acceptForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p><strong id="acceptUserName"></strong> ni tashkilotga qo'shishni tasdiqlaysizmi?</p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">{{ __('field.department') }} <span
                                            class="text-danger">*</span></label>
                                    <select name="dep_id" id="departmentSelect" class="form-control select2" required
                                            style="width: 100%;">
                                        <option value="">{{ __('action.select') }} {{ __('field.department') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">
                                                {{ $department->lang($department->id) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">{{ __('field.position') }}</label>
                                    <select name="post_id" id="positionSelect" class="form-control select2"
                                            style="width: 100%;">
                                        <option value="">{{ __('action.select') }} {{ __('field.position') }}</option>

                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">
                            <i class="ik ik-x"></i> Bekor qilish
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="ik ik-check"></i> Ha, qabul qilish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="ik ik-x-circle"></i> Tasdiqlash</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong id="rejectUserName"></strong> ning so'rovini rad etishni tasdiqlaysizmi?</p>
                    <p>Bu amalni keyincha qaytarib bo'lmaydi.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        <i class="ik ik-x"></i> Bekor qilish
                    </button>
                    <form id="rejectForm" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="ik ik-trash"></i> Ha, rad etish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "{{ __('action.select') }}",
                allowClear: true
            });

            $('#departmentSelect').on('change', function () {
                let depId = $(this).val();

                if (depId) {
                    $.ajax({
                        url: '{{ route('admin.get-positions', ':id') }}'.replace(':id', depId),
                        method: 'GET',
                        success: function (data) {
                            let $positionSelect = $('#positionSelect');
                            $positionSelect.empty();
                            $positionSelect.append('<option value="">{{ __('action.select') }} {{ __('field.position') }}</option>');

                            $.each(data.positions, function (index, position) {
                                $positionSelect.append('<option value="' + position.id + '">' + position.name + '</option>');
                            });

                            $positionSelect.trigger('change');
                        }
                    });
                } else {
                    $('#positionSelect').empty().append('<option value="">{{ __('action.select') }} {{ __('field.position') }}</option>');
                }
            });

            $('.accept-btn').on('click', function () {
                let name = $(this).data('name').trim() || 'Foydalanuvchi';
                let url = $(this).data('url');

                $('#acceptUserName').text(name);
                $('#acceptForm').attr('action', url);

                $('#departmentSelect').val(null).trigger('change');
                $('#positionSelect').empty().append('<option value="">{{ __('action.select') }} {{ __('field.position') }}</option>');

                $('#acceptModal').modal('show');
            });

            $('.reject-btn').on('click', function () {
                let name = $(this).data('name').trim() || 'Foydalanuvchi';
                let url = $(this).data('url');

                $('#rejectUserName').text(name);
                $('#rejectForm').attr('action', url);
                $('#rejectModal').modal('show');
            });
        });
    </script>
@endsection
