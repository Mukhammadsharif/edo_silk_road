<?php

namespace App\Services\Api;

use App\Models\Document\DocumentJournals;
use App\Models\MobileSignToken;
use App\Models\User;

/**
 * Ссылка на подпись для мобильного приложения.
 *
 * Собственная таблица и собственная страница — специально, чтобы мобилка
 * не зависела от таблицы токенов Telegram. Иначе вход в одном клиенте
 * задевал бы другой: createAuthToken() в TelegramMiniAppToken удаляет
 * записи по telegram_user_id.
 */
class MobileSignLinkIssuer implements SignLinkIssuer
{
    /** Столько живёт ссылка. Подпись — операция на минуты, не на часы. */
    private const TTL_MINUTES = 15;

    public function issue(User $user, DocumentJournals $journal, string $signType): array
    {
        $record = MobileSignToken::issue(
            userId:   (int) $user->id,
            jId:      (int) $journal->id,
            docId:    (int) $journal->doc_id,
            signType: $signType,
            ttl:      self::TTL_MINUTES
        );

        return [
            'url'        => route('mobile.app.sign', $record->token),
            'expires_in' => self::TTL_MINUTES * 60,
        ];
    }
}
