@if($subscriptions->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>#</th>
                <th>Tashkilot</th>
                <th>Tarif</th>
                <th>Narxi (oylik)</th>
                <th>Boshlanish</th>
                <th>Tugash</th>
                <th>Holati</th>
                <th>Yaratilgan</th>
                @if($showActions ?? true)
                    <th>Harakat</th>
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
                                <span class="badge badge-success">Faol</span>
                                @if($req->ends_at < now())
                                    <span class="badge badge-secondary ml-1">Muddat tugagan</span>
                                @endif
                                @break
                            @case(2)
                                <span class="badge badge-warning">Kutilmoqda</span>
                                @break
                            @case(0)
                                <span class="badge badge-danger">Rad etilgan</span>
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
                                    <i class="ik ik-check"></i> Tasdiqlash
                                </button>

                                <button type="button" class="btn btn-danger btn-sm reject-btn ml-1"
                                        data-id="{{ $req->id }}"
                                        data-org="{{ $req->organization->name_uz ?? 'Noma\'lum' }}"
                                        data-tariff="{{ $req->tariff->name }}">
                                    <i class="ik ik-x"></i> Rad etish
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
        <p>Bu bo'limda hozircha ma'lumot yo'q</p>
    </div>
@endif
