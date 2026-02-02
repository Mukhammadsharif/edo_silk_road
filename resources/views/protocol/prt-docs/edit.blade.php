@extends('layouts.app')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="page-header-title">
                        <i class="ik ik-file-text bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ $document->docType->lang($document->docType->id) }}</h5>
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
                                <a href="{{ url('protocol/secretary/view', [$document->id, $document->doc_hash]) }}">
                                    {{ $document->doc_name }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.edit_document') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-1">
                <div class="card">
                    <div class="card-header">
                        <h3><i data-lucide="square-pen"></i> {{ __('action.edit_document') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('prt-secretary-update', [$document->id, $document->doc_hash]) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{ __('field.doc_name') }} <span class="text-red">*</span></label>
                                        <input type="text" name="doc_name" class="form-control {{ $errors->has('doc_name') ? 'is-invalid' : '' }}"
                                               value="{{ $document->doc_name }}" placeholder="{{__('field.doc_name')}}"/>
                                        {!! $errors->first('doc_name', '<p class="text-danger">:message</p>') !!}
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>{{ __('field.doc_number') }}</label>
                                        <input type="text" name="doc_number" class="form-control"
                                               value="{{ $document->doc_number }}" placeholder="{{ __('field.doc_number') }}">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>{{ __('field.doc_date') }}</label>
                                        <input type="date" name="doc_date" class="form-control {{ $errors->has('doc_date') ? 'is-invalid' : '' }}"
                                               value="{{ date('Y-m-d', strtotime($document->doc_date)) }}"/>
                                        {!! $errors->first('doc_date', '<p class="text-danger">:message</p>') !!}
                                    </div>
                                </div>
                            </div>

{{--                            <div class="form-group row">--}}
{{--                                <div class="col-sm-12">--}}
{{--                                    <label>{{ __('field.doc_text') }} <span class="text-red">*</span></label>--}}
{{--                                    --}}{{--<textarea name="doc_text" id="div_editor1"></textarea>--}}
{{--                                    <div class="u-flow-xs">--}}
{{--                                        <div class="b-demo__editor" id="b-demo-editor">--}}
{{--                                            <div class="js-editor-wrapper" data-demo-type="feature-rich">--}}
{{--                                                <textarea name="doc_text" id="cke5-feature-rich-demo" style="display: none;">{!! $documentText->doc_text !!}</textarea>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    {!! $errors->first('doc_text', '<p class="text-danger">:message</p>') !!}--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="form-group">
                                <span class="text-muted mb-10">{{ __('field.doc_files') }}</span>
                                @forelse($documentFiles as $key => $file)
                                    <div class="sl-item">
                                        <div class="sl-right" id="file_id_{{ $file->id }}">
                                            @switch(strtolower($file->file_ext))
                                                @case('jpg')
                                                @case('jpeg')
                                                @case('png')
                                                    <a href="#" target="_blank" class="link"
                                                       onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>',
                                                           'modal',
                                                           'width=800,height=900,top=30,left=500');
                                                           return false;">
                                                        {{ $file->file_name }}
                                                    </a>
                                                    <div class="like-comm float-right">
                                                        <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}"
                                                           class="link text-secondary"><i data-lucide="download"></i>{{ __('action.download') }}
                                                        </a>
                                                        <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                                        <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                            <i class="ik ik-trash-2"></i>
                                                        </button>
                                                    </div>
                                                    @break
                                                @case('pdf')
                                                    <a href="#"
                                                       target="_blank" class="link"
                                                       onclick="window.open('<?php echo('/protocol/file/pre-view/' . $file->id); ?>',
                                                           'modal',
                                                           'width=800,height=900,top=30,left=500');
                                                           return false;">
                                                        {{ $file->file_name }}
                                                    </a>
                                                    <div class="like-comm float-right">
                                                        <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}"
                                                           class="link text-secondary"><i data-lucide="download"></i>{{ __('action.download') }}
                                                        </a>
                                                        <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                                        <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                            <i class="ik ik-trash-2"></i>
                                                        </button>
                                                    </div>
                                                    @break
                                                @default
                                                    <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}" class="link">
                                                        {{ $file->file_name }}
                                                    </a>
                                                    <div class="like-comm float-right">
                                                        <a href="{{ route('prt-document-file.download', ['id'=>$file->id]) }}"
                                                           class="link mr-10 text-secondary"> {{ $file->file_name }}
                                                            <i data-lucide="download"></i> {{ __('action.download') }}
                                                        </a>
                                                        <small class="text-muted">({{ number_format($file->file_size/1024, 2) }}KB)</small>
                                                        <button type="button" class="btn btn-icon btn-outline-danger ml-5 deleteItem" data-id="{{$file->id}}">
                                                            <i class="ik ik-trash-2"></i>
                                                        </button>
                                                    </div>
                                            @endswitch
                                        </div><hr>
                                    </div>
                                @empty
                                    <p class="text-left">-</p>
                                @endforelse
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label>{{ __('field.attach_file') }}</label>
                                    <div class="custom-file">
                                        <input type="file" name="doc_files[]" class="custom-file-input"
                                               id="customFile" multiple>
                                        <label class="custom-file-label"
                                               for="customFile">{{ __('field.upload_files') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <a href="{{ route('home') }}" class="btn btn-light">
                                        <i class="ik ik-x"></i> {{ __('action.cancel') }}
                                    </a>
                                    <button type="submit" id="submitBtn" class="btn btn-primary float-right">
                                        <i data-lucide="save"></i> {{ __('action.save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="ConfirmModal" class="modal fade">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h4 class="modal-title"><i class="ik ik-trash-2"></i>  {{ __('message.delete_confirm') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="text" name="item_conf_id" id="item_conf_id" hidden>
                        <h5><i class="ik ik-info"></i> {{ __('message.delete_you_want_doc') }}</h5>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary pull-left" data-dismiss="modal">{{ __('action.cancel') }}</button>
                        <button type="button" class="btn btn-warning deleteItemConfirm">{{ __('action.delete_yes') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="responseModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h4 class="modal-title">{{ __('message.success_message') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="responseModalLabel"></p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-lightt" data-dismiss="modal">
                            <i class="ik ik-x"></i> {{ __('action.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

{{--        @include('rich-editor.ckeditor5')--}}

        <script type="text/javascript">
            $('.deleteItem').on('click', function () {

                let file_id = $(this).data('id');

                $('#ConfirmModal').modal('show');

                $('#item_conf_id').val(file_id);

            });

            $('.deleteItemConfirm').on('click', function () {

                let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                let id = $('#item_conf_id').val();

                let url = '/protocol/file/delete/'+id;

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {_token: CSRF_TOKEN, id: id},
                    dataType: 'JSON',
                    beforeSend: function(){
                        $('#ConfirmModal').modal('toggle')
                    },
                    success: function(res){
                        $('#responseModalLabel').text(res.message)
                        $('#responseModal').modal('toggle')

                        if (res.res === 2) {
                            $('#file_id_'+res.id).hide();
                        } else {
                            $('#responseModalLabel').text('Error! Delete file');
                        }

                    },
                    complete:function(res){

                    },

                    error: function (data) {
                        console.log(data)
                    }
                });
            });

        </script>

    </div>
@endsection
