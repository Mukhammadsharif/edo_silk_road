<div class="card">
    <div class="card-header">
        <h5><i class="ik ik-more-vertical"></i> {{ __('document.comment_history') }}</h5>
    </div>

    <div class="card-body chat-box scrollable">
        <div style="max-height: 400px; overflow-y: auto;">
            @foreach($documentComments as $key => $comment)
                <div class="sl-item">
                    <div class="sl-left fw-600 f-16"><i class="ik ik-user"></i>
                        @php($user_fio = $comment->createdUser->personal??0)
                        @if($user_fio)
                            {{ $user_fio->shortName() }}
                        @endif
                    </div>
                    <div class="sl-right">
                        <p class="text-muted">
                            @php($department = $comment->createdUser->department??0)
                            @if($department)
                                {{ $comment->createdUser->department->lang($comment->createdUser->department->id) }}
                            @endif
                            -
                            @php($position = $comment->createdUser->position??0)
                            @if($department)
                                {{ $comment->createdUser->position->lang($comment->createdUser->position->id) }}
                            @endif
                        </p>
                        <p class="mt-2">
                            {!! $comment->doc_text !!}
                        </p>
                        <div class="like-comm mt-10">
                        <span>
                            <i class="ik ik-clock"></i> {{ date('d.m.Y, H:i', strtotime($comment->created_date)) }}
                        </span>

                            <span class="pull-right">
                            @if($comment && $comment->commentUsers && $comment->commentUsers->count())
                                    <i class="ik ik-eye ml-10" style="font-size: 18px; cursor: pointer"
                                       onclick="showCommentUsers({{ $comment->id }})"
                                    ></i>
                                    @include('protocol.comment_user_modal')
                                @endif
                        </span>
                        </div>
                    </div>
                </div>
                <hr>
            @endforeach
        </div>

        <div class="col-md-6">
            <button type="button" class="btn btn-outline-success" id="openForm">
                <span><i class="ik ik-message-circle"></i> {{ __('action.to_leave_comment') }}...</span>
            </button>
        </div>

        <form id="commentForm" name="commentForm">
            @csrf
            <div class="form-group row mt-10">
                <div class="col-md-12">
                    <div class="form-group">
                        <h5><i class="ik ik-message-circle"></i> {{ __('field.leave_comment') }}...</h5>
                        <textarea name="r_text" id="div_editor4" required></textarea>
                        <input name="com_doc_id" value="{{ $comment_inputs['doc_id'] }}" hidden>
                        <input name="com_doc_hash" value="{{ $comment_inputs['doc_hash'] }}" hidden>
                        <input name="com_doc_type" value="{{ $comment_inputs['doc_type'] }}" hidden>
                    </div>

                    <button type="button" class="btn btn-outline-danger mr-10" id="closeForm">
                        <span><i class="ik ik-slash"></i> {{ __('action.cancel') }}</span>
                    </button>

                    <button type="submit" class="btn btn-outline-primary">
                        <span><i class="ik ik-send"></i> {{ __('action.send') }}</span>
                    </button>

                    <div class="mt-10" id="resultMessage"></div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let editor4cfg = {}
    editor4cfg.toolbar = "mytoolbar";
    editor4cfg.toolbar_mytoolbar = "{bold,italic}|{justifyleft,justifycenter,justifyright}|{fontname,fontsize}|{forecolor,backcolor}";
    let editor4 = new RichTextEditor("#div_editor4", editor4cfg);
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
            $(this).hide();
        })

        $('#closeForm').on('click', function () {
            $('#commentForm').hide();
            $('#openForm').show();
        })

        $('#commentForm').on('submit', function (e) {
            $('#resultMessage').empty();
            e.preventDefault();
            $.ajax({
                url: "{{ route('doc.comment.send', [$document->id, $document->doc_hash]) }}",
                method: 'POST',
                data: $(this).serialize(),
                beforeSend: function () {},
            }).done(function (response) {
                console.log(response);
                let result_message = '';
                if (response.success === 200) {
                    result_message = `<div class="alert alert-success" role="alert"><i class="ik ik-check-circle"></i> `+response.message+`</div>`;
                    location.reload();
                } else {
                    result_message = `<div class="alert alert-success" role="alert"><i class="ik ik-alert-circle"></i> `+response.message+`</div>`;
                }

                $('#resultMessage').append(result_message);

            }).error(function (err) {
                console.log(err)
            })
        });
    });

    function showCommentUsers(id) {
        $('.mh-class').removeClass("bg-yellow").addClass("bg-green");

        let routeUrl = "{{ route('doc.comment.get', ':id') }}";

        let url = routeUrl.replace(':id', id);

        $.ajax({
            url: url,
            method: 'GET',
            beforeSend: function () {},
        }).done(function (response) {
            if (response.success === 200) {
                let comments = ``

                if(response.comments?.length) {

                    response?.comments.forEach((item, index) => {
                        comments = comments + `
                        <p>${++index}. ${item?.user}
                        ${item?.status === 2 ? formatDate(new Date(item?.time)) : '<i class="ik ik-eye-off" style="color: red"></i>'}
                        </p><br>`
                    })
                }

                $('.modal-comments').html(comments)

                $('#commentModal').modal('show');
            } else if (response.success === 201) {
                $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
            } else {
                $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
            }

        }).error(function (err) {
            console.log(err)
        })

    }

    function formatDate(date) {
        // Extract components from the date
        let hours = String(date.getHours()).padStart(2, '0');
        let minutes = String(date.getMinutes()).padStart(2, '0');
        //let seconds = String(date.getSeconds()).padStart(2, '0');
        let day = String(date.getDate()).padStart(2, '0');
        let month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() is zero-based
        let year = date.getFullYear();

        // Format as "hh:mm:ss dd.mm.yyyy"
        return `${day}.${month}.${year} ${hours}:${minutes}`;
    }
</script>
