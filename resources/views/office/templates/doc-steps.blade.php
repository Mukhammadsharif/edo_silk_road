@php
    use App\Models\Admin\Role;use App\Models\Admin\UserRole;use App\Models\Admin\UserWorkHistory;use App\Models\Journal\JournalTypeControls;
    $controlRoles = Role::whereIn('name', ['Ijro nazorati', 'Control_Emp'])->pluck('id')->toArray();
    $userRoles = UserRole::whereIn('role_id', $controlRoles)->pluck('model_id')->toArray();
    $controlUsers = UserWorkHistory::whereIn('user_id', $userRoles)->where('status', 1)->get();
    $isInJournalTypeControls = JournalTypeControls::where('journal_type_id', $journal->j_type)->where('status', 1)->first();
@endphp

<div xmlns="http://www.w3.org/1999/html">
    <div class="card latest-update-card">
        <div class="card-header">
            <h3>{{ __('field.document_steps') }}</h3>
            <div class="card-header-right">
                <ul class="list-unstyled card-option">
                    <li><i class="ik ik-chevron-left action-toggle"></i></li>
                    <li><i class="ik ik-minus minimize-card"></i></li>
                    <li><i class="ik ik-x close-card"></i></li>
                </ul>
            </div>
        </div>
        <div class="card-block">
            <div class="scroll-widget">
                <div class="latest-update-box">
                    <div class="row pt-30 pb-30">
                        <div class="col-auto text-right update-meta pr-0">
                            <i class="ik ik-check fw-600 bg-green update-icon"></i>
                        </div>
                        <div class="col pl-5">
                            <h6 class="font-weight-bold">{{ __('status.created') }}</h6>
                            <p class="text-muted mb-0"> {{ $journal->fromUser->personal->shortName()??'' }}
                                - {{ $journal->fromUser->userPost->lang($journal->fromUser->userPost->id)??'' }}
                            </p>
                        </div>
                    </div>

                    @if($journal)
                        <div class="row pb-30">
                            <div class="col-auto text-right update-meta pr-0">
                                @if($journal->status == 1)
                                    <i class="ik ik-send fw-600 bg-red update-icon"></i>
                                @else
                                    <i class="ik ik-send bg-green update-icon"></i>
                                @endif
                            </div>
                            <div class="col pl-5">
                                <h6 class="font-weight-bold">{{ __('status.pending_sending') }}</h6>
                                <p class="text-muted mb-0">{{ $journal->fromUser->personal->shortName()??'' }}
                                    - {{ $journal->fromUser->userPost->lang($journal->fromUser->userPost->id)??'' }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if(isset($signer) && $signer)
                        <div class="row pb-30">
                            <div class="col-auto text-right update-meta pr-0">
                                @if($journal->status > 5)
                                    <i class="ik ik-feather fw-600 bg-green update-icon"></i>
                                @else
                                    <i class="ik ik-feather bg-red update-icon"></i>
                                @endif
                            </div>
                            <div class="col pl-5">
                                <h6 class="font-weight-bold">{{ __('field.signer') }}</h6>
                                <p class="text-muted mb-0">{{ $signer->toSign->personal->shortName()??'' }}
                                    - {{ $signer->toSign->userPost->lang($signer->toSign->userPost->id)??'' }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if(isset($agreement_users) && $agreement_users->count())
                        <div class="row pb-15">
                            <div class="col-auto text-right update-meta pr-0">
                                @if($journal->status > 6)
                                    <i class="fa fa-handshake fw-600 bg-green update-icon"></i>
                                @else
                                    <i class="fa fa-handshake bg-red update-icon"></i>
                                @endif
                            </div>
                            <div class="col pl-5">
                                <h6 class="font-weight-bold">{{ __('field.agreement_person') }}</h6>
                                @foreach($agreement_users as $key => $item)
                                    <p class="text-muted mb-0">{{ $item->toUser->personal->shortName()??'' }}
                                        - {{ $item->toUser->userPost->lang($item->toUser->userPost->id)??'' }}
                                        @if(in_array($item->status, [1,2]))
                                            <span class="ml-10"><i class="ik ik-user text-yellow"></i></span>
                                        @elseif(in_array($item->status, [5,6]))
                                            <span class="ml-10"><i class="ik ik-user-check text-green"></i></span>
                                        @else
                                            <span class="ml-10"><i class="ik ik-user-x text-red"></i></span>
                                        @endif
                                    </p><br>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(isset($controlUsers) && $controlUsers && $isInJournalTypeControls)
                        <div class="row pb-15">
                            <div class="col-auto text-right update-meta pr-0">
                                @if($journal->status > 7)
                                    <i class="ik ik-paperclip fw-600 bg-green update-icon"></i>
                                @else
                                    <i class="ik ik-paperclip bg-red update-icon"></i>
                                @endif
                            </div>
                            <div class="col pl-5">
                                <h6 class="font-weight-bold">{{ __('role.office_control') }}</h6>
                                @foreach($controlUsers as $key => $item)
                                    <p class="text-muted mb-0">{{ $item->personal->shortName()??'' }}
                                        - {{ $item->userPost->lang($item->userPost->id)??'' }}
                                    </p><br>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(isset($directorDocument) && $directorDocument)
                        <div class="row pb-30">
                            <div class="col-auto text-right update-meta pr-0">
                                @if($journal->status > 8)
                                    <i class="ik ik-check fw-600 bg-green update-icon"></i>
                                @else
                                    <i class="ik ik-check bg-red update-icon"></i>
                                @endif
                            </div>
                            <div class="col pl-5">
                                <h6 class="font-weight-bold">{{ __('field.verifier') }}</h6>
                                <p class="text-muted mb-0">{{ $directorDocument->toSign->personal->shortName()??'' }}
                                    - {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if(isset($selectedPerformers) && $selectedPerformers && $selectedPerformers->count())
                        <div class="row pb-30">
                            <div class="col-auto text-right update-meta pr-0">
                                @if($journal->status > 9)
                                    <i class="ik ik-file-minus fw-600 bg-green update-icon"></i>
                                @else
                                    <i class="ik ik-file-minus bg-red update-icon"></i>
                                @endif
                            </div>
                            <div class="col pl-5">
                                <h6 class="font-weight-bold">{{ __('title.exec_document') }}</h6>
                                @foreach($selectedPerformers as $performer)
                                    <p class="text-muted mb-0">{{ $performer->toSign->personal->shortName()??'' }}
                                        - {{ $performer->toUser->userPost->lang($performer->toUser->userPost->id)??'' }}
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="row pb-30">
                        <div class="col-auto text-right update-meta pr-0">
                            @if($journal->status == 10)
                                <i class="ik ik-check fw-600 bg-green update-icon"></i>
                            @else
                                <i class="ik ik-archive bg-red update-icon"></i>
                            @endif
                        </div>
                        <div class="col pl-5">
                            <h6 class="font-weight-bold">{{ __('status.completed') }}</h6>
                            <p class="text-muted mb-0">{{ $directorDocument->toSign->personal->shortName()??'' }}
                                - {{ $directorDocument->toUser->userPost->lang($directorDocument->toUser->userPost->id)??'' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-yellow text-black">
            @if($journal->status == 1)
                <h6><i class="ik ik-feather f-18"></i> <strong>({{ $journal->fromUser->personal->shortName()??'' }}
                        )</strong> {{ __('status.pending_sending') }}...</h6>
            @elseif($journal->status == 2)
                <h6><i class="ik ik-feather f-18"></i> {{ __('status.document_declined') }}</h6>
            @elseif($journal->status == 3)
                <h6><i class="ik ik-feather f-18"></i> <strong>({{ $journal->fromUser->personal->shortName()??'' }}
                        )</strong> {{ __('status.pending_editing') }}...</h6>
            @elseif($journal->status == 4)
                <h6><i class="ik ik-feather f-18"></i> {{ __('status.document_deleted') }}</h6>
            @elseif($journal->status == 5 && isset($signer) && $signer)
                <h6><i class="ik ik-feather f-18"></i> <strong>({{ $signer->toSign->personal->shortName()??'' }}
                        )</strong> {{ __('status.pending_signing') }}...</h6>
            @elseif($journal->status == 6 && isset($agreement_users) && $agreement_users->count())
                <h6><i class="ik ik-feather f-18"></i> @foreach($agreement_users->where('status', 2) as $key => $item)
                        <strong>{{ $item->toUser->personal->shortName()??'' }}</strong> {{ __('status.pending_agreement') }}
                        ...
                    @endforeach </h6>
            @elseif($journal->status == 7 && isset($controlUsers) && $controlUsers->count())
                <h6><i class="ik ik-feather f-18"></i> @foreach($controlUsers as $key => $item)
                        <strong>{{ $item->personal->shortName()??'' }}</strong> {{ __('status.pending_control') }}...
                    @endforeach </h6>
            @elseif($journal->status == 8 && isset($directorDocument) && $directorDocument)
                <h6><i class="ik ik-feather f-18"></i>
                    <strong>({{ $directorDocument->toSign->personal->shortName()??'' }}
                        )</strong> {{ __('status.pending_resolution') }}...</h6>
            @elseif($journal->status == 9)
                <h6><i class="ik ik-feather f-18"></i> {{ __('status.pending_execution') }}...</h6>
            @elseif($journal->status == 10)
                <h6><i class="ik ik-feather f-18"></i> {{ __('status.completed') }}</h6>
            @elseif($journal->status == 11)
                <h6><i class="ik ik-feather f-18"></i> {{ __('status.executed') }}</h6>
            @endif
        </div>
    </div>
</div>
