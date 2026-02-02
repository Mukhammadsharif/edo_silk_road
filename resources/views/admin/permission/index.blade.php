@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-user bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.roles') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('menu.roles') }}</a>
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
                        <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                            <i data-lucide="circle-plus"></i>{{ __('action.create') }}
                        </a>
                    </div>
                    <div class="card-body template-demo">
                        <div class="dt-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr class="text-center">
                                        <th>{{ __('field.number') }}</th>
                                        <th>{{ __('field.title') }}</th>
                                        <th>{{ __('field.name') }}</th>
                                        <th>{{ __('field.status') }}</th>
                                        <th>{{ __('field.parent_id') }}</th>
                                        <th colspan="2">{{ __('field.actions') }}</th>
                                        <th>{{ __('field.created_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($models as $key => $model)
                                    <tr id="rowID{{ $model->id }}" class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $model->name }}</td>
                                        <td>{{ $model->lang($model->lang_id) }}</td>
                                        <td>
                                            <span class="badge badge-pill @if($model->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $model->isActive($model->id) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-pill badge-secondary mb-1">
                                                {{ $model->parent->name??'-' }}
                                            </span>
                                        </td>
                                        <td colspan="2">
                                            @can('permission-edit')
                                                <a href="{{ url('admin/permissions/'.$model->id.'/edit') }}"
                                                   class="btn btn-sm btn-info">
                                                    <i data-lucide="square-pen"></i> {{ __('action.edit') }}
                                                </a>
                                            @endcan

                                            @can('permission-delete')
                                                <button value="{{ $model->id }}"
                                                        class="btn btn-sm btn-danger delete_btn">
                                                    <i class="ik ik-trash-2"></i> {{ __('action.delete') }}
                                                </button>
                                            @endcan
                                        </td>
                                        <td>{{ date('d.m.y H:i', strtotime($model->created_at)) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {!! $models->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>O`chirish</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><i class="ik ik-message-circle"></i> Siz, tanlagan yozuvlar o`chiriladi!</p>
                    <input type="hidden" id="deleteing_id">
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ik ik-x"></i>{{ __('action.close') }}
                    </button>
                    <button type="button" class="btn btn-danger yes_delete_btn">
                        <i class="ik ik-trash-2"></i>{{ __('action.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script>

        $(document).on('click', '.delete_btn', function () {
            let model_id = $(this).val();
            $('#DeleteModal').modal('show');
            $('#deleteing_id').val(model_id);
        });

        $(document).on('click', '.yes_delete_btn', function (e) {
            e.preventDefault();

            $(this).text('Deleting..');
            let id = $('#deleteing_id').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "DELETE",
                url: "/admin/permissions/" + id,
                dataType: "json",
                success: function (response) {
                    // console.log(response);
                    $('#DeleteModal').modal('hide');
                    if (response.status === 200) {
                        $("#rowID"+id).addClass("table-danger").fadeOut(2000, function () {
                            this.remove();
                        });
                        showSuccessMessageInTopCenter('top-center')
                    } else {
                        showErrorMessageInTopCenter('top-center')
                    }
                }
            });
        });
    </script>
@endsection
