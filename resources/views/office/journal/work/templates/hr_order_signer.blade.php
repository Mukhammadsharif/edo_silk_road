<div class="table-tr-3td">
    <table>
        <tr>
            <td>
                {{ $directorDocument->toUser->userPost->docLang($directorDocument->toUser->userPost->id, $document->doc_lang)??'' }}
            </td>
            <td>
                <p>
                    {!! $directorDocument->signQrCode($document->id, $directorDocument->to_user, $directorDocument->id) !!}
                </p>
            </td>
            <td>
                {{ $directorDocument->toUser->personal->shortNameLang($document->doc_lang)??''}}
            </td>
        </tr>
    </table>
</div>
