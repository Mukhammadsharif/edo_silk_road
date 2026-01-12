@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-package bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('field.all_subscriptions') }}</h5>
                            <span>{{ __('field.active_pending_rejected_expired') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('field.subscription_management') }}</h3>
                    </div>
                    <div class="card-body">
                        @include('templates.alert')

                        <ul class="nav nav-tabs mb-4" id="subscriptionTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                                    {{ __('field.pending') }}
                                    <span class="badge badge-warning ml-1">{{ $pendingCount }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="active-tab" data-toggle="tab" href="#active" role="tab">
                                    {{ __('field.active') }}
                                    <span class="badge badge-success ml-1">{{ $activeCount }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="expired-tab" data-toggle="tab" href="#expired" role="tab">
                                    {{ __('field.expired') }}
                                    <span class="badge badge-secondary ml-1">{{ $expiredCount }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab">
                                    {{ __('field.rejected') }}
                                    <span class="badge badge-danger ml-1">{{ $rejectedCount }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" id="subscriptionTabContent">
                            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                                @include('admin.subscriptions.partials.table', ['subscriptions' => $pending])
                            </div>

                            <div class="tab-pane fade" id="active" role="tabpanel">
                                @include('admin.subscriptions.partials.table', ['subscriptions' => $active])
                            </div>

                            <div class="tab-pane fade" id="expired" role="tabpanel">
                                @include('admin.subscriptions.partials.table', ['subscriptions' => $expired, 'showActions' => false])
                            </div>

                            <div class="tab-pane fade" id="rejected" role="tabpanel">
                                @include('admin.subscriptions.partials.table', ['subscriptions' => $rejected, 'showActions' => false])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.subscriptions.partials.modals')

    <script>
        $(document).ready(function () {
            $('.approve-btn').on('click', function () {
                let id = $(this).data('id');
                let org = $(this).data('org');
                let tariff = $(this).data('tariff');

                $('#approveOrgName').text(org);
                $('#approveTariffName').text(tariff);
                $('#approveForm').attr('action', '{{ route("admin.subscription-requests.approve", ":id") }}'.replace(':id', id));

                $('#approveModal').modal('show');
            });

            $('.reject-btn').on('click', function () {
                let id = $(this).data('id');
                let org = $(this).data('org');
                let tariff = $(this).data('tariff');

                $('#rejectOrgName').text(org);
                $('#rejectTariffName').text(tariff);
                $('#rejectForm').attr('action', '{{ route("admin.subscription-requests.reject", ":id") }}'.replace(':id', id));

                $('#rejectModal').modal('show');
            });
        });
    </script>
@endsection
