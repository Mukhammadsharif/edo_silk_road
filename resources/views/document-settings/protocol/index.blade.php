@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="page-header-title">
                        <i class="ik ik-file bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('menu.prt_doc_types') }}</h5>
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
                                <a href="#">{{ __('menu.prt_doc_types') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('menu.list') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('menu.prt_doc_types') }}</h3>
                    </div>
                    <div class="card-body">
                        @foreach($parentDocTypes as $key => $item)
                            <div class="alert alert-primary" role="alert">
                                {{ $key + 1 }}. {{ $item->lang($item->id) }}
                                @if(count($item->child))
                                    @include('document-settings.protocol.manageChild',['childs' => $item->child])
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-header d-block">
                        <a href="{{ route('protocol-types.create') }}" class="btn btn-primary">
                            <i class="ik ik-plus-circle"></i> {{ __('action.create') }}
                        </a>
                    </div>
                    <div class="card-body template-demo">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr class="text-center">
                                <th>{{ __('field.number') }}</th>
                                <th>{{ __('field.title') }}</th>
                                <th>{{ __('field.doc_code') }}</th>
                                <th>{{ __('field.role_id') }}</th>
                                <th>{{ __('field.order_number') }}</th>
                                <th>{{ __('field.status') }}</th>
                                <th>{{ __('field.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($models as $key => $model)
                                <tr id="rowID{{ $model->id }}" class="text-center">
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        <span class="text-primary">{{ $model->lang($model->parent_id) }}
                                            <i class="ik ik-corner-right-down"></i>
                                        </span><br>
                                        {{ $model->lang($model->id) }}
                                    </td>
                                    <td>{{ $model->doc_code }}</td>
                                    <td>{{ $model->roleID->name??'-' }}</td>
                                    <td>{{ $model->order_number }}</td>
                                    <td>
                                            <span
                                                class="badge badge-pill @if($model->status == 1) badge-green @else badge-danger @endif text-white">
                                                {{ $model->isActive($model->status) }}
                                            </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('protocol-types.edit', $model->doc_hash) }}"
                                           class="btn btn-sm btn-info">
                                            <i class="ik ik-edit-1"></i> {{ __('action.edit') }}
                                        </a>
                                    </td>
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
                url: "/protocol/prt-doc-types/" + id,
                dataType: "json",
                success: function (response) {
                    // console.log(response);
                    $('#DeleteModal').modal('hide');
                    if (response.status === 200) {
                        $("#rowID" + id).addClass("table-danger").fadeOut(2000, function () {
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
