<div class="col-sm-12">
    @if($signatory_users->count())
        <hr>
        <label class="text-primary fw-700"><i class="ik ik-arrow-down"></i>
            <span class="">{{ __('execution.signatory') }}</span>
        </label>
        <div class="input-group mb-2 mt-2">
            <input type="text" class="form-control" id="allUserToSearch" onkeyup="searchToAllUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
            <div class="form-radio" style="overflow-y: scroll; max-height: 400px">
                @foreach($signatory_users as $key => $signatory_user)
                    <div class="select-to-all-user">
                        <div class="radio radio-inline radio-primary">
                            <label>
                                <input type="radio" name="to_emp" value="{{ $signatory_user->id }}"
                                    {{ $signer && $signer->to_user === $signatory_user->id ? 'checked' : ''}}>
                                <i class="helper"></i>
                                <b>{{ $signatory_user->personal->fullName()??'-' }}</b> -
                                {{ $signatory_user->userPost->lang($signatory_user->userPost->id)??'' }}
                            </label>
                        </div><br>
                    </div>
                @endforeach
            </div>
    @endif

    @if($agreement_users->count())
        <div class="form-group row">
            <div class="col-sm-12"><hr>
                <label class="text-success fw-700"><i class="ik ik-chevrons-down"></i> {{ __('execution.agreement') }}</label>
                <div class="input-group mb-2 mt-2">
                    <input type="text" class="form-control" id="userSearch" onkeyup="searchUserFunction()" placeholder="{{ __('action.search') }}...">
                </div>
                <div class="form-radio" style="overflow-y: scroll; max-height: 400px">
                    @foreach($agreement_users as $key => $agreement_user)
                        <div class="select-user">
                            <div class="checkbox-fade fade-in-success">
                                <label>
                                    <input type="checkbox" name="to_agree[]" value="{{ $agreement_user->id }}"
                                        {{ $agreements && $agreements->count() &&  in_array($agreement_user->id, $agreements->pluck('to_user')->toArray()) ? 'checked' : ''}}>
                                    <span class="cr">
                                    <i class="cr-icon ik ik-check txt-success"></i>
                                </span>
                                    <span><b>{{ $agreement_user->personal->fullName()??'-' }}</b> -
                                    {{ $agreement_user->userPost->lang($agreement_user->userPost->id)??'' }}
                                </span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

        <hr>
        <label class="text-primary fw-700"><i class="ik ik-arrow-down"></i>
            <span class="">{{ __('execution.resolution') }}</span>
        </label>
        <div class="input-group mb-2 mt-2">
            <input type="text" class="form-control" id="userToSearch" onkeyup="searchToUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
        <div class="form-radio" style="overflow-y: scroll; max-height: 400px">
            @foreach($resolution_users as $key => $resolution_user)
                <div class="select-to-user">
                    <div class="radio radio-inline radio-primary">
                        <label>
                            <input type="radio" name="resolution_user" value="{{ $resolution_user->id }}"
                                {{ $verifier && $verifier->to_user === $resolution_user->id ? 'checked' : ''}}
                            >
                            <i class="helper"></i>
                            <b>{{ $resolution_user->personal->fullName()??'-' }}</b> -
                            {{ $resolution_user->userPost->lang($resolution_user->userPost->id)??'' }}
                        </label>
                    </div><br>
                </div>
            @endforeach
        </div>
</div>

<script>
    function searchToUserFunction() {
        let input = document.getElementById("userToSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-to-user');
        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function searchUserFunction() {
        let input = document.getElementById("userSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-user');

        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function searchCorUserFunction() {
        let input = document.getElementById("corUserSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-cor-user');

        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }

    function searchToAllUserFunction() {
        let input = document.getElementById("allUserToSearch");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('select-to-all-user');

        for (i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }
</script>
