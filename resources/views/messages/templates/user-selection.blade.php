<div class="col-md-4">
    <label>{{ __('action.select_users') }} <span class="text-red">*</span></label>
    <div class="input-group mb-2">
        <input type="text" class="form-control" id="searchResPerformers" placeholder="{{ __('action.search') }}...">
    </div>
    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-hover mb-0 without-header">
            <tbody id="unSelectedUsers"></tbody>
        </table>
    </div>
    <div id="selectedUsersContainer">
        <input type="hidden" name="selected_users[]" id="selectedUsersInput" value="">
    </div>
</div>
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script>
    $(document).ready(function () {
        let allUsers = [];
        let selectedUserIds = [];

        $.ajax({
            url: '{{ route("users.work-history.active") }}',
            method: 'GET',
            success: function (response) {
                allUsers = response.users || [];
                renderUsers(allUsers);
            },
            error: function (xhr) {
                console.error('Error fetching users:', xhr);
                $('#unSelectedUsers').html('<tr><td>Error loading users.</td></tr>');
            }
        });

        function renderUsers(users) {
            const $tbody = $('#unSelectedUsers').empty();
            users.forEach(user => {
                if (!user.id || isNaN(parseInt(user.id))) {
                    console.warn(`Invalid user ID: ${user.id}, skipping user`);
                    return;
                }
                const isChecked = selectedUserIds.includes(parseInt(user.id)) ? 'checked' : '';
                $tbody.append(`
                <tr data-user-id="${user.id}">
                    <td>
                        <label class="custom-control custom-checkbox" style="cursor: pointer">
                            <input type="checkbox" class="custom-control-input select_all_child" value="${user.id}" ${isChecked}>
                            <span class="custom-control-label">
                                <img src="${user.image || ''}" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;" onerror="this.src='{{ asset('images/default.png') }}';">
                                ${user.personal?.l_name || ''} ${user.personal?.f_name || ''}
                                <span style="font-style: italic"> - (${user.position || ''})</span>
                            </span>
                        </label>
                    </td>
                </tr>
            `);
            });
            updateSelectedUsersInput();
        }

        function updateSelectedUsersInput() {
            const $container = $('#selectedUsersContainer');
            $container.empty(); // Clear existing inputs
            if (selectedUserIds.length === 0) {
                // Add an empty input to ensure the field is submitted
                $container.append('<input type="hidden" name="selected_users[]" value="">');
            } else {
                selectedUserIds.forEach(id => {
                    $container.append(`<input type="hidden" name="selected_users[]" value="${id}">`);
                });
            }
            console.log('Selected user IDs:', selectedUserIds); // Debug log
        }

        $('#searchResPerformers').on('input', function () {
            const query = $(this).val().toLowerCase();
            const filteredUsers = allUsers.filter(user => {
                const fullName = `${user.personal?.f_name || ''} ${user.personal?.m_name || ''} ${user.personal?.l_name || ''}`.toLowerCase();
                return fullName.includes(query);
            });
            renderUsers(filteredUsers);
        });

        $(document).on('change', '.select_all_child', function () {
            const userId = parseInt($(this).val());
            if (isNaN(userId) || userId <= 0) {
                console.warn(`Invalid user ID: ${$(this).val()}, ignoring`);
                $(this).prop('checked', false);
                return;
            }
            if ($(this).is(':checked')) {
                if (!selectedUserIds.includes(userId)) {
                    selectedUserIds.push(userId);
                }
            } else {
                selectedUserIds = selectedUserIds.filter(id => id !== userId);
            }
            updateSelectedUsersInput();
        });
    });
</script>
