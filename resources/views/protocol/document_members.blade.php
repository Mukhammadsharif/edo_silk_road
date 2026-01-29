@if(count($documentMemberRoles))
    <div class="table-tr-3td hr-prt-docs">
        @foreach($documentMemberRoles as $key1 => $item1)
            <div class="text-left " style="margin-bottom: 10px">
                <strong>{{ $item1->docLang($item1->id, $document->doc_lang) }}</strong>
            </div>
            @if(count($documentMembers) > 0)
                <div class="verifier-section-modern" style="margin-bottom: 10px">
                    <table style="border-collapse: separate;border-spacing: 0 10px;" class="signature-table">
                        @foreach($documentMembers as $key => $item)
                            @if($item1->code == $item->role_id)
                                <tr>
                                    <td class="position-cell">
                                        {{ $item->getDepartment($item->user_id, $document->doc_lang) }}

                                        {{ $item->getPosition($item->user_id, $document->doc_lang) }}
                                    </td>
                                    <td class="qr-cell">
                                        @if($item->status != 3)
                                            <span class="badge badge-pill {{ $item->statusName->badgeClass->name??'' }}">
                                                {{ $item->statusDoc($item->status) }}
                                            </span>
                                        @else
                                            {!! $item->QrCode($item->doc_id, $item->user_id) !!}
                                        @endif
                                    </td>
                                    <td class="name-cell">
                                        @php($memberShortName = $item->userWork->personal??0)
                                        @if($memberShortName)
                                            {{ $memberShortName->shortNameLang($document->doc_lang) }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
            @endif
        @endforeach
    </div>
@endif

<link rel="stylesheet" href="{{ asset('css/templates/office/journal/work/templates/verifier.css') }}">
