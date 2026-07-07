<div class="form-section-modern">
    @if($signatory_users->count())
        <div class="section-divider"></div>
        <div class="section-title-modern">
            <i data-lucide="pen-tool"></i>
            <span>{{ __('execution.signatory') }}</span>
        </div>
        <div class="search-box-modern">
            <i data-lucide="search"></i>
            <input type="text" class="form-control-modern border-none" id="allUserToSearch" onkeyup="searchToAllUserFunction()" placeholder="{{ __('action.search') }}...">
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

    @php $approval_groups = $approval_groups ?? []; @endphp
    @if($agreement_users->count() || count($approval_groups))
        <div class="section-divider"></div>
        <div class="section-title-modern text-success">
            <i data-lucide="check-circle"></i>
            <span>{{ __('execution.agreement') }}</span>
        </div>

        {{-- Группы согласующих: выбор группы отмечает всех её участников --}}
        @if(count($approval_groups))
            @php $loc = session()->get('locale', app()->getLocale()); @endphp
            <div class="approval-groups-box">
                <div class="approval-groups-label">
                    <i data-lucide="users-round"></i>
                    <span>{{ $loc === 'ru' ? 'Группы согласующих' : ($loc === 'en' ? 'Approval groups' : 'Kelishuvchilar guruhlari') }}</span>
                </div>
                <div class="approval-groups-chips">
                    @foreach($approval_groups as $g)
                        <label class="approval-group-chip">
                            <input type="checkbox" class="approval-group-cb"
                                   value="{{ $g['id'] }}"
                                   onchange="toggleApprovalGroup('{{ $g['id'] }}', this.checked)">
                            <i data-lucide="users"></i>
                            <span>{{ $g['name'] }}</span>
                            <b>{{ count($g['members']) }}</b>
                        </label>
                    @endforeach
                </div>
            </div>
            <script>window.__approvalGroups = @json($approval_groups);</script>
        @endif

        <div class="search-box-modern">
            <i data-lucide="search"></i>
            <input type="text" class="form-control-modern border-none" id="userSearch" onkeyup="searchUserFunction()" placeholder="{{ __('action.search') }}...">
        </div>
        <div class="users-list-modern" id="agreementUsersList">
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
                                <span class="user-name">{{ optional($agreement_user->personal)->fullName() ?? '-' }}</span>
                                <span class="user-position">{{ optional($agreement_user->userPost)->lang(optional($agreement_user->userPost)->id) ?? '' }}</span>
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
        <input type="text" class="form-control-modern border-none" id="userToSearch" onkeyup="searchToUserFunction()" placeholder="{{ __('action.search') }}...">
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

<script>window.__agAssigned = @json(isset($agreements) ? $agreements->pluck('to_user')->values() : []);</script>

<script>
    /**
     * Выбор группы согласующих: отмечает всех участников группы в списке to_agree[].
     * Если участника нет в текущем списке — добавляет карточку с чекбоксом.
     * Снятие группы снимает её участников, если они не входят в другую выбранную группу.
     */
    (function () {
        const selectedGroups = new Set();
        const initialChecked = new Set();

        // Запоминаем изначально отмеченных согласующих (режим редактирования),
        // чтобы снятие группы не убирало уже назначенных вручную людей.
        document.querySelectorAll('#agreementUsersList input[name="to_agree[]"]:checked')
            .forEach(function (cb) { initialChecked.add(String(cb.value)); });

        // Принудительно отмечаем всех УЖЕ назначенных согласующих (в т.ч. добавленных
        // ранее через группу), чтобы они гарантированно попали в to_agree[] и не
        // потерялись при сохранении (update() пересоздаёт согласующих только из to_agree[]).
        (window.__agAssigned || []).forEach(function (v) {
            const cb = document.querySelector('#agreementUsersList input[name="to_agree[]"][value="' + v + '"]');
            if (cb) { cb.checked = true; initialChecked.add(String(v)); }
        });

        function groupsData() { return window.__approvalGroups || []; }

        function findGroup(id) {
            return groupsData().find(g => String(g.id) === String(id));
        }

        function memberInOtherSelected(memberId, exceptGroupId) {
            return groupsData().some(g =>
                String(g.id) !== String(exceptGroupId) &&
                selectedGroups.has(String(g.id)) &&
                (g.members || []).some(m => String(m.id) === String(memberId))
            );
        }

        function ensureCheckbox(m) {
            const list = document.getElementById('agreementUsersList');
            if (!list) return null;
            let cb = list.querySelector('input[name="to_agree[]"][value="' + m.id + '"]');
            if (cb) return cb;

            const wrap = document.createElement('div');
            wrap.className = 'select-user approval-added';
            wrap.innerHTML =
                '<label class="user-checkbox-card">' +
                '<input type="checkbox" name="to_agree[]" value="' + m.id + '">' +
                '<div class="user-card-content">' +
                '<div class="user-avatar bg-success"><i data-lucide="user-check"></i></div>' +
                '<div class="user-info">' +
                '<span class="user-name">' + (m.name || '-') + '</span>' +
                '<span class="user-position">' + (m.position || '') + '</span>' +
                '</div>' +
                '<div class="checkbox-indicator"><i data-lucide="check"></i></div>' +
                '</div></label>';
            list.appendChild(wrap);
            if (window.lucide) lucide.createIcons();
            return wrap.querySelector('input');
        }

        window.toggleApprovalGroup = function (groupId, checked) {
            const g = findGroup(groupId);
            if (!g) return;
            if (checked) selectedGroups.add(String(groupId));
            else selectedGroups.delete(String(groupId));

            (g.members || []).forEach(function (m) {
                const cb = ensureCheckbox(m);
                if (!cb) return;
                if (checked) {
                    cb.checked = true;
                } else if (!memberInOtherSelected(m.id, groupId) && !initialChecked.has(String(m.id))) {
                    cb.checked = false;
                }
            });
        };
    })();
</script>

<style>
    .approval-groups-box { margin: 8px 0 14px; padding: 12px 14px; background: #f7f4ff; border: 1px solid #e6ddff; border-radius: 12px; }
    .approval-groups-label { display: flex; align-items: center; gap: 7px; font-weight: 700; color: #5b34d6; font-size: .9rem; margin-bottom: 10px; }
    .approval-groups-label i { width: 17px; height: 17px; }
    .approval-groups-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .approval-group-chip { display: inline-flex; align-items: center; gap: 7px; margin: 0; padding: 7px 12px; background: #fff; border: 1.5px solid #d9ccff; border-radius: 999px; cursor: pointer; font-size: .86rem; color: #3a2f66; transition: all .15s; user-select: none; }
    .approval-group-chip i { width: 15px; height: 15px; color: #794DFD; }
    .approval-group-chip b { background: #efeaff; color: #6b40e6; border-radius: 999px; padding: 1px 8px; font-size: .75rem; }
    .approval-group-chip input { display: none; }
    .approval-group-chip:hover { border-color: #794DFD; }
    .approval-group-chip:has(input:checked) { background: #794DFD; border-color: #794DFD; color: #fff; }
    .approval-group-chip:has(input:checked) i { color: #fff; }
    .approval-group-chip:has(input:checked) b { background: rgba(255,255,255,.25); color: #fff; }
</style>
