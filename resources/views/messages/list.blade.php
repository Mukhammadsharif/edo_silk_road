@php use App\Models\Messages\MessageFiles; @endphp
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="fa fa-sitemap bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('field.messages') }} ({{ __('title.'.$type) }})</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('field.messages') }}</a></li>
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
                        <form action="{{ route('messages.list', ['type' => $type]) }}" method="POST" role="search"
                              class="form-inline w-100">
                            @csrf
                            <input type="text" name="s_text" placeholder="{{ __('field.subject') }}"
                                   value="{{ $s_text??'' }}" class="form-control w-25 mb-2 mr-sm-2">

                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_from" class="form-control my-bootstrap-datepicker"
                                       placeholder="{{ __('input.from_day') }}" value="{{ $date_from??'' }}"
                                       autocomplete="off">
                            </div>
                            <div class="mb-2 mr-sm-2">
                                <input type="text" name="date_till" class="form-control my-bootstrap-datepicker"
                                       placeholder="{{ __('input.till_day') }}" value="{{ $date_till??'' }}"
                                       autocomplete="off">
                            </div>

                            @if(!in_array($type, ['deleted', 'outbox']))
                                <div class="mb-2 mr-sm-2">
                                    <select name="created_user" class="form-control select2">
                                        <option value="">{{ __('field.users') }}</option>
                                        @forelse($users as $key => $item)
                                            <option value="{{ $item->id }}"
                                                    {{ ($created_user == $item->id ? "selected":"") }}
                                            >
                                                {{ $item->personal->shortName()??'' }}
                                            </option>
                                        @empty
                                            <option>{{ __('field.not_found') }}</option>
                                        @endforelse
                                    </select>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-info mb-2 mr-10">
                                <i data-lucide="search"></i>
                                {{ __('action.search') }} ({{ $models->total() }})
                            </button>
                            <a href="{{ route('messages.list', ['type' => $type]) }}" class="btn btn-light mb-2">
                                <i data-lucide="rotate-ccw"></i> {{ __('action.clear') }}
                            </a>
                        </form>
                    </div>
                    <div class="card-body template-demo">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr class="text-center">
                                    <th>{{ __('field.#') }}</th>
                                    <th>{{ __('field.subject') }}</th>
                                    <th>{{ __('field.date') }}</th>
                                    <th>{{ __('field.files') }}</th>
                                    @if($type == 'outbox')
                                        <th>{{ __('field.actions') }}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($models as $key => $item)
                                    <tr class="text-center">
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            <a href="{{ route('messages.show', [$item->id, $item->hash_code]) }}"
                                               class="text-primary">
                                                {{ $item->subject??__('field.absent') }}
                                            </a>
                                        </td>
                                        <td>{{ date('d.m.Y, H:i', strtotime($item->created_at)) }}</td>
                                        <td>
                                            @php
                                                $files = MessageFiles::where('message_id', $item->id)->get();
                                            @endphp
                                            @forelse($files as $file)
                                                <div>
                                                    <a href="{{ route('messages.file.pre-view',['id'=>$file->id]) }}"
                                                       target="_blank" class="link text-info"
                                                       onclick="window.open('<?php echo('/messages/file/pre-view/' . $file->id); ?>',
                                                   'modal',
                                                   'width=800,height=900,top=30,left=500');
                                                   return false;"><i
                                                                class="fa {{ strtoupper($file->file_ext) == 'PDF' ? 'fa-file-pdf' : (in_array(strtoupper($file->file_ext), ['PNG', 'JPG', 'JPEG']) ? 'fa-file-image' : 'fa-file') }}"></i>
                                                        {{ Str::limit($file->file_name, 20) }}
                                                    </a>
                                                    <a href="{{ route('messages.file.download', $file->id) }}"
                                                       class="text-primary ml-2"
                                                       title="Download {{ $file->file_name }}">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                    @if($type == 'outbox')
                                                        <button type="button"
                                                                class="text-danger ml-2 delete-file-btn btn-sm"
                                                                data-id="{{ $file->id }}" data-type="file"
                                                                title="Delete {{ $file->file_name }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @empty
                                                {{ __('field.absent') }}
                                            @endforelse
                                        </td>
                                        @if($type == 'outbox')
                                            <td>
                                                <button type="button" class="btn btn-danger delete-message-btn"
                                                        data-id="{{ $item->id }}" data-type="message"
                                                        title="Delete Message">
                                                    <i class="fa fa-trash"></i> {{ __('action.delete') }}
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('field.not_found') }}</td>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="DeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger d-flex flex-row justify-content-end">
                    <i class="ik ik-x-circle text-white cancel_delete_btn" style="font-size: 20px; cursor: pointer"></i>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <lord-icon src="/build/json/trash-bin.json" trigger="loop"
                                   colors="primary:#0ab39c,secondary:#405189"
                                   style="width:120px;height:120px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15">
                            <h4>{{ __('text.delete_confirm') }}</h4>
                            <p class="text-muted mx-4 mb-0"><span
                                        class="ri-question-line me-1"></span>{{ __('text.delete_confirm_text') }}</p>
                        </div>
                    </div>
                    <input type="hidden" id="deleting_id">
                    <input type="hidden" id="deleting_type">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light cancel_delete_btn" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>{{ __('action.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger yes_delete_btn">
                        <i class="ri-check-line me-1"></i>{{ __('action.yes_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete Modal -->

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.delete-file-btn, .delete-message-btn').click(function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                var type = $(this).data('type');
                $('#DeleteModal').modal('show');
                $('#deleting_id').val(id);
                $('#deleting_type').val(type);
            });

            $('.yes_delete_btn').click(function (e) {
                e.preventDefault();
                var id = $('#deleting_id').val();
                var type = $('#deleting_type').val();
                var url = type === 'file' ? '{{ url("messages/file/del") }}/' + id : '/messages/delete/' + id;

                $(this).text('Deleting...');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        if (response.res == 2 || response.res == 1) {
                            $('#success_message').addClass('alert alert-success');
                            $('#success_message').text(response.message);
                            $('#DeleteModal').modal('hide');
                            $('.yes_delete_btn').text('Yes Delete');
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            $('#success_message').addClass('alert alert-danger');
                            $('#success_message').text(response.message);
                            $('.yes_delete_btn').text('Yes Delete');
                        }
                    },
                    error: function () {
                        $('#success_message').addClass('alert alert-danger');
                        $('#success_message').text('{{ __('error_occurred') }}');
                        $('.yes_delete_btn').text('Yes Delete');
                    }
                });
            });

            $('.cancel_delete_btn').click(function (e) {
                $('#DeleteModal').modal('hide');
            });
        });
    </script>
@endsection
