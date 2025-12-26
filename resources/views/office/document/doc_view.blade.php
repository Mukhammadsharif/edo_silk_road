@extends('layouts.app_create_doc')
@section('content')

    <div class="container-fluid">

        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-edit bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('action.edit_document') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    @include('templates.alert')
                </div>
                <div class="col-md-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('field.document') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 offset-1">
                <div class="card">
                    <div class="card-body">
                        <input type="text" id="doc_key" value="">
                        <button class="btn btn-outline-secondary docView">Doc view</button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/ajax/jquery.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.docView').on('click', function () {

                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                let key = $('#doc_key').val();

                let url = '/office/doc/view/' + key + '?_token=' + CSRF_TOKEN;

                $.ajax({
                    url: url,
                    type: 'GET',
                    //data: {_token: CSRF_TOKEN, key: key},
                    dataType: 'JSON',
                    beforeSend: function(){
                    },
                    success: function(response){
                        console.log(response);
                        const parsedData = JSON.parse(response);
                        console.log(parsedData.fileUrl);
                    },

                    error: function (data) {
                        console.log(data)
                    }
                });
            });
        });
    </script>
@endsection
