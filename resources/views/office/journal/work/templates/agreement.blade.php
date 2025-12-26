@if(count($agreement_users))
    <div class="table-tr-3td doc-ag-users">
        <p class="text-start text-muted size12 mb-0 ms-0" style="text-align: left !important;">{{ __('title.agreement_employees') }}:</p>
        <table>
            @foreach($agreement_users as $key => $item)
                <tr>
                    <td>
                        {{ $item->toUser->userPost->docLang($item->toUser->userPost->id, $document->doc_lang)??'' }}
                    </td>
                    <td class="hr-prt-docs-qr">
                        {!! $item->signQrCode($document->id, $item->to_user, $item->id) !!}
                    </td>
                    <td>
                        {{ $item->toUser->personal->shortNameLang($document->doc_lang)??'' }}<br>
                        <span class="badge badge-pill {{ $item->statusSign->badgeClass??'' }}">
                        {{ $item->statusSign->lang($item->statusSign->id)??'' }}
                    </span>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
