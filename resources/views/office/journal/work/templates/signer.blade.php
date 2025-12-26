@if($signer)
    <div class="table-tr-3td doc-ag-users">
        <table>
            <tr>
                <td>
                    {{ $signer->toSign->userPost->docLang($signer->toSign->userPost->id, $document->doc_lang)??'' }}
                </td>
                <td class="hr-prt-docs-qr">
                    {!! $signer->signQrCode($document->id, $signer->to_user, $signer->id) !!}
                </td>
                <td>
                    {{ $signer->toSign->personal->shortNamelang($document->doc_lang)??'' }}<br>
                    <span class="badge badge-pill {{ $signer->statusSign->badgeClass??'' }}">
                    {{ $signer->statusSign->lang($signer->statusSign->id)??'' }}
                </td>
            </tr>
        </table>
    </div>
@endif
