<div id="key_form">
    <h4 class="text-center"> {{ __('field.enter_key') }}</h4>
    <div class="form-group row">
        <div class="col-sm-6 doc_number mt-2">
            <input type="text" name="key" class="form-control" placeholder="0000" maxlength="4" style="height: 40px" id="key">
        </div>
        <div class="col-sm-6 text-center mt-2">
            <button id="submitBtn" class="btn btn-outline-dark" style="height: 40px; width: 100%">
                <i data-lucide="circle-check-big"></i> {{ __('action.confirm_key') }}
            </button>
        </div>
    </div>
    <span id="result"></span>
</div>
