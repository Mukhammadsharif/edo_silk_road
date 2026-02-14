@if ($message = Session::get('success'))
    <div class="alert-modern alert-success" role="alert">
        <div class="alert-icon">
            <i data-lucide="check-circle"></i>
        </div>
        <div class="alert-content">
            <strong>{{ __('message.success_message') }}</strong>
            <p>{{ $message }}</p>
        </div>
        <button type="button" class="alert-close" data-dismiss="alert" aria-label="Close">
            <i data-lucide="x"></i>
        </button>
    </div>
@elseif($message = Session::get('error'))
    <div class="alert-modern alert-warning" role="alert">
        <div class="alert-icon">
            <i data-lucide="alert-triangle"></i>
        </div>
        <div class="alert-content">
            <strong>{{ __('message.error_message') }}</strong>
            <p>{{ $message }}</p>
        </div>
        <button type="button" class="alert-close" data-dismiss="alert" aria-label="Close">
            <i data-lucide="x"></i>
        </button>
    </div>
@elseif($errors->any())
    <div class="alert-modern alert-danger" role="alert">
        <div class="alert-icon">
            <i data-lucide="alert-circle"></i>
        </div>
        <div class="alert-content">
            <strong>{{ __('message.error_message') }}</strong>
            <ul class="alert-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="alert-close" data-dismiss="alert" aria-label="Close">
            <i data-lucide="x"></i>
        </button>
    </div>
@endif


<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        document.querySelectorAll('.alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.alert-modern').style.animation = 'slideOut 0.3s ease';
                setTimeout(() => this.closest('.alert-modern').remove(), 300);
            });
        });
    });
</script>

<link rel="stylesheet" href="{{ asset('css/templates/templates/alert.css') }}">

<style>
    @keyframes slideOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-10px); } }
</style>
