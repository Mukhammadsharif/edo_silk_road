@php
    use App\Models\Admin\Role;use App\Models\Admin\UserRole;use App\Models\Admin\UserWorkHistory;use App\Models\Journal\JournalTypeControls;
    $controlRoles = Role::whereIn('name', ['Ijro nazorati', 'Control_Emp'])->pluck('id')->toArray();
    $userRoles = UserRole::whereIn('role_id', $controlRoles)->pluck('model_id')->toArray();
    $controlUsers = UserWorkHistory::whereIn('user_id', $userRoles)->where('status', 1)->get();
    $isInJournalTypeControls = JournalTypeControls::where('journal_type_id', $journal->j_type)->where('status', 1)->first();
@endphp

<div class="doc-steps-modern">
    <div class="steps-card-modern">
        <div class="steps-header-modern">
            <div class="header-title">
                <i data-lucide="workflow" class="text-white"></i>
                <h3>{{ __('field.document_steps') }}</h3>
            </div>
            <div class="header-actions">
                <button type="button" class="btn-icon" onclick="toggleStepsCard()">
                    <i data-lucide="chevron-up" id="toggleIcon" class="text-white"></i>
                </button>
            </div>
        </div>
        <div class="steps-body-modern" id="stepsBody">
            <div class="steps-timeline">
                    <div class="step-item completed">
                        <div class="step-indicator">
                            <div class="step-icon bg-success">
                                <i data-lucide="check" class="text-white"></i>
                            </div>
                            <div class="step-line"></div>
                        </div>
                        <div class="step-content">
                            <div class="step-title">{{ __('status.created') }}</div>
                            <div class="step-user">
                                <i data-lucide="user"></i>
                                <span>{{ $journal->fromUser->personal->shortName()??'' }} - {{ $journal->fromUser->userPost->lang($journal->fromUser->userPost->id)??'' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($journal)
                        <div class="step-item {{ $journal->status == 1 ? 'pending' : 'completed' }}">
                            <div class="step-indicator">
                                <div class="step-icon {{ $journal->status == 1 ? 'bg-warning' : 'bg-success' }}">
                                    <i data-lucide="send" class="text-white"></i>
                                </div>
                                <div class="step-line"></div>
                            </div>
                            <div class="step-content">
                                <div class="step-title">{{ __('status.pending_sending') }}</div>
                                <div class="step-user">
                                    <i data-lucide="user"></i>
                                    <span>{{ $journal->fromUser->personal->shortName()??'' }} - {{ $journal->fromUser->userPost->lang($journal->fromUser->userPost->id)??'' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($signer) && $signer)
                        <div class="step-item {{ $journal->status > 5 ? 'completed' : 'pending' }}">
                            <div class="step-indicator">
                                <div class="step-icon {{ $journal->status > 5 ? 'bg-success' : 'bg-warning' }}">
                                    <i data-lucide="pen-tool" class="text-white"></i>
                                </div>
                                <div class="step-line"></div>
                            </div>
                            <div class="step-content">
                                <div class="step-title">{{ __('field.signer') }}</div>
                                <div class="step-user">
                                    <i data-lucide="user"></i>
                                    <span>{{ $signer->toSign->personal->shortName()??'' }} - {{ $signer->toSign->userPost->lang($signer->toSign->userPost->id)??'' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($agreement_users) && $agreement_users->count())
                        <div class="step-item {{ $journal->status > 6 ? 'completed' : 'pending' }}">
                            <div class="step-indicator">
                                <div class="step-icon {{ $journal->status > 6 ? 'bg-success' : 'bg-warning' }}">
                                    <i data-lucide="users" class="text-white"></i>
                                </div>
                                <div class="step-line"></div>
                            </div>
                            <div class="step-content">
                                <div class="step-title">{{ __('field.agreement_person') }}</div>
                                <div class="step-users-list">
                                    @foreach($agreement_users as $key => $item)
                                        <div class="step-user-item">
                                            <div class="step-user">
                                                <i data-lucide="user"></i>
                                                <span>{{ $item->toUser->personal->shortName()??'' }} - {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}</span>
                                            </div>
                                            <div class="step-status">
                                                @if(in_array($item->status, [1,2]))
                                                    <span class="status-badge status-pending">
                                                        <i data-lucide="clock"></i>
                                                        Kutilmoqda
                                                    </span>
                                                @elseif(in_array($item->status, [5,6]))
                                                    <span class="status-badge status-approved">
                                                        <i data-lucide="check-circle"></i>
                                                        Tasdiqlandi
                                                    </span>
                                                @else
                                                    <span class="status-badge status-rejected">
                                                        <i data-lucide="x-circle"></i>
                                                        Rad etildi
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($controlUsers) && $controlUsers && $isInJournalTypeControls)
                        <div class="step-item {{ $journal->status > 7 ? 'completed' : 'pending' }}">
                            <div class="step-indicator">
                                <div class="step-icon {{ $journal->status > 7 ? 'bg-success' : 'bg-warning' }}">
                                    <i data-lucide="shield-check" class="text-white"></i>
                                </div>
                                <div class="step-line"></div>
                            </div>
                            <div class="step-content">
                                <div class="step-title">{{ __('role.office_control') }}</div>
                                <div class="step-users-list">
                                    @foreach($controlUsers as $key => $item)
                                        <div class="step-user-item">
                                            <div class="step-user">
                                                <i data-lucide="user"></i>
                                                <span>{{ $item->personal->shortName()??'' }} - {{ $item->userPost->lang($item->userPost->id)??'' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($directorDocument) && $directorDocument)
                        <div class="step-item {{ $journal->status > 8 ? 'completed' : 'pending' }}">
                            <div class="step-indicator">
                                <div class="step-icon {{ $journal->status > 8 ? 'bg-success' : 'bg-warning' }}">
                                    <i data-lucide="file-check" class="text-white"></i>
                                </div>
                                <div class="step-line"></div>
                            </div>
                            <div class="step-content">
                                <div class="step-title">{{ __('field.verifier') }}</div>
                                <div class="step-user">
                                    <i data-lucide="user"></i>
                                    <span>{{ $directorDocument->toSign->personal->shortName()??'' }} - {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($selectedPerformers) && $selectedPerformers && $selectedPerformers->count())
                        <div class="step-item {{ $journal->status > 9 ? 'completed' : 'pending' }}">
                            <div class="step-indicator">
                                <div class="step-icon {{ $journal->status > 9 ? 'bg-success' : 'bg-warning' }}">
                                    <i data-lucide="file-text" class="text-white"></i>
                                </div>
                                <div class="step-line"></div>
                            </div>
                            <div class="step-content">
                                <div class="step-title">{{ __('title.exec_document') }}</div>
                                <div class="step-users-list">
                                    @foreach($selectedPerformers as $performer)
                                        <div class="step-user-item">
                                            <div class="step-user">
                                                <i data-lucide="user"></i>
                                                <span>{{ $performer->toSign->personal->shortName()??'' }} - {{ $performer->toUser->userPost->lang($performer->toUser->userPost->id)??'' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="step-item {{ $journal->status == 10 ? 'completed' : 'pending' }}">
                        <div class="step-indicator">
                            <div class="step-icon {{ $journal->status == 10 ? 'bg-success' : 'bg-warning' }}">
                                <i data-lucide="check-circle" class="text-white"></i>
                            </div>
                        </div>
                        <div class="step-content">
                            <div class="step-title">{{ __('status.completed') }}</div>
                            <div class="step-user">
                                <i data-lucide="user"></i>
                                <span>{{ $directorDocument->toSign->personal->shortName()??'' }} - {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}</span>
                            </div>
                        </div>
                    </div>
            </div>
        </div>

        <div class="steps-footer-modern">
            <div class="current-status">
                @if($journal->status == 1)
                    <div class="status-item status-pending">
                        <i data-lucide="clock"></i>
                        <span><strong>{{ $journal->fromUser->personal->shortName()??'' }}</strong> - {{ __('status.pending_sending') }}</span>
                    </div>
                @elseif($journal->status == 2)
                    <div class="status-item status-rejected">
                        <i data-lucide="x-circle"></i>
                        <span>{{ __('status.document_declined') }}</span>
                    </div>
                @elseif($journal->status == 3)
                    <div class="status-item status-editing">
                        <i data-lucide="edit"></i>
                        <span><strong>{{ $journal->fromUser->personal->shortName()??'' }}</strong> - {{ __('status.pending_editing') }}</span>
                    </div>
                @elseif($journal->status == 4)
                    <div class="status-item status-deleted">
                        <i data-lucide="trash-2"></i>
                        <span>{{ __('status.document_deleted') }}</span>
                    </div>
                @elseif($journal->status == 5 && isset($signer) && $signer)
                    <div class="status-item status-pending">
                        <i data-lucide="pen-tool"></i>
                        <span><strong>{{ $signer->toSign->personal->shortName()??'' }}</strong> - {{ __('status.pending_signing') }}</span>
                    </div>
                @elseif($journal->status == 6 && isset($agreement_users) && $agreement_users->count())
                    <div class="status-item status-pending">
                        <i data-lucide="users"></i>
                        <span>
                            @foreach($agreement_users->where('status', 2) as $key => $item)
                                <strong>{{ $item->toUser->personal->shortName()??'' }}</strong> - {{ __('status.pending_agreement') }}
                            @endforeach
                        </span>
                    </div>
                @elseif($journal->status == 7 && isset($controlUsers) && $controlUsers->count())
                    <div class="status-item status-pending">
                        <i data-lucide="shield-check"></i>
                        <span>
                            @foreach($controlUsers as $key => $item)
                                <strong>{{ $item->personal->shortName()??'' }}</strong> - {{ __('status.pending_control') }}
                            @endforeach
                        </span>
                    </div>
                @elseif($journal->status == 8 && isset($directorDocument) && $directorDocument)
                    <div class="status-item status-pending">
                        <i data-lucide="file-check"></i>
                        <span><strong>{{ $directorDocument->toSign->personal->shortName()??'' }}</strong> - {{ __('status.pending_resolution') }}</span>
                    </div>
                @elseif($journal->status == 9)
                    <div class="status-item status-pending">
                        <i data-lucide="file-text"></i>
                        <span>{{ __('status.pending_execution') }}</span>
                    </div>
                @elseif($journal->status == 10)
                    <div class="status-item status-completed">
                        <i data-lucide="check-circle"></i>
                        <span>{{ __('status.completed') }}</span>
                    </div>
                @elseif($journal->status == 11)
                    <div class="status-item status-completed">
                        <i data-lucide="check-circle-2"></i>
                        <span>{{ __('status.executed') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/templates/office/templates/doc-steps.css') }}">

<script>
    function toggleStepsCard() {
        const stepsBody = document.getElementById('stepsBody');
        const toggleIcon = document.getElementById('toggleIcon');

        stepsBody.classList.toggle('collapsed');

        if (stepsBody.classList.contains('collapsed')) {
            toggleIcon.setAttribute('data-lucide', 'chevron-down');
        } else {
            toggleIcon.setAttribute('data-lucide', 'chevron-up');
        }

        // Reinitialize lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Initialize lucide icons on load
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
