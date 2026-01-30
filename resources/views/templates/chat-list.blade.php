<aside class="right-sidebar">
    <div class="sidebar-chat" data-plugin="chat-sidebar">
        <div class="sidebar-chat-info">
            <h6>{{ __('document.comment_history') }}</h6>
            <form class="mr-t-10">
                <div class="form-group">
                    <input type="text" class="form-control" id="chatListSearch" placeholder="{{ __('input.search') }}...">
                    <i data-lucide="search"></i>
                </div>
            </form>
        </div>
        <div class="chat-list">
            <div class="list-group row" id="chatList">
            </div>
        </div>
    </div>
</aside>

<div class="chat-panel" hidden>
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <a href="javascript:void(0);"><i class="ik ik-message-square text-success"></i></a>
            <span class="user-name">John Doe</span>
            <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
        </div>
        <div class="card-body">
            <div class="widget-chat-activity flex-1">
                <div class="messages">
                    <div class="message media reply">
                        <figure class="user--online">
                            <a href="#">
                                <img src="#" class="rounded-circle" alt="">
                            </a>
                        </figure>
                        <div class="message-body media-body">
                            <p>Epic Cheeseburgers come in all kind of styles.</p>
                        </div>
                    </div>
                    <div class="message media">
                        <figure class="user--online">
                            <a href="#">
                                <img src="#" class="rounded-circle" alt="">
                            </a>
                        </figure>
                        <div class="message-body media-body">
                            <p>Cheeseburgers make your knees weak.</p>
                        </div>
                    </div>
                    <div class="message media reply">
                        <figure class="user--offline">
                            <a href="#">
                                <img src="#" class="rounded-circle" alt="">
                            </a>
                        </figure>
                        <div class="message-body media-body">
                            <p>Cheeseburgers will never let you down.</p>
                            <p>They'll also never run around or desert you.</p>
                        </div>
                    </div>
                    <div class="message media">
                        <figure class="user--online">
                            <a href="#">
                                <img src="#" class="rounded-circle" alt="">
                            </a>
                        </figure>
                        <div class="message-body media-body">
                            <p>A great cheeseburger is a gastronomical event.</p>
                        </div>
                    </div>
                    <div class="message media reply">
                        <figure class="user--busy">
                            <a href="#">
                                <img src="#" class="rounded-circle" alt="">
                            </a>
                        </figure>
                        <div class="message-body media-body">
                            <p>There's a cheesy incarnation waiting for you no matter what you palete preferences are.</p>
                        </div>
                    </div>
                    <div class="message media">
                        <figure class="user--online">
                            <a href="#">
                                <img src="#" class="rounded-circle" alt="">
                            </a>
                        </figure>
                        <div class="message-body media-body">
                            <p>If you are a vegan, we are sorry for you loss.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form action="javascript:void(0)" class="card-footer" method="post">
            <div class="d-flex justify-content-end">
                <textarea class="border-0 flex-1" rows="1" placeholder="Type your message here"></textarea>
                <button class="btn btn-icon" type="submit"><i class="ik ik-arrow-right text-success"></i></button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


    });

    chatList()

    function chatList() {
        $.ajax({
            url: '{{ route('doc.comment.counts') }}',
            method: 'GET',
            beforeSend: function () {},
        }).done(function (response) {
            if (response.success === 200) {
                console.log(response)
                chatListAppend(response);

                $('.chatListCount').html(response.comments?.length > 0 ? response.comments?.length  : '');
            }

        })
    }

    $('#chatListSearch').on('keyup', function(){
        chatListSearch();
    });

    chatListSearch();

    function chatListSearch(){

        let keyword = $('#chatListSearch').val();

        if (keyword.length > 2) {

            $.post('{{ route("doc.comment.search") }}',
                {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    keyword:keyword
                },
                function(response){
                    chatListAppend(response);
                    //console.log(data);
                });
        } else {
            chatList();
        }
    }

    function chatListAppend(response){
        let comments_blog = ``;
        let user_fio = ``;
        let doc_id = ``;
        let doc_type = ``;

        if(response.comments.length <= 0){
            comments_blog += `<a href="javascript:void(0)" class="list-group-item">
                            <span>{{ __('field.not_found') }}</span>
                        </a>`;
        }

        if(response.comments.length) {
            let comments = response.comments;

            comments.forEach((value, index) => {
                let comment = value.comment;

                doc_id = value.doc_id;

                doc_type = comment.doc_type;

                if (Object.keys(comment).indexOf('created_user') !== -1) {
                    user_fio = comment.created_user.personal.l_name+' '+comment.created_user.personal.f_name;
                }

                let comment_text = extractContent(comment.doc_text);

                comment_text = comment_text.substring(0,40)+'...';

                let doc_url = window.location.origin + '/comment/view/' + doc_id + '/' + doc_type;

                comments_blog = comments_blog + `
                        <a href="`+doc_url+`" class="list-group-item" data-chat-user="Gene Newman">
                            <figure class="user--online">
                                <img src="/images/profile_image1.png" class="rounded-circle" alt="">
                            </figure>
                        <span><span class="name">`+user_fio+`</span>  <span class="username">`+comment_text+`</span><br>
                        <div class="chat-time">`+dateFormat(comment.created_date)+`</div> </span>`
            });
        }

        $('#chatList').html(comments_blog);
    }

    function dateFormat(chat_date) {

        let  d = new Date(chat_date),
        dateFormat = [d.getDate(),d.getMonth()+1,d.getFullYear()].join('.')+' '+
                     [d.getHours(),d.getMinutes()].join(':');
        return dateFormat
    }

    function extractContent(s) {
        var span = document.createElement('span');
        span.innerHTML = s;
        return span.textContent || span.innerText;
    }
</script>
