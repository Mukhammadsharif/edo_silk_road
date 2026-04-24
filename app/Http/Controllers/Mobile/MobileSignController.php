<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentJournals;
use App\Models\TelegramMiniAppToken;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;

class MobileSignController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * GET /mobile/sign/{token}
     */
    public function show(string $token)
    {
        $record = TelegramMiniAppToken::findValidSign($token);

        if (!$record) {
            return view('mobile.sign-error', [
                'message' => 'Havola muddati tugagan yoki noto\'g\'ri. Telegramga qaytib qayta urinib ko\'ring.',
            ]);
        }

        $journal = DocumentJournals::with(['doc', 'files', 'journal'])->find($record->sign_doc_id);

        if (!$journal) {
            return view('mobile.sign-error', ['message' => 'Hujjat topilmadi.']);
        }

        Auth::setUser($record->user);

        $doc = $journal->doc;

        // Создаём Sanctum token для API запросов со страницы подписи
        // Удаляем старые mobile-sign токены и создаём новый
        $record->user->tokens()->where('name', 'mobile-sign')->delete();
        $sanctumToken = $record->user->createToken('mobile-sign')->plainTextToken;

        // HTML тело документа
        $htmlDoc = null;
        try {
            $htmlDoc = $this->documentService->getHtmlDoc($journal, $doc);
        } catch (\Throwable $e) {}

        return view('mobile.sign', [
            'token'      => $token,
            'api_token'  => $sanctumToken,
            'journal'    => $journal,
            'doc'        => $doc,
            'files'      => $journal->files,
            'doc_id'     => $journal->doc_id,
            'doc_type'   => $record->sign_doc_type,
            'html_doc'   => $htmlDoc,
            'user'       => $record->user,
        ]);
    }

    /**
     * POST /mobile/sign/{token}/complete
     */
    public function complete(string $token)
    {
        $record = TelegramMiniAppToken::where('sign_token', $token)->first();
        if ($record) {
            // Удаляем mobile-sign Sanctum токены
            $record->user?->tokens()->where('name', 'mobile-sign')->delete();

            $record->update([
                'sign_token'            => null,
                'sign_doc_id'           => null,
                'sign_doc_type'         => null,
                'sign_token_expires_at' => null,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
