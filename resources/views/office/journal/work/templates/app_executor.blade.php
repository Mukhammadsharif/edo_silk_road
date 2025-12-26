<div class="table-tr-3td doc-ag-users">
    <table>
        <tr>
            <td>
                {{ date('d.m.Y', strtotime($document->doc_date)) }}
            </td>
            <td class="hr-prt-docs-qr">
                {!! $document->execQrCode($document->id) !!}
            </td>
            <td>
                {{ $journal->fromUser->personal->shortNameLang($document->doc_lang)??'' }}
            </td>
        </tr>
    </table>
</div>
