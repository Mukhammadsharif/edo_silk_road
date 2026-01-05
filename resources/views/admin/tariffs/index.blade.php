@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-package bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.tariffs') }}</h5>
                            <span>{{ __('menu.list') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ik ik-home"></i></a></li>
                            <li class="breadcrumb-item active">{{ __('menu.tariffs') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3>{{ __('menu.tariffs') }}</h3>
                        <a href="{{ route('tariffs.create') }}" class="btn btn-primary">
                            <i class="ik ik-plus"></i> {{ __('action.create') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @include('templates.alert')
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('field.code') }}</th>
                                    <th>{{ __('field.name') }}</th>
                                    <th>{{ __('field.max_users') }}</th>
                                    <th>{{ __('field.max_documents') }}</th>
                                    <th>{{ __('field.price_monthly') }}</th>
                                    <th>{{ __('field.price_yearly') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                    <th>{{ __('field.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $item)
                                    <tr>
                                        <td>{{ $models->firstItem() + $key }}</td>
                                        <td><strong>{{ $item->code }}</strong></td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->max_users }}</td>
                                        <td>{{ $item->max_documents }}</td>
                                        <td>{{ number_format($item->price_monthly) }} so'm</td>
                                        <td>{{ number_format($item->price_yearly) }} so'm</td>
                                        <td>{!! $item->getStatusBadgeAttribute() !!}</td>
                                        <td class="d-flex flex-row justify-content-around">
                                            <a href="{{ route('tariffs.edit', $item->id) }}" class="btn btn-info btn-sm">
                                                <i class="ik ik-edit ml-1"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                    data-id="{{ $item->id }}"
                                                    data-name="{{ $item->name }}">
                                                <i class="ik ik-trash ml-1"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ __('field.not_found') }}</td>
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
    </div>

    <div class="modal fade" id="deleteTariffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="ik ik-alert-octagon"></i> O'chirishni tasdiqlash</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong id="deleteTariffName"></strong> tarifini o'chirishni tasdiqlaysizmi?</p>
                    <p class="text-danger"><strong>Diqqat!</strong> Bu amalni ortga qaytarib bo'lmaydi.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        <i class="ik ik-x"></i> Bekor qilish
                    </button>
                    <form id="deleteTariffForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="ik ik-trash"></i> Ha, o'chirish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('.delete-btn').on('click', function () {
                let id   = $(this).data('id');
                let name = $(this).data('name').trim() || 'Tarif';

                $('#deleteTariffName').text(name);
                $('#deleteTariffForm').attr('action', '{{ route("tariffs.destroy", ":id") }}'.replace(':id', id));

                $('#deleteTariffModal').modal('show');
            });
        });
    </script>
@endsection
