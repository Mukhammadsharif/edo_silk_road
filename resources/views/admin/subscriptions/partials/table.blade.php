@if($subscriptions->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>#</th>
                <th>{{ __('field.organization') }}</th>
                <th>{{ __('field.tariff') }}</th>
                <th>{{ __('field.price_monthly') }}</th>
                <th>{{ __('field.start_date') }}</th>
                <th>{{ __('field.end_date') }}</th>
                <th>{{ __('field.status') }}</th>
                <th>{{ __('field.created_at') }}</th>
                @if($showActions ?? true)
                    <th>{{ __('field.actions') }}</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($subscriptions as $key => $req)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $req->organization->name_uz ?? 'Noma\'lum' }}</td>
                    <td><strong>{{ $req->tariff->name }}</strong></td>
                    <td>{{ number_format($req->tariff->price_monthly) }} so'm</td>
                    <td>{{ $req->starts_at?->format('d.m.Y') }}</td>
                    <td>{{ $req->ends_at?->format('d.m.Y') }}</td>
                    <td>
                        @switch($req->status)
                            @case(1)
                                @if($req->ends_at > now())
                                    <span class="badge badge-success">{{ __('field.active') }}</span>
                                @endif
                                @if($req->ends_at < now())
                                    <span class="badge badge-secondary ml-1">{{ __('field.expired') }}</span>
                                @endif
                                @break
                            @case(2)
                                <span class="badge badge-warning">{{ __('field.pending') }}</span>
                                @break
                            @case(0)
                                <span class="badge badge-danger">{{ __('field.rejected') }}</span>
                                @break
                        @endswitch
                    </td>
                    <td>{{ $req->created_at->format('d.m.Y H:i') }}</td>
                    @if($showActions ?? true)
                        <td>
                            @if($req->status == 2)
                                <button type="button" class="btn btn-success btn-sm approve-btn"
                                        data-id="{{ $req->id }}"
                                        data-org="{{ $req->organization->name_uz ?? 'Noma\'lum' }}"
                                        data-tariff="{{ $req->tariff->name }}">
                                    <i class="ik ik-check"></i> {{ __('field.approve') }}
                                </button>

                                <button type="button" class="btn btn-danger btn-sm reject-btn ml-1"
                                        data-id="{{ $req->id }}"
                                        data-org="{{ $req->organization->name_uz ?? 'Noma\'lum' }}"
                                        data-tariff="{{ $req->tariff->name }}">
                                    <i class="ik ik-x"></i> {{ __('field.reject') }}
                                </button>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
        {!! $subscriptions->links() !!}
    </div>
@else
    <div class="text-center py-5 text-muted">
        <i class="ik ik-package display-4 d-block mb-3"></i>
        <p>{{ __('field.no_data') }}</p>
    </div>
@endif
