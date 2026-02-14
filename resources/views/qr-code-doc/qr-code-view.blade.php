<div class="actions-body-modern-print">
    @if(isset($qrKey))
        @if($qrKey && $qrKey->key_code)
            <div class="btn-modern btn-primary btn-rounded" id="key_content">
                {{ $qrKey->key_code }}
            </div>

            <button type="button" id="show_code" class="btn-modern btn-primary btn-rounded">
                <i data-lucide="copy"></i> {{ __('action.view_code') }}
            </button>

            <button type="button" id="hide_code" class="btn-modern btn-danger btn-rounded">
                <i data-lucide="circle-alert"></i> {{ __('action.hide_code') }}
            </button>

            <button id="update_code" type="button" class="btn-modern btn-warning btn-rounded">
                <i data-lucide="rotate-ccw"></i> {{ __('action.update_code') }}
            </button>
        @endif
    @else
        <button id="create_code" type="button" class="btn-modern btn-success btn-rounded">
            <i data-lucide="refresh-ccw"></i> {{ __('action.create_code') }}
        </button>
    @endif
</div>

