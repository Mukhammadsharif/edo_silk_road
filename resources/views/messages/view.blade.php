@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="fa fa-envelope bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('message.view') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('messages.list', ['type' => 'inbox']) }}">{{ __('field.messages') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('action.view') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Message Info -->
            <div class="col-md-2 message-info">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('field.card_info') }}</h3>
                    </div>
                    <div class="card-body">
                        <h6>{{ __('field.date') }}</h6>
                        <p>{{ \Carbon\Carbon::parse($message->created_at)->format('d.m.Y, H:i') }}</p>

                        <h6>{{ __('field.from') }}</h6>
                        <p>{{ $message->sender->personal->shortName() ?? '-' }}</p>

                        <h6>{{ __('field.files') }}</h6>
                        @forelse($message->files as $file)
                            <div>
                                <a href="{{ route('messages.file.pre-view',['id'=>$file->id]) }}"
                                   target="_blank" class="link text-info"
                                   onclick="window.open('<?php echo('/messages/file/pre-view/' . $file->id); ?>',
                                                   'modal',
                                                   'width=800,height=900,top=30,left=500');
                                                   return false;" class="text-info" title="Preview {{ $file->file_name }}">
                                    <i class="fa {{ strtoupper($file->file_ext) == 'PDF' ? 'fa-file-pdf' : (in_array(strtoupper($file->file_ext), ['PNG', 'JPG', 'JPEG']) ? 'fa-file-image' : 'fa-file') }}"></i>
                                    {{ Str::limit($file->file_name, 20) }}
                                </a>
                                <a href="{{ route('messages.file.download', $file->id) }}" class="text-primary ml-2" title="Download {{ $file->file_name }}">
                                    <i class="fa fa-download"></i>
                                </a>
{{--                                <a href="#" class="text-danger ml-2 delete-file" data-id="{{ $file->id }}" title="Delete {{ $file->file_name }}">--}}
{{--                                    <i class="fa fa-trash"></i>--}}
{{--                                </a>--}}
                            </div>
                        @empty
                            <p>{{ __('field.absent') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ $message->subject ?? '-' }}</h3>
                    </div>
                    <div class="card-body">
                        <h6>{{ __('field.text') }}</h6>
                        <div class="border p-3">{!! $message->text??'' !!}</div>
                    </div>
                    <div class="card-footer d-flex flex-row justify-content-end">
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#forwardModal">
                            <i class="ik ik-fast-forward"></i> {{ __('action.forward') }}
                        </button>
                        <button class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#replyModal">
                            <i class="ik ik-repeat"></i> {{ __('action.reply') }}
                        </button>
                    </div>
                </div>

                @if($message->replyMessage)
                    <div class="card">
                        <div class="card-body">
                            <h6>{{ __('field.main_message') }}</h6>
                            <div class="border p-3">{!! $message->replyMessage->text??'' !!}</div>
                        </div>

                        <div class="card-footer">
                            @forelse($message->replyMessage->files as $key => $file)
                                <div>
                                    {{ ++$key }}.
                                    <a href="{{ route('messages.file.pre-view',['id'=>$file->id]) }}"
                                       target="_blank" class="link text-info"
                                       onclick="window.open('<?php echo('/messages/file/pre-view/' . $file->id); ?>',
                                                   'modal',
                                                   'width=800,height=900,top=30,left=500');
                                                   return false;" class="text-info" title="Preview {{ $file->file_name }}">
                                        <i class="fa {{ strtoupper($file->file_ext) == 'PDF' ? 'fa-file-pdf' : (in_array(strtoupper($file->file_ext), ['PNG', 'JPG', 'JPEG']) ? 'fa-file-image' : 'fa-file') }}"></i>
                                        {{ Str::limit($file->file_name, 20) }}
                                    </a>
                                    <a href="{{ route('messages.file.download', $file->id) }}" class="text-primary ml-2" title="Download {{ $file->file_name }}">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            @empty
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('field.recipients') }}</h3>
                    </div>
                    <div class="card-body feeds-widget">
                        @forelse($recipients as $recipient)
                            <div class="feed-item">
                                <a href="#">
                                    <div class="feeds-left">
                                        @if($recipient->status == 1)
                                            <i class="ik ik-user text-default"></i>
                                        @elseif($recipient->status == 2)
                                            <i class="ik ik-check-circle text-success"></i>
                                        @else
                                            <i class="ik ik-alert-circle text-warning"></i>
                                        @endif
                                    </div>
                                    <div class="feeds-body">
                                        <h4 class="title text-primary">{{ $recipient->user->personal->shortName() ?? '-'  }}<small class="float-right text-muted">{{ $recipient->read_date ? \Carbon\Carbon::parse($recipient->read_date)->format('d.m.Y, H:i') : '' }}</small></h4>
                                        <small>{{ $recipient->user->userPost->lang($recipient->user->userPost->id)??'' }}</small>
                                    </div>
                                </a>
                            </div>
                        @empty
                            {{ __('field.no_recipients') }}
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forward Modal -->
    <div class="modal fade" id="forwardModal" tabindex="-1" role="dialog" aria-labelledby="forwardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forwardModalLabel">{{ __('action.forward_message') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="forwardForm">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('action.select_users') }} <span class="text-red">*</span></label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="searchResPerformers" placeholder="{{ __('action.search') }}...">
                            </div>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-hover mb-0 without-header">
                                    <tbody id="unSelectedUsers"></tbody>
                                </table>
                            </div>
                            <div id="selectedUsersContainer">
                                <input type="hidden" name="selected_users[]" id="selectedUsersInput" value="">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('action.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="forwardSubmit">{{ __('action.forward') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">{{ __('action.reply_message') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="replyForm">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('field.subject') }}</label>
                            <input type="text" id="subject" class="form-control" name="subject" value="Re: {{ $message->subject ?? '' }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('field.text') }}</label>
                            <textarea class="form-control" id="text" name="text" rows="5" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>{{ __('action.attach_file') }}</label>
                            <div id="file-inputs-container">
                                <div class="file-input-group d-flex flex-row align-items-baseline justify-content-between mb-2">
                                    <div class="custom-file" style="display: none;">
                                        <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-0">
                                        <label class="custom-file-label" for="customFile-0">{{ __('action.choose') }}</label>
                                    </div>
                                    <input type="text" class="form-control" readonly placeholder="{{ __('action.choose') }}"
                                           onclick="document.getElementById('customFile-0').click();" style="cursor: pointer;">

                                    <button type="button" class="btn btn-icon btn-outline-secondary btn-sm ms-2 ml-2 add-file-input">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('action.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="replySubmit">{{ __('action.send') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="loadModal modal fade" tabindex="-1" role="dialog" id="spinnerModal">
        <div class="modal-dialog modal-dialog-centered text-center" role="document">
            <span class="fa fa-spinner fa-spin fa-3x w-100"></span>
        </div>
    </div>

    <!-- Response (success, error) Modal -->
    <div class="modal fade" id="responseModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-content-class">
                    <h5 class="modal-title" id="resModalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 id="resModalMessage"></h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        {{ __('action.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

        <script>
            $(document).ready(function() {
                // Forward modal user list
                let allUsers = [];
                let selectedUserIds = [];

                $.ajax({
                    url: '{{ route("users.work-history.active") }}',
                    method: 'GET',
                    success: function(response) {
                        allUsers = response.users || [];
                        renderUsers(allUsers);
                    },
                    error: function(xhr) {
                        console.error('Error fetching users:', xhr);
                        $('#unSelectedUsers').html('<tr><td>{{ __('error_occurred') }}</td></tr>');
                    }
                });

                function renderUsers(users) {
                    const $tbody = $('#unSelectedUsers').empty();
                    users.forEach(user => {
                        if (!user.id || isNaN(parseInt(user.id))) {
                            console.warn(`Invalid user ID: ${user.id}, skipping user`);
                            return;
                        }
                        const isChecked = selectedUserIds.includes(parseInt(user.id)) ? 'checked' : '';
                        $tbody.append(`
                            <tr data-user-id="${user.id}">
                                <td>
                                    <label class="custom-control custom-checkbox" style="cursor: pointer">
                                        <input type="checkbox" class="custom-control-input select_all_child" value="${user.id}" ${isChecked}>
                                        <span class="custom-control-label">
                                            <img src="${user.image || ''}" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;" onerror="this.src='{{ asset('images/default.png') }}';">
                                            ${user.personal?.l_name || ''} ${user.personal?.f_name || ''}
                                            <span style="font-style: italic"> - (${user.position || ''})</span>
                                        </span>
                                    </label>
                                </td>
                            </tr>
                        `);
                    });
                    updateSelectedUsersInput();
                }

                function updateSelectedUsersInput() {
                    const $container = $('#selectedUsersContainer').empty();
                    if (selectedUserIds.length === 0) {
                        $container.append('<input type="hidden" name="selected_users[]" value="">');
                    } else {
                        selectedUserIds.forEach(id => {
                            $container.append(`<input type="hidden" name="selected_users[]" value="${id}">`);
                        });
                    }
                }

                $('#searchResPerformers').on('input', function() {
                    const query = $(this).val().toLowerCase();
                    const filteredUsers = allUsers.filter(user => {
                        const fullName = `${user.personal?.f_name || ''} ${user.personal?.m_name || ''} ${user.personal?.l_name || ''}`.toLowerCase();
                        return fullName.includes(query);
                    });
                    renderUsers(filteredUsers);
                });

                $(document).on('change', '.select_all_child', function() {
                    const userId = parseInt($(this).val());
                    if (isNaN(userId) || userId <= 0) {
                        console.warn(`Invalid user ID: ${$(this).val()}, ignoring`);
                        $(this).prop('checked', false);
                        return;
                    }
                    if ($(this).is(':checked')) {
                        if (!selectedUserIds.includes(userId)) {
                            selectedUserIds.push(userId);
                        }
                    } else {
                        selectedUserIds = selectedUserIds.filter(id => id !== userId);
                    }
                    updateSelectedUsersInput();
                });

                // Forward submit
                $('#forwardSubmit').click(function() {
                    if (selectedUserIds.length === 0) {
                        $('#resModalTitle').text('{{ __('message.error_message') }}');
                        $('#resModalMessage').text('{{ __('field.select_at_least_one_user') }}');
                        $('#responseModal').modal('toggle');
                        $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                        return;
                    }
                    $.ajax({
                        url: '{{ route('messages.forward', $message->id) }}',
                        type: 'POST',
                        data: $('#forwardForm').serialize(),
                        success: function(response) {
                            if (response.res === 1) {
                                $('#resModalTitle').text('{{ __('message.success_message') }}');
                                $('#resModalMessage').text(response.message);
                                $('#responseModal').modal('toggle');
                                $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                                $('#forwardModal').modal('hide');
                                selectedUserIds = [];
                                renderUsers(allUsers);
                            } else {
                                $('#resModalTitle').text('{{ __('message.error_message') }}');
                                $('#resModalMessage').text(response.message);
                                $('#responseModal').modal('toggle');
                                $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                                // alert(response.message);
                            }
                        },
                        error: function() {
                            $('#resModalTitle').text('{{ __('message.error_message') }}');
                            $('#resModalMessage').text('{{ __('error_occurred') }}');
                            $('#responseModal').modal('toggle');
                            $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        }
                    });
                });

                // Reply submit
                $('#replySubmit').click(function() {
                    let formData = new FormData();
                    formData.append('subject', $('#subject').val());
                    formData.append('text', $('#text').val());
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content')); // CSRF token

                    $('input[name="doc_files[]"]').each(function() {
                        const files = this.files;
                        for (let i = 0; i < files.length; i++) {
                            formData.append('doc_files[]', files[i]);
                        }
                    });

                    $.ajax({
                        url: '{{ route('messages.reply', $message->id) }}',
                        type: 'POST',
                        data: formData,
                        processData: false,     // 🚨 This is required
                        contentType: false,     // 🚨 This is also required
                        success: function(response) {
                            if (response.res === 1) {
                                $('#resModalTitle').text('{{ __('message.success_message') }}');
                                $('#resModalMessage').text(response.message);
                                $('#responseModal').modal('toggle');
                                $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                                $('#replyModal').modal('hide');
                                $('#replyForm')[0].reset();
                            } else {
                                $('#resModalTitle').text('{{ __('message.error_message') }}');
                                $('#resModalMessage').text(response.message);
                                $('#responseModal').modal('toggle');
                                $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                            }
                        },
                        error: function() {
                            $('#resModalTitle').text('{{ __('message.error_message') }}');
                            $('#resModalMessage').text('{{ __('error_occurred') }}');
                            $('#responseModal').modal('toggle');
                            $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                        }
                    });
                });
            });

            let fileInputCounter = 1;

            document.addEventListener('DOMContentLoaded', function () {
                // Handle Add Button Click
                document.querySelector('.add-file-input').addEventListener('click', function () {
                    const container = document.getElementById('file-inputs-container');
                    const newFileInputGroup = document.createElement('div');
                    newFileInputGroup.className = 'file-input-group d-flex align-items-center mb-2';

                    // Use local counter to ensure correct ID
                    const currentCounter = fileInputCounter;

                    // Create new file input
                    const newFileInputDiv = document.createElement('div');
                    newFileInputDiv.className = 'custom-file';
                    newFileInputDiv.style.display = 'none';
                    newFileInputDiv.innerHTML = `
        <input type="file" name="doc_files[]" class="custom-file-input" id="customFile-${currentCounter}">
        <label class="custom-file-label" for="customFile-${currentCounter}">{{ __('action.choose') }}</label>
    `;
                    newFileInputGroup.appendChild(newFileInputDiv);

                    // Create readonly text input to display file name
                    const textInput = document.createElement('input');
                    textInput.type = 'text';
                    textInput.className = 'form-control';
                    textInput.readOnly = true;
                    textInput.placeholder = "{{ __('action.choose') }}";
                    textInput.style.cursor = 'pointer';
                    newFileInputGroup.appendChild(textInput);

                    // Create Remove Button
                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-icon btn-outline-warning btn-sm ms-2 remove-file-input ml-2';
                    removeButton.innerHTML = '<i class="ik ik-minus"></i>';
                    {{--removeButton.innerHTML = '<i class="fas fa-trash"></i> {{ __("action.remove") }}';--}}
                    removeButton.addEventListener('click', function () {
                        newFileInputGroup.remove();
                    });
                    newFileInputGroup.appendChild(removeButton);

                    // Append to container before attaching event listeners
                    container.appendChild(newFileInputGroup);

                    // Now attach event listeners
                    const fileInput = newFileInputDiv.querySelector(`#customFile-${currentCounter}`);
                    textInput.onclick = () => {
                        const input = document.getElementById(`customFile-${currentCounter}`);
                        if (input) input.click();
                    };

                    // Update text input when file is selected
                    fileInput.addEventListener('change', function () {
                        textInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.upload_files') }}";
                    });

                    // Increment counter after everything is set
                    fileInputCounter++;
                });

                // Update initial file input text when file is selected
                const initialFileInput = document.getElementById('customFile-0');
                const initialTextInput = document.querySelector('.file-input-group .form-control');
                initialFileInput.addEventListener('change', function () {
                    initialTextInput.value = this.files.length > 0 ? this.files[0].name : "{{ __('action.upload_files') }}";
                });
            })
        </script>
@endsection
