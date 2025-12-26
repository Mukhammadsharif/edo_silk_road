@if ($message = Session::get('success'))
    <div class="alert bg-success alert-success text-white alert-dismissible mb-0 fade show" role="alert">
        <strong><i class="ik ik-check-circle"></i> {{ __('message.success_message') }}</strong> {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="ik ik-x"></i>
        </button>
    </div>
@elseif($message = Session::get('error'))
    <div class="alert bg-warning alert-warning text-white alert-dismissible mb-0 fade show" role="alert">
        <strong><i class="ik ik-alert-circle"></i> {{ __('message.error_message') }}</strong> {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="ik ik-x"></i>
        </button>
    </div>
@elseif($errors->any())
    <div class="alert bg-danger alert-danger text-white alert-dismissible mb-0 fade show" role="alert">
        <strong><i class="ik ik-alert-circle"></i> {{ __('message.error_message') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="ik ik-x"></i>
        </button>
    </div>
@endif
