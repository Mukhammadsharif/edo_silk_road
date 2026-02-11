@if($documentMember && $documentMember->status == (int)2)
    <div class="card">
        <div class="card-header">
            <h3><i data-lucide="circle-check-big"></i> {{ __('action.to_sign') }}</h3>
        </div>

        <div class="card-body">
            <button type="button" class="btn btn-outline-danger m-sm-1 openConfirmForm" data-id="5">
                {{ __('action.disagree') }}
            </button>

            {{--            <button type="button" class="btn btn-outline-warning m-sm-1 openConfirmForm" data-id="4">--}}
            {{--                {{ __('action.re-editing') }}--}}
            {{--            </button>--}}
            <button type="button" class="btn btn-outline-warning m-sm-1 openConfirmForm" data-id="7">
                {{ __('action.neutral') }}
            </button>

            <button type="button" class="btn btn-outline-success m-sm-1 openConfirmForm" data-id="3">
                {{ __('action.to_sign_pr') }}
            </button>
        </div>

        <div class="card-body confirmForm bg-light" style="display: none">
            <div id="loading" class="loading-gif" style="display: none"></div>
            <form id="loginSignForm" name=testform>
                <div class="form-group row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ __('field.leave_comment') }}..</label>
                            <textarea name="ecp_r_text" id="div_editor1"></textarea>
                        </div>
                    </div>
                    @if($signType->doc_sign_type === 1)
                        <div class="col-sm-12" id="ecpContainer">
                            <label><i data-lucide="key-round"></i> {{ __('action.select_ecp_key') }}
                            </label><br>
                            <label id="message"></label>
                            <select name="key" class="form-control form-control-primary select2" style="width: 100% !important;"
                                    onchange="cbChanged(this)"></select>
                        </div>
                    @endif
                    <div class="col-sm-8 mt-5">
                        <label hidden><i data-lucide="key-round"></i> {{ __('action.to_sign_by_key') }}
                        </label><br>
                        @if($signType->doc_sign_type === 2)
                            <button type="submit" class="btn btn-outline-success modal-bg-class"
                                    id="viaLogin">
                                <span class="confirm_type">{{ __('action.to_sign') }}</span>
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-success modal-bg-class"
                                    onclick="sign()" id="viaEsp">
                                <span class="confirm_type">{{ __('action.to_sign') }}</span> ({{ __('field.ecp') }})
                            </button>
                        @endif
                    </div>
                    <div class="col-sm-4 mt-5">
                        <img src="{{ url('loading.gif') }}" style="display: none" width="60px" class="loadingGif">
                    </div>
                </div>
                <div class="form-group">
                    <input name="ecp_doc_type" value="pr_emp_hr_order" hidden/>
                    <input name="ecp_doc_id" value="{{ $document->id }}" hidden/>
                    <input name="ecp_confirm_id" id="ecp_confirm_id" hidden>
                </div>
                <div class="form-group">
                    <label id="keyId" hidden></label>
                    <textarea name="pkcs7" hidden></textarea>
                </div>

            </form>
        </div>
    </div>
@endif

@include('rich-editor.rich-editor')

@include('e-imzo.e-imzo')

@include('templates.ecp.login-sign')

@include('modals.loader-response')

<script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.openConfirmForm').on('click', function () {
            $('.confirmForm').show();
            let btn_id = $(this).data('id');
            let confirm_type = $(this).html();
            let modal_bg_class = "btn-outline-success";
            let viaLogin = $('#viaLogin')
            let viaEsp = $('#viaEsp')
            let ecpContainer = $('#ecpContainer')
            // viaEsp.show()
            // viaLogin.hide()
            ecpContainer.show()
            if (btn_id === 5) {
                modal_bg_class = "btn-outline-danger";
                // viaEsp.hide()
                // viaLogin.show()
                // ecpContainer.hide()
            } else if (btn_id === 7) {
                modal_bg_class = "btn-outline-warning";
                // viaEsp.hide()
                // viaLogin.show()
                // ecpContainer.hide()
            }
            $('.modal-bg-class').removeClass().addClass(`btn-modern modal-bg-class ${modal_bg_class}`);
            $('.confirm_type').html(confirm_type);
            $('#ecp_confirm_id').val(btn_id);
        });
    });

    const signLogin = () => {
        $.ajax({
            url: "{{ route('prt-secretary-update', [$document->id,1]) }}",
            method: 'PUT',
            data: $('#testform').serialize(),
            beforeSend: function(){
                $('.loadModal').modal('show');
            },
        }).done(function(response){

            setTimeout(function () {
                location.reload();
            }, 1000);

        }).error(function(err){
            console.log(err)
        })
    }
</script>
