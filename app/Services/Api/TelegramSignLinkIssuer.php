<?php

namespace App\Services\Api;

use App\Models\Document\DocumentJournals;
use App\Models\TelegramMiniAppToken;
use App\Models\User;

/**
 * Ссылка на подпись для Telegram Mini App — прежний механизм.
 *
 * Нужен, если контроллеры мини-аппа когда-нибудь переведут на общие сервисы.
 * Сейчас они работают по-старому и этот класс не задействован, но он
 * фиксирует, как именно устроена вторая реализация интерфейса.
 */
class TelegramSignLinkIssuer implements SignLinkIssuer
{
    public function issue(User $user, DocumentJournals $journal, string $signType): array
    {
        // Берём действующий auth-токен пользователя: sign-токен пишется
        // в ту же строку, так устроена модель мини-аппа
        $record = TelegramMiniAppToken::where('user_id', $user->id)
            ->where('auth_token_expires_at', '>', now())
            ->latest('id')
            ->firstOrFail();

        $signToken = $record->createSignToken((int) $journal->doc_id, $signType);

        return [
            'url'        => url("/mobile/sign/{$signToken}"),
            'expires_in' => 900,
        ];
    }
}
