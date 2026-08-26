<div class="table-tr-3td doc-ag-users">
    <table>
        @foreach($agreement_users as $key => $item)
            <tr>
                <td>
                    {{ $item->toUser?->userPost?->docLang($item->toUser?->userPost?->id, $document->doc_lang) ?? '' }}
                </td>
                <td class="hr-prt-docs-qr">
                    {!! $item->signQrCode($document->id, $item->to_user, $item->id) !!}
                </td>
                <td>
                    {{ $item->toUser?->personal?->shortNameLang($document->doc_lang) ?? '' }}<br>
                    <span class="badge badge-pill {{ $item->statusSign->badgeClass??'' }}">
                        {{ $item->statusSign->lang($item->statusSign->id)??'' }}
                    </span>
                </td>
            </tr>
        @endforeach
    </table>
</div>
