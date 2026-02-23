<div class="prt-doc-subpage" id="doc_content">
    <div id="printPage" class="printPage">
        <div class="doc-text">
            {!! $document->docText->doc_text??'' !!}
        </div>

        @if(count($cnChairman) > 0)
            <div class="table-tr-3td hr-prt-docs">
                <p>
                    @if($cnType === 'cn_ken')
                        {{ __('sup_board_chairman') }}:
                    @elseif($cnType === 'cn_aks')
                        {{ __('chairman_committee') }}:
                    @elseif($cnType === 'cn_ris')
                        {{ __('chairman_risk_committee') }}:
                    @elseif($cnType === 'cn_vac')
                        {{ __('chairman_nomination_committee') }}:
                    @elseif($cnType === 'cn_aud')
                        {{ __('chairman_audit_committee') }}:
                    @else
                        {{ __('chairman_committee') }}:
                    @endif
                </p>
                <table>
                    @foreach($cnChairman as $key => $item)
                        <tr>
                            <td>
                                @switch(session()->get('locale'))
                                    @case('ru')
                                        {{ $item->userWork->userPost->post_name_ru??'' }}
                                        @break
                                    @case('en')
                                        {{ $item->userWork->userPost->post_name_en??'' }}
                                        @break
                                    @default
                                        {{ $item->userWork->userPost->post_name??'' }}
                                @endswitch
                            </td>
                            <td>{!! $item->qrCode($document->id, $item->user_id) !!}</td>
                            <td>
                                {{ $item->userWork->personal->shortName()??'' }}<br>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if(count($cnMembers) > 0)
            <div class="table-tr-3td hr-prt-docs">
                <p>
                    @if($cnType === 'cn_ken')
                        {{ __('sup_board_members') }}:
                    @elseif($cnType === 'cn_aks')
                        {{ __('member_committee') }}:
                    @elseif($cnType === 'cn_ris')
                        {{ __('members_risk_committee') }}:
                    @elseif($cnType === 'cn_vac')
                        {{ __('members_nomination_committee') }}:
                    @elseif($cnType === 'cn_aud')
                        {{ __('members_audit_committee') }}:
                    @else
                        {{ __('chairman_committee') }}:
                    @endif
                </p>
                <table>
                    @foreach($cnMembers as $key => $item)
                        <tr>
                            <td>
                                @switch(session()->get('locale'))
                                    @case('ru')
                                        {{ $item->userWork->userPost->post_name_ru??'' }}
                                        @break
                                    @case('en')
                                        {{ $item->userWork->userPost->post_name_en??'' }}
                                        @break
                                    @default
                                        {{ $item->userWork->userPost->post_name??'' }}
                                @endswitch
                            </td>
                            <td class="hr-prt-docs-qr">{!! $item->qrCode($document->id, $item->user_id) !!}</td>
                            <td>
                                {{ $item->userWork->personal->shortName()??'' }}<br>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if(count($cnInvited) > 0)
            <div class="table-tr-3td hr-prt-docs">
                <p>{{ __('invited') }}:</p>
                <table>
                    @foreach($cnInvited as $key => $item)
                        <tr>
                            <td>
                                @switch(session()->get('locale'))
                                    @case('ru')
                                        {{ $item->userWork->userPost->post_name_ru??'' }}
                                        @break
                                    @case('en')
                                        {{ $item->userWork->userPost->post_name_en??'' }}
                                        @break
                                    @default
                                        {{ $item->userWork->userPost->post_name??'' }}
                                @endswitch
                            </td>
                            <td>{!! $item->memQrCode($document->id, $item->user_id) !!}</td>
                            <td>
                                {{ $item->userWork->personal->shortName()??'' }}<br>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <div class="table-tr-3td hr-prt-docs">
            <p>{{ __('corporate_secretary') }}:</p>
            <table>
                <tr>
                    <td></td>
                    <td>{!! $document->qrCode($document->id, $document->created_id) !!}</td>
                    <td>
                        {{ $document->createdId->personal->shortName()??'' }}<br>
                        @if($document->status > 1)
                            <span class="badge badge-success">
                                {{ __('cn_for') }}
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                {{ __('signature_pending') }}
                            </span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="doc-performer">
            <span>{{ __('performer') }}:</span> {{ $document->createdId->personal->shortName()??'' }}
        </div>

        <div class="doc-tel">
            {{ __('tel_number') }} ({{ __('tel_in') }}
            {{ $document->createdId->personal->tel_number2??'-' }})
        </div>
    </div>
</div>
