<!-- Cancel/Return Modal -->
<div class="modal fade" id="openCancelReturnModal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-yellow">
                <h5 class="modal_title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="confirmForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="card-block">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{ __('action.leave_comment') }} <span class="text-red">*</span></label>
                                    <input type="text" name="confirm_id" id="confirm_id" hidden>
                                    <textarea name="r_text" id="div_editor2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-warning float-right modal_btn"></button>
                </div>
            </form>

        </div>
    </div>
</div>



<!-- Loading Modal -->
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

<link rel="stylesheet" href="{{ asset('/richeditor//richtexteditor/rte_theme_default.css') }}" />
<script type="text/javascript" src="{{ asset('/richeditor//richtexteditor/rte.js') }}"></script>
<script type="text/javascript" src='{{ asset('/richeditor//richtexteditor/plugins/all_plugins.js') }}'></script>

<script>
    let editor1cfg = {}
    editor1cfg.toolbar = "mytoolbar";
    editor1cfg.toolbar_mytoolbar = "{bold,italic}|{justifyleft,justifycenter,justifyright}|{fontname,fontsize}|{forecolor,backcolor}";
    let editor1 = new RichTextEditor("#div_editor1", editor1cfg);

    let editor2cfg = {}
    editor2cfg.toolbar = "mytoolbar";
    editor2cfg.toolbar_mytoolbar = "{bold,italic,justifyleft,justifycenter,justifyright,fontsize,forecolor,backcolor}";
    let editor2 = new RichTextEditor("#div_editor2", editor2cfg);
</script>
<style>
    .keyboard-selected {
        background: #f8f9fa !important;
    }
</style>
<script src="{{ asset('/ajax/jquery.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#searchResPerformers').on('keyup', function(e){
            if (e.which !== 38 && e.which !== 40 && e.which !== 13) {
                let search_key = $(this).val();
                if(search_key.length>= 3){
                    ajax_search(search_key);
                } else {
                    $('#unSelectedUsers').empty();
                }
            }
        });

        function ajax_search(search_key = ''){

            let doc_id = $('#doc_id').val();
            $.post('{{ route("res-performer.search") }}',
                {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    search_key: search_key,
                    doc_id: doc_id
                },
                function(response){
                    console.log(response);
                    table_post_row(response);
                });
        }

        function table_post_row(res){
            let htmlView = '';
            if(res.users.length <= 0){
                htmlView+= `
               <tr class="feed-item unselect-user listItem">
                  <td>
                    <div class="d-inline-block align-middle">
                        <div class="d-inline-block">{{ __('field.user_not_found') }}</div>
                    </div>
                  </td>
               </tr>`;
            }
            for(let i = 0; i < res.users.length; i++){
                let user = res.users[i];
                let personal = user.personal;
                let user_post = user.user_post;
                htmlView += `
                <tr class="feed-item select-user listItem`+user.id+`">
                   <td>
                   <button value="`+user.id+`" type="button" class="close removeItem">
                        <span class="text-danger" aria-hidden="true">×</span>
                   </button>
                    <div class="d-inline-block align-middle">
                    <div class="d-inline-block">
                    <h6 class="mb-0">`+personal.l_name+' '+personal.f_name+' '+personal.m_name+`</h6>
                    <p class="mb-0">`+user_post.name_uz+`</p>
                    </div><br><br>
                    <small class="float-left removeItem">
                    <select class="form-control" name="user_role[]">`;
                    for (let i = 0; i < res.user_roles.length; i++){
                        if (i === 1) {
                            htmlView += `<option value=`+res.user_roles[i].id+` selected>`+res.user_roles[i].title+`</option>`;
                        } else {
                            htmlView += `<option value=`+res.user_roles[i].id+`>`+res.user_roles[i].title+`</option>`;
                        }
                    }
                htmlView+= `</select>
                    </small>
                    </div>
                    <input value=`+user.id+` name="user_id[]" hidden/>
                    <input value=`+user.dep_id+` name="dep_id[]" hidden/>
                    </td>
                </tr>`;
            }
            $('#unSelectedUsers').html(htmlView);
        }

        let performer_tr = document.getElementById('unSelectedUsers');
        let liSelected;
        let index = -1;

        document.addEventListener('keydown', function(event) {
            var len = performer_tr.getElementsByTagName('tr').length - 1;
            if (event.which === 40) {
                index++;
                //down
                if (liSelected) {
                    removeClass(liSelected, 'keyboard-selected');
                    next = performer_tr.getElementsByTagName('tr')[index];
                    if (typeof next !== undefined && index <= len) {

                        liSelected = next;
                    } else {
                        index = 0;
                        liSelected = performer_tr.getElementsByTagName('tr')[0];
                    }
                    addClass(liSelected, 'keyboard-selected');
                } else {
                    index = 0;
                    liSelected = performer_tr.getElementsByTagName('tr')[0];
                    addClass(liSelected, 'keyboard-selected');
                }
            } else if (event.which === 38) {
                //up
                if (liSelected) {
                    removeClass(liSelected, 'keyboard-selected');
                    index--;
                    //console.log(index);
                    next = performer_tr.getElementsByTagName('tr')[index];
                    if (typeof next !== undefined && index >= 0) {
                        liSelected = next;
                    } else {
                        index = len;
                        liSelected = performer_tr.getElementsByTagName('tr')[len];
                    }
                    addClass(liSelected, 'keyboard-selected');
                } else {
                    index = 0;
                    liSelected = performer_tr.getElementsByTagName('tr')[len];
                    addClass(liSelected, 'keyboard-selected');
                }
            } else if (event.which === 13) {
                console.log(liSelected)
                $('.selected_users').removeClass('keyboard-selected').addClass('bg-light').append(liSelected);
            }
        }, false);

        function removeClass(el, className) {
            if (el.classList) {
                el.classList.remove(className);
            } else {
                el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
            }
        }

        function addClass(el, className) {
            if (el.classList) {
                el.classList.add(className);
            } else {
                el.className += ' ' + className;
            }
        }

        $('tbody').on('click', '.select-user', function () {
            $(this).removeClass('select-user')
                .addClass('unselect-user')
                .addClass('bg-light')
                .appendTo('tbody#selectedUsers');

            $('#selectedUsers .performer_type').show();

            $('.unselect-user .removeItem').show();

        });

        $('#selectedUsers').on('click', '.removeItem', function () {

            let listVal = $(this).val();

            let listItem = '.listItem' + listVal;

            $(listItem).empty();
        });

        // Submit once
        $("body").on("submit", "form", function() {
            $(this).submit(function() {
                return false;
            });
            return true;
        });

        $('.openConfirmForm').on('click', function () {
            $('.confirmForm').show();
            let confirm_type = $(this).html();
            let modal_bg_class = "btn-outline-success";
            $('.modal-bg-class').removeClass().addClass(`btn-modern modal-bg-class ${modal_bg_class}`);
            $('.confirm_type').html(confirm_type);
            $('#ecp_confirm_id').val();
        });

        $('.openCancelReturnModal').on('click', function () {
            let btn_id = $(this).data('id');
            let modal_title = "{{ __('action.cancel_doc') }}";
            let modal_btn = "{{ __('action.cancel') }}";
            if (btn_id === 3) {
                modal_title = "{{ __('action.back_edit_doc') }}";
                modal_btn = "{{ __('action.send') }}";
            }
            $('.modal_title').html(modal_title);
            $('.modal_btn').html(modal_btn);
            $('#confirm_id').val(btn_id);
            $('#openCancelReturnModal').modal('show');
        });

        $('#confirmForm').on('submit', function(e){

            e.preventDefault();

            $.ajax({
                url: "{{ route('document.send.to.edit', $journal->id) }}",
                method: 'GET',
                data: $(this).serialize(),
                beforeSend: function(){
                    $('#openCancelReturnModal').modal('toggle');
                },
            }).done(function(response){
                if (response.success === 200) {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-yellow']).addClass('bg-success');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else if (response.success === 201) {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                } else {
                    $('#resModalTitle').text(response.title);
                    $('#resModalMessage').text(response.message);
                    $('#responseModal').modal('toggle');
                    $('.modal-content-class').removeClass(['bg-success']).addClass('bg-yellow');
                    setTimeout(function () {
                        window.location.href = '/director/work-docs';
                    }, 3000);
                }

            }).error(function(err){
                console.log(err)
            })

        });

    });
</script>
