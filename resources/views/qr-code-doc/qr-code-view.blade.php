@if(isset($qrKey))
    @if($qrKey && $qrKey->key_code)
        <div class="btn btn-outline-primary btn-rounded mr-5" id="key_content">
            {{ $qrKey->key_code }}
        </div>

        <button type="button" id="show_code" class="btn btn-outline-primary btn-rounded mr-5">
            <i class="ik ik-copy"></i> {{ __('action.view_code') }}
        </button>

        <button type="button" id="hide_code" class="btn btn-outline-danger btn-rounded mr-5">
            <i class="ik ik-alert-triangle"></i> {{ __('action.hide_code') }}
        </button>

        <button id="update_code" type="button" class="btn btn-outline-warning btn-rounded mr-5">
            <i class="ik ik-refresh-cw"></i> {{ __('action.update_code') }}
        </button>
    @endif
@else
    <button id="create_code" type="button" class="btn btn-outline-success btn-rounded mr-5">
        <i class="ik ik-refresh-cw"></i> {{ __('action.create_code') }}
    </button>
@endif

