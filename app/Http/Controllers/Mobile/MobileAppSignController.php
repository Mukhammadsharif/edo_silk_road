<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentJournals;
use App\Models\MobileSignToken;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;

/**
 * Страница подписи E-IMZO для мобильного приложения.
 *
 * Своя, а не общая с мини-аппом: токены лежат в отдельной таблице
 * mobile_sign_tokens, и вход в один клиент не должен ломать ссылку,
 * выданную другому.
 *
 * Разметку переиспользуем — вид у страницы тот же, менять его незачем.
 */
class MobileAppSignController extends Controller
{
    public function __construct(private DocumentService $documentService) {}

    /**
     * GET /mobile/app/sign/{token}
     */
    public function show(string $token)
    {
        $record = MobileSignToken::findValid($token);

        if (!$record) {
            return view('mobile.sign-error', [
                'message' => 'Havola muddati tugagan yoki allaqachon ishlatilgan. Ilovaga qaytib qayta urinib ko\'ring.',
            ]);
        }

        $journal = DocumentJournals::with(['doc', 'files', 'journal'])->find($record->j_id);

        if (!$journal) {
            return view('mobile.sign-error', ['message' => 'Hujjat topilmadi.']);
        }

        Auth::setUser($record->user);

        /*
         | Токен для запросов со страницы подписи.
         |
         | Имя привязано к записи (mobile-app-sign:{id}), а не общее:
         | при общем имени вторая открытая вкладка гасила бы токен первой.
         | Живёт столько же, сколько сама ссылка.
         */
        $tokenName = 'mobile-app-sign:' . $record->id;
        $record->user->tokens()->where('name', $tokenName)->delete();

        $apiToken = $record->user->createToken($tokenName, ['*'], $record->expires_at);

        // Тело документа: если шаблон не собрался, страница всё равно
        // должна открыться — подписывают вложение, а не предпросмотр
        $htmlDoc = null;

        try {
            $htmlDoc = $this->documentService->getHtmlDoc($journal, $journal->doc);
        } catch (\Throwable $e) {
            report($e);
        }

        return view('mobile.sign', [
            'token'      => $token,
            'api_token'  => $apiToken->plainTextToken,
            'journal'    => $journal,
            'doc'        => $journal->doc,
            'files'      => $journal->files,
            'doc_id'     => $record->doc_id,
            'doc_type'   => $record->sign_type,
            'html_doc'   => $htmlDoc,
            'user'       => $record->user,

            // Куда страница сообщает об успешном завершении
            'complete_url' => route('mobile.app.sign.complete', $token),
        ]);
    }

    /**
     * POST /mobile/app/sign/{token}/complete
     *
     * Гасит одноразовый токен и служебный Sanctum-токен страницы.
     */
    public function complete(string $token)
    {
        $record = MobileSignToken::where('token', $token)->first();

        if ($record) {
            $record->user?->tokens()
                ->where('name', 'mobile-app-sign:' . $record->id)
                ->delete();

            $record->markUsed();
        }

        return response()->json(['status' => 'ok']);
    }
}
