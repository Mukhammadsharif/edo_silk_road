<div class="form-section-modern">
    @if($signatory_users->count())
        <div class="section-divider"></div>
        <div class="section-title-modern">
            <i data-lucide="pen-tool"></i>
            <span>{{ __('execution.signatory') }}</span>
        </div>
        <div class="search-box-modern">
            <i data-lucide="search"></i>
            <input type="text" class="form-control-modern" id="allUserToSearch" onkeyup="searchToAllUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
        <div class="users-list-modern">
            @foreach($signatory_users as $key => $signatory_user)
                <div class="select-to-all-user">
                    <label class="user-radio-card">
                        <input type="radio" name="to_emp" value="{{ $signatory_user->id }}"
                            {{ $signer && $signer->to_user === $signatory_user->id ? 'checked' : ''}}>
                        <div class="user-card-content">
                            <div class="user-avatar">
                                <i data-lucide="user"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ $signatory_user->personal->fullName()??'-' }}</span>
                                <span class="user-position">{{ $signatory_user->userPost->lang($signatory_user->userPost->id)??'' }}</span>
                            </div>
                            <div class="radio-indicator"></div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    @endif

    @if($agreement_users->count())
        <div class="section-divider"></div>
        <div class="section-title-modern text-success">
            <i data-lucide="check-circle"></i>
            <span>{{ __('execution.agreement') }}</span>
        </div>
        <div class="search-box-modern">
            <i data-lucide="search"></i>
            <input type="text" class="form-control-modern" id="userSearch" onkeyup="searchUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
        <div class="users-list-modern">
            @foreach($agreement_users as $key => $agreement_user)
                <div class="select-user">
                    <label class="user-checkbox-card">
                        <input type="checkbox" name="to_agree[]" value="{{ $agreement_user->id }}"
                            {{ $agreements && $agreements->count() &&  in_array($agreement_user->id, $agreements->pluck('to_user')->toArray()) ? 'checked' : ''}}>
                        <div class="user-card-content">
                            <div class="user-avatar bg-success">
                                <i data-lucide="user-check"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ $agreement_user->personal->fullName()??'-' }}</span>
                                <span class="user-position">{{ $agreement_user->userPost->lang($agreement_user->userPost->id)??'' }}</span>
                            </div>
                            <div class="checkbox-indicator">
                                <i data-lucide="check"></i>
                            </div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    @endif

        <div class="section-divider"></div>
        <div class="section-title-modern">
            <i data-lucide="file-check"></i>
            <span>{{ __('execution.resolution') }}</span>
        </div>
        <div class="search-box-modern">
            <i data-lucide="search"></i>
            <input type="text" class="form-control-modern" id="userToSearch" onkeyup="searchToUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
        <div class="users-list-modern">
            @foreach($resolution_users as $key => $resolution_user)
                <div class="select-to-user">
                    <label class="user-radio-card">
                        <input type="radio" name="resolution_user" value="{{ $resolution_user->id }}"
                            {{ $verifier && $verifier->to_user === $resolution_user->id ? 'checked' : ''}}>
                        <div class="user-card-content">
                            <div class="user-avatar">
                                <i data-lucide="user"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name">{{ $resolution_user->personal->fullName()??'-' }}</span>
                                <span class="user-position">{{ $resolution_user->userPost->lang($resolution_user->userPost->id)??'' }}</span>
                            </div>
                            <div class="radio-indicator"></div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/templates/edit-execution-steps.css') }}">

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
