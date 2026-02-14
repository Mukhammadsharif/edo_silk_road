<div class="comments-card-modern">
    <div class="comments-header-modern">
        <i data-lucide="message-square" class="text-white"></i>
        <h3>{{ __('document.comment_history') }}</h3>
    </div>

    <div class="comments-body-modern">
        <div class="comments-list-scroll">
            @foreach($documentComments as $key => $comment)
                <div class="comment-item-modern">
                    <div class="comment-avatar">
                        <i data-lucide="user" class="text-white"></i>
                    </div>
                    <div class="comment-content">
                        <div class="comment-author">
                            @php($user_fio = $comment->createdUser->personal??0)
                            @if($user_fio)
                                {{ $user_fio->shortName() }}
                            @endif
                        </div>
                        <div class="comment-position">
                            @php($department = $comment->createdUser->department??0)
                            @if($department)
                                {{ $comment->createdUser->department->lang($comment->createdUser->department->id) }}
                            @endif
                            -
                            @php($position = $comment->createdUser->position??0)
                            @if($position)
                                {{ $comment->createdUser->position->lang($comment->createdUser->position->id) }}
                            @endif
                        </div>
                        <div class="comment-text">
                            {!! $comment->doc_text !!}
                        </div>
                        <div class="comment-meta">
                            <span class="comment-time">
                                <i data-lucide="clock"></i>
                                {{ date('d.m.Y, H:i', strtotime($comment->created_date)) }}
                            </span>
                            @if($comment && $comment->commentUsers && $comment->commentUsers->count())
                                <button type="button" class="comment-views-btn" onclick="showCommentUsers({{ $comment->id }})">
                                    <i data-lucide="eye"></i>
                                    <span>{{ __('field.views') ?? 'Просмотры' }}</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="comment-form-section">
            <button type="button" class="btn-modern btn-success" id="openForm">
                <i data-lucide="message-circle" class="icon"></i>
                <span>{{ __('action.to_leave_comment') }}</span>
            </button>

            <form id="commentForm" name="commentForm">
                @csrf
                <div class="form-editor-modern">
                    <div class="form-header">
                        <i data-lucide="message-circle" class="text-white"></i>
                        <h5>{{ __('field.leave_comment') }}</h5>
                    </div>
                    <textarea name="r_text" id="div_editor4" required></textarea>
                    <input name="com_doc_id" value="{{ $comment_inputs['doc_id'] }}" hidden>
                    <input name="com_doc_hash" value="{{ $comment_inputs['doc_hash'] }}" hidden>
                    <input name="com_doc_type" value="{{ $comment_inputs['doc_type'] }}" hidden>

                    <div class="form-actions">
                        <button type="button" class="btn-modern btn-danger" id="closeForm">
                            <i data-lucide="x"></i>
                            <span>{{ __('action.cancel') }}</span>
                        </button>
                        <button type="submit" class="btn-modern btn-primary">
                            <i data-lucide="send" class="text-white"></i>
                            <span>{{ __('action.send') }}</span>
                        </button>
                    </div>

                    <div class="result-message" id="resultMessage"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Modal -->
@include('protocol.comment_user_modal')

<link rel="stylesheet" href="{{ asset('css/templates/templates/comment_form.css') }}">

<script>
    let editor4cfg = {}
    editor4cfg.toolbar = "mytoolbar";
    editor4cfg.toolbar_mytoolbar = "{bold,italic}|{justifyleft,justifycenter,justifyright}|{fontname,fontsize}|{forecolor,backcolor}";
    let editor4 = new RichTextEditor("#div_editor4", editor4cfg);

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

<script type="text/javascript">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#commentForm').hide();

        $('#openForm').on('click', function () {
            $('#commentForm').show();
            $('.form-editor-modern').slideDown(300);
            $(this).hide();
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        $('#closeForm').on('click', function () {
            $('#commentForm').hide();
            $('.form-editor-modern').slideUp(300);
            $('#openForm').show();
        });

        $('#commentForm').on('submit', function (e) {
            $('#resultMessage').empty();
            e.preventDefault();

            // Add loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalHtml = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i data-lucide="loader" class="spinning"></i><span>Yuborilmoqda...</span>');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            $.ajax({
                url: "{{ route('doc.comment.send', [$document->id, $document->doc_hash]) }}",
                method: 'POST',
                data: $(this).serialize(),
            }).done(function (response) {
                console.log(response);
                let result_message = '';
                if (response.success === 200) {
                    result_message = `<div class="alert alert-success" role="alert"><i data-lucide="check-circle"></i><span>${response.message}</span></div>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    result_message = `<div class="alert alert-danger" role="alert"><i data-lucide="alert-circle"></i><span>${response.message}</span></div>`;
                }

                $('#resultMessage').html(result_message);
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

            }).fail(function (err) {
                console.log(err);
                submitBtn.prop('disabled', false).html(originalHtml);
                $('#resultMessage').html('<div class="alert alert-danger" role="alert"><i data-lucide="alert-circle"></i><span>Xatolik yuz berdi</span></div>');
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        });
    });

    function showCommentUsers(id) {
        let routeUrl = "{{ route('doc.comment.get', ':id') }}";
        let url = routeUrl.replace(':id', id);

        $.ajax({
            url: url,
            method: 'GET',
        }).done(function (response) {
            if (response.success === 200) {
                let comments = '';

                if(response.comments?.length) {
                    response.comments.forEach((item, index) => {
                        const viewStatus = item?.status === 2
                            ? formatDate(new Date(item?.time))
                            : '<i data-lucide="eye-off" style="color: #ef4444;"></i>';

                        comments += `<p>${++index}. ${item?.user} <span>${viewStatus}</span></p>`;
                    });
                }

                $('.modal-comments').html(comments);
                $('#commentModal').modal('show');

                // Reinitialize icons after content load
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }).fail(function (err) {
            console.log(err);
        });
    }

    function formatDate(date) {
        let hours = String(date.getHours()).padStart(2, '0');
        let minutes = String(date.getMinutes()).padStart(2, '0');
        let day = String(date.getDate()).padStart(2, '0');
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let year = date.getFullYear();

        return `${day}.${month}.${year} ${hours}:${minutes}`;
    }
</script>

<style>
    /* Spinning animation for loading icon */
    @keyframes spinning {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .spinning {
        animation: spinning 1s linear infinite;
    }
</style>
