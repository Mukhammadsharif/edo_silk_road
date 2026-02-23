@if(count($documentHrEmp))
    <div class="table-tr-3td hr-prt-docs">
        <div class="text-left " style="margin-bottom: 10px">
            <strong>{{ __('field.familiar_staff') }}</strong>
        </div>
        <div class="" style="margin-bottom: 10px">
            <table>
                @foreach($documentHrEmp as $key => $item)
                    <tr>
                        <td>
                            @php($department = $item->userWork->department??0)
                            @if($department)
                                {{ $department->lang($department->id) }}
                            @endif

                            @php($position = $item->userWork->position??0)
                            @if($position)
                                {{ $position->lang($position->id) }}
                            @endif
                        </td>
                        <td>
                            @if($item->status != 3)
                                <span class="badge badge-pill {{ $item->statusName->badgeClass->name??'' }}">
                                    {{ $item->statusDoc($item->status) }}
                                </span>
                            @else
                                {!! $item->QrCode($item->doc_id, $item->user_id) !!}
                            @endif
                        </td>
                        <td>
                            @php($memberShortName = $item->userWork->personal??0)
                            @if($memberShortName)
                                {{ $memberShortName->shortNameLang($document->doc_lang) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endif
